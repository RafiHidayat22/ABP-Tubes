<?php

namespace App\Services;

class SolarCalculationService
{
    /**
     * Menghitung kapasitas maksimal panel surya
     * 
     * @param float $landArea Luas lahan dalam m²
     * @param float $solarIrradiance Radiasi matahari dalam kWh/m²/day
     * @param float $panelEfficiency Efisiensi panel dalam persen (default 20%)
     * @param float $systemLosses Losses sistem dalam persen (default 14%)
     * @return array
     */
    public function calculateSolarCapacity(
        float $landArea,
        float $solarIrradiance,
        float $panelEfficiency = 20,
        float $systemLosses = 14
    ): array {
        // Konstanta
        $peakSunHours = $solarIrradiance; // Peak sun hours per day
        $panelAreaRatio = 0.75; // 75% lahan bisa digunakan untuk panel
        $standardTestCondition = 1; // 1 kW/m² (STC)

        // Luas panel yang tersedia
        $usableArea = $landArea * $panelAreaRatio;

        // Kapasitas maksimal sistem (kW)
        // Formula: Area × Panel Efficiency × STC
        $maxPowerCapacity = $usableArea * ($panelEfficiency / 100) * $standardTestCondition;

        // Faktor performance ratio
        $performanceRatio = 1 - ($systemLosses / 100);

        // Produksi energi harian (kWh/day)
        // Formula: Max Capacity × Peak Sun Hours × Performance Ratio
        $dailyEnergyProduction = $maxPowerCapacity * $peakSunHours * $performanceRatio;

        // Produksi energi bulanan (kWh/month)
        $monthlyEnergyProduction = $dailyEnergyProduction * 30;

        // Produksi energi tahunan (kWh/year)
        $yearlyEnergyProduction = $dailyEnergyProduction * 365;

        return [
            'usable_area' => round($usableArea, 2),
            'max_power_capacity' => round($maxPowerCapacity, 2),
            'daily_energy_production' => round($dailyEnergyProduction, 2),
            'monthly_energy_production' => round($monthlyEnergyProduction, 2),
            'yearly_energy_production' => round($yearlyEnergyProduction, 2),
            'panel_efficiency' => $panelEfficiency,
            'system_losses' => $systemLosses,
            'performance_ratio' => round($performanceRatio * 100, 2),
        ];
    }

    /**
     * Menghitung estimasi biaya dan ROI
     */
    public function calculateFinancialMetrics(
        float $maxPowerCapacity,
        float $yearlyEnergyProduction,
        float $installationCostPerKW = 15000000, // Rp 15 juta per kW
        float $electricityTariff = 1444.70 // TDL PLN per kWh
    ): array {
        $totalInstallationCost = $maxPowerCapacity * $installationCostPerKW;
        $yearlySavings = $yearlyEnergyProduction * $electricityTariff;
        $paybackPeriod = $totalInstallationCost / $yearlySavings;

        return [
            'installation_cost' => round($totalInstallationCost, 2),
            'yearly_savings' => round($yearlySavings, 2),
            'payback_period_years' => round($paybackPeriod, 2),
            'roi_25_years' => round((($yearlySavings * 25) - $totalInstallationCost) / $totalInstallationCost * 100, 2),
        ];
    }
}