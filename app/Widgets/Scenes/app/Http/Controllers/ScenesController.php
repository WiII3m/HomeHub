<?php
namespace App\Widgets\Scenes\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Widgets\Scenes\App\Services\ScenesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScenesController extends Controller
{
    private ScenesService $scenesService;

    public function __construct(ScenesService $scenesService)
    {
        $this->scenesService = $scenesService;
    }

    public function trigger(Request $request, string $sceneId): JsonResponse
    {
        $request->validate([

        ]);

        $result = $this->scenesService->triggerScene($sceneId);

        if (isset($result['success']) && $result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Scène déclenchée avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Erreur lors du déclenchement de la scène'
        ], 500);
    }
}
