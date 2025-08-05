<?php

use Illuminate\Support\Facades\Route;
use App\Widgets\Thermometers\App\Http\Controllers\ThermometersController;

// Routes web spécifiques au widget Thermometers
Route::prefix('widgets/thermometers')->group(function () {
    Route::get('/history', [ThermometersController::class, 'getTemperatureHistory'])->name('thermometers.history');
});

// Routes API pour le middleware
Route::prefix('api/widgets/thermometers')->group(function () {
    Route::post('/process', [ThermometersController::class, 'processRealtimeData'])->name('api.thermometers.process');
});
