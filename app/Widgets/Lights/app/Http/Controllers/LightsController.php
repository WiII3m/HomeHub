<?php
namespace App\Widgets\Lights\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Widgets\Lights\App\Services\LightsService;

class LightsController extends Controller
{
    private LightsService $lightsService;

    public function __construct(LightsService $lightsService)
    {
        $this->lightsService = $lightsService;
    }

    public function toggleLight(Request $request)
    {
        try {
            $deviceId = $request->input('device_id');
            $state = $request->boolean('state');

            if (!$deviceId) {
                return response()->json(['success' => false, 'error' => 'Device ID requis'], 400);
            }

            $result = $this->lightsService->toggleLight($deviceId, $state);

            return response()->json([
                'success' => true,
                'message' => $state ? 'Lumière allumée' : 'Lumière éteinte'
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur toggle light: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
