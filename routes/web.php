<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Fitter'),
        'status' => 'online',
        'type' => 'Sync & Backup Server API (Offline-First Architecture)',
        'version' => '1.0.0'
    ]);
});
