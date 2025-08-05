<?php
namespace App\Widgets\Cameras;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\Cameras\App\Services\CamerasService;

class CamerasWidget implements WidgetInterface
{
    private CamerasService $camerasService;
    private array $config;

    public function __construct(CamerasService $camerasService)
    {
        $this->camerasService = $camerasService;
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
            'cameras' => $this->camerasService->getCameras(),
            'config' => $this->getConfig()
        ];
    }

    public function render(): string
    {
        $data = $this->getData();

        return view('widgets::Cameras.resources.views.cameras-widget', $data)->render();
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
