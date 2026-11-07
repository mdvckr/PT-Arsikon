<?php

namespace App\Services;

use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    /**
     * Daftar kunci sensitif yang wajib di-redact dan tidak boleh masuk database log.
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_token',
        'remember_token',
        'secret',
        'app_key',
        'authorization',
        'cookie',
        'cvv',
        'pin',
    ];

    /**
     * Catat log keamanan umum.
     */
    public static function record(
        string $event,
        string $severity = 'info',
        ?array $details = null,
        ?User $user = null,
        ?Request $request = null
    ): ?SecurityLog {
        try {
            $req = $request ?: request();
            $userId = $user?->id ?: auth()->id();

            $sanitizedDetails = $details ? static::sanitize($details) : null;

            $securityLog = SecurityLog::create([
                'event'      => $event,
                'severity'   => in_array($severity, ['info', 'warning', 'critical']) ? $severity : 'info',
                'user_id'    => $userId,
                'ip_address' => $req ? $req->ip() : null,
                'user_agent' => $req ? substr((string) $req->userAgent(), 0, 500) : null,
                'url'        => $req ? substr((string) $req->fullUrl(), 0, 1000) : null,
                'method'     => $req ? substr((string) $req->method(), 0, 10) : null,
                'details'    => $sanitizedDetails,
            ]);

            return $securityLog;
        } catch (\Throwable $e) {
            // Kegagalan pencatatan log keamanan tidak boleh merusak request utama pengguna
            Log::error('SecurityLogger failed: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Catat login berhasil (Layer 7.1).
     */
    public static function logLoginSuccess(User $user, ?Request $request = null): ?SecurityLog
    {
        return static::record(
            event: 'auth.login_success',
            severity: 'info',
            details: [
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first() ?? 'user',
            ],
            user: $user,
            request: $request
        );
    }

    /**
     * Catat login gagal (Layer 7.1).
     */
    public static function logLoginFailed(?string $email, ?Request $request = null, ?string $reason = null): ?SecurityLog
    {
        return static::record(
            event: 'auth.login_failed',
            severity: 'warning',
            details: [
                'attempted_email' => $email,
                'reason'          => $reason ?: 'Invalid credentials',
            ],
            user: null,
            request: $request
        );
    }

    /**
     * Catat penguncian akun akibat rate limit login terlampaui (Layer 7.1).
     */
    public static function logLockout(?string $email, ?Request $request = null): ?SecurityLog
    {
        return static::record(
            event: 'auth.lockout',
            severity: 'critical',
            details: [
                'attempted_email' => $email,
                'message'         => 'Login lockout triggered: too many failed attempts (15-minute lock).',
            ],
            user: null,
            request: $request
        );
    }

    /**
     * Catat logout pengguna.
     */
    public static function logLogout(?User $user, ?Request $request = null): ?SecurityLog
    {
        return static::record(
            event: 'auth.logout',
            severity: 'info',
            details: [
                'email' => $user?->email,
            ],
            user: $user,
            request: $request
        );
    }

    /**
     * Catat respons 403 / percobaan akses tidak diizinkan (Layer 7.1).
     */
    public static function logForbidden(Request $request, ?string $reason = null): ?SecurityLog
    {
        return static::record(
            event: 'access.forbidden',
            severity: 'warning',
            details: [
                'reason'   => $reason ?: 'Unauthorized access attempt',
                'route'    => $request->route()?->getName(),
                'user_id'  => auth()->id(),
                'user_role'=> auth()->user()?->getRoleNames()->first(),
            ],
            user: auth()->user(),
            request: $request
        );
    }

    /**
     * Catat perubahan role/permission atau penambahan akun admin (Layer 7.1).
     */
    public static function logRoleOrPermissionChange(User $targetUser, array $changes, ?User $actor = null): ?SecurityLog
    {
        return static::record(
            event: 'admin.role_change',
            severity: 'critical',
            details: [
                'target_user_id'    => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'changes'           => $changes,
                'performed_by'      => $actor?->email ?: auth()->user()?->email,
            ],
            user: $actor ?: auth()->user(),
            request: null
        );
    }

    /**
     * Sanitasi rekursif untuk menyamarkan (redact) kata sandi, token, dan data rahasia.
     */
    public static function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $isSensitive = false;
            $lowerKey = strtolower((string) $key);

            foreach (static::$sensitiveKeys as $pattern) {
                if (str_contains($lowerKey, $pattern)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = static::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
