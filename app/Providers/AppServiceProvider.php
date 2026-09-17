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
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by(strtolower((string) $request->input('email'))),
            ];
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

        $this->app->make(\Illuminate\Log\Logger::class)->getLogger()->pushProcessor(new class {
            public function __invoke(array $record): array
            {
                foreach (['password', 'password_confirmation', 'token', 'api_token', 'remember_token', 'email'] as $field) {
                    $record['message'] = preg_replace(
                        '/(' . preg_quote($field, '/') . '["\']?\s*[:=>]\s*)([^\s,\'"}]+)/i',
                        '$1[REDACTED]',
                        $record['message']
                    );
                }
                return $record;
            }
        });
    }
}
