<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationHelper
{
    /**
     * Send notification to Admins and Owners.
     */
    public static function notifyAdmins(string $title, string $message, string $type = 'info', ?string $url = null): void
    {
        try {
            $admins = User::role(['Owner', 'Admin', 'Admin Gudang Pusat'])->get();
            if ($admins->isEmpty()) {
                $admins = User::all();
            }
            Notification::send($admins, new SystemNotification($title, $message, $type, $url));
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific user.
     */
    public static function notifyUser(User $user, string $title, string $message, string $type = 'info', ?string $url = null): void
    {
        try {
            $user->notify(new SystemNotification($title, $message, $type, $url));
        } catch (\Throwable $e) {
            Log::warning('Failed to send user notification: ' . $e->getMessage());
        }
    }
}
