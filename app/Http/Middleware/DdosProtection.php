<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Proteksi Anti-DDoS & HTTP Flood tingkat aplikasi.
 *
 * Fitur:
 * - Pemeriksaan IP Whitelist
 * - Deteksi Scanner/Bad Bot otomatis berdasarkan User-Agent
 * - Deteksi anomali lonjakan singkat (Burst Flood Detection)
 * - Batasan trafik berkelanjutan (Sustained Rate Limit)
 * - Karantina / Temporary IP Ban dengan cooldown dinamis
 * - Security Event Logging untuk audit keamanan
 * - Dukungan response RFC 6585 (Header Retry-After & 429 Too Many Requests)
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
        // 1. Periksa apakah proteksi diaktifkan
        if (!config('security.anti_ddos.enabled', true)) {
            return $next($request);
        }

        // 2. Bypass pada environment testing (kecuali ditest secara spesifik) atau IP whitelist
        $ip = $request->ip();
        $whitelist = config('security.anti_ddos.ip_whitelist', ['127.0.0.1', '::1']);

        if ((app()->environment('testing') && config('security.anti_ddos.bypass_in_testing', true)) || in_array($ip, $whitelist, true)) {
            return $next($request);
        }

        // 3. Deteksi Scanner / Bot Berbahaya via User-Agent
        if ($this->isMaliciousUserAgent($request)) {
            Log::channel('single')->warning("[DDoS/Bot Detection] Akses mencurigakan diblokir", [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);

            return $this->buildBlockedResponse($request, 3600, 'Akses ditolak: User-Agent teridentifikasi sebagai automated scanner.');
        }

        $baseKey = 'anti_ddos:' . sha1($ip);
        $blockedKey = $baseKey . ':quarantine';
        $burstKey = $baseKey . ':burst';
        $sustainedKey = $baseKey . ':sustained';

        $blockDuration = (int) config('security.anti_ddos.block_duration_seconds', 300);

        // 4. Periksa apakah IP sedang dalam masa karantina (Temporary Ban)
        if ($this->limiter->tooManyAttempts($blockedKey, 1)) {
            $retryAfter = $this->limiter->availableIn($blockedKey);
            return $this->buildThrottledResponse(
                $request,
                $retryAfter > 0 ? $retryAfter : $blockDuration,
                'IP Anda sedang dalam masa karantina keamanan akibat aktivitas mencurigakan berulang.'
            );
        }

        // 5. Deteksi Lonjakan Singkat (Burst Flood Detection)
        $burstLimit = (int) config('security.anti_ddos.burst_limit', 30);
        $burstWindow = (int) config('security.anti_ddos.burst_window_seconds', 10);

        if ($this->limiter->tooManyAttempts($burstKey, $burstLimit)) {
            // Karantina IP ke status blocked
            $this->limiter->hit($blockedKey, $blockDuration);

            Log::channel('single')->warning("[DDoS/Burst Flood] Ambang batas lonjakan terlampaui. IP dikarantina.", [
                'ip' => $ip,
                'hits' => $burstLimit,
                'window' => "{$burstWindow}s",
                'quarantine' => "{$blockDuration}s",
                'url' => $request->fullUrl(),
            ]);

            return $this->buildThrottledResponse(
                $request,
                $blockDuration,
                'Terdeteksi lonjakan request tidak normal (HTTP Flood). IP Anda sementara diblokir.'
            );
        }

        // 6. Batasan Trafik Berkelanjutan (Sustained Rate Limit)
        $sustainedLimit = (int) config('security.anti_ddos.sustained_limit', 300);
        $sustainedWindow = (int) config('security.anti_ddos.sustained_window_seconds', 60);

        if ($this->limiter->tooManyAttempts($sustainedKey, $sustainedLimit)) {
            $this->limiter->hit($blockedKey, $blockDuration);

            Log::channel('single')->warning("[DDoS/Sustained Flood] Ambang batas request berkelanjutan terlampaui.", [
                'ip' => $ip,
                'limit' => $sustainedLimit,
                'window' => "{$sustainedWindow}s",
                'url' => $request->fullUrl(),
            ]);

            return $this->buildThrottledResponse(
                $request,
                $blockDuration,
                'Terlalu banyak permintaan berkelanjutan. Silakan tunggu beberapa menit.'
            );
        }

        // Hit counter untuk monitoring
        $this->limiter->hit($burstKey, $burstWindow);
        $this->limiter->hit($sustainedKey, $sustainedWindow);

        /** @var Response $response */
        $response = $next($request);

        // Tambahkan header rate limit tracking pada response normal jika diperlukan
        $remainingSustained = max(0, $sustainedLimit - $this->limiter->attempts($sustainedKey));
        $response->headers->set('X-RateLimit-Limit', (string) $sustainedLimit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remainingSustained);

        return $response;
    }

    /**
     * Cek apakah User-Agent tergolong scanner / bot berbahaya.
     */
    protected function isMaliciousUserAgent(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        // Cek request POST/PUT/DELETE dengan User-Agent kosong
        if (config('security.anti_ddos.block_empty_user_agent_on_post', true)) {
            if (empty(trim($userAgent)) && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return true;
            }
        }

        if (empty($userAgent)) {
            return false;
        }

        $badAgents = config('security.anti_ddos.bad_user_agents', []);
        $lowerUa = strtolower($userAgent);

        foreach ($badAgents as $badAgent) {
            if (str_contains($lowerUa, strtolower($badAgent))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Membangun HTTP response 429 Too Many Requests yang terstandarisasi.
     */
    protected function buildThrottledResponse(Request $request, int $retryAfterSeconds, string $message): Response
    {
        $headers = [
            'Retry-After' => (string) $retryAfterSeconds,
            'X-RateLimit-Reset' => (string) (time() + $retryAfterSeconds),
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 429,
                'error' => 'Too Many Requests',
                'message' => $message,
                'retry_after_seconds' => $retryAfterSeconds,
            ], 429, $headers);
        }

        if (view()->exists('errors.429')) {
            return response()->view('errors.429', [
                'message' => $message,
                'retryAfter' => $retryAfterSeconds,
            ], 429, $headers);
        }

        return response($message, 429, $headers);
    }

    /**
     * Membangun HTTP response 403 Forbidden untuk bot berbahaya.
     */
    protected function buildBlockedResponse(Request $request, int $retryAfterSeconds, string $message): Response
    {
        $headers = [
            'Retry-After' => (string) $retryAfterSeconds,
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 403,
                'error' => 'Forbidden',
                'message' => $message,
            ], 403, $headers);
        }

        return response($message, 403, $headers);
    }
}
