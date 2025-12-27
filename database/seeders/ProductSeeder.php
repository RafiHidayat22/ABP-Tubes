<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Monocrystalline Solar Panel 100W',
                'description' => 'High efficiency monocrystalline solar panel with 25 years warranty',
                'image' => 'products/panel1.jpg',
                'efficiency' => 21.5,
                'power_output' => 100,
                'price' => 1500000,
                'stock' => 50
            ],
            [
                'name' => 'Polycrystalline Solar Panel 150W',
                'description' => 'Cost-effective polycrystalline panel for home use',
                'image' => 'products/panel2.jpg',
                'efficiency' => 18.0,
                'power_output' => 150,
                'price' => 1800000,
                'stock' => 30
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}