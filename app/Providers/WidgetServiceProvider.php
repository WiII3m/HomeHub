<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Widgets\Core\WidgetManager;

class WidgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WidgetManager::class, function ($app) {
            $manager = new WidgetManager();
            $manager->discoverWidgets();
            return $manager;
        });

        $this->registerWidgetServices();
    }

    public function boot(): void
    {
        $this->registerWidgetRoutes();
        
        $this->registerWidgetViews();
        
        $this->registerWidgetCommands();
        
        $this->publishWidgetAssets();
        
        $this->generateNodeConfigIfNeeded();
        
        $this->registerBladeDirectives();
    }

    private function registerWidgetServices(): void
    {
        $widgetsPath = app_path('Widgets');
        
        if (!is_dir($widgetsPath)) {
            \Log::warning('Widgets directory not found: ' . $widgetsPath);
            return;
        }
        
        $widgetDirs = glob($widgetsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($widgetDirs as $widgetDir) {
            $widgetName = basename($widgetDir);
            $servicePath = $widgetDir . "/app/Services/{$widgetName}Service.php";
            
            if (file_exists($servicePath)) {
                $serviceClass = "App\\Widgets\\{$widgetName}\\App\\Services\\{$widgetName}Service";
                
                if (class_exists($serviceClass)) {
                    $this->app->bind($serviceClass, function ($app) use ($serviceClass) {
                        return new $serviceClass($app->make(\App\Services\TuyaService::class));
                    });
                    
                    \Log::info("Auto-registered widget service: {$serviceClass}");
                } else {
                    \Log::warning("Widget service class not found: {$serviceClass}");
                }
            }
        }
    }

    private function registerWidgetRoutes(): void
    {
        $routeFiles = glob(app_path('Widgets/*/routes/routes.php'));
        
        foreach ($routeFiles as $routeFile) {
            $this->loadRoutesFrom($routeFile);
        }
        
    }

    private function registerWidgetViews(): void
    {
        $this->loadViewsFrom(app_path('Widgets'), 'widgets');
    }

    private function registerWidgetCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $widgetManager = $this->app->make(WidgetManager::class);
            $commands = $widgetManager->discoverCommands();
            
            foreach ($commands as $command) {
                if (class_exists($command['class'])) {
                    $this->commands([$command['class']]);
                }
            }
        }
    }

    private function publishWidgetAssets(): void
    {
        
    }

    private function generateNodeConfigIfNeeded(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $configPath = storage_path('node-config.json');
            $widgetManager = $this->app->make(WidgetManager::class);
            
            $plugins = $widgetManager->discoverNodePlugins();
            
            if (!empty($plugins)) {
                $shouldGenerate = false;
                
                if (!file_exists($configPath)) {
                    $shouldGenerate = true;
                    \Log::info('Node.js config file does not exist, generating...');
                } else {
                    $configTime = filemtime($configPath);
                    $latestWidgetTime = $this->getLatestWidgetModificationTime();
                    
                    if ($latestWidgetTime > $configTime) {
                        $shouldGenerate = true;
                        \Log::info('Widget modifications detected, regenerating Node.js config...');
                    }
                }
                
                if ($shouldGenerate) {
                    $success = $widgetManager->generateNodeConfig();
                    if ($success) {
                        \Log::info('Node.js configuration auto-generated successfully');
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error auto-generating Node.js configuration: ' . $e->getMessage());
        }
    }

    private function getLatestWidgetModificationTime(): int
    {
        $latestTime = 0;
        $widgetsPath = app_path('Widgets');
        
        if (file_exists($widgetsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($widgetsPath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'json', 'js'])) {
                    $latestTime = max($latestTime, $file->getMTime());
                }
            }
        }
        
        return $latestTime;
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('renderAllWidgets', function () {
            return "<?php if(isset(\$renderedWidgets) && is_array(\$renderedWidgets)): ?>
                <?php foreach(\$renderedWidgets as \$widgetName => \$widgetData): ?>
                    <div id=\"widget-<?php echo e(\$widgetName); ?>\" class=\"widget-item\" style=\"display: none; margin-bottom: 24px;\">
                        <?php echo \$widgetData['html']; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>";
        });
    }
}