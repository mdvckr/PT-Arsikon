<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy {
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan',
            'admin_pusat', 'admin', 'Admin Pengadaan Proyek', 'Admin Logistik Pusat'
        ]) || $user->can('view purchase orders');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan',
            'admin_pusat', 'admin', 'Admin Pengadaan Proyek', 'Admin Logistik Pusat'
        ]) || $user->can('view purchase orders');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan', 'admin_pusat', 'admin', 'Admin Pengadaan Proyek'
        ]) || $user->can('create purchase orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin Pengadaan', 'Admin Pengadaan', 'admin_pusat', 'admin', 'Admin Pengadaan Proyek'
        ]) || $user->can('edit purchase orders');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'admin_pusat'
        ]) || $user->can('delete purchase orders');
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek'])) {
            return false;
        }

        return $user->hasAnyRole(['Admin Pusat', 'Admin', 'admin_pusat', 'Admin Pengadaan', 'Admin Pengadaan'])
            || $user->can('approve purchase orders');
    }
}
