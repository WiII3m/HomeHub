<?php
namespace App\Widgets\Thermometers\App\Console\Commands;

use Illuminate\Console\Command;
use App\Widgets\Thermometers\App\Models\TemperatureLog;
use App\Widgets\Thermometers\App\Services\ThermometersService;
use Illuminate\Support\Facades\DB;

class ThermometersSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thermometers:sync {--device-id=} {--force-full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les logs de température depuis l\'API Tuya (Widget Thermometers)';

    public function __construct(
        private ThermometersService $thermometersService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌡️  [THERMOMETERS WIDGET] Début de la synchronisation des températures...');

        try {
            // Récupérer tous les thermomètres
            $thermometers = $this->thermometersService->getDevices();

            if (empty($thermometers)) {
                $this->warn('Aucun thermomètre trouvé.');
                return 1;
            }

            $deviceIdFilter = $this->option('device-id');
            $forceFullSync = $this->option('force-full');

            $syncedDevices = 0;
            $totalLogs = 0;

            foreach ($thermometers as $thermometer) {
                $deviceId = $thermometer['id'];
                $deviceName = $thermometer['name'];

                // Filtrer par device-id si spécifié
                if ($deviceIdFilter && $deviceId !== $deviceIdFilter) {
                    continue;
                }

                $this->info("📱 Synchronisation de {$deviceName} ({$deviceId})...");

                // Déterminer la période à synchroniser
                $lastLog = TemperatureLog::getLastLogForDevice($deviceId);

                if ($forceFullSync || !$lastLog) {
                    // Synchronisation complète (7 jours)
                    $startTime = (time() - (7 * 24 * 60 * 60)) * 1000;
                    $this->info("   🔄 Synchronisation complète (7 jours)");
                } else {
                    // Synchronisation incrémentale
                    $startTime = $lastLog->timestamp + 1; // +1ms pour éviter les doublons
                    $this->info("   ⚡ Synchronisation incrémentale depuis " . date('Y-m-d H:i:s', $startTime / 1000));
                }

                $endTime = time() * 1000;

                // Récupérer les nouveaux logs
                $logs = $this->thermometersService->getTemperatureLogsForSync($deviceId, $startTime, $endTime);

                if (empty($logs)) {
                    $this->info("   ✅ Aucun nouveau log");
                    continue;
                }

                // Sauvegarder en base
                $savedLogs = $this->saveLogsToDatabase($logs);
                $totalLogs += $savedLogs;
                $syncedDevices++;

                $this->info("   ✅ {$savedLogs} nouveaux logs sauvegardés");
            }

            $this->info("🎉 [THERMOMETERS WIDGET] Synchronisation terminée : {$syncedDevices} appareils, {$totalLogs} logs au total");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ [THERMOMETERS WIDGET] Erreur lors de la synchronisation : " . $e->getMessage());
            \Log::error('Erreur thermometers:sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sauvegarder les logs en base de données
     */
    private function saveLogsToDatabase(array $logs): int
    {
        $savedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($logs as $log) {
                TemperatureLog::create([
                    'device_id' => $log['device_id'],
                    'timestamp' => $log['timestamp'],
                    'code' => $log['code'],
                    'value' => $log['value'],
                    'raw_data' => $log['raw_data'] ?? null,
                ]);
                $savedCount++;
            }

            DB::commit();
            return $savedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
