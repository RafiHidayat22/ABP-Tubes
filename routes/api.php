<?php

use App\Http\Controllers\Api\SolarCalculationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check (public)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Solar Panel API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authenticated user info & logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Power estimation routes (now protected)
    Route::prefix('powerestimation')->group(function () {
        Route::apiResource('solar-calculations', SolarCalculationController::class);
        Route::get('solar-calculations/{id}/financial', [SolarCalculationController::class, 'getFinancialMetrics']);
    });
});