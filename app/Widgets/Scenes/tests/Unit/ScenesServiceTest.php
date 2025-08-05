<?php

namespace App\Widgets\Scenes\Tests\Unit;

use App\Widgets\Scenes\App\Services\ScenesService;
use App\Services\TuyaService;
use Tests\TestCase;
use Mockery;

class ScenesServiceTest extends TestCase
{
    private ScenesService $service;
    private Mockery\MockInterface $tuyaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tuyaServiceMock = Mockery::mock(TuyaService::class);
        $this->service = new ScenesService($this->tuyaServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getScenes(): void
    {
        $mockApiResponse = [
            'has_more' => '',
            'list' => [
                [
                    'id' => 'cGoWlNAQieUFkkDT',
                    'name' => 'LIGHT_BASE_SCENE_CLOSE',
                    'running_mode' => 'cloud',
                    'space_id' => 182289232,
                    'status' => 'enable',
                    'type' => 'scene'
                ],
                [
                    'id' => 'wdySVoywUlK2noxO',
                    'name' => 'Salon',
                    'running_mode' => 'cloud',
                    'space_id' => 182289232,
                    'status' => 'enable',
                    'type' => 'scene'
                ],
                [
                    'id' => 'DRVcEBYPjYDVEneK',
                    'name' => 'Cuisine',
                    'running_mode' => 'cloud',
                    'space_id' => 182289232,
                    'status' => 'enable',
                    'type' => 'scene'
                ]
            ],
            'total' => 3
        ];

        $this->tuyaServiceMock
            ->shouldReceive('getHomeId')
            ->andReturn('home_123');

        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('GET', '/v2.0/cloud/scene/rule', [], [
                'space_id' => 'home_123',
                'type' => 'scene'
            ])
            ->once()
            ->andReturn($mockApiResponse);

        $result = $this->service->getScenes();

        $expected = [
            [
                'id' => 'wdySVoywUlK2noxO',
                'name' => 'Salon',
                'status' => 'enable',
                'type' => 'scene'
            ],
            [
                'id' => 'DRVcEBYPjYDVEneK',
                'name' => 'Cuisine',
                'status' => 'enable',
                'type' => 'scene'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_triggerScene(): void
    {
        // Test succès
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v2.0/cloud/scene/rule/scene_123/actions/trigger')
            ->once()
            ->andReturn(['result' => 'success']);

        $result = $this->service->triggerScene('scene_123');
        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);

        // Test avec erreur API
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v2.0/cloud/scene/rule/invalid_scene/actions/trigger')
            ->once()
            ->andThrow(new \Exception('Scene not found'));

        $result = $this->service->triggerScene('invalid_scene');
        $this->assertFalse($result['success']);
        $this->assertIsString($result['error']);
    }
}
