<?php

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

// Authentication & Sync endpoints place-holders for Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // CRUD Sync routes for offline-first clients
    // Route::apiResource('routines', RoutineSyncController::class);
    // Route::apiResource('workout-logs', WorkoutLogSyncController::class);
    // Route::apiResource('body-measurements', BodyMeasurementSyncController::class);
    // Route::apiResource('daily-food-logs', DailyFoodLogSyncController::class);
    // Route::apiResource('daily-habit-logs', DailyHabitLogSyncController::class);
});
