<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReverbBroadcastingConfigTest extends TestCase
{
    public function test_reverb_broadcasting_driver_and_channels_are_configured(): void
    {
        $this->assertEquals('reverb', config('broadcasting.default'));
        $this->assertNotNull(config('broadcasting.connections.reverb'));
        $this->assertNotEmpty(config('broadcasting.connections.reverb.key'));
    }
}

