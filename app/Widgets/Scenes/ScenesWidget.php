<?php
namespace App\Widgets\Scenes;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\Scenes\App\Services\ScenesService;
use Illuminate\Support\Facades\Route;

class ScenesWidget implements WidgetInterface
{
    private ScenesService $scenesService;
    private array $config;

    public function __construct(ScenesService $scenesService)
    {
        $this->scenesService = $scenesService;
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
            'scenes' => $this->scenesService->getScenes(),
            'config' => $this->getConfig()
        ];
    }

    public function render(): string
    {
        $data = $this->getData();

        return view('widgets::Scenes.resources.views.scenes-widget', $data)->render();
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
