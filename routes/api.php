<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Fitter Sync & Backup Server
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Rutas de Autenticación
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Renovación biométrica mediante Refresh Token
    Route::middleware(['auth:sanctum', 'ability:issue-token'])->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Rutas protegidas para la sesión activa
    Route::middleware(['auth:sanctum', 'ability:access-api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Alias y rutas protegidas de sincronización para clientes offline-first
Route::middleware(['auth:sanctum', 'ability:access-api'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    // CRUD Sync routes for offline-first clients
    // Route::apiResource('routines', RoutineSyncController::class);
    // Route::apiResource('workout-logs', WorkoutLogSyncController::class);
    // Route::apiResource('body-measurements', BodyMeasurementSyncController::class);
    // Route::apiResource('daily-food-logs', DailyFoodLogSyncController::class);
    // Route::apiResource('daily-habit-logs', DailyHabitLogSyncController::class);
});
