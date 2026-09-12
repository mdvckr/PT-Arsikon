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
            // Target users with Admin roles or assigned to Central Warehouse
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Super Admin']);
            })->orWhereHas('warehouses', function ($q) {
                $q->where('is_central', true);
            })->get();

            if ($admins->isEmpty()) {
                $admins = User::all();
            }

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url));
            }
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
