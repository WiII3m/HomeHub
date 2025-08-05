<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VerifyPin
{
    public function handle(Request $request, Closure $next)
    {
        $appPin = config('app.pin');
        
        if (empty($appPin)) {
            return redirect('/pin');
        }

        if (!Session::get('pin_verified', false)) {
            return redirect('/pin');
        }

        return $next($request);
    }
}