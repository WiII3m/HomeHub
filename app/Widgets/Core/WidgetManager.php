<?php
namespace App\Widgets\Core;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class WidgetManager
{
    private array $widgets = [];
    private array $widgetInstances = [];

    public function discoverWidgets(): array
    {
        $widgetsPath = app_path('Widgets');
        $discoveredWidgets = [];

        if (!File::exists($widgetsPath)) {
            return [];
        }

        $directories = File::directories($widgetsPath);

        foreach ($directories as $directory) {
            $widgetName = basename($directory);

            if ($widgetName === 'Core') {
                continue;
            }

            $configPath = $directory . '/config.json';
            $widgetClass = "App\\Widgets\\{$widgetName}\\{$widgetName}Widget";

            if (File::exists($configPath) && class_exists($widgetClass)) {
                $config = json_decode(File::get($configPath), true);

                if ($config && isset($config['enabled']) && $config['enabled']) {
                    $discoveredWidgets[$widgetName] = [
                        'name' => $widgetName,
                        'class' => $widgetClass,
                        'config' => $config,
                        'path' => $directory
                    ];
                }
            }
        }

        $this->widgets = $discoveredWidgets;
        return $discoveredWidgets;
    }

    public function getWidget(string $widgetName): ?WidgetInterface
    {
        if (isset($this->widgetInstances[$widgetName])) {
            return $this->widgetInstances[$widgetName];
        }

        if (!isset($this->widgets[$widgetName])) {
            return null;
        }

        $widgetClass = $this->widgets[$widgetName]['class'];

        try {
            $this->widgetInstances[$widgetName] = App::make($widgetClass);
            return $this->widgetInstances[$widgetName];
        } catch (\Exception $e) {
            \Log::error("Failed to create widget instance for {$widgetName}: " . $e->getMessage());
            return null;
        }
    }

    public function getAllWidgets(): array
    {
        $instances = [];

        foreach ($this->widgets as $widgetName => $widgetInfo) {
            $widget = $this->getWidget($widgetName);
            if ($widget) {
                $instances[$widgetName] = $widget;
            }
        }

        return $instances;
    }

    public function getCoreAssets(): array
    {
        $coreAssets = ['js' => [], 'css' => []];

        // fichiers JavaScript Core
        $coreJsPath = app_path('Widgets/Core/resources/js');
        $coreJsOrder = ['polyfills.js', 'http-client.js', 'widget.js', 'realtime.js'];

        if (File::exists($coreJsPath)) {
            foreach ($coreJsOrder as $coreFile) {
                $fullPath = $coreJsPath . '/' . $coreFile;
                if (File::exists($fullPath)) {
                    $coreAssets['js'][] = '/widgets/core/resources/js/' . $coreFile;
                }
            }
        }

        // Auto-découverte des fichiers CSS Core
        $coreCssPath = app_path('Widgets/Core/resources/css');
        if (File::exists($coreCssPath)) {
            $coreStyleFiles = File::files($coreCssPath);
            foreach ($coreStyleFiles as $file) {
                if ($file->getExtension() === 'css') {
                    $coreAssets['css'][] = '/widgets/core/resources/css/' . $file->getFilename();
                }
            }
        }

        return $coreAssets;
    }

    public function getAllAssets(): array
    {
        $allAssets = ['js' => [], 'css' => []];

        $coreAssets = $this->getCoreAssets();
        $allAssets['js'] = array_merge($allAssets['js'], $coreAssets['js']);
        $allAssets['css'] = array_merge($allAssets['css'], $coreAssets['css']);

        // Puis les assets des widgets individuels
        foreach ($this->getAllWidgets() as $widget) {
            $assets = $widget->getAssets();

            if (isset($assets['js'])) {
                foreach ($assets['js'] as $jsFile) {
                    $allAssets['js'][] = '/widgets/' . strtolower($widget->getName()) . '/' . $jsFile;
                }
            }

            if (isset($assets['css'])) {
                foreach ($assets['css'] as $cssFile) {
                    $allAssets['css'][] = '/widgets/' . strtolower($widget->getName()) . '/' . $cssFile;
                }
            }
        }

        return $allAssets;
    }

    public function renderAllWidgets(): array
    {
        $renderedWidgets = [];

        foreach ($this->getAllWidgets() as $widget) {
            try {
                $renderedWidgets[$widget->getName()] = [
                    'name' => $widget->getName(),
                    'title' => $widget->getTitle(),
                    'icon' => $widget->getIcon(),
                    'html' => $widget->render(),
                    'config' => $widget->getConfig()
                ];
            } catch (\Exception $e) {
                \Log::error("Failed to render widget {$widget->getName()}: " . $e->getMessage());
            }
        }

        return $renderedWidgets;
    }

    public function getAllWidgetsData(): array
    {
        $widgetsData = [];

        foreach ($this->getAllWidgets() as $widget) {
            try {
                $widgetsData[$widget->getName()] = $widget->getData();
            } catch (\Exception $e) {
                \Log::error("Failed to get data for widget {$widget->getName()}: " . $e->getMessage());
                $widgetsData[$widget->getName()] = [];
            }
        }

        return $widgetsData;
    }

