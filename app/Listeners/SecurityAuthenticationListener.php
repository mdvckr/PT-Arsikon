<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class SecurityAuthenticationListener
{
    /**
     * Tangani event Login berhasil.
     */
    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            SecurityLogger::logLoginSuccess($event->user);
        }
    }

    /**
     * Tangani event Login gagal.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;
        SecurityLogger::logLoginFailed($email);
    }

    /**
     * Tangani event Lockout (akun/IP dikunci).
     */
    public function handleLockout(Lockout $event): void
    {
        $email = $event->request?->input('email');
        SecurityLogger::logLockout($email, $event->request);
    }

    /**
     * Tangani event Logout.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            SecurityLogger::logLogout($event->user);
        }
    }

    /**
     * Tangani event Reset Password.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            SecurityLogger::record(
                event: 'auth.password_reset',
                severity: 'info',
                details: ['email' => $event->user->email],
                user: $event->user
            );
        }
    }

    /**
     * Subscribe multiple events to this listener.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class         => 'handleLogin',
            Failed::class        => 'handleFailed',
            Lockout::class       => 'handleLockout',
            Logout::class        => 'handleLogout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
