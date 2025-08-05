<?php
namespace App\Widgets\Scenes\App\Services;

use App\Services\TuyaService;
use Illuminate\Support\Facades\Cache;

class ScenesService
{
    private TuyaService $tuyaService;

    public function __construct(TuyaService $tuyaService)
    {
        $this->tuyaService = $tuyaService;
    }

    public function getScenes(): array
    {
        return Cache::remember('tuya_scenes', app()->environment('production') ? 1800 : 0, function () {
            try {
                $result = $this->tuyaService->makeRequest('GET', '/v2.0/cloud/scene/rule', [], [
                    'space_id' => $this->tuyaService->getHomeId(),
                    'type' => 'scene'
                ]);

                $scenes = [];

                if (isset($result['list'])) {
                    foreach ($result['list'] as $scene) {
                        if (!str_contains($scene['name'], 'LIGHT_')) {
                            $scenes[] = [
                                'id' => $scene['id'],
                                'name' => $scene['name'],
                                'status' => $scene['status'],
                                'type' => $scene['type']
                            ];
                        }
                    }
                }

                return $scenes;
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function triggerScene(string $sceneId): array
    {
        try {
            $result = $this->tuyaService->makeRequest('POST', "/v2.0/cloud/scene/rule/{$sceneId}/actions/trigger");

            return [
                'success' => true,
                'message' => 'Scène déclenchée avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

}
