<?php
use Illuminate\Support\Facades\Route;
use App\Widgets\Scenes\App\Http\Controllers\ScenesController;

Route::prefix('widgets/scenes')->group(function () {
    Route::post('/trigger/{sceneId}', [ScenesController::class, 'trigger'])->name('scenes.trigger');
});
