<?php

namespace App\Widgets\HelloWorld\Tests\Unit;

use Tests\TestCase;
use App\Widgets\HelloWorld\App\Services\HelloWorldService;

class HelloWorldServiceTest extends TestCase
{
    private HelloWorldService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HelloWorldService();
    }

    public function test_getDevices(): void
    {
        $result = $this->service->getDevices();

        // Assert structure
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        // Assert devices structure
        foreach ($result as $deviceId => $device) {
            $this->assertStringStartsWith('device_', $deviceId);
            $this->assertArrayHasKey('id', $device);
            $this->assertArrayHasKey('name', $device);
            $this->assertArrayHasKey('online', $device);
            $this->assertIsBool($device['online']);
        }
    }
}
