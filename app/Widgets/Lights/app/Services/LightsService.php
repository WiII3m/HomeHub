<?php
namespace App\Widgets\Lights\App\Services;

use App\Services\TuyaService;

class LightsService
{
    private TuyaService $tuyaService;

    public function __construct(TuyaService $tuyaService)
    {
        $this->tuyaService = $tuyaService;
    }

    public function getLightsByRoom(): array
    {
        try {
            $rooms = $this->tuyaService->getRooms();
            $lightsByRoom = [];

            if (count($rooms) === 0) {
                return [];
            }

            foreach ($rooms as $room) {
                if (!is_array($room) || !isset($room['room_id']) || !isset($room['name'])) {
                    continue;
                }

                try {
                    $roomDevices = $this->tuyaService->getRoomDevices($room['room_id']);
                    $roomLights = [];

                    if (is_array($roomDevices)) {
                        foreach ($roomDevices as $device) {
                            if (!is_array($device)) {
                                continue;
                            }

                            if ($this->isLightDevice($device)) {
                                $roomLights[] = [
                                    'id' => $device['id'] ?? 0,
                                    'name' => $device['name'] ?? 'Lumière sans nom',
                                    'online' => $device['online'] ?? false,
                                    'switch_state' => $this->getLightSwitchState($device['status'] ?? []),
                                ];
                            }
                        }
                    }

                    if (!empty($roomLights)) {
                        $lightsByRoom[] = [
                            'room_id' => $room['room_id'],
                            'room_name' => $room['name'],
                            'lights' => $roomLights
                        ];
                    }

                } catch (\Exception $e) {
                    continue;
                }
            }

            return $lightsByRoom;

        } catch (\Exception $e) {
            return [];
        }
    }

    public function isLightDevice(array $device): bool
    {
        if (!isset($device['status']) || !is_array($device['status'])) {
            return false;
        }

        foreach ($device['status'] as $status) {
            if (isset($status['code']) && $status['code'] === 'switch_led') {
                return true;
            }
        }

        return false;
    }

    public function getLightSwitchState(array $deviceStatus): bool
    {
        foreach ($deviceStatus as $status) {
            if (isset($status['code'], $status['value']) && $status['code'] === 'switch_led') {
                return (bool) $status['value'];
            }
        }

        return false;
    }

    public function toggleLight(string $deviceId, bool $state): bool
    {
        try {
            $command = [
                'commands' => [
                    [
                        'code' => 'switch_led',
                        'value' => $state
                    ]
                ]
            ];

            $result = $this->tuyaService->makeRequest('POST', "/v1.0/devices/{$deviceId}/commands", $command);

            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
