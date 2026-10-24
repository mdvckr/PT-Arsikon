<?php

namespace App\Policies;

use App\Models\PurchaseReceipt;
use App\Models\User;

class PurchaseReceiptPolicy
{
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
     * po_nota_harian has full CRUD access to Nota Pembelian Harian.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin PO', 'Admin PO Proyek',
            'admin_pusat', 'po_lapangan', 'po_nota_harian', 'admin-po-nota', 'Admin Gudang Proyek'
        ]) || $user->can('create purchase receipts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseReceipt $purchaseReceipt): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'Admin PO', 'Admin PO Proyek',
            'admin_pusat', 'po_lapangan', 'po_nota_harian', 'admin-po-nota'
        ]) || $user->can('edit purchase receipts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseReceipt $purchaseReceipt): bool
    {
        return $user->hasAnyRole([
            'Admin Pusat', 'Admin', 'admin_pusat', 'po_nota_harian', 'admin-po-nota'
        ]) || $user->can('delete purchase receipts');
    }
}
