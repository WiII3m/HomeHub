<?php

namespace App\Widgets\HelloWorld\App\Services;

/**
 * Service HelloWorld
 * Génère des faux devices pour démonstration
 */
class HelloWorldService
{
    /**
     * Génère 4 devices de démonstration avec status aléatoire
     *
     * @return array Liste des devices avec leurs propriétés
     */
    public function getDevices(): array
    {
        $devices = [];

        // Créer 4 devices avec des propriétés simulées
        for ($i = 1; $i <= 4; $i++) {
            $devices["device_$i"] = [
                'id' => "device_$i",
                'name' => "Device $i",
                'online' => $this->generateRandomStatus(), // Status aléatoire
            ];
        }

        return $devices;
    }

    /**
     * Génère un status online/offline aléatoire
     * 70% de chance d'être online (plus réaliste)
     */
    private function generateRandomStatus(): bool
    {
        return rand(1, 10) <= 7; // 70% online, 30% offline
    }
}
