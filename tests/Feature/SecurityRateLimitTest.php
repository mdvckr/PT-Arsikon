<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['anti-ddos'])->get('/_test/ping', function () {
            return response('pong');
        });

        Route::middleware(['throttle:strict-action'])->get('/_test/strict', function () {
            return response('strict-ok');
        });
    }

    public function test_global_security_headers_are_attached(): void
    {
        $response = $this->get('/_test/ping');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_anti_ddos_middleware_blocks_bad_user_agent(): void
    {
        config(['security.anti_ddos.enabled' => true]);
        config(['security.anti_ddos.bypass_in_testing' => false]);
        config(['security.anti_ddos.ip_whitelist' => []]);

        $response = $this->withHeaders([
            'User-Agent' => 'sqlmap/1.7#stable',
        ])->get('/_test/ping');

        $response->assertStatus(403);
    }

    public function test_strict_action_rate_limiter_throttles_with_429(): void
    {
        config(['security.rate_limits.strict_actions' => 2]);

        $this->get('/_test/strict')->assertStatus(200);
        $this->get('/_test/strict')->assertStatus(200);

        // Request ke-3 harus 429
        $response = $this->get('/_test/strict');
        $response->assertStatus(429);
        $this->assertTrue($response->headers->has('Retry-After'));
    }
}
