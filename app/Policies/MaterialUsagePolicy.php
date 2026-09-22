<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class MaterialUsagePolicy
{
    /**
     * Determine if user can bypass approval for material usage.
     * Admin Gudang Proyek bisa bypass approval di project warehouse mereka sendiri.
     */
    public function bypassApproval(User $user, Warehouse $warehouse): bool
    {
        if (!$user->hasRole('Admin Gudang Proyek')) {
            return false;
        }

        if ($warehouse->isCentral()) {
            return false;
        }

        return $user->hasAccessToWarehouse($warehouse);
    }

    /**
     * Determine if user can create material usage without material request.
     */
    public function createWithoutRequest(User $user, Warehouse $warehouse): bool
    {
        return $this->bypassApproval($user, $warehouse);
    }
}