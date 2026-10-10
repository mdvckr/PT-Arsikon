<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();
            $maxAttempts = $user
                ? (int) config('security.rate_limits.api_authenticated', 120)
                : (int) config('security.rate_limits.api_guest', 30);

            $identifier = $user ? 'user:' . $user->id : 'ip:' . $request->ip();

            return Limit::perMinute($maxAttempts)
                ->by($identifier)
                ->response(function (Request $request, array $headers) use ($maxAttempts) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);

                    return response()->json([
                        'status' => 429,
                        'error' => 'Too Many Requests',
                        'message' => 'Batas kuota request API Anda telah terlampaui. Silakan coba lagi nanti.',
                        'rate_limit' => $maxAttempts,
                        'retry_after_seconds' => $retryAfter,
                    ], 429, $headers);
                });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
