<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Tangani secara terpusat jika terjadi Throttle / Rate Limit terlampaui
        $this->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            $headers = $e->getHeaders();
            $retryAfter = (int) ($headers['Retry-After'] ?? 60);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 429,
                    'error' => 'Too Many Requests',
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
                    'retry_after_seconds' => $retryAfter,
                ], 429, $headers);
            }

            if (view()->exists('errors.429')) {
                return response()->view('errors.429', [
                    'message' => 'Terlalu banyak permintaan dari koneksi Anda.',
                    'retryAfter' => $retryAfter,
                ], 429, $headers);
            }
        });

        // Tangani abort(429, ...) jika dipanggil dari aplikasi
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 429) {
                $headers = $e->getHeaders();
                $retryAfter = (int) ($headers['Retry-After'] ?? 60);

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 429,
                        'error' => 'Too Many Requests',
                        'message' => $e->getMessage() ?: 'Terlalu banyak permintaan.',
                        'retry_after_seconds' => $retryAfter,
                    ], 429, $headers);
                }

                if (view()->exists('errors.429')) {
                    return response()->view('errors.429', [
                        'message' => $e->getMessage() ?: 'Terlalu banyak permintaan.',
                        'retryAfter' => $retryAfter,
                    ], 429, $headers);
                }
            }
        });
    }
}
