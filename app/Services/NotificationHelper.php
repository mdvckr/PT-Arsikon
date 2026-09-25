<?php

namespace App\Services;

use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Send notification to Admins and Owners (General administrative broadcast).
     * Excludes pure regular Karyawan.
     */
    public static function notifyAdmins(string $title, string $message, string $type = 'info', ?string $url = null, ?string $soundType = null): void
    {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin Gudang Proyek', 'Admin PO', 'Super Admin']);
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Karyawan');
            })->get();

            if ($admins->isEmpty()) {
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Super Admin']);
                })->get();
            }

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification specifically to Central Warehouse Admins & Approvers.
     * Used when: Project Warehouse submits Material Request to Central, Returns to Central, Supplier goods receipt.
     * Excludes: Admin Gudang Proyek (of project warehouses) and Karyawan.
     */
    public static function notifyCentralWarehouseAdmins(
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        ?string $soundType = null
    ): void {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Owner', 'Admin', 'Admin Gudang Pusat', 'Super Admin']);
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Karyawan');
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send central warehouse admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification specifically to Purchasing / PO Admins.
     * Used when: Material Request contains manual/custom items requiring procurement.
     */
    public static function notifyPurchasingAdmins(
        string $title,
        string $message,
        string $type = 'warning',
        ?string $url = null,
        ?string $soundType = null
    ): void {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin PO', 'Owner', 'Super Admin']);
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Karyawan');
            })->get();

            if ($admins->isEmpty()) {
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Owner', 'Admin']);
                })->get();
            }

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send purchasing notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification specifically to Project Warehouse Admins for a specific warehouse.
     * Used when: Karyawan requests tools/materials in this project warehouse.
     * Excludes: Central Admins, Owner, Super Admin, and other Karyawan.
     */
    public static function notifyProjectWarehouseAdmins(
        int $warehouseId,
        string $title,
        string $message,
        string $type = 'approval_needed',
        ?string $url = null,
        ?string $soundType = 'approval'
    ): void {
        try {
            // Find Admin Gudang Proyek assigned to this project warehouse
            $projectAdmins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin Gudang Proyek', 'User']);
            })->whereHas('warehouses', function ($w) use ($warehouseId) {
                $w->where('warehouses.id', $warehouseId);
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Karyawan');
            })->get();

            // Fallback if no specific project admin assigned to that warehouse: notify any user assigned to this warehouse (excluding regular Karyawan)
            if ($projectAdmins->isEmpty()) {
                $projectAdmins = User::whereHas('warehouses', function ($w) use ($warehouseId) {
                    $w->where('warehouses.id', $warehouseId);
                })->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'Karyawan');
                })->get();
            }

            foreach ($projectAdmins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send project warehouse admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification specifically to users who have approval authority for a transaction.
     * If $warehouseId is specified and it's a project warehouse: targets project warehouse admins only!
     * If no warehouse or central warehouse: targets central warehouse approvers.
     */
    public static function notifyApprovers(
        string $title,
        string $message,
        string $type = 'approval_needed',
        ?string $url = null,
        ?int $warehouseId = null
    ): void {
        try {
            $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

            if ($warehouse && !$warehouse->is_central) {
                self::notifyProjectWarehouseAdmins($warehouseId, $title, $message, $type, $url, 'approval');
                return;
            }

            self::notifyCentralWarehouseAdmins($title, $message, $type, $url, 'approval');
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
