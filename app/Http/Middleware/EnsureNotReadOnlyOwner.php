<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Keamanan Operasional RBAC:
 * Mengunci peran 'Owner' agar bersikap murni Read-Only (View Only).
 * Owner dilarang keras melakukan aksi mutasi data (Create, Update, Delete,
 * serta aksi Approval/Reject/Cancel/Ship/Receive) di seluruh modul operasional.
 */
class EnsureNotReadOnlyOwner
{
    /**
     * Route yang dikecualikan dari pemblokiran mutasi bagi Owner:
     * Hanya perpindahan workspace, penandaan notifikasi telah dibaca,
     * pembaruan profil akun sendiri, dan autentikasi.
     *
     * @var array<int, string>
     */
    protected array $allowedRoutePatterns = [
        'workspace.switch',
        'notifications.markAllAsRead',
        'notifications.markAsRead',
        'profile.*',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika bukan Owner atau request hanya membaca (GET, HEAD, OPTIONS), lanjutkan
        if (!$user || !$user->hasRole('Owner') || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Periksa apakah rute saat ini termasuk pengecualian non-operasional
        $routeName = $request->route()?->getName();
        if ($routeName) {
            foreach ($this->allowedRoutePatterns as $pattern) {
                if (\Illuminate\Support\Str::is($pattern, $routeName)) {
                    return $next($request);
                }
            }
        }

        // Blokir semua mutasi operasional (POST, PUT, PATCH, DELETE) untuk Owner
        $errorMessage = 'Akses Ditolak: Peran Owner memiliki wewenang View-Only (hanya dapat melihat laporan dan ringkasan eksekutif). Tidak diizinkan melakukan penambahan, perubahan, penghapusan data, ataupun persetujuan operasional.';

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 403);
        }

        abort(403, $errorMessage);
    }
}
