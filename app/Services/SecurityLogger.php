<?php

namespace App\Services;

use App\Models\AuditLog;
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

            // Cara B: Gaungkan insiden keamanan ke audit_logs (Dual-Write / Audit Bridge)
            static::bridgeToAuditLog($securityLog, $req);

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

    /**
     * Menggaungkan / mem-bridge log keamanan ke tabel audit_logs utama (Cara B).
     * Seluruh event keamanan direfleksikan ke audit_logs agar langsung terbaca di Audit Trail sistem.
     */
    protected static function bridgeToAuditLog(SecurityLog $secLog, ?Request $req): void
    {
        try {
            $auditEvent = match ($secLog->event) {
                'auth.login_success' => 'LOGIN',
                'auth.logout'        => 'LOGOUT',
                'auth.login_failed'  => 'AUTH_FAILED',
                'auth.lockout'       => 'SECURITY_LOCKOUT',
                'access.forbidden'   => 'ACCESS_FORBIDDEN',
                'admin.role_change'  => 'ROLE_CHANGE',
                default              => strtoupper('SECURITY_' . str_replace(['.', '-'], '_', $secLog->event)),
            };

            $summary = match ($secLog->event) {
                'auth.login_success' => 'Pengguna berhasil masuk ke sistem.',
                'auth.logout'        => 'Pengguna keluar dari sistem.',
                'auth.login_failed'  => 'Percobaan login gagal untuk akun: ' . ($secLog->details['attempted_email'] ?? 'Unknown'),
                'auth.lockout'       => 'Peringatan Keamanan: Percobaan login gagal berulang kali. Akun/IP dikunci selama 15 menit.',
                'access.forbidden'   => 'Percobaan akses tidak diizinkan (403 Forbidden) pada rute: ' . ($secLog->details['route'] ?? $secLog->url ?? '-'),
                'admin.role_change'  => 'Perubahan hak akses / peran untuk pengguna ID #' . ($secLog->details['target_user_id'] ?? '-'),
                default              => 'Aktivitas keamanan terdeteksi [' . strtoupper($secLog->severity) . ']: ' . $secLog->event,
            };

            AuditLog::create([
                'user_id'        => $secLog->user_id,
                'event'          => $auditEvent,
                'auditable_type' => 'Security',
                'auditable_id'   => $secLog->id,
                'old_values'     => null,
                'new_values'     => [
                    'severity'   => $secLog->severity,
                    'event'      => $secLog->event,
                    'summary'    => $summary,
                    'details'    => $secLog->details,
                ],
                'ip_address'     => $secLog->ip_address,
                'user_agent'     => $secLog->user_agent,
            ]);
        } catch (\Throwable $e) {
            // Kegagalan bridging log tidak boleh menggagalkan proses utama
            Log::warning('Failed bridging security log to audit log: ' . $e->getMessage());
        }
    }
}
