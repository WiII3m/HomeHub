<?php

namespace App\Widgets\Thermometers;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\Thermometers\App\Services\ThermometersService;

class ThermometersWidget implements WidgetInterface
{
    private ThermometersService $thermometersService;
    private array $config;

    public function __construct(ThermometersService $thermometersService)
    {
        $this->thermometersService = $thermometersService;
        $this->config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
    }

    public function getName(): string
    {
        return $this->config['name'];
    }

    public function getAuthor(): string
    {
        return $this->config['author'];
    }

    public function getTitle(): string
    {
        return $this->config['title'];
    }

    public function getIcon(): string
    {
        return $this->config['icon'];
    }

    public function getData(): array
    {
        return [
            'thermometers' => $this->thermometersService->getThermometers(),
            'config' => $this->getConfig()
        ];
    }

    public function render(): string
    {
        $data = $this->getData();

        return view('widgets::Thermometers.resources.views.thermometers-widget', $data)->render();
    }

    public function getAssets(): array
    {
        return $this->config['assets'];
    }


    public function getConfig(): array
    {
        return $this->config;
    }
}
