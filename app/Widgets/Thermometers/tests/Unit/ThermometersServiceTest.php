<?php

namespace App\Widgets\Thermometers\Tests\Unit;

use App\Widgets\Thermometers\App\Services\ThermometersService;
use App\Services\TuyaService;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Cache;

class ThermometersServiceTest extends TestCase
{
    private ThermometersService $service;
    private Mockery\MockInterface $tuyaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->tuyaServiceMock = Mockery::mock(TuyaService::class);
        $this->service = new ThermometersService($this->tuyaServiceMock);

        $this->mockGetAllDevices = [
            [
                'active_time' => 1735306972,
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
                    ['code' => 'va_temperature', 'value' => 260],
                    ['code' => 'va_humidity', 'value' => 46],
                    ['code' => 'battery_percentage', 'value' => 79],
                    ['code' => 'temp_unit_convert', 'value' => 'c'],
                    ['code' => 'maxtemp_set', 'value' => 390],
                    ['code' => 'minitemp_set', 'value' => 0],
                    ['code' => 'maxhum_set', 'value' => 60],
                    ['code' => 'minihum_set', 'value' => 20],
                    ['code' => 'temp_alarm', 'value' => 'cancel'],
                    ['code' => 'hum_alarm', 'value' => 'cancel'],
                    ['code' => 'temp_sensitivity', 'value' => 3],
                    ['code' => 'hum_sensitivity', 'value' => 3]
                ],
                'sub' => '',
                'time_zone' => '+02:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754252995,
                'uuid' => '978dd362176808ea'
            ],
            [
                'active_time' => 1729027437,
                'biz_type' => 0,
                'category' => 'wsdcg',
                'create_time' => 1728047280,
                'icon' => 'smart/icon/ay1574490117625MIxZY/f36a9d707612825b36fa56c607965fca.png',
                'id' => 'bfdb6ddbf1877a9d68q3jb',
                'ip' => '82.67.95.204',
                'lat' => 45.0537,
                'local_key' => '^(`vW~~r{W~W0J=L',
                'lon' => -0.611,
                'model' => 'TH08',
                'name' => '[Salle de bain] Thermomètre',
                'online' => 1,
                'owner_id' => 182289232,
                'product_id' => '43ykthb7hebzsuaf',
                'product_name' => 'Temperature & Humidity Sensor',
                'status' => [
                    ['code' => 'va_temperature', 'value' => 250],
                    ['code' => 'va_humidity', 'value' => 51],
                    ['code' => 'battery_percentage', 'value' => 62],
                    ['code' => 'temp_unit_convert', 'value' => 'c'],
                    ['code' => 'maxtemp_set', 'value' => 390],
                    ['code' => 'minitemp_set', 'value' => 0],
                    ['code' => 'maxhum_set', 'value' => 60],
                    ['code' => 'minihum_set', 'value' => 20],
                    ['code' => 'temp_alarm', 'value' => 'cancel'],
                    ['code' => 'hum_alarm', 'value' => 'cancel'],
                    ['code' => 'temp_sensitivity', 'value' => 3],
                    ['code' => 'hum_sensitivity', 'value' => 3]
                ],
                'sub' => '',
                'time_zone' => '+02:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754252934,
                'uuid' => '171b4c85e4590b68'
            ],
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
                    ['code' => 'work_mode', 'value' => 'white'],
                    ['code' => 'bright_value_v2', 'value' => 200],
                    ['code' => 'temp_value_v2', 'value' => 0],
                    ['code' => 'colour_data_v2', 'value' => '{"h":0,"s":1000,"v":1000}']
                ],
                'sub' => '',
                'time_zone' => '+01:00',
                'uid' => 'eu17066525044470NFdg',
                'update_time' => 1754240037,
                'uuid' => '0449f6680e9dcc36'
            ]
        ];

        $this->mockGetDevices = [
            'bff7644e588d518b595vg7' => [
                'id' => 'bff7644e588d518b595vg7',
                'name' => 'Chambre enfants',
                'online' => 1,
                'status' => [
                    ['code' => 'va_temperature', 'value' => 260],
                    ['code' => 'va_humidity', 'value' => 46],
                    ['code' => 'battery_percentage', 'value' => 79],
                    ['code' => 'temp_unit_convert', 'value' => 'c'],
                    ['code' => 'maxtemp_set', 'value' => 390],
                    ['code' => 'minitemp_set', 'value' => 0],
                    ['code' => 'maxhum_set', 'value' => 60],
                    ['code' => 'minihum_set', 'value' => 20],
                    ['code' => 'temp_alarm', 'value' => 'cancel'],
                    ['code' => 'hum_alarm', 'value' => 'cancel'],
                    ['code' => 'temp_sensitivity', 'value' => 3],
                    ['code' => 'hum_sensitivity', 'value' => 3]
                ]
            ],
            'bfdb6ddbf1877a9d68q3jb' => [
                'id' => 'bfdb6ddbf1877a9d68q3jb',
                'name' => 'Salle de bain',
                'online' => 1,
                'status' => [
                    ['code' => 'va_temperature', 'value' => 250],
                    ['code' => 'va_humidity', 'value' => 51],
                    ['code' => 'battery_percentage', 'value' => 62],
                    ['code' => 'temp_unit_convert', 'value' => 'c'],
                    ['code' => 'maxtemp_set', 'value' => 390],
                    ['code' => 'minitemp_set', 'value' => 0],
                    ['code' => 'maxhum_set', 'value' => 60],
                    ['code' => 'minihum_set', 'value' => 20],
                    ['code' => 'temp_alarm', 'value' => 'cancel'],
                    ['code' => 'hum_alarm', 'value' => 'cancel'],
                    ['code' => 'temp_sensitivity', 'value' => 3],
                    ['code' => 'hum_sensitivity', 'value' => 3]
                ]
            ]
        ];

        $this->mockGetThermometers = [
            'bff7644e588d518b595vg7' => [
                'temperature' => 26,
                'humidity' => 46,
                'heat_index' => 26.4,
                'thermal_emoji' => '🙂',
                'temperature_color' => '#27ae60',
                'humidity_color' => '#5cb85c',
                'online' => 1,
                'id' => 'bff7644e588d518b595vg7',
                'name' => 'Chambre enfants',
                'battery' => 79
            ],
            'bfdb6ddbf1877a9d68q3jb' => [
                'temperature' => 25,
                'humidity' => 51,
                'heat_index' => 25.9,
                'thermal_emoji' => '🙂',
                'temperature_color' => '#27ae60',
                'humidity_color' => '#5cb85c',
                'online' => 1,
                'id' => 'bfdb6ddbf1877a9d68q3jb',
                'name' => 'Salle de bain',
                'battery' => 62
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getDevices(): void
    {
        $this->tuyaServiceMock
            ->shouldReceive('getAllDevices')
            ->once()
            ->andReturn($this->mockGetAllDevices);

        $this->assertEquals($this->mockGetDevices, $this->service->getDevices());
    }

    public function test_getThermometers(): void
    {
        $this->tuyaServiceMock
            ->shouldReceive('getAllDevices')
            ->once()
            ->andReturn($this->mockGetAllDevices);

        $this->assertEquals($this->mockGetThermometers, $this->service->getThermometers());
    }
}
