<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PinController;

// Routes PIN (avec protection brute force)
Route::get('/pin', [PinController::class, 'showForm'])->name('pin.form');
Route::post('/pin/verify', [PinController::class, 'verify'])->name('pin.verify')->middleware('pin.throttle');
Route::post('/pin/logout', [PinController::class, 'logout'])->name('pin.logout');
Route::post('/pin/change', [PinController::class, 'change'])->name('pin.change')->middleware('pin.verified');

// Dashboard principal (protégé par PIN)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('pin.verified');


// Routes pour servir les assets des widgets (structure Laravel standard)
Route::get('/widgets/{widget}/resources/js/{file}', function ($widget, $file) {
    $path = app_path("Widgets/" . ucfirst($widget) . "/resources/js/" . $file);

    if (!file_exists($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'js') {
        abort(404);
    }

    return response(file_get_contents($path))
        ->header('Content-Type', 'application/javascript');
})->name('widgets.resources.js');

Route::get('/widgets/{widget}/resources/css/{file}', function ($widget, $file) {
    $path = app_path("Widgets/" . ucfirst($widget) . "/resources/css/" . $file);

    if (!file_exists($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'css') {
        abort(404);
    }

    return response(file_get_contents($path))
        ->header('Content-Type', 'text/css');
})->name('widgets.resources.css');

Route::get('/widgets/{widget}/public/images/{file}', function ($widget, $file) {
    $path = app_path("Widgets/" . ucfirst($widget) . "/public/images/" . $file);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    // Vérifier que c'est bien une image
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        abort(404);
    }
    
    $mimeType = match($extension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        default => 'application/octet-stream'
    };
    
    return response(file_get_contents($path))
        ->header('Content-Type', $mimeType);
})->name('widgets.public.images');
