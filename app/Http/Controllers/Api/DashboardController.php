<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SolarCalculation;
use App\Services\SolarCalculationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $solarCalculationService;

    public function __construct(SolarCalculationService $solarCalculationService)
    {
        $this->solarCalculationService = $solarCalculationService;
    }

    /**
     * Get dashboard home data
     * GET /api/dashboard/home
     */
    public function home(Request $request)
    {
        // Ambil user yang sedang login
        $user = $request->user();

        // Ambil last solar calculation dari user ini
        $lastCalculation = SolarCalculation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Siapkan data last check
        $lastCheck = null;
        if ($lastCalculation) {
            // Hitung financial metrics untuk last calculation
            $financialMetrics = $this->solarCalculationService->calculateFinancialMetrics(
                $lastCalculation->max_power_capacity,
                $lastCalculation->yearly_energy_production
            );

            $lastCheck = [
                'id' => $lastCalculation->id,
                'address' => $lastCalculation->address,
                'land_area' => (float) $lastCalculation->land_area,
                'max_power_capacity' => (float) $lastCalculation->max_power_capacity,
                'daily_energy_production' => (float) $lastCalculation->daily_energy_production,
                'monthly_energy_production' => (float) $lastCalculation->monthly_energy_production,
                'yearly_energy_production' => (float) $lastCalculation->yearly_energy_production,
                'solar_irradiance' => (float) $lastCalculation->solar_irradiance,
                'panel_efficiency' => (float) $lastCalculation->panel_efficiency,
                'system_losses' => (float) $lastCalculation->system_losses,
                'checked_at' => $lastCalculation->created_at->format('Y-m-d H:i:s'),
                'checked_at_human' => $lastCalculation->created_at->diffForHumans(),
                'financial_metrics' => $financialMetrics,
            ];
        }

        // Ambil produk yang aktif (limit 6 untuk home)
        $products = Product::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'efficiency' => (float) $product->efficiency,
                    'power_output' => (float) $product->power_output,
                    'price' => (float) $product->price,
                    'price_formatted' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                    'stock' => $product->stock,
                    'in_stock' => $product->stock > 0,
                ];
            });

        // Hitung statistik tambahan
        $statistics = [
            'total_calculations' => SolarCalculation::where('user_id', $user->id)->count(),
            'total_products_available' => Product::where('is_active', true)->where('stock', '>', 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'last_check' => $lastCheck,
                'products' => $products,
                'statistics' => $statistics,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 200);
    }

    /**
     * Get dashboard statistics
     * GET /api/dashboard/statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        $totalCalculations = SolarCalculation::where('user_id', $user->id)->count();
        $totalEnergyPotential = SolarCalculation::where('user_id', $user->id)
            ->sum('yearly_energy_production');
        $avgPowerCapacity = SolarCalculation::where('user_id', $user->id)
            ->avg('max_power_capacity');
        $lastCalculationDate = SolarCalculation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        $stats = [
            'total_calculations' => $totalCalculations,
            'total_energy_potential' => (float) $totalEnergyPotential,
            'total_energy_potential_formatted' => number_format($totalEnergyPotential, 2, ',', '.') . ' kWh/tahun',
            'average_power_capacity' => $avgPowerCapacity ? (float) $avgPowerCapacity : 0,
            'average_power_capacity_formatted' => $avgPowerCapacity ? number_format($avgPowerCapacity, 2, ',', '.') . ' kW' : '0 kW',
            'last_calculation_date' => $lastCalculationDate ? $lastCalculationDate->format('Y-m-d H:i:s') : null,
            'last_calculation_date_human' => $lastCalculationDate ? $lastCalculationDate->diffForHumans() : null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Statistik berhasil diambil',
            'data' => $stats,
        ], 200);
    }

    /**
     * Get recent calculations (for dashboard history)
     * GET /api/dashboard/recent-calculations
     */
    public function recentCalculations(Request $request)
    {
        $user = $request->user();
        $limit = $request->query('limit', 5);

        $calculations = SolarCalculation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($calc) {
                return [
                    'id' => $calc->id,
                    'address' => $calc->address,
                    'land_area' => (float) $calc->land_area,
                    'max_power_capacity' => (float) $calc->max_power_capacity,
                    'yearly_energy_production' => (float) $calc->yearly_energy_production,
                    'created_at' => $calc->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $calc->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Kalkulasi terbaru berhasil diambil',
            'data' => $calculations,
        ], 200);
    }
}