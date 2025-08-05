<?php

namespace App\Widgets\HelloWorld\Tests\Feature;

use Tests\TestCase;
use App\Widgets\HelloWorld\HelloWorldWidget;
use App\Widgets\HelloWorld\App\Services\HelloWorldService;
use Mockery;

/**
 * Tests d'intégration pour HelloWorldWidget
 * Teste le rendu HTML complet avec données mockées
 */
class HelloWorldWidgetTest extends TestCase
{
    private HelloWorldWidget $widget;
    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock du service pour contrôler les données
        $this->mockService = Mockery::mock(HelloWorldService::class);

        // Créer l'instance du widget avec le mock
        $this->widget = new HelloWorldWidget($this->mockService);
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
        $mockDevices = [];

        for ($i = 1; $i <= 4; $i++) {
            $mockDevices["device_$i"] = [
                'id' => "device_$i",
                'name' => "Device $i",
                'online' => rand(0,1) == 1,
            ];
        }

        $this->mockService
            ->shouldReceive('getDevices')
            ->once()
            ->andReturn($mockDevices);

        // Act
        $data = $this->widget->getData();

        // Assert - Structure des données
        $this->assertIsArray($data);
        $this->assertArrayHasKey('devices', $data);
        $this->assertArrayHasKey('config', $data);

        $this->assertEquals($mockDevices, $data['devices']);
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
        // Arrange - Devices avec états variés
        $testDevices = [
            'device_online' => [
                'id' => 'device_online',
                'name' => 'Device Online',
                'online' => true
            ],
            'device_offline' => [
                'id' => 'device_offline',
                'name' => 'Device Offline',
                'online' => false
            ],
            'device_test' => [
                'id' => 'device_test',
                'name' => 'Mon Device Test',
                'online' => true
            ]
        ];

        $this->mockService
            ->shouldReceive('getDevices')
            ->once()
            ->andReturn($testDevices);

        // Act
        $html = $this->widget->render();

        // Assert - Structure HTML de base
        $this->assertStringContainsString('<div class="panel panel-default">', $html);
        $this->assertStringContainsString('<div class="panel-body">', $html);

        // Assert - Header avec configuration
        $this->assertStringContainsString('class="devices-list', $html);

        // Assert - Devices online
        $this->assertStringContainsString('data-device-id="device_online"', $html);
        $this->assertStringContainsString('data-device-id="device_test"', $html);
        $this->assertStringContainsString('En ligne', $html);
        $this->assertStringContainsString('online', $html);
        $this->assertStringContainsString('Device de démonstration', $html);

        // Assert - Devices offline
        $this->assertStringContainsString('data-device-id="device_offline"', $html);
        $this->assertStringContainsString('Hors ligne', $html);
        $this->assertStringContainsString('offline', $html);

        // Assert - Noms des devices
        $this->assertStringContainsString('Device Online', $html);
        $this->assertStringContainsString('Device Offline', $html);
        $this->assertStringContainsString('Mon Device Test', $html);

        // Assert - Compter les devices rendus
        $deviceCount = substr_count($html, 'data-device-id=');
        $this->assertEquals(3, $deviceCount, 'Should render exactly 3 devices');
    }
}
