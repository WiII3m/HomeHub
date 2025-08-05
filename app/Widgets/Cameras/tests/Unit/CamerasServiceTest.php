<?php

namespace App\Widgets\Cameras\Tests\Unit;

use App\Widgets\Cameras\App\Services\CamerasService;
use App\Services\TuyaService;
use Tests\TestCase;
use Mockery;

class CamerasServiceTest extends TestCase
{
    private CamerasService $service;
    private Mockery\MockInterface $tuyaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tuyaServiceMock = Mockery::mock(TuyaService::class);
        $this->service = new CamerasService($this->tuyaServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getCameras(): void
    {
        $mockDevices = [
            [
                'active_time' => 1735306972,
                'biz_type' => 0,
                'category' => 'dj',
                'create_time' => 1706814753,
                'icon' => 'smart/icon/ay15431996119293jn9C/7b3b3f7fab929733a601706def946f77.png',
                'id' => 'bf49119e426de0dbadciax',
                'ip' => '82.67.95.204',
                'lat' => 45.08,
                'local_key' => '8-]<]q1FY6iBJ&*s',
                'lon' => -0.5699,
                'model' => '',
                'name' => 'Spot nº2',
                'online' => 1,
                'owner_id' => 182289232,
                'product_id' => 'beh2vselu6vwlwmu',
                'product_name' => 'PAR16-GL-WIFILIC-TY-RGBCW(970710)',
                'status' => [
                    ['code' => 'switch_led', 'value' => ''],
                    ['code' => 'work_mode', 'value' => 'white']
                ],
                'sub' => '',
                'time_zone' => '+01:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754259356,
                'uuid' => '0449f6680e9dcc36'
            ],
            [
                'active_time' => 1752774186,
                'biz_type' => 0,
                'category' => 'sp',
                'create_time' => 1752774186,
                'icon' => 'smart/icon/ay1541056239985fDGjj/19c157bb1c72c20bf2b6b38b0edf3f75.png',
                'id' => 'bfddac4d500c3243d9nhzw',
                'ip' => '82.67.95.204',
                'lat' => 45.05,
                'local_key' => 'z}q2Vz]=Y;Z.SBNq',
                'lon' => -0.61,
                'model' => '5525002001',
                'name' => 'Caméra Rez de chaussé',
                'online' => false,
                'owner_id' => 182289232,
                'product_id' => 'ykkdlv30vpl4lisy',
                'product_name' => 'LSC Smart Camera PTZ Dualband indoor',
                'status' => [
                    ['code' => 'basic_flip', 'value' => ''],
                    ['code' => 'basic_osd', 'value' => 1],
                    ['code' => 'basic_private', 'value' => 1],
                    ['code' => 'motion_sensitivity', 'value' => 1],
                    ['code' => 'basic_nightvision', 'value' => 0],
                    ['code' => 'motion_switch', 'value' => 1],
                    ['code' => 'record_switch', 'value' => 1]
                ],
                'sub' => '',
                'time_zone' => '+02:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1753227523,
                'uuid' => 'ybgdd7ab85b7baf6da3a'
            ],
            [
                'active_time' => 1729028388,
                'biz_type' => 0,
                'category' => 'sp',
                'create_time' => 1729028388,
                'icon' => 'smart/icon/ay1543031096643TGWCa/718b444d7eb885a1b949031be842a24b.png',
                'id' => 'bf69c655d66cd06c74qjgg',
                'ip' => '82.67.95.204',
                'lat' => 45.08,
                'local_key' => ';5LE~&t:FQ~F1',
                'lon' => -0.5699,
                'model' => '5525001200',
                'name' => 'Caméra Chambre enfants',
                'online' => true,
                'owner_id' => 182289232,
                'product_id' => '5egzavnxtqsczrho',
                'product_name' => 'LSC PTZ Camera',
                'status' => [
                    ['code' => 'basic_flip', 'value' => ''],
                    ['code' => 'basic_osd', 'value' => 1],
                    ['code' => 'basic_private', 'value' => ''],
                    ['code' => 'motion_sensitivity', 'value' => 0],
                    ['code' => 'basic_nightvision', 'value' => 0],
                    ['code' => 'motion_switch', 'value' => ''],
                    ['code' => 'record_switch', 'value' => 1]
                ],
                'sub' => '',
                'time_zone' => '+02:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754254017,
                'uuid' => 'ybgd46ebadb38a210773'
            ],
            [
                'active_time' => 1729028160,
                'biz_type' => 0,
                'category' => 'wsdcg',
                'create_time' => 1728047023,
                'icon' => 'smart/icon/ay1574490117625MIxZY/f36a9d707612825b36fa56c607965fca.png',
                'id' => 'bff7644e588d518b595vg7',
                'ip' => '82.67.95.204',
                'lat' => 45.0537,
                'local_key' => '-/b@ytC1(ow*S^Pq',
                'lon' => -0.611,
                'model' => 'TH08',
                'name' => '[Chambre enfants] Thermomètre',
                'online' => 1,
                'owner_id' => 182289232,
                'product_id' => '43ykthb7hebzsuaf',
                'product_name' => 'Temperature & Humidity Sensor',
                'status' => [
                    ['code' => 'va_temperature', 'value' => 233],
                    ['code' => 'va_humidity', 'value' => 56],
                    ['code' => 'battery_percentage', 'value' => 75]
                ],
                'sub' => '',
                'time_zone' => '+02:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754294591,
                'uuid' => '978dd362176808ea'
            ]
        ];

        $expected = [
            'bfddac4d500c3243d9nhzw' => [
                'id' => 'bfddac4d500c3243d9nhzw',
                'name' => 'Rez de chaussé',
                'online' => false,
                'basic_private' => 1
            ],
            'bf69c655d66cd06c74qjgg' => [
                'id' => 'bf69c655d66cd06c74qjgg',
                'name' => 'Chambre enfants',
                'online' => true,
                'basic_private' => ''
            ]
        ];

        $this->tuyaServiceMock
            ->shouldReceive('getAllDevices')
            ->once()
            ->andReturn($mockDevices);

        $this->assertEquals($expected, $this->service->getCameras());
    }

    public function test_toggleBasicPrivate(): void
    {
        // Test 1: Activer caméra (enable=true -> privacy=false)
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v1.0/devices/cam_1/commands', [
                'commands' => [
                    [
                        'code' => 'basic_private',
                        'value' => false // enable=true -> privacy=false
                    ]
                ]
            ])
            ->once()
            ->andReturn(['success' => true]);

