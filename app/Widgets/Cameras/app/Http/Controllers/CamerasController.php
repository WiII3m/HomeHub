<?php
namespace App\Widgets\Cameras\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Widgets\Cameras\App\Services\CamerasService;

class CamerasController extends Controller
{
    public function __construct(
        private CamerasService $camerasService
    ) {}

    public function toggle(Request $request)
    {
        try {
            $enable = $request->input('enable', true);
            $deviceId = $request->input('device_id');
            $result = $this->camerasService->toggleBasicPrivate($enable, $deviceId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getStreamUrl(Request $request)
    {
        try {
            $deviceId = $request->input('device_id');
            $streamUrl = $this->camerasService->getCameraStreamUrl($deviceId);
            return response()->json(['success' => true, 'data' => ['url' => $streamUrl]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function controlPtz(Request $request)
    {
        try {
            $direction = $request->input('direction');
            $deviceId = $request->input('device_id');

            if (!$direction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Direction PTZ requise'
                ], 400);
            }

            $result = $this->camerasService->controlCameraPtz($direction, $deviceId);

            return response()->json([
                'success' => true,
                'data' => ['result' => $result]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
