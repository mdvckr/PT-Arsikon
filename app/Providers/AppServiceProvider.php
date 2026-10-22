<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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

        // Kebijakan Kompleksitas Password Default (Layer 4.2 Master Plan v2)
        Password::defaults(function () {
            $rule = Password::min(10)
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        \Illuminate\Support\Facades\Blade::directive('qty', function ($expression) {
            return "<?php echo format_quantity($expression); ?>";
        });

        // 1. Rate Limiter Login: 15-menit lockout per IP dan per Email+IP (Layer 4.2 Master Plan v2)
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email', ''));
            $ip = $request->ip();

            $lockoutMinutes = (int) config('security.rate_limits.lockout_minutes', 15);
            $accountAttempts = (int) config('security.rate_limits.login_max_attempts', 5);
            $ipAttempts = (int) config('security.rate_limits.login_ip_per_minute', 15);

            return [
                // Batas percobaan login per kombinasi akun & IP: 5 percobaan per 15 menit
                Limit::perMinutes($lockoutMinutes, $accountAttempts)
                    ->by('email:' . $email . '|' . $ip)
                    ->response(function (Request $request, array $headers) use ($lockoutMinutes) {
                        $seconds = (int) ($headers['Retry-After'] ?? ($lockoutMinutes * 60));
                        $minutes = ceil($seconds / 60);
                        if ($request->expectsJson()) {
                            return response()->json([
                                'status' => 429,
                                'error' => 'Too Many Requests',
                                'message' => "Terlalu banyak percobaan masuk untuk akun ini. Akun dikunci sementara. Silakan coba lagi dalam {$minutes} menit.",
                                'retry_after_seconds' => $seconds,
                            ], 429, $headers);
                        }
                        return back()->withInput($request->only('email', 'remember'))->withErrors([
                            'email' => "Terlalu banyak percobaan masuk untuk akun ini. Akun dikunci sementara demi keamanan. Coba lagi dalam {$minutes} menit.",
                        ]);
                    }),

                // Batas total percobaan login dari IP yang sama: 15 percobaan per 15 menit (mencegah credential stuffing masif)
                Limit::perMinutes($lockoutMinutes, $ipAttempts)
                    ->by('ip:' . $ip)
                    ->response(function (Request $request, array $headers) use ($lockoutMinutes) {
                        $seconds = (int) ($headers['Retry-After'] ?? ($lockoutMinutes * 60));
                        $minutes = ceil($seconds / 60);
                        if ($request->expectsJson()) {
                            return response()->json([
                                'status' => 429,
                                'error' => 'Too Many Requests',
                                'message' => "Terlalu banyak permintaan login dari koneksi IP Anda. Silakan coba lagi dalam {$minutes} menit.",
                                'retry_after_seconds' => $seconds,
                            ], 429, $headers);
                        }
                        return back()->withInput($request->only('email', 'remember'))->withErrors([
                            'email' => "Terlalu banyak permintaan login dari alamat IP Anda. Silakan coba lagi dalam {$minutes} menit.",
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
