<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    /**
     * Mendapatkan koordinat dari alamat menggunakan OpenCage Geocoding API
     * Alternatif gratis: Nominatim (OpenStreetMap)
     */
    public function getCoordinatesFromAddress(string $address): ?array
    {
        try {
            // Menggunakan Nominatim (gratis, tidak perlu API key)
            $response = $this->client->get('https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'SolarPanelCalculator/1.0'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            return [
                'latitude' => (float) $data[0]['lat'],
                'longitude' => (float) $data[0]['lon'],
                'display_name' => $data[0]['display_name'],
            ];
        } catch (GuzzleException $e) {
            Log::error('Geocoding API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mendapatkan data radiasi matahari dari NASA POWER API
     * NASA POWER API gratis dan tidak memerlukan API key
     */
    public function getSolarIrradianceFromNASA(float $latitude, float $longitude): ?array
    {
        try {
            // NASA POWER API untuk data radiasi matahari
            $response = $this->client->get('https://power.larc.nasa.gov/api/temporal/monthly/point', [
                'query' => [
                    'parameters' => 'ALLSKY_SFC_SW_DWN', // Solar irradiance
                    'community' => 'RE',
                    'longitude' => $longitude,
                    'latitude' => $latitude,
                    'format' => 'JSON',
                    'start' => date('Y') . '01', // Tahun ini, bulan Januari
                    'end' => date('Y') . '12', // Tahun ini, bulan Desember
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['properties']['parameter']['ALLSKY_SFC_SW_DWN'])) {
                return null;
            }

            $monthlyData = $data['properties']['parameter']['ALLSKY_SFC_SW_DWN'];
            
            // Hitung rata-rata tahunan (kWh/m²/day)
            $averageIrradiance = array_sum($monthlyData) / count($monthlyData);

            return [
                'average_irradiance' => round($averageIrradiance, 2),
                'monthly_data' => $monthlyData,
                'unit' => 'kWh/m²/day',
                'source' => 'NASA POWER API',
            ];
        } catch (GuzzleException $e) {
            Log::error('NASA API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alternatif: Menggunakan data manual berdasarkan koordinat
     * (untuk development/testing tanpa API)
     */
    public function getEstimatedSolarIrradiance(float $latitude): float
    {
        // Estimasi sederhana berdasarkan latitude
        // Indonesia sekitar 4-5.5 kWh/m²/day
        $absLatitude = abs($latitude);
        
        if ($absLatitude < 10) {
            return 5.0; // Daerah tropis
        } elseif ($absLatitude < 30) {
            return 4.5;
        } elseif ($absLatitude < 50) {
            return 4.0;
        } else {
            return 3.0;
        }
    }
}