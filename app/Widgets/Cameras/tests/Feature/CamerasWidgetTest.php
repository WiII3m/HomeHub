<?php
namespace App\Widgets\Cameras\Tests\Feature;

use App\Widgets\Cameras\CamerasWidget;
use App\Widgets\Cameras\App\Services\CamerasService;
use Tests\TestCase;
use Mockery;

class CamerasWidgetTest extends TestCase
{
    private CamerasWidget $widget;
    private Mockery\MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(CamerasService::class);
        $this->widget = new CamerasWidget($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_widget_config(): void
    {
        $this->assertIsString($this->widget->getName());
        $this->assertIsString($this->widget->getAuthor());
        $this->assertIsString($this->widget->getTitle());
        $this->assertIsString($this->widget->getIcon());

        $config = $this->widget->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('title', $config);
        $this->assertArrayHasKey('icon', $config);
    }

    public function test_widget_getData(): void
    {
        $mockCameras = [
            [
                'id' => 'bfddac4d500c3243d9nhzw',
                'name' => 'Rez de chaussé',
                'online' => false,
                'basic_private' => 1
            ]
        ];

        $this->mockService
            ->shouldReceive('getCameras')
            ->once()
            ->andReturn($mockCameras);

        // Act
        $data = $this->widget->getData();

        // Assert - Structure des données
        $this->assertIsArray($data);
        $this->assertArrayHasKey('cameras', $data);
        $this->assertArrayHasKey('config', $data);

        $this->assertEquals($mockCameras, $data['cameras']);
        $this->assertIsArray($data['config']);
    }

    public function test_widget_getAssets(): void
    {
        $assets = $this->widget->getAssets();

        $this->assertIsArray($assets);

        $this->assertArrayHasKey('css', $assets);
        $this->assertArrayHasKey('js', $assets);

        $this->assertIsArray($assets['css']);
        $this->assertIsArray($assets['js']);
    }

    public function test_widget_render(): void
    {
        // Utiliser les données cohérentes du mock $expected
        $testCameras = [
            [
                'id' => 'bfddac4d500c3243d9nhzw',
                'name' => 'Rez de chaussé',
                'online' => false,
                'basic_private' => 1
            ],
            [
                'id' => 'bf69c655d66cd06c74qjgg',
                'name' => 'Chambre enfants',
                'online' => true,
                'basic_private' => ''
            ]
        ];

        // Transformer les données en format attendu par la vue : indexé par device_id
        $indexedCameras = [];
        foreach ($testCameras as $camera) {
            $indexedCameras[$camera['id']] = $camera;
        }

        $this->mockService
            ->shouldReceive('getCameras')
            ->once()
            ->andReturn($indexedCameras);

        // Act
        $html = $this->widget->render();

        // Assert - Injection JavaScript complète
        $this->assertStringContainsString('window.camerasState', $html);
        $this->assertStringContainsString('window.camerasWidgetConfig', $html);

        // Debug: voir ce qui est généré exactement
        // dump($html);
        
        // Assert - États variés correctement injectés (PHP true/false devient 1/false en JSON)
        $this->assertStringContainsString('"online":true', $html);
        $this->assertStringContainsString('"online":false', $html);
        $this->assertStringContainsString('"basic_private":1', $html);
        $this->assertStringContainsString('"basic_private":""', $html);
    }

}
