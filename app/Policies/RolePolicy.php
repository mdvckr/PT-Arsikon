<?php

namespace App\Policies;

use App\Http\Controllers\RoleController;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar jabatan / role.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Owner', 'Admin Pusat']);
    }

    /**
     * Tentukan apakah pengguna dapat membuat jabatan baru.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Owner', 'Admin Pusat']);
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui jabatan / hak aksesnya.
     */
    public function update(User $user, Role $role): bool
    {
        // Hanya Owner dan Admin Pusat yang dapat mengelola role
        return $user->hasAnyRole(['Owner', 'Admin Pusat']);
    }

    /**
     * Tentukan apakah pengguna dapat menghapus jabatan.
     */
    public function delete(User $user, Role $role): bool
    {
        if (!$user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            return false;
        }

        // Role sistem inti tidak boleh dihapus sama sekali
        if (in_array($role->name, RoleController::PROTECTED_ROLES, true)) {
            return false;
        }

        // Role yang masih memiliki pengguna tidak boleh dihapus
        if ($role->users()->count() > 0) {
            return false;
        }

        return true;
    }
}
