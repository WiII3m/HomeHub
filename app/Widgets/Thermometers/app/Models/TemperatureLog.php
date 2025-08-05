<?php

namespace App\Widgets\Thermometers\App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureLog extends Model
{
    protected $fillable = [
        'device_id',
        'timestamp',
        'code',
        'value',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'timestamp' => 'integer',
    ];

    public static function getLastLogForDevice(string $deviceId): ?self
    {
        return self::where('device_id', $deviceId)
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    public static function getLogsForPeriod(string $deviceId, int $startTime, int $endTime): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('device_id', $deviceId)
            ->whereBetween('timestamp', [$startTime, $endTime])
            ->orderBy('timestamp', 'asc')
            ->get();
    }

    public static function getLogsByCodeForPeriod(string $deviceId, int $startTime, int $endTime): array
    {
        $logs = self::getLogsForPeriod($deviceId, $startTime, $endTime);
        
        return $logs->groupBy('code')->map(function ($items) {
            return $items->pluck('value', 'timestamp');
        })->toArray();
    }
}
