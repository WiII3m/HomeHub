<?php

namespace App\Widgets\Thermometers\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Widgets\Thermometers\App\Models\TemperatureLog;
use Carbon\Carbon;

class TemperatureLogSeeder extends Seeder
{
    /**
     * Seeder pour créer des données de test de température
     */
    public function run(): void
    {
        // IDs des thermomètres existants
        $deviceIds = [
            'bff7644e588d518b595vg7', // Chambre enfants
            'bfdb6ddbf1877a9d68q3jb', // Salle de bain  
            'bff9870e5dbb82dc06g05d', // Salon
            'bf05bc3b3c08316bcfd7rj'  // Chambre parents
        ];

        $this->command->info('🌡️  Génération des données de test de température...');

        foreach ($deviceIds as $deviceId) {
            $this->generateLogsForDevice($deviceId);
        }

        $this->command->info('✅ Données de test créées avec succès !');
    }

    /**
     * Générer des logs de température pour un appareil
     */
    private function generateLogsForDevice(string $deviceId): void
    {
        $now = Carbon::now();
        $logsCount = 0;

        // Générer 7 jours de données (environ 10 logs par heure)
        for ($day = 6; $day >= 0; $day--) {
            for ($hour = 0; $hour < 24; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 6) { // Toutes les 6 minutes
                    $timestamp = $now->copy()
                        ->subDays($day)
                        ->setHour($hour)
                        ->setMinute($minute)
                        ->setSecond(0)
                        ->timestamp * 1000; // Tuya utilise les millisecondes

                    // Générer température réaliste avec variations
                    $baseTemp = $this->getBaseTemperatureForDevice($deviceId);
                    $timeVariation = $this->getTimeVariation($hour);
                    $randomVariation = (rand(-50, 50) / 100); // ±0.5°C
                    $temperature = round(($baseTemp + $timeVariation + $randomVariation) * 10); // Tuya format (285 = 28.5°C)

                    // Générer humidité réaliste
                    $baseHumidity = $this->getBaseHumidityForDevice($deviceId);
                    $humidityVariation = rand(-10, 10);
                    $humidity = max(30, min(80, $baseHumidity + $humidityVariation));

                    // Créer les logs température et humidité
                    TemperatureLog::create([
                        'device_id' => $deviceId,
                        'timestamp' => $timestamp,
                        'code' => 'va_temperature',
                        'value' => (string) $temperature,
                        'raw_data' => [
                            'event_time' => $timestamp,
                            'code' => 'va_temperature',
                            'value' => $temperature
                        ]
                    ]);

                    TemperatureLog::create([
                        'device_id' => $deviceId,
                        'timestamp' => $timestamp,
                        'code' => 'va_humidity',
                        'value' => (string) $humidity,
                        'raw_data' => [
                            'event_time' => $timestamp,
                            'code' => 'va_humidity',
                            'value' => $humidity
                        ]
                    ]);

                    $logsCount += 2;
                }
            }
        }

        $deviceName = $this->getDeviceName($deviceId);
        $this->command->info("   📊 {$deviceName}: {$logsCount} logs générés");
    }

    /**
     * Température de base selon l'appareil/pièce
     */
    private function getBaseTemperatureForDevice(string $deviceId): float
    {
        return match ($deviceId) {
            'bff7644e588d518b595vg7' => 20.5, // Chambre enfants
            'bfdb6ddbf1877a9d68q3jb' => 22.0, // Salle de bain
            'bff9870e5dbb82dc06g05d' => 21.5, // Salon
            'bf05bc3b3c08316bcfd7rj' => 19.8, // Chambre parents
            default => 21.0
        };
    }

    /**
     * Humidité de base selon l'appareil/pièce
     */
    private function getBaseHumidityForDevice(string $deviceId): int
    {
        return match ($deviceId) {
            'bff7644e588d518b595vg7' => 45, // Chambre enfants
            'bfdb6ddbf1877a9d68q3jb' => 65, // Salle de bain (plus humide)
            'bff9870e5dbb82dc06g05d' => 50, // Salon
            'bf05bc3b3c08316bcfd7rj' => 48, // Chambre parents
            default => 50
        };
    }

    /**
     * Variation de température selon l'heure (cycle jour/nuit)
     */
    private function getTimeVariation(int $hour): float
    {
        // Température plus basse la nuit (2h-6h), plus haute l'après-midi (14h-18h)
        $variations = [
            0 => -1.0,   // Minuit
            1 => -1.2,
            2 => -1.5,   // Plus froid
            3 => -1.7,
            4 => -1.5,
            5 => -1.2,
            6 => -0.8,   // Lever du soleil
            7 => -0.3,
            8 => 0.2,
            9 => 0.7,
            10 => 1.2,
            11 => 1.8,
            12 => 2.2,   // Midi
            13 => 2.5,
            14 => 2.8,   // Plus chaud
            15 => 2.6,
            16 => 2.3,
            17 => 1.8,
            18 => 1.2,   // Coucher du soleil
            19 => 0.6,
            20 => 0.0,
            21 => -0.4,
            22 => -0.7,
            23 => -0.9
        ];

        return $variations[$hour] ?? 0;
    }

    /**
     * Nom de l'appareil pour l'affichage
     */
    private function getDeviceName(string $deviceId): string
    {
        return match ($deviceId) {
            'bff7644e588d518b595vg7' => 'Chambre enfants',
            'bfdb6ddbf1877a9d68q3jb' => 'Salle de bain',
            'bff9870e5dbb82dc06g05d' => 'Salon',
            'bf05bc3b3c08316bcfd7rj' => 'Chambre parents',
            default => 'Appareil inconnu'
        };
    }
}