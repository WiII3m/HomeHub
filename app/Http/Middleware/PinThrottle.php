<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class PinThrottle
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $key = 'pin_attempts_' . $ip;
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'error' => 'Trop de tentatives. Réessayez dans 5 minutes.',
                'blocked_until' => Cache::get($key . '_blocked_until'),
                'attempts_remaining' => 0
            ], 429);
        }
        
        $response = $next($request);
        
        if ($response->getStatusCode() === 401) {
            $newAttempts = $attempts + 1;
            
            if ($newAttempts >= 3) {
                $blockedUntil = now()->addMinutes(5);
                Cache::put($key, $newAttempts, 300);
                Cache::put($key . '_blocked_until', $blockedUntil->timestamp, 300);
            } else {
                Cache::put($key, $newAttempts, 300);
            }
        }
        
        if ($response->getStatusCode() === 200) {
            Cache::forget($key);
            Cache::forget($key . '_blocked_until');
        }
        
        return $response;
    }
}