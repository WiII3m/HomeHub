<?php
use Illuminate\Support\Facades\Route;
use App\Widgets\Lights\App\Http\Controllers\LightsController;

Route::prefix('widgets/lights')->group(function () {
    Route::post('/toggle', [LightsController::class, 'toggleLight'])->name('lights.toggle');
});
