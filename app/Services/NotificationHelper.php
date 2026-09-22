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
    public static function notifyAdmins(string $title, string $message, string $type = 'info', ?string $url = null, ?string $soundType = null): void
    {
        try {
            // Target users with Admin roles or assigned to Central Warehouse
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin Gudang Proyek', 'Admin PO', 'Super Admin']);
            })->orWhereHas('warehouses', function ($q) {
                $q->where('is_central', true);
            })->get();

            if ($admins->isEmpty()) {
                $admins = User::all();
            }

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification specifically to users who have approval authority for a transaction.
     * e.g. for Material Request or Tool Assignment approval.
     */
    public static function notifyApprovers(string $title, string $message, string $type = 'approval_needed', ?string $url = null, ?int $warehouseId = null): void
    {
        try {
            // Approver roles: Owner, Admin, Admin Gudang Pusat
            $query = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Super Admin']);
            });

            // If a specific project warehouse is targeted, also include Admin Gudang Proyek assigned to that warehouse
            if ($warehouseId) {
                $query->orWhere(function ($sub) use ($warehouseId) {
                    $sub->whereHas('roles', fn($r) => $r->where('name', 'Admin Gudang Proyek'))
                        ->whereHas('warehouses', fn($w) => $w->where('warehouses.id', $warehouseId));
                });
            }

            $approvers = $query->get()->unique('id');

            if ($approvers->isEmpty()) {
                $approvers = User::all();
            }

            foreach ($approvers as $approver) {
                $approver->notify(new SystemNotification($title, $message, $type, $url, 'approval'));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send approvers notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific user.
     */
    public static function notifyUser(User $user, string $title, string $message, string $type = 'info', ?string $url = null, ?string $soundType = null): void
    {
        try {
            $user->notify(new SystemNotification($title, $message, $type, $url, $soundType));
        } catch (\Throwable $e) {
            Log::warning('Failed to send user notification: ' . $e->getMessage());
        }
    }
}
