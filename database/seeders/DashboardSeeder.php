<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SolarCalculation;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user testing
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Buat beberapa solar calculation untuk testing
        $calculations = [
            [
                'address' => 'Jakarta Pusat, DKI Jakarta, Indonesia',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'land_area' => 100,
                'solar_irradiance' => 5.0,
            ],
            [
                'address' => 'Bandung, Jawa Barat, Indonesia',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'land_area' => 150,
                'solar_irradiance' => 4.8,
            ],
            [
                'address' => 'Surabaya, Jawa Timur, Indonesia',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'land_area' => 80,
                'solar_irradiance' => 5.2,
            ],
        ];

        foreach ($calculations as $calcData) {
            // Hitung solar capacity
            $panelEfficiency = 20;
            $systemLosses = 14;
            
            $maxPowerCapacity = ($calcData['land_area'] * $calcData['solar_irradiance'] * ($panelEfficiency / 100)) / 1000;
            $dailyEnergyProduction = $maxPowerCapacity * $calcData['solar_irradiance'] * (1 - ($systemLosses / 100));
            $monthlyEnergyProduction = $dailyEnergyProduction * 30;
            $yearlyEnergyProduction = $dailyEnergyProduction * 365;

            SolarCalculation::create([
                'user_id' => $user->id,
                'address' => $calcData['address'],
                'latitude' => $calcData['latitude'],
                'longitude' => $calcData['longitude'],
                'land_area' => $calcData['land_area'],
                'solar_irradiance' => $calcData['solar_irradiance'],
                'panel_efficiency' => $panelEfficiency,
                'system_losses' => $systemLosses,
                'max_power_capacity' => $maxPowerCapacity,
                'daily_energy_production' => $dailyEnergyProduction,
                'monthly_energy_production' => $monthlyEnergyProduction,
                'yearly_energy_production' => $yearlyEnergyProduction,
                'nasa_data' => [
                    'average_irradiance' => $calcData['solar_irradiance'],
                    'source' => 'Seeder data',
                ],
            ]);
        }

        // Buat products
        $products = [
            [
                'name' => 'Panel Surya Monocrystalline 300W',
                'description' => 'Panel surya monocrystalline dengan efisiensi tinggi 20%. Cocok untuk instalasi rumah dan komersial. Tahan lama hingga 25 tahun.',
                'efficiency' => 20.00,
                'power_output' => 300.00,
                'price' => 2500000,
                'stock' => 50,
            ],
            [
                'name' => 'Panel Surya Polycrystalline 250W',
                'description' => 'Panel surya polycrystalline dengan harga ekonomis. Efisiensi 17% dengan performa stabil. Ideal untuk budget terbatas.',
                'efficiency' => 17.00,
                'power_output' => 250.00,
                'price' => 1800000,
                'stock' => 75,
            ],
            [
                'name' => 'Panel Surya Monocrystalline 400W Premium',
                'description' => 'Panel surya premium dengan teknologi PERC. Efisiensi tertinggi 22% dan output maksimal. Untuk kebutuhan industri.',
                'efficiency' => 22.00,
                'power_output' => 400.00,
                'price' => 3500000,
                'stock' => 30,
            ],
            [
                'name' => 'Panel Surya Bifacial 350W',
                'description' => 'Panel surya bifacial yang dapat menangkap cahaya dari dua sisi. Efisiensi 20% dengan produktivitas lebih tinggi.',
                'efficiency' => 20.00,
                'power_output' => 350.00,
                'price' => 3000000,
                'stock' => 40,
            ],
            [
                'name' => 'Panel Surya Portable 100W',
                'description' => 'Panel surya portable untuk keperluan camping, mobile, atau backup power. Mudah dipasang dan dipindahkan.',
                'efficiency' => 18.00,
                'power_output' => 100.00,
                'price' => 1200000,
                'stock' => 100,
            ],
            [
                'name' => 'Panel Surya Flexible 200W',
                'description' => 'Panel surya flexible yang dapat dipasang di permukaan lengkung. Cocok untuk RV, boat, atau atap tidak rata.',
                'efficiency' => 19.00,
                'power_output' => 200.00,
                'price' => 2200000,
                'stock' => 60,
            ],
        ];

        foreach ($products as $index => $productData) {
            Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'image' => 'products/panel' . ($index + 1) . '.jpg', // Placeholder path
                'efficiency' => $productData['efficiency'],
                'power_output' => $productData['power_output'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Dashboard seeder completed successfully!');
        $this->command->info('Test user credentials:');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: password');
    }
}