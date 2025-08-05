<?php

namespace App\Widgets\Thermometers\App\Services;

use App\Services\TuyaService;

class ThermometersService
{
    private TuyaService $tuyaService;

    public function __construct(TuyaService $tuyaService)
    {
        $this->tuyaService = $tuyaService;
    }

    public function getDevices(): array
    {
        $rawDevices = $this->tuyaService->getAllDevices();

        $filteredDevices = [];

        foreach ($rawDevices as $device) {
            if ($device['product_name'] === 'Temperature & Humidity Sensor') {
                $filteredDevices[$device['id']] = [
                    'id' => $device['id'],
                    'name' => $this->generateSensorName($device['name']),
                    'online' => $device['online'],
                    'status' => $device['status'] ?? []
                ];
            }
        }

        return $filteredDevices;
    }

    public function getThermometers(): array
    {
        $devices = $this->getDevices();

        $thermometers = [];

        foreach ($devices as $key => $device) {
            if ($device['online'] && !empty($device['status'])) {
                $thermometers[$key] = $this->parseTemperatureData($device['status']);
                $thermometers[$key]['online'] = $device['online'];
                $thermometers[$key]['id'] = $device['id'];
                $thermometers[$key]['name'] = $device['name'];
                $thermometers[$key]['battery'] = $this->getBatteryData($device['status']);
            } else {
                $thermometers[$key] = [
                    'online' => false,
                    'id' => $device['id'],
                    'temperature' => null,
                    'humidity' => null,
                    'name' => $device['name'],
                    'battery' => null
                ];
            }
        }

        return $thermometers;
    }

    public function getBatteryData(array $deviceStatus): int
    {
        foreach ($deviceStatus as $statusItem) {
            if ($statusItem['code'] === 'battery_percentage') {
                return $statusItem['value'];
            }
        }

        return null;
    }

    public function parseTemperatureData(array $deviceStatus): array
    {
        $result = ['temperature' => null, 'humidity' => null, 'heat_index' => null, 'thermal_emoji' => null, 'temperature_color' => null, 'humidity_color' => null];

        foreach ($deviceStatus as $dp) {
            switch ($dp['code']) {
                case 'va_temperature':
                case 'temp_current':
                    $result['temperature'] = $dp['value'] / 10;
                    break;
                case 'va_humidity':
                case 'humidity_value':
                    $result['humidity'] = $dp['value'];
                    break;
            }
        }

        if ($result['temperature'] !== null && $result['humidity'] !== null) {
            $result['heat_index'] = $this->calculateHeatIndex($result['temperature'], $result['humidity']);
            $result['thermal_emoji'] = $this->getThermalEmoji($result['heat_index']);
            $result['temperature_color'] = $this->temperatureColor($result['heat_index']);
            $result['humidity_color'] = $this->humidityColor($result['humidity']);
        }

        return $result;
    }

    private function calculateHeatIndex(float $temperature, float $humidity): float
    {
        $heatIndex = -8.78469475556 + 1.61139411 * $temperature + 2.33854883889 * $humidity
            - 0.14611605 * $temperature * $humidity - 0.012308094 * $temperature * $temperature
            - 0.0164248277778 * $humidity * $humidity
            + 0.002211732 * $temperature * $temperature * $humidity
            + 0.00072546 * $temperature * $humidity * $humidity
            - 0.000003582 * $temperature * $temperature * $humidity * $humidity;

        return round($heatIndex, 1);
    }

    private function getThermalEmoji(float $heatIndex): string
    {
        if ($heatIndex <= 18) return "🥶";
        if ($heatIndex <= 23) return "😌";
        if ($heatIndex <= 27) return "🙂";
        if ($heatIndex <= 30) return "😓";
        if ($heatIndex <= 35) return "😰";
        return "🔥";
    }

    private function temperatureColor(float $heatIndex): string
    {
        if ($heatIndex <= 18) return '#17a2b8';
        if ($heatIndex <= 23) return '#2980b9';
        if ($heatIndex <= 27) return '#27ae60';
        if ($heatIndex <= 30) return '#f39c12';
        if ($heatIndex <= 35) return '#c0392b';
        return '#c0392b';
    }

    private function humidityColor(float $humidity): string
    {
        return ($humidity >= 40 && $humidity <= 60) ? '#5cb85c' : '#d9534f';
    }

    public function getTemperatureLogsForSync(string $deviceId, int $startTime, int $endTime): array
    {
        try {
            $allLogs = [];
            $nextRowKey = null;
            $requestCount = 0;

            while ($requestCount < 50) {
                $params = [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'type' => '7',
                    'size' => 100
                ];

                if ($nextRowKey) {
                    $params['next_row_key'] = $nextRowKey;
                }

                $response = $this->tuyaService->makeRequest('GET', "/v1.0/devices/{$deviceId}/logs", [], $params);

                if (isset($response['logs']) && !empty($response['logs'])) {
                    $allLogs = array_merge($allLogs, $response['logs']);

                    if (isset($response['next_row_key']) && !empty($response['next_row_key'])) {
                        $nextRowKey = $response['next_row_key'];
                    } else {
                        break;
                    }
                } else {
                    break;
                }

                $requestCount++;

                if ($requestCount > 50) {
                    \Log::warning("Too many pagination requests during sync, stopping at {$requestCount}");
                    break;
                }
            }

            $formattedLogs = [];
            foreach ($allLogs as $log) {
                $formattedLogs[] = [
                    'device_id' => $deviceId,
                    'timestamp' => intval($log['event_time']),
                    'code' => $log['code'],
                    'value' => $log['value'],
                    'raw_data' => $log
                ];
            }

            return $formattedLogs;
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération des logs pour sync {$deviceId}: " . $e->getMessage());
            return [];
        }
    }

