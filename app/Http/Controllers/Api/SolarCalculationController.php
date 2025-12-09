<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolarCalculation;
use App\Services\ExternalApiService;
use App\Services\SolarCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolarCalculationController extends Controller
{
    protected $externalApiService;
    protected $solarCalculationService;

    public function __construct(
        ExternalApiService $externalApiService,
        SolarCalculationService $solarCalculationService
    ) {
        $this->externalApiService = $externalApiService;
        $this->solarCalculationService = $solarCalculationService;
    }

    /**
     * Display a listing of the resource.
     * GET /api/solar-calculations
     */
    public function index()
    {
        $calculations = SolarCalculation::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => $calculations,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/solar-calculations
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
            'land_area' => 'required|numeric|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'solar_irradiance' => 'nullable|numeric|min:0',
            'panel_efficiency' => 'nullable|numeric|min:1|max:100',
            'system_losses' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil koordinat dari alamat jika tidak disediakan
        if (!$request->latitude || !$request->longitude) {
            $coordinates = $this->externalApiService->getCoordinatesFromAddress($request->address);
            
            if (!$coordinates) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendapatkan koordinat dari alamat. Mohon periksa alamat atau masukkan koordinat secara manual.',
                ], 400);
            }

            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];
        } else {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
        }

        // Ambil data radiasi matahari dari NASA jika tidak disediakan
        $solarIrradiance = $request->solar_irradiance;
        $nasaData = null;

        if (!$solarIrradiance) {
            $nasaResponse = $this->externalApiService->getSolarIrradianceFromNASA($latitude, $longitude);
            
            if ($nasaResponse) {
                $solarIrradiance = $nasaResponse['average_irradiance'];
                $nasaData = $nasaResponse;
            } else {
                // Fallback ke estimasi
                $solarIrradiance = $this->externalApiService->getEstimatedSolarIrradiance($latitude);
                $nasaData = [
                    'average_irradiance' => $solarIrradiance,
                    'source' => 'Estimated based on latitude',
                ];
            }
        }

        // Hitung kapasitas solar
        $calculation = $this->solarCalculationService->calculateSolarCapacity(
            $request->land_area,
            $solarIrradiance,
            $request->panel_efficiency ?? 20,
            $request->system_losses ?? 14
        );

        // Simpan ke database
        $solarCalculation = SolarCalculation::create([
            'address' => $request->address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'land_area' => $request->land_area,
            'solar_irradiance' => $solarIrradiance,
            'panel_efficiency' => $request->panel_efficiency ?? 20,
            'system_losses' => $request->system_losses ?? 14,
            'max_power_capacity' => $calculation['max_power_capacity'],
            'daily_energy_production' => $calculation['daily_energy_production'],
            'monthly_energy_production' => $calculation['monthly_energy_production'],
            'yearly_energy_production' => $calculation['yearly_energy_production'],
            'nasa_data' => $nasaData,
        ]);

        // Hitung metrik finansial
        $financialMetrics = $this->solarCalculationService->calculateFinancialMetrics(
            $calculation['max_power_capacity'],
            $calculation['yearly_energy_production']
        );

        return response()->json([
            'success' => true,
            'message' => 'Kalkulasi berhasil dibuat',
            'data' => [
                'calculation' => $solarCalculation,
                'details' => $calculation,
                'financial_metrics' => $financialMetrics,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     * GET /api/solar-calculations/{id}
     */
    public function show($id)
    {
        $calculation = SolarCalculation::find($id);

        if (!$calculation) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // Hitung ulang metrik finansial
        $financialMetrics = $this->solarCalculationService->calculateFinancialMetrics(
            $calculation->max_power_capacity,
            $calculation->yearly_energy_production
        );

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diambil',
            'data' => [
                'calculation' => $calculation,
                'financial_metrics' => $financialMetrics,
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/solar-calculations/{id}
     */
    public function update(Request $request, $id)
    {
        $calculation = SolarCalculation::find($id);

        if (!$calculation) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'sometimes|required|string|max:500',
            'land_area' => 'sometimes|required|numeric|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'solar_irradiance' => 'nullable|numeric|min:0',
            'panel_efficiency' => 'nullable|numeric|min:1|max:100',
            'system_losses' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update koordinat jika alamat berubah
        $latitude = $calculation->latitude;
        $longitude = $calculation->longitude;

        if ($request->has('address') && $request->address !== $calculation->address) {
            if (!$request->latitude || !$request->longitude) {
                $coordinates = $this->externalApiService->getCoordinatesFromAddress($request->address);
                
                if ($coordinates) {
                    $latitude = $coordinates['latitude'];
                    $longitude = $coordinates['longitude'];
                }
            }
        }

        if ($request->latitude) {
            $latitude = $request->latitude;
        }
        
        if ($request->longitude) {
            $longitude = $request->longitude;
        }

        // Update solar irradiance jika koordinat berubah
        $solarIrradiance = $calculation->solar_irradiance;
        $nasaData = $calculation->nasa_data;

        if ($request->has('solar_irradiance')) {
            $solarIrradiance = $request->solar_irradiance;
        } elseif ($latitude !== $calculation->latitude || $longitude !== $calculation->longitude) {
            $nasaResponse = $this->externalApiService->getSolarIrradianceFromNASA($latitude, $longitude);
            
            if ($nasaResponse) {
                $solarIrradiance = $nasaResponse['average_irradiance'];
                $nasaData = $nasaResponse;
            }
        }

        // Recalculate
        $newCalculation = $this->solarCalculationService->calculateSolarCapacity(
            $request->land_area ?? $calculation->land_area,
            $solarIrradiance,
            $request->panel_efficiency ?? $calculation->panel_efficiency,
            $request->system_losses ?? $calculation->system_losses
        );

        // Update database
        $calculation->update([
            'address' => $request->address ?? $calculation->address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'land_area' => $request->land_area ?? $calculation->land_area,
            'solar_irradiance' => $solarIrradiance,
            'panel_efficiency' => $request->panel_efficiency ?? $calculation->panel_efficiency,
            'system_losses' => $request->system_losses ?? $calculation->system_losses,
            'max_power_capacity' => $newCalculation['max_power_capacity'],
            'daily_energy_production' => $newCalculation['daily_energy_production'],
            'monthly_energy_production' => $newCalculation['monthly_energy_production'],
            'yearly_energy_production' => $newCalculation['yearly_energy_production'],
            'nasa_data' => $nasaData,
        ]);

        // Financial metrics
        $financialMetrics = $this->solarCalculationService->calculateFinancialMetrics(
            $newCalculation['max_power_capacity'],
            $newCalculation['yearly_energy_production']
        );

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => [
                'calculation' => $calculation,
                'details' => $newCalculation,
                'financial_metrics' => $financialMetrics,
            ],
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/solar-calculations/{id}
     */
    public function destroy($id)
    {
        $calculation = SolarCalculation::find($id);

        if (!$calculation) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $calculation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}