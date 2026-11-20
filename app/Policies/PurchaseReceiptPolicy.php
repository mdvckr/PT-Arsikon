<?php

namespace App\Policies;

use App\Models\PurchaseReceipt;
use App\Models\User;

class PurchaseReceiptPolicy {
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseReceipt $purchaseReceipt): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Admin Pengadaan Proyek has full CRUD access to Nota Pembelian Harian.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek',
            'admin_pusat', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Logistik Proyek'
        ]) || $user->can('create purchase receipts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseReceipt $purchaseReceipt): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek',
            'admin_pusat', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'
        ]) || $user->can('edit purchase receipts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseReceipt $purchaseReceipt): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'admin_pusat', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'
        ]) || $user->can('delete purchase receipts');
    }
}
