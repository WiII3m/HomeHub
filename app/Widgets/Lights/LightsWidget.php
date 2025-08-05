<?php

namespace App\Widgets\Lights;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\Lights\App\Services\LightsService;

class LightsWidget implements WidgetInterface
{
    private LightsService $lightsService;
    private array $config;

    public function __construct(LightsService $lightsService)
    {
        $this->lightsService = $lightsService;
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
            'lightsByRoom' => $this->lightsService->getLightsByRoom(),
            'config' => $this->getConfig()
        ];
    }

    public function render(): string
    {
        $data = $this->getData();

        return view('widgets::Lights.resources.views.lights-widget', $data)->render();
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
