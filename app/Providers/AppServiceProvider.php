<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::directive('qty', function ($expression) {
            return "<?php echo format_quantity($expression); ?>";
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip())->response(function (Request $request, array $headers) {
                return back()->withInput($request->only('email', 'remember'))->withErrors([
                    'email' => 'Terlalu banyak permintaan login dari koneksi Anda. Silakan tunggu sebentar sebelum mencoba lagi.',
                ]);
            });
        });

        RateLimiter::for('forgot_password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('reset_password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
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
