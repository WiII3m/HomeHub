<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exemption CSRF pour les API des widgets (appelées par Node.js)
        $middleware->validateCsrfTokens(except: [
            'api/widgets/*',
            'api/websocket/*',
            'pin/verify',
        ]);
        
        // Middleware PIN
        $middleware->alias([
            'pin.verified' => \App\Http\Middleware\VerifyPin::class,
            'pin.throttle' => \App\Http\Middleware\PinThrottle::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        App\Providers\WidgetServiceProvider::class,
    ])
    ->create();
