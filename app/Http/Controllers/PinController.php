<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PinController extends Controller
{
    public function showForm()
    {
        $appPin = config('app.pin');
        
        if (empty($appPin)) {
            return view('pin.not-configured');
        }

        if (Session::get('pin_verified', false)) {
            return redirect('/');
        }

        return view('pin.form');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6'
        ]);

        $appPin = config('app.pin');
        
        if (empty($appPin)) {
            return back()->withErrors(['pin' => 'PIN non configuré']);
        }

        if ($request->pin === $appPin) {
            Session::put('pin_verified', true);
            
            // Régénérer la session pour sécurité
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'redirect' => '/'
            ]);
        }

        $ip = $request->ip();
        $attemptsKey = 'pin_attempts_' . $ip;
        $currentAttempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0);
        $remainingAttempts = max(0, 2 - $currentAttempts);
        
        return response()->json([
            'success' => false,
            'error' => 'PIN incorrect',
            'attempts_remaining' => $remainingAttempts
        ], 401);
    }

    public function change(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|digits:6',
            'new_pin' => 'required|digits:6',
            'confirm_pin' => 'required|same:new_pin'
        ]);

        $appPin = config('app.pin');
        
        if ($request->current_pin !== $appPin) {
            return response()->json([
                'success' => false,
                'error' => 'PIN actuel incorrect'
            ]);
        }

        $this->updateEnvFile('APP_PIN', $request->new_pin);

        return response()->json([
            'success' => true,
            'message' => 'PIN modifié avec succès'
        ]);
    }

    public function logout()
    {
        // Invalider la session complètement 
        Session::flush();
        Session::regenerate();
        
        return redirect('/pin');
    }

    private function updateEnvFile($key, $value)
    {
        $envPath = base_path('.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
            
            file_put_contents($envPath, $envContent);
            
            if (function_exists('config_clear')) {
                config_clear();
            }
        }
    }
}