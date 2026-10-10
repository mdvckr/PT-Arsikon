<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_attached(): void
    {
        $middleware = new SecurityHeaders();
        $request = Request::create('/test-endpoint', 'GET');

        $response = $middleware->handle($request, function () {
            $resp = new Response('OK');
            $resp->headers->set('X-Powered-By', 'PHP/8.2.12');
            return $resp;
        });

        $this->assertFalse($response->headers->has('X-Powered-By'));
        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    public function test_hsts_header_is_attached_on_https(): void
    {
        $middleware = new SecurityHeaders();
        $request = Request::create('https://example.com/test-endpoint', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertTrue($response->headers->has('Strict-Transport-Security'));
        $this->assertStringContainsString('max-age=', $response->headers->get('Strict-Transport-Security'));
    }
}
