<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * 6 Role Sistem Inti (Protected Canonical Roles) yang dilindungi dari penghapusan
     * dan perubahan nama untuk menjamin stabilitas otorisasi sistem.
     */
    public const PROTECTED_ROLES = [
        'Owner',
        'Admin Pusat',
        'Admin Gudang Pusat',
        'Admin Gudang Proyek',
        'Admin PO',
        'Karyawan',
    ];

    /**
     * Nama-nama yang dipesan sistem (Reserved Names) yang tidak boleh dibuat kembali
     * guna menghindari kebingungan, celah duplikasi, dan alias redundan.
     */
    public const RESERVED_ROLE_NAMES = [
        'admin',
        'user',
        'super admin',
        'superadmin',
        'administrator',
        'root',
        'system',
        'guest',
    ];

    /**
     * Metadata kategori operasional untuk mempercantik UI dan membedakan peran kerja.
     */
    public const ROLE_METADATA = [
        'Owner' => [
            'category'    => 'Eksekutif & Direksi',
            'badge_color' => '#92400e',
            'badge_bg'    => '#fef3c7',
            'icon'        => 'fa-crown',
            'description' => 'Akses tertinggi sistem dan pengawasan seluruh operasional perusahaan.',
        ],
        'Admin Pusat' => [
            'category'    => 'Manajemen Sistem',
            'badge_color' => '#1e40af',
            'badge_bg'    => '#dbeafe',
            'icon'        => 'fa-user-shield',
            'description' => 'Full akses sistem, manajemen akun, gudang, jabatan, dan monitoring logistik.',
        ],
        'Admin Gudang Pusat' => [
            'category'    => 'Logistik Sentral',
            'badge_color' => '#065f46',
            'badge_bg'    => '#d1fae5',
            'icon'        => 'fa-warehouse',
            'description' => 'Operasional gudang sentral, penerimaan barang supplier, dan distribusi ke proyek.',
        ],
        'Admin Gudang Proyek' => [
            'category'    => 'Site Proyek',
            'badge_color' => '#9a3412',
            'badge_bg'    => '#ffedd5',
            'icon'        => 'fa-helmet-safety',
            'description' => 'Logistik lapangan site proyek, penerimaan distribusi, dan pemakaian material proyek.',
        ],
        'Admin PO' => [
            'category'    => 'Pengadaan & PO',
            'badge_color' => '#5b21b6',
            'badge_bg'    => '#ede9fe',
            'icon'        => 'fa-file-invoice-dollar',
            'description' => 'Fokus pengadaan, Purchase Order (PO), verifikasi pembayaran, dan supplier.',
        ],
        'Karyawan' => [
            'category'    => 'Operasional Lapangan',
            'badge_color' => '#374151',
            'badge_bg'    => '#f3f4f6',
            'icon'        => 'fa-id-badge',
            'description' => 'Peminjaman alat kerja mandiri dan pembuatan permintaan material.',
        ],
    ];

    public function index(Request $request)
    {
        $this->authorizeAdminAccess();

        $query = Role::withCount(['users', 'permissions'])->with('permissions');

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        $roles = $query->orderBy('name')->get();
        $protectedRoles = self::PROTECTED_ROLES;
        $roleMetadata = self::ROLE_METADATA;
        $groupedPermissions = $this->getGroupedPermissions();

        return view('roles.index', compact('roles', 'protectedRoles', 'roleMetadata', 'groupedPermissions'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdminAccess();

        $name = trim((string) $request->input('name'));
        $lowerName = strtolower($name);

        // 1. Cek pencegahan Reserved Names
        if (in_array($lowerName, self::RESERVED_ROLE_NAMES, true)) {
            return back()->withInput()->withErrors([
                'name' => "Nama jabatan '{$name}' dipesan oleh sistem untuk alasan keamanan dan tidak dapat digunakan.",
            ]);
        }

        // 2. Cek pencegahan nama duplikat secara case-insensitive
        $existing = Role::whereRaw('LOWER(name) = ?', [$lowerName])->first();
        if ($existing) {
            return back()->withInput()->withErrors([
                'name' => "Jabatan dengan nama '{$existing->name}' sudah ada di dalam sistem.",
            ]);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:50',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'base_role'     => 'nullable|string|exists:roles,name',
        ]);

        $role = Role::create([
            'name'       => $name,
            'guard_name' => 'web',
        ]);

        // Copy permission dari role dasar jika dipilih, atau gunakan pilihan eksplisit
        if (!empty($validated['base_role'])) {
            $baseRole = Role::findByName($validated['base_role'], 'web');
            $role->syncPermissions($baseRole->permissions);
        } elseif (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', "Jabatan kustom '{$role->name}' berhasil ditambahkan ke sistem.");
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeAdminAccess();

        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $rules = [
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];

        // Role kustom dapat diubah namanya, role inti terkunci namanya
        if (!$isProtected) {
            $rules['name'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role->id),
            ];
        }

        $validated = $request->validate($rules);

        if (!$isProtected && !empty($validated['name'])) {
            $newName = trim($validated['name']);
            $lowerNewName = strtolower($newName);

            if (in_array($lowerNewName, self::RESERVED_ROLE_NAMES, true)) {
                return back()->withInput()->withErrors([
                    'name' => "Nama jabatan '{$newName}' dilarang oleh sistem.",
                ]);
            }

            $duplicate = Role::whereRaw('LOWER(name) = ? AND id != ?', [$lowerNewName, $role->id])->first();
            if ($duplicate) {
                return back()->withInput()->withErrors([
                    'name' => "Jabatan dengan nama '{$duplicate->name}' sudah ada.",
                ]);
            }

            $role->name = $newName;
            $role->save();
        }

        // Sinkronisasi permission yang dipilih
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', "Hak akses jabatan '{$role->name}' berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        $this->authorizeAdminAccess();

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->with('error', "Jabatan sistem inti '{$role->name}' dilindungi dan tidak dapat dihapus.");
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return back()->with('error', "Jabatan '{$role->name}' masih terikat pada {$usersCount} akun pengguna. Pindahkan pengguna terlebih dahulu sebelum menghapus.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Jabatan '{$roleName}' berhasil dihapus dari sistem.");
    }

    /**
     * Autorisasi ketat: Hanya Owner dan Admin Pusat yang dapat mengelola peran & izin akses.
     */
    protected function authorizeAdminAccess(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            abort(403, 'Akses terbatas untuk Administrator Utama (Owner / Admin Pusat).');
        }
    }

    /**
     * Mengelompokkan seluruh izin akses (permissions) ke dalam modul fungsional.
     */
    protected function getGroupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();

        $modules = [
            'Pengguna & Akses' => [
                'icon' => 'fa-users-gear',
                'items' => ['view users', 'create users', 'edit users', 'delete users', 'projects.manage', 'warehouses.manage'],
            ],
            'Master Data' => [
                'icon' => 'fa-boxes-stacked',
                'items' => ['view materials', 'create materials', 'edit materials', 'delete materials',
                            'view tools', 'create tools', 'edit tools', 'delete tools',
                            'view categories', 'create categories', 'edit categories', 'delete categories',
                            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'],
            ],
            'Logistik & Penerimaan' => [
                'icon' => 'fa-truck-ramp-box',
                'items' => ['view goods receipts', 'create goods receipts', 'confirm goods receipts',
                            'view distributions', 'create distributions', 'ship distributions', 'receive distributions'],
            ],
            'Permintaan & Pemakaian' => [
                'icon' => 'fa-clipboard-check',
                'items' => ['view material requests', 'create material requests', 'approve material requests',
                            'view material usages', 'create material usages', 'cancel material usages'],
            ],
            'Alat & Inventaris' => [
                'icon' => 'fa-wrench',
                'items' => ['view tool assignments', 'create tool assignments', 'return tool assignments',
                            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
                            'view inventory', 'delete inventory', 'view stock opname', 'create stock opname', 'approve stock opname'],
            ],
            'Pengadaan & Pembayaran' => [
                'icon' => 'fa-file-invoice-dollar',
                'items' => ['view procurement', 'create procurement', 'approve procurement',
                            'view purchase orders', 'create purchase orders', 'send purchase orders', 'cancel purchase orders',
                            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
                            'view payments', 'create payments', 'verify payments',
                            'view returns', 'create returns', 'approve returns', 'receive returns'],
            ],
            'Laporan & Audit' => [
                'icon' => 'fa-chart-pie',
                'items' => ['view reports', 'view audit logs'],
            ],
        ];

        $grouped = [];
        foreach ($modules as $groupName => $meta) {
            $grouped[$groupName] = [
                'icon' => $meta['icon'],
                'permissions' => $permissions->whereIn('name', $meta['items']),
            ];
        }

        // Izin lain yang belum masuk kelompok di atas
        $categorizedNames = collect($modules)->flatMap(fn($m) => $m['items'])->toArray();
        $remaining = $permissions->whereNotIn('name', $categorizedNames);
        if ($remaining->isNotEmpty()) {
            $grouped['Izin Tambahan Lainnya'] = [
                'icon' => 'fa-shield-halved',
                'permissions' => $remaining,
            ];
        }

        return $grouped;
    }
}
