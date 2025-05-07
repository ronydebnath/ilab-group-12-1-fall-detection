<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ElderlyAuthController;
use App\Http\Controllers\Api\ElderlyProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FallEventController;
use App\Http\Controllers\ModelWeightController;


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

        // Device Token Routes
        Route::post('/device-token', [App\Http\Controllers\Api\DeviceTokenController::class, 'update']);
    });

    // Model Weight Routes
    Route::prefix('model-weights')->group(function () {
        Route::post('/', [ModelWeightController::class, 'store']);
        Route::get('/active', [ModelWeightController::class, 'getActive']);
        Route::post('/{version}/activate', [ModelWeightController::class, 'activate']);
        Route::get('/history', [ModelWeightController::class, 'history']);
        Route::delete('/{version}', [ModelWeightController::class, 'delete']);
    });
}); 