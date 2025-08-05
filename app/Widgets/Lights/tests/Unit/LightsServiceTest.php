<?php

namespace App\Widgets\Lights\Tests\Unit;

use App\Widgets\Lights\App\Services\LightsService;
use App\Services\TuyaService;
use Tests\TestCase;
use Mockery;

class LightsServiceTest extends TestCase
{
    private LightsService $service;
    private Mockery\MockInterface $tuyaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tuyaServiceMock = Mockery::mock(TuyaService::class);
        $this->service = new LightsService($this->tuyaServiceMock);

        $this->mockRooms = [
            [
                "name" => "Salon",
                "room_id" => "1234"
            ],
            [
                "name" => "Chambre",
                "room_id" => "5678"
            ]
        ];

        $this->mockDevices = [
            "chambre" => [
                [
                    'active_time' => 1735306687,
                    'biz_type' => 0,
                    'category' => 'dj',
                    'create_time' => 1706985797,
                    'icon' => 'smart/icon/ay1541056239985fDGjj/16558598464f725bd03c7.png',
                    'id' => 'bf61fc088c0790193ezmxl',
                    'ip' => '82.67.95.204',
                    'lat' => 45.05,
                    'local_key' => 'E[J0\'p?I25][UNXj',
                    'lon' => -0.61,
                    'model' => 'MEKA',
                    'name' => 'Spot n°4',
                    'online' => 1,
                    'owner_id' => 182289232,
                    'product_id' => 'klk7drvbtapxmo8t',
                    'product_name' => 'LSC Glass GU10 RGBCCT',
                    'status' => [
                        ['code' => 'switch_led', 'value' => 1],
                        ['code' => 'work_mode', 'value' => 'white'],
                        ['code' => 'bright_value_v2', 'value' => 10],
                        ['code' => 'temp_value_v2', 'value' => 0],
                        ['code' => 'colour_data_v2', 'value' => '{"h":0,"s":1000,"v":1000}']
                    ],
                    'sub' => '',
                    'time_zone' => '+01:00',
                    'uid' => 'eu17066525044470NFdg',
                    'update_time' => 1754247705,
                    'uuid' => '9f94c43ee3bdbb97'
                ],
                [
                    'active_time' => 1735306421,
                    'biz_type' => 0,
                    'category' => 'dj',
                    'create_time' => 1726830421,
                    'icon' => 'smart/icon/bay1628652352170c2wS/ebc84cdd571f2750d5263fb3148f4799.jpg',
                    'id' => 'bfe7527ec6a35484abmwyq',
                    'ip' => '82.67.95.204',
                    'lat' => 45.0537,
                    'local_key' => 'W{aQ+GJSXfb_hx!_',
                    'lon' => -0.611,
                    'model' => 'cb2l',
                    'name' => 'Hotte ampoule 1/2',
                    'online' => '',
                    'owner_id' => 182289232,
                    'product_id' => 'k3okx0w3bsgmindp',
                    'product_name' => 'Smart Bulb',
                    'status' => [
                        ['code' => 'switch_led', 'value' => 1],
                        ['code' => 'work_mode', 'value' => 'white'],
                        ['code' => 'bright_value_v2', 'value' => 400],
                        ['code' => 'temp_value_v2', 'value' => 0],
                        ['code' => 'colour_data_v2', 'value' => '{"h":0,"s":1000,"v":1000}']
                    ],
                    'sub' => '',
                    'time_zone' => '+01:00',
                    'uid' => 'eu17066525044470NFdg',
                    'update_time' => 1754140879,
                    'uuid' => 'c9b5c962ab836a67'
                ]
            ],
            "salon" => [
                [
                    'active_time' => 1752053176,
                    'biz_type' => 0,
                    'category' => 'dj',
                    'create_time' => 1706814911,
                    'icon' => 'smart/icon/ay1541056239985fDGjj/16558598464f725bd03c7.png',
                    'id' => 'bfa84493bed9fe172aabvp',
                    'ip' => '82.67.95.204',
                    'lat' => 45.08,
                    'local_key' => 'TZOZd!lH3Yl[H.hV',
                    'lon' => -0.5699,
                    'model' => 'MEKA',
                    'name' => 'Spot n°1',
                    'online' => 1,
                    'owner_id' => 182289232,
                    'product_id' => 'klk7drvbtapxmo8t',
                    'product_name' => 'LSC Glass GU10 RGBCCT',
                    'status' => [
                        ['code' => 'switch_led', 'value' => ''],
                        ['code' => 'work_mode', 'value' => 'white'],
                        ['code' => 'bright_value_v2', 'value' => 50],
                        ['code' => 'temp_value_v2', 'value' => 0],
                        ['code' => 'colour_data_v2', 'value' => '{"h":0,"s":1000,"v":1000}'],
                        ['code' => 'scene_data_v2', 'value' => '{"scene_num":1,"scene_units":[{"bright":200,"h":0,"s":0,"temperature":0,"unit_change_mode":"static","unit_gradient_duration":13,"unit_switch_duration":14,"v":0}]}']
                    ],
                    'sub' => '',
                    'time_zone' => '+02:00',
                    'uid' => 'eu17066525044470NFdg',
                    'update_time' => 1754247784,
                    'uuid' => '87409550ce6b8284'
                ],
                [
                    'active_time' => 1735306421,
                    'biz_type' => 0,
                    'category' => 'dj',
                    'create_time' => 1726830607,
                    'icon' => 'smart/icon/bay1628652352170c2wS/ebc84cdd571f2750d5263fb3148f4799.jpg',
                    'id' => 'bff9d1170bd05e35b2objq',
                    'ip' => '82.67.95.204',
                    'lat' => 45.08,
                    'local_key' => 'L{(OZ^Ir?~ss?<`j',
                    'lon' => -0.5699,
                    'model' => 'cb2l',
                    'name' => 'Hotte ampoule 2/2',
                    'online' => 1,
                    'owner_id' => 182289232,
                    'product_id' => 'k3okx0w3bsgmindp',
                    'product_name' => 'Smart Bulb',
                    'status' => [
                        ['code' => 'switch_led', 'value' => 1],
                        ['code' => 'work_mode', 'value' => 'white'],
                        ['code' => 'bright_value_v2', 'value' => 10],
                        ['code' => 'temp_value_v2', 'value' => 0],
                        ['code' => 'colour_data_v2', 'value' => '{"h":0,"s":1000,"v":1000}']
                    ],
                    'sub' => '',
                    'time_zone' => '+01:00',
                    'uid' => 'eu17066525044470NFdg',
                    'update_time' => 1754253467,
                    'uuid' => '340ce7c7c918a380'
                ],
                [
                    'active_time' => 1752504074,
                    'biz_type' => 0,
                    'category' => 'dd',
                    'create_time' => 1706978760,
                    'icon' => 'smart/icon/ay1541056239985fDGjj/bb358e2551cd67aed86a27906d1f719f.png',
                    'id' => 'bf8bf3df85d5bc90b9bvgv',
                    'ip' => '82.67.95.204',
                    'lat' => 45.0537,
                    'local_key' => 'K#{NS2}B6tS]HqPm',
                    'lon' => -0.611,
                    'model' => '6125000400',
                    'name' => 'Évier',
                    'online' => 1,
                    'owner_id' => 182289232,
                    'product_id' => 'rabmlrrq3qjivwxr',
                    'product_name' => 'LSC smart ledstrip rgbic+cct 5m',
                    'status' => [
                        ['code' => 'switch_led', 'value' => ''],
                        ['code' => 'work_mode', 'value' => 'white'],
                        ['code' => 'bright_value', 'value' => 50],
                        ['code' => 'temp_value', 'value' => 0],
                        ['code' => 'colour_data', 'value' => '{"h":223,"s":489,"v":1000}']
                    ],
                    'sub' => '',
                    'time_zone' => '+02:00',
                    'uid' => 'eu17066525044470NFdg',
                    'update_time' => 1754249252,
                    'uuid' => '5794926e0bfd1cd6'
                ]
            ]
        ];

