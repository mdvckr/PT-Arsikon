<?php

namespace Tests\Unit;

use App\Http\Middleware\DdosProtection;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DdosProtectionTest extends TestCase
{
    public function test_blocks_malicious_user_agent(): void
    {
        config(['security.anti_ddos.enabled' => true]);
        config(['security.anti_ddos.bypass_in_testing' => false]);
        config(['security.anti_ddos.ip_whitelist' => []]);

        $limiter = $this->app->make(RateLimiter::class);
        $middleware = new DdosProtection($limiter);

        $request = Request::create('/test-endpoint', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.100',
            'HTTP_USER_AGENT' => 'sqlmap/1.5.2#stable',
        ]);

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_rate_limits_burst_floods(): void
    {
        config(['security.anti_ddos.enabled' => true]);
        config(['security.anti_ddos.bypass_in_testing' => false]);
        config(['security.anti_ddos.ip_whitelist' => []]);
        config(['security.anti_ddos.burst_limit' => 3]);
        config(['security.anti_ddos.burst_window_seconds' => 10]);

        $limiter = $this->app->make(RateLimiter::class);
        $middleware = new DdosProtection($limiter);

        $ip = '198.51.100.55';

        // Hit sampai ambang batas
        for ($i = 0; $i < 3; $i++) {
            $request = Request::create('/test-endpoint', 'GET', [], [], [], [
                'REMOTE_ADDR' => $ip,
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ]);
            $response = $middleware->handle($request, function () {
                return new Response('OK');
            });
            $this->assertEquals(200, $response->getStatusCode());
        }

        // Hit ke-4 (melebihi batas burst)
        $request = Request::create('/test-endpoint', 'GET', [], [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Retry-After'));
    }
}
