<?php

namespace App\Widgets\Thermometers\Tests\Feature;

use App\Widgets\Thermometers\ThermometersWidget;
use App\Widgets\Thermometers\App\Services\ThermometersService;
use Tests\TestCase;
use Mockery;

class ThermometersWidgetTest extends TestCase
{
    private ThermometersWidget $widget;
    private Mockery\MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(ThermometersService::class);
        $this->widget = new ThermometersWidget($this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    public function test_widget_getConfig(): void
    {
        // Act & Assert - Getters de configuration
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
        $this->mockService
            ->shouldReceive('getThermometers')
            ->once()
            ->andReturn([]);

        // Act
        $data = $this->widget->getData();

        // Assert - Structure des données
        $this->assertIsArray($data);
        $this->assertArrayHasKey('thermometers', $data);
        $this->assertArrayHasKey('config', $data);

        $this->assertEquals([], $data['thermometers']);
        $this->assertIsArray($data['config']);
    }

    public function test_widget_render(): void
    {
        $testThermometers = [
            'id' => 'thermo_complete'
        ];

        $this->mockService
            ->shouldReceive('getThermometers')
            ->once()
            ->andReturn($testThermometers);

        // Act
        $html = $this->widget->render();

        // Assert - Structure HTML de base
        $this->assertStringContainsString('thermometers-dynamic-container', $html);


        // Assert - Injection JavaScript complète
        $this->assertStringContainsString('window.thermometersState', $html);
        $this->assertStringContainsString('window.thermometersWidgetConfig', $html);

        // Assert - Thermomètre complet
        $this->assertStringContainsString('"id":"thermo_complete"', $html);
    }
}
