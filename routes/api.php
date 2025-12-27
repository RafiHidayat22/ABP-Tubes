<?php

use App\Http\Controllers\Api\SolarCalculationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

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

// Midtrans webhook (public)
Route::post('/webhook/midtrans', [OrderController::class, 'webhook']);

// Product routes (public)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


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


    // Products (Admin only)
    Route::post('/products', [ProductController::class, 'store']);

    // Cart
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::post('/cart/{id}/increment', [CartController::class, 'increment']);  // BARU
Route::post('/cart/{id}/decrement', [CartController::class, 'decrement']);  // BARU
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);  // BARU

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
});