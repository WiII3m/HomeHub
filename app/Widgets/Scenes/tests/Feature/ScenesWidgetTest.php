<?php

namespace App\Widgets\Scenes\Tests\Feature;

use App\Widgets\Scenes\ScenesWidget;
use App\Widgets\Scenes\App\Services\ScenesService;
use Tests\TestCase;
use Mockery;

class ScenesWidgetTest extends TestCase
{
    private ScenesWidget $widget;
    private Mockery\MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(ScenesService::class);
        $this->widget = new ScenesWidget($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_widget_getConfig(): void
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

    public function test_widget_getAssets(): void
    {
        $assets = $this->widget->getAssets();

        $this->assertIsArray($assets);

        $this->assertArrayHasKey('css', $assets);
        $this->assertArrayHasKey('js', $assets);

        $this->assertIsArray($assets['css']);
        $this->assertIsArray($assets['js']);
    }

    public function test_widget_getData(): void
    {
        $mockScenes = [
            [
                'id' => 'scene_data',
                'name' => 'Test Data',
                'status' => 'enabled',
                'type' => 'scene'
            ]
        ];

        $this->mockService
            ->shouldReceive('getScenes')
            ->once()
            ->andReturn($mockScenes);

        $data = $this->widget->getData();

        // Assert - Structure des données
        $this->assertIsArray($data);
        $this->assertArrayHasKey('scenes', $data);
        $this->assertArrayHasKey('config', $data);

        $this->assertEquals($mockScenes, $data['scenes']);
        $this->assertIsArray($data['config']);
    }

    public function test_widget_render(): void
    {
        $testScenes = [];

        for ($i = 0; $i <= 4; $i++) {
            $testScenes[] = [
                'id' => "scene_$i",
                'name' => "Scène $i",
                'status' => 'enabled',
                'type' => 'scene'
            ];
        }

        $this->mockService
            ->shouldReceive('getScenes')
            ->once()
            ->andReturn($testScenes);

        // Act
        $html = $this->widget->render();

        // Assert - Classes CSS importantes
        $this->assertStringContainsString('scene-item', $html);
        $this->assertStringContainsString('scene-name', $html);

        // Assert - Scènes générées
        for ($i = 0; $i <= 4; $i++) {
            $this->assertStringContainsString("data-scene-id=\"scene_$i\"", $html);
            $this->assertStringContainsString("Scène $i", $html);
        }
    }
}
