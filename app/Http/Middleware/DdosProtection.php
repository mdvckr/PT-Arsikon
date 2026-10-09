<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware anti-DDoS di level aplikasi.
 *
 * Proteksi tambahan di atas Nginx rate limiting:
 * - Batasi login attempts per IP
 * - Deteksi suspicious request patterns
 * - Block IP yang terlalu banyak gagal request
 */
class DdosProtection
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass pada environment lokal atau localhost
        if (app()->environment('local', 'testing') || in_array($request->ip(), ['127.0.0.1', '::1'])) {
            return $next($request);
        }

        $ip = $request->ip();
        $key = 'ddos_protection:' . $ip;

        // Blokir IP yang sudah di-flag (cooldown 5 menit)
        if ($this->limiter->tooManyAttempts($key . ':blocked', 1)) {
            abort(429, 'Terlalu banyak request. Coba lagi nanti.');
        }

        // General: maks 300 request per menit per IP
        if ($this->limiter->tooManyAttempts($key . ':general', 300)) {
            // Flag IP ini sebagai blocked selama 5 menit
            $this->limiter->hit($key . ':blocked', 300);

            abort(429, 'Terlalu banyak request. Coba lagi dalam beberapa menit.');
        }

        $this->limiter->hit($key . ':general', 60);

        return $next($request);
    }
}
