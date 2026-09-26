<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Protected core roles that cannot be deleted.
     */
    protected array $protectedRoles = [
        'Owner',
        'Admin Pusat',
        'Admin',
        'Admin Gudang Pusat',
        'Admin Gudang Proyek',
        'Admin PO',
        'Karyawan',
        'User',
    ];

    public function index(Request $request)
    {
        $this->authorizeAdminAccess();

        $query = Role::withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $roles = $query->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $protectedRoles = $this->protectedRoles;

        return view('roles.index', compact('roles', 'permissions', 'protectedRoles'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdminAccess();

        $validated = $request->validate([
            'name'          => 'required|string|max:50|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'base_role'     => 'nullable|string|exists:roles,name',
        ]);

        $role = Role::create([
            'name'       => trim($validated['name']),
            'guard_name' => 'web',
        ]);

        // Copy permissions from base role if selected, or from explicit permissions array
        if (!empty($validated['base_role'])) {
            $baseRole = Role::findByName($validated['base_role'], 'web');
            $role->syncPermissions($baseRole->permissions);
        } elseif (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', "Jabatan / Role '{$role->name}' berhasil dibuat.");
    }

    public function destroy(Role $role)
    {
        $this->authorizeAdminAccess();

        if (in_array($role->name, $this->protectedRoles)) {
            return back()->with('error', "Jabatan sistem utama '{$role->name}' tidak boleh dihapus.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Jabatan '{$role->name}' masih digunakan oleh {$role->users()->count()} pengguna. Pindahkan pengguna ke jabatan lain terlebih dahulu.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Jabatan '{$roleName}' berhasil dihapus.");
    }

    /**
     * Only Owner, Admin Pusat, or Super Admin can manage roles.
     */
    protected function authorizeAdminAccess(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin'])) {
            abort(403, 'Akses terbatas untuk Administrator Utama / Admin Pusat.');
        }
    }
}