        $result1 = $this->service->toggleBasicPrivate(true, 'cam_1');
        $this->assertTrue($result1);

        // Test 2: Désactiver caméra (enable=false -> privacy=true)
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v1.0/devices/cam_2/commands', [
                'commands' => [
                    [
                        'code' => 'basic_private',
                        'value' => true // enable=false -> privacy=true
                    ]
                ]
            ])
            ->once()
            ->andReturn(['success' => true]);

        $result2 = $this->service->toggleBasicPrivate(false, 'cam_2');
        $this->assertTrue($result2);
    }

    public function test_getCameraStreamUrl(): void
    {
        $this->tuyaServiceMock
            ->shouldReceive('makeRequest')
            ->with('POST', '/v1.0/devices/cam_1/stream/actions/allocate', [
                'type' => 'hls'
            ])
            ->once()
            ->andReturn(['url' => 'https://stream.example.com/cam_1.m3u8']);

        $result = $this->service->getCameraStreamUrl('cam_1');
        $this->assertEquals('https://stream.example.com/cam_1.m3u8', $result);
    }

    public function test_controlCameraPtz(): void
    {
        $validDirections = ['UP', 'DOWN', 'LEFT', 'RIGHT', 'STOP'];

        // Test toutes les directions valides
        foreach ($validDirections as $direction) {
            $this->tuyaServiceMock
                ->shouldReceive('makeRequest')
                ->with('POST', '/v1.0/cameras/cam_1/configs/ptz', [
                    'value' => $direction
                ])
                ->once()
                ->andReturn(['success' => true]);

            $result = $this->service->controlCameraPtz($direction, 'cam_1');
            $this->assertTrue($result);
        }
    }

}
