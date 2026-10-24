<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Hapus header yang membocorkan identitas server / framework
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
            @header_remove('Server');
        }

        // 2. Anti-Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Anti-MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 4. XSS Protection browser warisan
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 5. Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 6. Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');

        return $response;
    }
}