    public function getAllWidgetsMetadata(): array
    {
        $widgetsMetadata = [];
        foreach ($this->getAllWidgets() as $widget) {
            try {
                $widgetsMetadata[$widget->getName()] = [
                    'name' => $widget->getName(),
                    'author' => $widget->getAuthor(),
                    'title' => $widget->getTitle(),
                    'icon' => $widget->getIcon(),
                    'data' => $widget->getData()
                ];
            } catch (\Exception $e) {
                \Log::error("Failed to get metadata for widget {$widget->getName()}: " . $e->getMessage());
                $widgetsMetadata[$widget->getName()] = [
                    'name' => $widget->getName(),
                    'title' => ucfirst($widget->getName()),
                    'icon' => '📱',
                    'data' => []
                ];
            }
        }
        return $widgetsMetadata;
    }

    public function discoverNodePlugins(): array
    {
        $plugins = [];

        foreach ($this->widgets as $widgetName => $widgetInfo) {
            $nodePluginPath = $widgetInfo['path'] . '/node-plugin';
            $configPath = $nodePluginPath . '/config.json';
            $middlewarePath = $nodePluginPath . '/middleware.js';

            if (File::exists($configPath) && File::exists($middlewarePath)) {
                try {
                    $pluginConfig = json_decode(File::get($configPath), true);

                    if ($pluginConfig && isset($pluginConfig['enabled']) && $pluginConfig['enabled']) {
                        $plugins[] = [
                            'name' => $pluginConfig['name'] ?? strtolower($widgetName),
                            'enabled' => $pluginConfig['enabled'],
                            'priority' => $pluginConfig['priority'] ?? 100,
                            'middleware_path' => realpath($middlewarePath),
                            'conditions' => $pluginConfig['conditions'] ?? [],
                            'description' => $pluginConfig['description'] ?? "Plugin for {$widgetName} widget",
                            'widget_name' => $widgetName,
                            'widget_path' => $widgetInfo['path']
                        ];

                        \Log::info("Discovered Node.js plugin for widget: {$widgetName}");
                    }
                } catch (\Exception $e) {
                    \Log::error("Error parsing Node.js plugin config for widget {$widgetName}: " . $e->getMessage());
                }
            }
        }

        usort($plugins, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $plugins;
    }

    public function generateNodeConfig(): bool
    {
        try {
            $plugins = $this->discoverNodePlugins();

            $config = [
                'generated_at' => now()->toISOString(),
                'laravel_version' => app()->version(),
                'total_widgets' => count($this->widgets),
                'total_plugins' => count($plugins),
                'plugins' => $plugins
            ];

            $configPath = storage_path('node-config.json');

            $success = File::put($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if ($success) {
                \Log::info("Node.js configuration generated successfully", [
                    'path' => $configPath,
                    'plugins_count' => count($plugins),
                    'plugins' => array_column($plugins, 'name')
                ]);
                return true;
            } else {
                \Log::error("Failed to write Node.js configuration file");
                return false;
            }

        } catch (\Exception $e) {
            \Log::error("Error generating Node.js configuration: " . $e->getMessage());
            return false;
        }
    }

    public function getNodePluginsStats(): array
    {
        $plugins = $this->discoverNodePlugins();

        return [
            'total_plugins' => count($plugins),
            'enabled_plugins' => count(array_filter($plugins, fn($p) => $p['enabled'])),
            'plugins' => array_map(function($plugin) {
                return [
                    'name' => $plugin['name'],
                    'widget' => $plugin['widget_name'],
                    'enabled' => $plugin['enabled'],
                    'priority' => $plugin['priority']
                ];
            }, $plugins)
        ];
    }

    public function discoverCommands(): array
    {
        $commands = [];
        $widgetsPath = app_path('Widgets');

        if (!File::exists($widgetsPath)) {
            return [];
        }

        $directories = File::directories($widgetsPath);

        foreach ($directories as $directory) {
            $widgetName = basename($directory);
            $commandsPath = $directory . '/app/Console/Commands';

            if (!File::exists($commandsPath)) {
                continue;
            }

            $commandFiles = File::files($commandsPath);

            foreach ($commandFiles as $file) {
                if ($file->getExtension() === 'php') {
                    $commandName = $file->getBasename('.php');
                    $className = "App\\Widgets\\{$widgetName}\\App\\Console\\Commands\\{$commandName}";

                    if (class_exists($className)) {
                        $commands[] = [
                            'widget' => $widgetName,
                            'class' => $className,
                            'file' => $file->getPathname()
                        ];
                    }
                }
            }
        }

        return $commands;
    }

    public function registerCommands(): void
    {
        $commands = $this->discoverCommands();

        foreach ($commands as $command) {
            try {
                app('Illuminate\Contracts\Console\Kernel')->resolve($command['class']);
                \Log::info("✅ Command registered: {$command['class']} (Widget: {$command['widget']})");
            } catch (\Exception $e) {
                \Log::error("❌ Failed to register command {$command['class']}: " . $e->getMessage());
            }
        }
    }

    public function getAllCommands(): array
    {
        return $this->discoverCommands();
    }
}
