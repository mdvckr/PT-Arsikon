<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
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

        // 1. Sembunyikan informasi server / framework dari header
        if (config('security.headers.remove_x_powered_by', true)) {
            $response->headers->remove('X-Powered-By');
            $response->headers->remove('Server');
            if (function_exists('header_remove')) {
                @header_remove('X-Powered-By');
                @header_remove('Server');
            }
        }

        // 2. Proteksi Clickjacking (Master Plan v2 Layer 3.4)
        $xFrameOptions = config('security.headers.x_frame_options', 'DENY');
        $response->headers->set('X-Frame-Options', $xFrameOptions);

        // 3. Mencegah MIME-type Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 4. Proteksi XSS pada browser warisan
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 5. Referrer Policy aman
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 6. Permissions Policy (Membatasi akses sensor & hardware perangkat)
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

        // 7. Batasi kebijakan lintas-domain untuk file seperti Flash / PDF
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // 8. HTTP Strict Transport Security (HSTS) jika koneksi HTTPS
        if ($request->isSecure() && config('security.headers.hsts_enabled', true)) {
            $hsts = 'max-age=' . config('security.headers.hsts_max_age', 31536000);
            if (config('security.headers.hsts_include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }
            if (config('security.headers.hsts_preload', false)) {
                $hsts .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        // 9. Content Security Policy (CSP) - Default Report-Only (Master Plan v2 Layer 3.4)
        if (config('security.headers.csp.enabled', true)) {
            $headerName = config('security.headers.csp.report_only', true)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $policy = config('security.headers.csp.policy');
            if ($policy) {
                $response->headers->set($headerName, $policy);
            }
        }

        return $response;
    }
}
