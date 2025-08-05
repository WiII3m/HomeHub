<?php
namespace App\Widgets\Cameras\App\Services;

use App\Services\TuyaService;

class CamerasService
{
    private TuyaService $tuyaService;

    public function __construct(TuyaService $tuyaService)
    {
        $this->tuyaService = $tuyaService;
    }

    public function getCameras(): array
    {
        $devices = $this->tuyaService->getAllDevices();
        $camerasState = [];

        foreach ($devices as $device) {
            if (str_contains($device['product_name'], 'Camera')) {

                $basicPrivate = false;

                if( isset($device['status']) && is_array($device['status']) ){
                    foreach ($device['status'] as $key => $value) {
                    if( $value['code'] === "basic_private" )
                        $basicPrivate = $value['value'];
                    }
                }

                $camerasState[$device['id']] = [
                    'id' => $device['id'],
                    'name' => $this->cleanCameraName($device['name']),
                    'online' => $device['online'],
                    'basic_private' => $basicPrivate
                ];
            }
        }

        return $camerasState;
    }

    private function cleanCameraName(string $name): string
    {
        $words = explode(' ', trim($name));

        if (!empty($words) && in_array(strtolower($words[0]), ['caméra', 'camera'])) {
            array_shift($words);
        }

        $cleanName = implode(' ', $words);

        return empty($cleanName) ? 'Caméra' : $cleanName;
    }

    public function toggleBasicPrivate(bool $enable, string $deviceId): bool
    {
        $commands = [
            [
                'code' => 'basic_private',
                'value' => !$enable,
            ]
        ];

        try {
            $this->tuyaService->makeRequest('POST', "/v1.0/devices/{$deviceId}/commands", [
                'commands' => $commands
            ]);
            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'Tuya API error:')) {
                throw new \Exception($errorMessage);
            } else {
                throw new \Exception("Tuya API error: " . $errorMessage);
            }
        }
    }

    public function getCameraStreamUrl(string $deviceId): string
    {
        $result = $this->tuyaService->makeRequest('POST', "/v1.0/devices/{$deviceId}/stream/actions/allocate", [
            'type' => 'hls'
        ]);

        return $result['url'];
    }

    public function controlCameraPtz(string $direction, string $deviceId): bool
    {
        $validDirections = ['UP', 'DOWN', 'LEFT', 'RIGHT', 'STOP'];
        if (!in_array($direction, $validDirections)) {
            throw new \Exception("Direction PTZ invalide: {$direction}");
        }

        try {
            $this->tuyaService->makeRequest('POST', "/v1.0/cameras/{$deviceId}/configs/ptz", [
                'value' => $direction
            ]);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
