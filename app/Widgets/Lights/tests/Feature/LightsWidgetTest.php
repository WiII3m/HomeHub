<?php
namespace App\Widgets\Lights\Tests\Feature;

use App\Widgets\Lights\LightsWidget;
use App\Widgets\Lights\App\Services\LightsService;
use Tests\TestCase;
use Mockery;

class LightsWidgetTest extends TestCase
{
    private LightsWidget $widget;
    private Mockery\MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(LightsService::class);
        $this->widget = new LightsWidget($this->mockService);
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
        $mockLights = [
            [
                'room_id' => 'room_1',
                'room_name' => 'Test Room',
                'lights' => []
            ]
        ];

        $this->mockService
            ->shouldReceive('getLightsByRoom')
            ->once()
            ->andReturn($mockLights);

        $data = $this->widget->getData();

        // Assert - Structure des données
        $this->assertIsArray($data);
        $this->assertArrayHasKey('lightsByRoom', $data);
        $this->assertArrayHasKey('config', $data);

        $this->assertEquals($mockLights, $data['lightsByRoom']);
        $this->assertIsArray($data['config']);
    }

    public function test_widget_render(): void
    {
        $testLightsByRoom = [
            [
                'room_id' => 'salon_123',
                'room_name' => 'Salon',
                'lights' => [
                    [
                        'id' => 'light_online',
                        'name' => 'Lampe online',
                        'online' => true,
                        'switch_state' => true,
                        'status' => []
                    ],
                    [
                        'id' => 'light_offline',
                        'name' => 'Lampe offline',
                        'online' => false,
                        'switch_state' => false,
                        'status' => []
                    ]
                ]
            ],
            [
                'room_id' => 'cuisine_456',
                'room_name' => 'Cuisine',
                'lights' => [
                    [
                        'id' => 'light_kitchen',
                        'name' => 'Eclairage cuisine',
                        'online' => true,
                        'switch_state' => false,
                        'status' => []
                    ]
                ]
            ]
        ];

        $this->mockService
            ->shouldReceive('getLightsByRoom')
            ->once()
            ->andReturn($testLightsByRoom);

        // Act
        $html = $this->widget->render();

        // Assert - Container dynamique et injection JavaScript
        $this->assertStringContainsString('lights-dynamic-container', $html);
        $this->assertStringContainsString('window.lightsState', $html);
        $this->assertStringContainsString('<script>', $html);

        // Assert - Structure des données JSON
        $this->assertStringContainsString('"room_id":"salon_123"', $html);
        $this->assertStringContainsString('"room_name":"Salon"', $html);
        $this->assertStringContainsString('"room_id":"cuisine_456"', $html);
        $this->assertStringContainsString('"room_name":"Cuisine"', $html);
        $this->assertStringContainsString('"lights"', $html);

        // Assert - États variés des lumières
        $this->assertStringContainsString('"id":"light_online"', $html);
        $this->assertStringContainsString('"name":"Lampe online"', $html);
        $this->assertStringContainsString('"online":true', $html);
        $this->assertStringContainsString('"switch_state":true', $html);
        $this->assertStringContainsString('"id":"light_offline"', $html);
        $this->assertStringContainsString('"name":"Lampe offline"', $html);
        $this->assertStringContainsString('"online":false', $html);
        $this->assertStringContainsString('"switch_state":false', $html);
        $this->assertStringContainsString('"id":"light_kitchen"', $html);
        $this->assertStringContainsString('"name":"Eclairage cuisine"', $html);
    }
}
