<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Widgets\Core\WidgetManager;

class GenerateNodeConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'widgets:generate-node-config {--force : Force regeneration even if file exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Node.js plugin configuration from Laravel widgets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Generating Node.js plugin configuration...');
        
        // Initialiser le WidgetManager
        $widgetManager = app(WidgetManager::class);
        
        // Découvrir les widgets d'abord
        $this->line('📦 Discovering widgets...');
        $widgets = $widgetManager->discoverWidgets();
        $this->info("   Found " . count($widgets) . " widget(s): " . implode(', ', array_keys($widgets)));
        
        // Découvrir les plugins Node.js
        $this->line('🔧 Scanning Node.js plugins...');
        $plugins = $widgetManager->discoverNodePlugins();
        
        if (empty($plugins)) {
            $this->warn('⚠️  No Node.js plugins found!');
            $this->line('   Make sure your widgets have a node-plugin/ directory with:');
            $this->line('   - config.json (with enabled: true)');
            $this->line('   - middleware.js');
            return 1;
        }
        
        $this->info("   Found " . count($plugins) . " Node.js plugin(s):");
        foreach ($plugins as $plugin) {
            $this->line("   - {$plugin['name']} (widget: {$plugin['widget_name']}, priority: {$plugin['priority']})");
        }
        
        // Vérifier si le fichier existe déjà
        $configPath = storage_path('node-config.json');
        if (file_exists($configPath) && !$this->option('force')) {
            if (!$this->confirm('Configuration file already exists. Overwrite?')) {
                $this->info('👋 Aborted.');
                return 0;
            }
        }
        
        // Générer la configuration
        $this->line('✍️  Generating configuration file...');
        $success = $widgetManager->generateNodeConfig();
        
        if ($success) {
            $this->info("✅ Node.js configuration generated successfully!");
            $this->line("   📁 File: {$configPath}");
            
            // Afficher les statistiques
            $stats = $widgetManager->getNodePluginsStats();
            $this->line('');
            $this->line('📊 Plugin Statistics:');
            $this->line("   Total plugins: {$stats['total_plugins']}");
            $this->line("   Enabled plugins: {$stats['enabled_plugins']}");
            
            $this->line('');
            $this->info('🔄 Next steps:');
            $this->line('1. Restart your Node.js server to load the new configuration');
            $this->line('2. Check the Node.js logs to see if plugins loaded correctly');
            
            return 0;
        } else {
            $this->error('❌ Failed to generate Node.js configuration!');
            $this->line('   Check the Laravel logs for more details.');
            return 1;
        }
    }
}
