<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ElderlyProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FallEventController;


// Public routes
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Elderly Profiles Routes
        Route::apiResource('elderly-profiles', ElderlyProfileController::class);

        Route::apiResource('fall-events', FallEventController::class);
        Route::patch('fall-events/{fallEvent}/false-alarm', [FallEventController::class, 'markFalseAlarm']);
    });
}); 