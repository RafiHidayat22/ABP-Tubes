<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('land_area', 10, 2); // dalam meter persegi
            $table->decimal('solar_irradiance', 10, 2); // kWh/m²/day
            $table->decimal('panel_efficiency', 5, 2)->default(20); // persen
            $table->decimal('system_losses', 5, 2)->default(14); // persen
            $table->decimal('max_power_capacity', 10, 2); // dalam kW
            $table->decimal('daily_energy_production', 10, 2); // dalam kWh/day
            $table->decimal('monthly_energy_production', 10, 2); // dalam kWh/month
            $table->decimal('yearly_energy_production', 10, 2); // dalam kWh/year
            $table->json('nasa_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_calculations');
    }
};