<?php
use Illuminate\Support\Facades\Route;
use App\Widgets\Cameras\App\Http\Controllers\CamerasController;

Route::prefix('widgets/cameras')->group(function () {
    Route::post('/toggle', [CamerasController::class, 'toggle'])
        ->name('toggleCamera');
    Route::get('/stream', [CamerasController::class, 'getStreamUrl'])
        ->name('getCameraStream');
    Route::post('/ptz', [CamerasController::class, 'controlPtz'])
        ->name('controlCameraPtz');
});
