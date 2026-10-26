<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin PO',
            'admin_pusat', 'admin', 'po_lapangan', 'po_nota_harian', 'admin-po-nota', 'Admin Gudang Pusat'
        ]) || $user->can('view purchase orders');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin PO',
            'admin_pusat', 'admin', 'po_lapangan', 'po_nota_harian', 'admin-po-nota', 'Admin Gudang Pusat'
        ]) || $user->can('view purchase orders');
    }

    /**
     * Determine whether the user can create models.
     * po_nota_harian is strictly READ-ONLY on PO Lapangan (returns false => 403 Forbidden).
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin PO', 'admin_pusat', 'admin', 'po_lapangan'
        ]) || $user->can('create purchase orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            return false;
        }

        return $user->hasAnyRole([
            'Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin PO', 'admin_pusat', 'admin', 'po_lapangan'
        ]) || $user->can('edit purchase orders');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
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
        if ($user->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            return false;
        }

        return $user->hasAnyRole(['Admin Pusat', 'Admin', 'admin_pusat'])
            || $user->can('approve purchase orders');
    }
}