    public function getTemperatureHistory(string $deviceId, int $days = 1): array
    {
        $cacheKey = "tuya_temp_history_{$deviceId}_{$days}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($deviceId, $days) {
            $currentTime = time();
            $endTime = (floor($currentTime / 3600) * 3600) * 1000;
            $startTime = $endTime - ($days * 24 * 60 * 60 * 1000);

            try {
                $hoursNeeded = $days * 24;
                $requestsNeeded = max(1, ceil($hoursNeeded / 10));

                $allLogs = [];
                $nextRowKey = null;
                $requestCount = 0;

                while ($requestCount < $requestsNeeded) {
                    $params = [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'type' => '7',
                        'size' => 100
                    ];

                    if ($nextRowKey) {
                        $params['next_row_key'] = $nextRowKey;
                    }

                    $response = $this->tuyaService->makeRequest('GET', "/v1.0/devices/{$deviceId}/logs", [], $params);

                    if (isset($response['logs']) && !empty($response['logs'])) {
                        $allLogs = array_merge($allLogs, $response['logs']);

                        \Log::info("DEBUG - Request {$requestCount}", [
                            'logs_in_response' => count($response['logs']),
                            'total_logs_so_far' => count($allLogs),
                            'has_next_row_key' => isset($response['next_row_key'])
                        ]);

                        if (isset($response['next_row_key']) && !empty($response['next_row_key'])) {
                            $nextRowKey = $response['next_row_key'];
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }

                    $requestCount++;

                    if ($requestCount > 20) {
                        \Log::warning("Too many pagination requests, stopping at {$requestCount}");
                        break;
                    }
                }

                \Log::info("DEBUG - Final pagination result", [
                    'total_requests' => $requestCount,
                    'total_logs' => count($allLogs),
                    'period_coverage_hours' => count($allLogs) / 10
                ]);

                $logsStructure = ['logs' => $allLogs];

                \Log::info("DEBUG - raw logs", [
                    'logs_structure' => $logsStructure,
                ]);

                return $this->parseTemperatureHistoryLogs($logsStructure, $days);
            } catch (\Exception $e) {
                \Log::error("Erreur historique thermomètre {$deviceId}: " . $e->getMessage());
                return [];
            }
        });
    }

    private function parseTemperatureHistoryLogs(array $logs, int $days): array
    {
        if (!isset($logs['logs']) || empty($logs['logs'])) {
            return [];
        }

        $intervalHours = 1;
        $expectedPoints = 24;
        if ($days === 3) {
            $intervalHours = 3;
            $expectedPoints = 24;
        } elseif ($days === 7) {
            $intervalHours = 6;
            $expectedPoints = 28;
        }

        $currentTime = time();
        $endTime = (floor($currentTime / 3600) * 3600) * 1000;
        $ranges = [];

        for ($i = 0; $i < $expectedPoints; $i++) {
            $rangeEnd = $endTime - ($i * $intervalHours * 3600 * 1000);
            $rangeStart = $rangeEnd - ($intervalHours * 3600 * 1000);

            $displayTimestamp = $rangeStart;
            $displayKey = date('Y-m-d H:i', $displayTimestamp / 1000);

            $ranges[] = [
                'start' => $rangeStart,
                'end' => $rangeEnd,
                'timestamp' => $displayTimestamp,
                'display_key' => $displayKey,
                'temperature_values' => [],
                'humidity_values' => []
            ];
        }

        $ranges = array_reverse($ranges);

        foreach ($logs['logs'] as $log) {
            $logTimestamp = intval($log['event_time']);

            foreach ($ranges as &$range) {
                if ($logTimestamp >= $range['start'] && $logTimestamp < $range['end']) {
                    if (isset($log['code'], $log['value'])) {
                        switch ($log['code']) {
                            case 'va_temperature':
                            case 'temp_current':
                                $range['temperature_values'][] = $log['value'] / 10;
                                break;
                            case 'va_humidity':
                            case 'humidity_value':
                                $range['humidity_values'][] = $log['value'];
                                break;
                        }
                    }
                    break;
                }
            }
        }

        $result = [];
        foreach ($ranges as $range) {
            $avgTemp = !empty($range['temperature_values']) ?
                round(array_sum($range['temperature_values']) / count($range['temperature_values']), 1) : null;
            $avgHumidity = !empty($range['humidity_values']) ?
                round(array_sum($range['humidity_values']) / count($range['humidity_values']), 0) : null;

            $heatIndex = null;
            if ($avgTemp !== null && $avgHumidity !== null) {
                $heatIndex = $this->calculateHeatIndex($avgTemp, $avgHumidity);
            }

            $result[] = [
                'hour' => $range['display_key'],
                'timestamp' => $range['timestamp'],
                'temperature' => $avgTemp,
                'humidity' => $avgHumidity,
                'heat_index' => $heatIndex
            ];
        }

        return $result;
    }


    private function extractLocationFromDeviceName(string $name): string
    {
        if (preg_match('/\[(.*?)\]/', $name, $matches)) {
            return strtolower(str_replace(' ', '_', $matches[1]));
        }

        return strtolower(str_replace([' ', '[', ']'], ['_', '', ''], $name));
    }

    private function generateSensorLocationKey(string $name): string
    {
        return $this->extractLocationFromDeviceName($name);
    }

    private function generateSensorName(string $name): string
    {
        $name = $this->extractLocationFromDeviceName($name);

        return ucfirst(str_replace(['_', '[', ']'], [' ', '', ''], $name));
    }
}
