<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // TAMBAHKAN INI
        'address',
        'latitude',
        'longitude',
        'land_area',
        'solar_irradiance',
        'panel_efficiency',
        'system_losses',
        'max_power_capacity',
        'daily_energy_production',
        'monthly_energy_production',
        'yearly_energy_production',
        'nasa_data',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'land_area' => 'decimal:2',
        'solar_irradiance' => 'decimal:2',
        'panel_efficiency' => 'decimal:2',
        'system_losses' => 'decimal:2',
        'max_power_capacity' => 'decimal:2',
        'daily_energy_production' => 'decimal:2',
        'monthly_energy_production' => 'decimal:2',
        'yearly_energy_production' => 'decimal:2',
        'nasa_data' => 'array',
        'created_at' => 'datetime', // TAMBAHKAN INI
        'updated_at' => 'datetime', // TAMBAHKAN INI
    ];

    // TAMBAHKAN RELASI INI
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}