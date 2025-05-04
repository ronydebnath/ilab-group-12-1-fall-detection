<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ElderlyAuthController;
use App\Http\Controllers\Api\ElderlyProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FallEventController;


// Public routes
Route::prefix('v1')->group(function () {
    // Admin/Carer auth routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Elderly auth routes
    Route::post('elderly/register', [ElderlyAuthController::class, 'register']);
    Route::post('elderly/login', [ElderlyAuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Admin/Carer routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Elderly routes
        Route::post('/elderly/logout', [ElderlyAuthController::class, 'logout']);
        Route::get('/elderly/user', [ElderlyAuthController::class, 'user']);

        // Elderly Profiles Routes
        Route::apiResource('elderly-profiles', ElderlyProfileController::class);

        // Fall Events Routes
        Route::apiResource('fall-events', FallEventController::class);
        Route::patch('fall-events/{fallEvent}/false-alarm', [FallEventController::class, 'markFalseAlarm']);
    });
}); 