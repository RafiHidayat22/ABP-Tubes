<?php

use App\Http\Controllers\Api\SolarCalculationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('powerestimation')->group(function () {
    // Solar Calculation CRUD
    Route::apiResource('solar-calculations', SolarCalculationController::class);
    
    // Route tambahan jika diperlukan
    Route::get('solar-calculations/{id}/financial', [SolarCalculationController::class, 'getFinancialMetrics']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Solar Panel API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});