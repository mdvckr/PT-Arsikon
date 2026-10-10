<?php

namespace App\Services;

use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Kirim notifikasi langsung ke seluruh akun Admin Pusat (Pengambil Keputusan & Approval Utama).
     */
    public static function notifyAdminPusat(
        string $title,
        string $message,
        string $type = 'approval_needed',
        ?string $url = null,
        ?string $soundType = 'approval'
    ): void {
        try {
            $adminPusats = User::role('Admin Pusat')->get();
            foreach ($adminPusats as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi Admin Pusat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi pemantauan / monitoring ke Owner (Read-Only / Info Tanpa Tombol Aksi).
     */
    public static function notifyOwnerMonitoring(
        string $title,
        string $message,
        ?string $url = null
    ): void {
        try {
            $owners = User::role('Owner')->get();
            foreach ($owners as $owner) {
                // Notifikasi untuk Owner selalu bertipe 'info' untuk keperluan monitoring/laporan
                $owner->notify(new SystemNotification(
                    "[Pantauan] " . $title,
                    $message,
                    'info',
                    $url,
                    'info'
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi pantauan Owner: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi umum ke seluruh jajaran manajemen/admin dan masukkan ke pantauan Owner.
     * Pengguna dengan dual-role (misal Admin Gudang yang juga Karyawan) tetap menerima notifikasi.
     */
    public static function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        ?string $soundType = null
    ): void {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', [
                    'Admin Pusat',
                    'Admin Gudang Pusat',
                    'Admin Gudang Proyek',
                    'Admin PO',
                    'Owner',
                ]);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi admin: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke Admin Gudang Pusat dan Admin Pusat.
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
                $q->whereIn('name', ['Admin Pusat', 'Admin Gudang Pusat']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }

            // Sertakan salinan informasi pemantauan untuk Owner
            self::notifyOwnerMonitoring($title, $message, $url);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi admin gudang pusat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi pengadaan ke Admin PO dan Admin Pusat.
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
                $q->whereIn('name', ['Admin PO', 'Admin Pusat']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }

            // Sertakan salinan pemantauan untuk Owner
            self::notifyOwnerMonitoring($title, $message, $url);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi purchasing: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi spesifik ke Admin Gudang Proyek untuk gudang tertentu.
     * Aman dari konflik dual-role (Admin Gudang yang merangkap Karyawan tetap menerima).
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
            $projectAdmins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin Gudang Proyek', 'Admin Pusat']);
            })->whereHas('warehouses', function ($w) use ($warehouseId) {
                $w->where('warehouses.id', $warehouseId);
            })->get();

            if ($projectAdmins->isEmpty()) {
                // Fallback: Admin Pusat selalu menerima notifikasi
                $projectAdmins = User::role('Admin Pusat')->get();
            }

            foreach ($projectAdmins as $admin) {
                $admin->notify(new SystemNotification($title, $message, $type, $url, $soundType));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi admin gudang proyek: ' . $e->getMessage());
        }
    }

    /**
     * Alur Notifikasi Request & Approval:
     * 1. Mengirim notifikasi approval_needed ke Admin Pusat (Pengambil Keputusan Utama)
     * 2. Mengirim notifikasi approval_needed ke Admin Gudang terkait (jika berlaku di lapangan)
     * 3. Mengirim salinan informasi pemantauan read-only ke Owner (untuk monitoring laporan eksekutif)
     */
    public static function notifyApprovers(
        string $title,
        string $message,
        string $type = 'approval_needed',
        ?string $url = null,
        ?int $warehouseId = null
    ): void {
        // 1. Notifikasi Approval ke Admin Pusat
        self::notifyAdminPusat($title, $message, $type, $url, 'approval');

        // 2. Jika ada gudang proyek spesifik, kabari juga admin gudang proyek tersebut
        if ($warehouseId) {
            try {
                $warehouse = Warehouse::find($warehouseId);
                if ($warehouse && !$warehouse->is_central) {
                    self::notifyProjectWarehouseAdmins($warehouseId, $title, $message, $type, $url, 'approval');
                } elseif ($warehouse && $warehouse->is_central) {
                    $gudangPusat = User::role('Admin Gudang Pusat')->get();
                    foreach ($gudangPusat as $admin) {
                        $admin->notify(new SystemNotification($title, $message, $type, $url, 'approval'));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirim notifikasi gudang: ' . $e->getMessage());
            }
        }

        // 3. Masuk ke daftar pantauan Owner dalam bentuk laporan read-only
        self::notifyOwnerMonitoring($title, $message, $url);

        // 4. Siarkan event real-time instan melalui WebSocket Laravel Reverb
        try {
            event(new \App\Events\RequestSubmitted(
                title: $title,
                message: $message,
                requestType: 'approval_request',
                url: $url,
                soundType: 'approval',
                warehouseId: $warehouseId
            ));
        } catch (\Throwable $e) {
            Log::warning('Gagal menyiarkan event WebSocket RequestSubmitted: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi personal ke pengguna tertentu.
     */
    public static function notifyUser(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $url = null,
        ?string $soundType = null
    ): void {
        try {
            $user->notify(new SystemNotification($title, $message, $type, $url, $soundType));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim notifikasi user: ' . $e->getMessage());
        }
    }
}