        $this->expectedGetLightsByRoom = [
            [
                'room_id' => '1234',
                'room_name' => 'Salon',
                'lights' => [
                    [
                        'id' => 'bfa84493bed9fe172aabvp',
                        'name' => 'Spot n°1',
                        'online' => 1,
                        'switch_state' => false
                    ],
                    [
                        'id' => 'bff9d1170bd05e35b2objq',
                        'name' => 'Hotte ampoule 2/2',
                        'online' => 1,
                        'switch_state' => true
                    ],
                    [
                        'id' => 'bf8bf3df85d5bc90b9bvgv',
                        'name' => 'Évier',
                        'online' => 1,
                        'switch_state' => false
                    ]
                ]
            ],
            [
                'room_id' => '5678',
                'room_name' => 'Chambre',
                'lights' => [
                    [
                        'id' => 'bf61fc088c0790193ezmxl',
                        'name' => 'Spot n°4',
                        'online' => 1,
                        'switch_state' => true
                    ],
                    [
                        'id' => 'bfe7527ec6a35484abmwyq',
                        'name' => 'Hotte ampoule 1/2',
                        'online' => '',
                        'switch_state' => true
                    ]
                ]
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getLightsByRoom(): void
    {
        $this->tuyaServiceMock
            ->shouldReceive('getRooms')
            ->once()
            ->andReturn($this->mockRooms);

        $this->tuyaServiceMock
            ->shouldReceive('getRoomDevices')
            ->with('1234')
            ->once()
            ->andReturn($this->mockDevices['salon']);

        $this->tuyaServiceMock
            ->shouldReceive('getRoomDevices')
            ->with('5678')
            ->once()
            ->andReturn($this->mockDevices['chambre']);

        $this->assertEquals($this->expectedGetLightsByRoom, $this->service->getLightsByRoom());
    }

    public function test_toggleLight(): void
    {
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v1.0/devices/light_1/commands', [
                'commands' => [
                    ['code' => 'switch_led', 'value' => true]
                ]
            ])
            ->once()
            ->andReturn(['success' => true]);

        $result = $this->service->toggleLight('light_1', true);
        $this->assertTrue($result);

        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v1.0/devices/light_2/commands', [
                'commands' => [
                    ['code' => 'switch_led', 'value' => false]
                ]
            ])
            ->once()
            ->andReturn(['success' => true]);

        $result = $this->service->toggleLight('light_2', false);
        $this->assertTrue($result);
    }
}
