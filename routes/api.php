<?php

use Illuminate\Support\Facades\Route;
use App\Widgets\Core\WidgetManager;

// Routes pour la gestion des plugins Node.js
Route::prefix('widgets')->group(function () {
    Route::get('/node-plugins/stats', function () {
        $widgetManager = app(WidgetManager::class);
        $widgetManager->discoverWidgets();
        
        return response()->json([
            'success' => true,
            'stats' => $widgetManager->getNodePluginsStats(),
            'config_file_exists' => file_exists(storage_path('node-config.json')),
            'config_file_path' => storage_path('node-config.json'),
            'config_file_size' => file_exists(storage_path('node-config.json')) 
                ? filesize(storage_path('node-config.json')) 
                : 0
        ]);
    })->name('api.widgets.node-plugins.stats');
    
    Route::post('/node-plugins/generate', function () {
        $widgetManager = app(WidgetManager::class);
        $widgetManager->discoverWidgets();
        
        $success = $widgetManager->generateNodeConfig();
        
        return response()->json([
            'success' => $success,
            'message' => $success 
                ? 'Node.js configuration generated successfully' 
                : 'Failed to generate Node.js configuration',
            'stats' => $success ? $widgetManager->getNodePluginsStats() : null,
            'timestamp' => now()->toISOString()
        ]);
    })->name('api.widgets.node-plugins.generate');
});