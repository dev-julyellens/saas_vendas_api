<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/up');

        $response->assertOk();
    }

    public function test_api_root_returns_version(): void
    {
        $response = $this->getJson('/api');

        $response->assertOk()
            ->assertJsonPath('version', 'v1');
    }
}
