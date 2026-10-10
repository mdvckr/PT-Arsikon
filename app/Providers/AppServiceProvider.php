<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \Laravel\Sanctum\Sanctum::ignoreMigrations();
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        // Cegah silent failure pada mass-assignment saat development/testing
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        \Illuminate\Support\Facades\Blade::directive('qty', function ($expression) {
            return "<?php echo format_quantity($expression); ?>";
        });

        // 1. Rate Limiter Login: Perlindungan bertingkat (Email + IP dan Global IP)
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = $request->ip();

            $accountAttempts = (int) config('security.rate_limits.login_per_minute', 5);
            $ipAttempts = (int) config('security.rate_limits.login_ip_per_minute', 15);

            return [
                // Batas percobaan login per kombinasi akun & IP
                Limit::perMinute($accountAttempts)
                    ->by($email . '|' . $ip)
                    ->response(function (Request $request, array $headers) {
                        $seconds = $headers['Retry-After'] ?? 60;
                        if ($request->expectsJson()) {
                            return response()->json([
                                'status' => 429,
                                'error' => 'Too Many Requests',
                                'message' => "Terlalu banyak percobaan masuk untuk akun ini. Coba lagi dalam {$seconds} detik.",
                                'retry_after_seconds' => (int) $seconds,
                            ], 429, $headers);
                        }
                        return back()->withInput($request->only('email', 'remember'))->withErrors([
                            'email' => "Terlalu banyak percobaan masuk untuk akun ini. Silakan coba lagi dalam {$seconds} detik.",
                        ]);
                    }),

                // Batas total percobaan login dari IP yang sama (mencegah credential stuffing banyak akun)
                Limit::perMinute($ipAttempts)
                    ->by('ip:' . $ip)
                    ->response(function (Request $request, array $headers) {
                        $seconds = $headers['Retry-After'] ?? 60;
                        if ($request->expectsJson()) {
                            return response()->json([
                                'status' => 429,
                                'error' => 'Too Many Requests',
                                'message' => "Terlalu banyak permintaan login dari koneksi Anda. Silakan coba lagi nanti.",
                                'retry_after_seconds' => (int) $seconds,
                            ], 429, $headers);
                        }
                        return back()->withInput($request->only('email', 'remember'))->withErrors([
                            'email' => "Terlalu banyak permintaan login dari koneksi Anda. Silakan tunggu {$seconds} detik.",
                        ]);
                    }),
            ];
        });

        // 2. Rate Limiter Pemulihan Sandi & Verifikasi
        RateLimiter::for('forgot_password', function (Request $request) {
            return Limit::perMinute((int) config('security.rate_limits.strict_actions', 5))
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    $seconds = $headers['Retry-After'] ?? 60;
                    return back()->withErrors([
                        'email' => "Permintaan reset password terlalu sering. Tunggu {$seconds} detik sebelum mencoba lagi.",
                    ]);
                });
        });

        RateLimiter::for('reset_password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // 3. Rate Limiter Operasi Berat / Sensitif (Ekspor data, Sync Cloud)
        RateLimiter::for('strict-action', function (Request $request) {
            $identifier = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();
            $limit = (int) config('security.rate_limits.strict_actions', 10);

            return Limit::perMinute($limit)
                ->by($identifier)
                ->response(function (Request $request, array $headers) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 429,
                            'error' => 'Too Many Requests',
                            'message' => "Operasi dibatasi. Silakan tunggu {$retryAfter} detik sebelum mengulang.",
                            'retry_after_seconds' => $retryAfter,
                        ], 429, $headers);
                    }
                    abort(429, "Operasi dibatasi untuk menjaga kestabilan server. Silakan tunggu {$retryAfter} detik.", $headers);
                });
        });

        $this->app->make(\Illuminate\Log\Logger::class)->getLogger()->pushProcessor(function ($record) {
            $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_token', 'remember_token', 'email'];
            if ($record instanceof \Monolog\LogRecord) {
                $msg = $record->message;
                foreach ($sensitiveFields as $field) {
                    $msg = preg_replace(
                        '/(' . preg_quote($field, '/') . '["\']?\s*[:=>]\s*)([^\s,\'"}]+)/i',
                        '$1[REDACTED]',
                        $msg
                    );
                }
                return $record->with(message: $msg);
            } elseif (is_array($record)) {
                foreach ($sensitiveFields as $field) {
                    $record['message'] = preg_replace(
                        '/(' . preg_quote($field, '/') . '["\']?\s*[:=>]\s*)([^\s,\'"}]+)/i',
                        '$1[REDACTED]',
                        $record['message'] ?? ''
                    );
                }
                return $record;
            }
            return $record;
        });
    }
}
