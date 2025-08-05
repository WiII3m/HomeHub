<?php

namespace App\Widgets\Thermometers\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Widgets\Thermometers\App\Models\TemperatureLog;
use App\Widgets\Thermometers\App\Services\ThermometersService;
use Illuminate\Support\Facades\Cache;

class ThermometersController extends Controller
{
    private ThermometersService $thermometersService;

    public function __construct(ThermometersService $thermometersService)
    {
        $this->thermometersService = $thermometersService;
    }

    public function processRealtimeData(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
            'temp_current' => 'sometimes|integer',
            'humidity_value' => 'sometimes|integer',
        ]);

        $deviceStatus = [];

        if ($request->has('temp_current')) {
            $deviceStatus[] = [
                'code' => 'temp_current',
                'value' => $request->input('temp_current')
            ];
        }

        if ($request->has('humidity_value')) {
            $deviceStatus[] = [
                'code' => 'humidity_value',
                'value' => $request->input('humidity_value')
            ];
        }

        $result = $this->thermometersService->parseTemperatureData($deviceStatus);

        $response = [
            'device_id' => $request->input('device_id'),
            'temperature' => $result['temperature'],
            'humidity' => $result['humidity'],
            'heat_index' => $result['heat_index'],
            'thermal_emoji' => $result['thermal_emoji'],
            'temperature_color' => $result['temperature_color'],
            'humidity_color' => $result['humidity_color'],
            'timestamp' => now()->toISOString(),
            'success' => true
        ];

        return response()->json($response);
    }

    public function getTemperatureHistory(Request $request): JsonResponse
    {
        $deviceId = $request->input('device_id');
        $days = $request->input('days', 1);

        if (!$deviceId) {
            return response()->json(['success' => false, 'error' => 'Device ID requis'], 400);
        }

        try {
            $cacheKey = "temperature_history_{$deviceId}_{$days}";
            $history = Cache::remember($cacheKey, 300, function () use ($deviceId, $days) {
                return $this->getTemperatureHistoryFromDatabase($deviceId, $days);
            });

            \Log::info("DEBUG - Temperature history from database", [
                'device_id' => $deviceId,
                'days' => $days,
                'count' => count($history),
                'cached' => Cache::has("temperature_history_{$deviceId}_{$days}")
            ]);

            return response()->json(['success' => true, 'data' => $history]);
        } catch (\Exception $e) {
            \Log::error("Erreur historique température {$deviceId}: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function getTemperatureHistoryFromDatabase(string $deviceId, int $days): array
    {
        $currentTime = time();
        $endTime = (floor($currentTime / 3600) * 3600) * 1000;
        $startTime = $endTime - ($days * 24 * 60 * 60 * 1000);

        $logs = TemperatureLog::getLogsForPeriod($deviceId, $startTime, $endTime);

        if ($logs->isEmpty()) {
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

        foreach ($logs as $log) {
            $logTimestamp = $log->timestamp;

            foreach ($ranges as &$range) {
                if ($logTimestamp >= $range['start'] && $logTimestamp < $range['end']) {
                    switch ($log->code) {
                        case 'va_temperature':
                        case 'temp_current':
                            $range['temperature_values'][] = $log->value / 10;
                            break;
                        case 'va_humidity':
                        case 'humidity_value':
                            $range['humidity_values'][] = $log->value;
                            break;
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
                $deviceStatus = [
                    ['code' => 'va_temperature', 'value' => $avgTemp * 10],
                    ['code' => 'va_humidity', 'value' => $avgHumidity]
                ];
                $parsed = $this->thermometersService->parseTemperatureData($deviceStatus);
                $heatIndex = $parsed['heat_index'];
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
}
