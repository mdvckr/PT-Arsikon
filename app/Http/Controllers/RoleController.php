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
        'Admin Logistik Pusat',
        'Admin Logistik Proyek',
        'Site Engineer',
        'Site Manager',
        'Project Manager',
        'Admin Pengadaan',
        'Admin Pengadaan Proyek',
    ];

    /**
     * Nama-nama yang dipesan sistem (Reserved Names) yang tidak boleh dibuat kembali
     * guna menghindari kebingungan, celah duplikasi, dan alias redundan.
     */
    public const RESERVED_ROLE_NAMES = [
        'admin',
        'user',
        'admin proyek',
        'admin_proyek',
        'super admin',
        'superadmin',
        'administrator',
        'root',
        'system',
        'guest',
        'karyawan',
    ];

    /**
     * Metadata kategori operasional untuk mempercantik UI dan membedakan peran kerja.
     */
    public const ROLE_METADATA = [
        'Owner' => [
            'category'    => 'Eksekutif & Direksi',
            'icon'        => 'shield',
            'description' => 'Akses pengawasan menyeluruh laporan, keuangan, dan operasional tanpa hak modifikasi fisik.',
        ],
        'Admin Pusat' => [
            'category'    => 'Manajemen Sistem',
            'icon'        => 'shield-check',
            'description' => 'Superuser sistem: manajemen akun karyawan, penetapan gudang, hak akses jabatan, dan kontrol master data.',
        ],
        'Admin Logistik Pusat' => [
            'category'    => 'Logistik Sentral',
            'icon'        => 'warehouse',
            'description' => 'Operasional gudang pusat: penerimaan barang supplier, penerbitan surat jalan distribusi, dan stok alat.',
        ],
        'Admin Logistik Proyek' => [
            'category'    => 'Site Proyek - Logistik',
            'icon'        => 'hard-hat',
            'description' => 'Operasional gudang proyek: verifikasi penerimaan barang fisik, surat jalan masuk, dan stok lapangan.',
        ],
        'Site Engineer' => [
            'category'    => 'Site Proyek - Teknis',
            'icon'        => 'ruler',
            'description' => 'Pengawasan teknis lapangan, permohonan material (MR), pengajuan saldo kas proyek, dan log catatan cor readymix.',
        ],
        'Site Manager' => [
            'category'    => 'Site Proyek - Manajemen',
            'icon'        => 'briefcase',
            'description' => 'Pimpinan operasional proyek: persetujuan permohonan material, evaluasi log harian, dan pengawasan site.',
        ],
        'Project Manager' => [
            'category'    => 'Site Proyek - Manajemen',
            'icon'        => 'award',
            'description' => 'Pimpinan proyek strategis: persetujuan penuh kebutuhan material, kontrol inventaris, dan pengawasan proyek.',
        ],
        'Admin Pengadaan' => [
            'category'    => 'Pengadaan Pusat',
            'icon'        => 'file-spreadsheet',
            'description' => 'Pengadaan pusat: Purchase Order (PO) resmi, verifikasi nota, dan pembayaran termin vendor.',
        ],
        'Admin Pengadaan Proyek' => [
            'category'    => 'Site Proyek - Pembelian',
            'icon'        => 'receipt',
            'description' => 'Pencatatan nota pembelian harian bon toko material lokal langsung di lokasi proyek.',
        ],
    ];

    /**
     * Label representasi ramah pengguna Bahasa Indonesia untuk setiap hak akses teknis.
     */
    public const PERMISSION_LABELS = [
        // Pengguna & Master Akses
        'view users'                      => 'Lihat Pengguna',
        'create users'                    => 'Tambah Pengguna',
        'edit users'                      => 'Ubah Pengguna',
        'delete users'                    => 'Hapus Pengguna',
        'projects.manage'                 => 'Kelola Master Proyek',
        'warehouses.manage'               => 'Kelola Master Gudang',

        // Master Data
        'view materials'                  => 'Lihat Material',
        'create materials'                => 'Tambah Material',
        'edit materials'                  => 'Ubah Material',
        'delete materials'                => 'Hapus Material',
        'view tools'                      => 'Lihat Data Alat',
        'create tools'                    => 'Tambah Data Alat',
        'edit tools'                      => 'Ubah Data Alat',
        'delete tools'                    => 'Hapus Data Alat',
        'view categories'                 => 'Lihat Kategori',
        'create categories'               => 'Tambah Kategori',
        'edit categories'                 => 'Ubah Kategori',
        'delete categories'               => 'Hapus Kategori',
        'view suppliers'                  => 'Lihat Supplier',
        'create suppliers'                => 'Tambah Supplier',
        'edit suppliers'                  => 'Ubah Supplier',
        'delete suppliers'                => 'Hapus Supplier',

        // Logistik & Penerimaan
        'view goods receipts'             => 'Lihat Penerimaan Barang',
        'create goods receipts'           => 'Input Penerimaan Barang',
        'confirm goods receipts'          => 'Verifikasi Penerimaan Barang',
        'view distributions'              => 'Lihat Surat Jalan Distribusi',
        'create distributions'            => 'Buat Surat Jalan Distribusi',
        'ship distributions'              => 'Kirim Distribusi (Ekspedisi)',
        'receive distributions'           => 'Terima Distribusi di Proyek',
        'create transit losses'           => 'Catat Kerusakan/Selisih Transit',
        'approve transit losses'          => 'Setujui Selisih Transit',

        // Permintaan & Pemakaian Material
        'view material requests'          => 'Lihat Permintaan Material (MR)',
        'create material requests'        => 'Buat Permintaan Material (MR)',
        'approve material requests'       => 'Setujui Permintaan Material',
        'view material usages'            => 'Lihat Pemakaian Material',
        'create material usages'          => 'Catat Pemakaian Material',
        'cancel material usages'          => 'Batalkan Pemakaian Material',

        // Alat & Inventaris
        'view tool assignments'           => 'Lihat Peminjaman Alat',
        'create tool assignments'         => 'Pinjamkan Alat ke Proyek',
        'return tool assignments'         => 'Pengembalian Alat',
        'approve tool assignments'        => 'Setujui Peminjaman Alat',
        'cancel tool assignments'         => 'Batalkan Peminjaman Alat',
        'inspect return tool assignments' => 'Inspeksi Kondisi Alat Kembali',
        'view inventory'                  => 'Lihat Stok Inventaris',
        'delete inventory'                => 'Hapus Entri Stok Inventaris',
        'view stock opname'               => 'Lihat Stock Opname',
        'create stock opname'             => 'Input Stock Opname',
        'approve stock opname'            => 'Setujui Stock Opname',

        // Pengadaan & PO
        'view procurement'                => 'Lihat Pengadaan',
        'create procurement'              => 'Buat Pengadaan',
        'approve procurement'             => 'Setujui Pengadaan',
        'view purchase orders'            => 'Lihat Purchase Order (PO)',
        'create purchase orders'          => 'Buat Purchase Order (PO)',
        'edit purchase orders'            => 'Ubah Purchase Order (PO)',
        'approve purchase orders'         => 'Setujui Purchase Order (PO)',
        'send purchase orders'            => 'Kirim PO ke Vendor',
        'cancel purchase orders'          => 'Batalkan Purchase Order (PO)',
        'close purchase orders'           => 'Tutup PO Selesai',
        'view purchase receipts'          => 'Lihat Nota Bon Lapangan',
        'create purchase receipts'        => 'Input Nota Bon Lapangan',
        'delete purchase receipts'        => 'Hapus Nota Bon Lapangan',
        'view payments'                   => 'Lihat Pembayaran Termin',
        'create payments'                 => 'Input Pembayaran Termin',
        'verify payments'                 => 'Verifikasi Pembayaran',
        'view returns'                    => 'Lihat Retur Barang',
        'create returns'                  => 'Buat Pengajuan Retur',
        'approve returns'                 => 'Setujui Retur Barang',
        'receive returns'                 => 'Terima Bukti Retur Supplier',

        // Kasbon Proyek & Log Harian
        'view balance requests'           => 'Lihat Pengajuan Kasbon',
        'create balance requests'         => 'Ajukan Saldo Kasbon Proyek',
        'approve balance requests'        => 'Setujui Saldo Kasbon Proyek',
        'reject balance requests'         => 'Tolak Saldo Kasbon Proyek',
        'view daily logs'                 => 'Lihat Log Harian Proyek',

        // Laporan & Audit
        'view reports'                    => 'Akses Rekap & Laporan',
        'view audit logs'                 => 'Lihat Jejak Audit Sistem',
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

        // Metrik Ringkas KPI
        $stats = [
            'total_roles'     => Role::count(),
            'protected_roles' => count($protectedRoles),
            'custom_roles'    => max(0, Role::whereNotIn('name', $protectedRoles)->count()),
            'total_users'     => \Illuminate\Support\Facades\DB::table('model_has_roles')->distinct('model_id')->count('model_id'),
            'total_modules'   => count($groupedPermissions),
            'total_perms'     => Permission::count(),
        ];

        return view('roles.index', compact('roles', 'protectedRoles', 'roleMetadata', 'groupedPermissions', 'stats'));
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
        } elseif (!$isProtected && $request->has('sync_permissions')) {
            $role->syncPermissions([]);
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
        $permissions = Permission::orderBy('name')->get()->map(function ($p) {
            $p->label = self::PERMISSION_LABELS[$p->name] ?? ucwords(str_replace(['.', '_'], ' ', $p->name));
            return $p;
        });

        $modules = [
            'Pengguna & Akses' => [
                'icon'  => 'users',
                'desc'  => 'Hak pengelolaan akun staf, otorisasi peran, gudang & proyek',
                'items' => ['view users', 'create users', 'edit users', 'delete users', 'projects.manage', 'warehouses.manage'],
            ],
            'Master Data' => [
                'icon'  => 'boxes',
                'desc'  => 'Katalog material konstruksi, armada alat, kategori, dan vendor rekanan',
                'items' => ['view materials', 'create materials', 'edit materials', 'delete materials',
                            'view tools', 'create tools', 'edit tools', 'delete tools',
                            'view categories', 'create categories', 'edit categories', 'delete categories',
                            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'],
            ],
            'Logistik & Penerimaan' => [
                'icon'  => 'truck',
                'desc'  => 'Penerimaan barang fisik dari vendor & surat jalan distribusi antar gudang/site',
                'items' => ['view goods receipts', 'create goods receipts', 'confirm goods receipts',
                            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
                            'create transit losses', 'approve transit losses'],
            ],
            'Permintaan & Pemakaian' => [
                'icon'  => 'clipboard-check',
                'desc'  => 'Formulir Material Request (MR) site dan pencatatan pemakaian lapangan',
                'items' => ['view material requests', 'create material requests', 'approve material requests',
                            'view material usages', 'create material usages', 'cancel material usages'],
            ],
            'Alat & Inventaris' => [
                'icon'  => 'wrench',
                'desc'  => 'Peminjaman alat kerja, monitoring stok fisik, dan audit stock opname',
                'items' => ['view tool assignments', 'create tool assignments', 'return tool assignments',
                            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
                            'view inventory', 'delete inventory', 'view stock opname', 'create stock opname', 'approve stock opname'],
            ],
            'Pengadaan & Pembayaran' => [
                'icon'  => 'receipt',
                'desc'  => 'Penerbitan Purchase Order (PO), nota bon lokal, termin & retur barang',
                'items' => ['view procurement', 'create procurement', 'approve procurement',
                            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'approve purchase orders', 'send purchase orders', 'cancel purchase orders', 'close purchase orders',
                            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
                            'view payments', 'create payments', 'verify payments',
                            'view returns', 'create returns', 'approve returns', 'receive returns'],
            ],
            'Kas Proyek & Log Harian' => [
                'icon'  => 'wallet',
                'desc'  => 'Pengajuan saldo operasional proyek (kasbon) dan buku jurnal harian teknis',
                'items' => ['view balance requests', 'create balance requests', 'approve balance requests', 'reject balance requests',
                            'view daily logs'],
            ],
            'Laporan & Audit' => [
                'icon'  => 'chart-column',
                'desc'  => 'Akses rekapitulasi data analitik dan rekam jejak aktivitas sistem',
                'items' => ['view reports', 'view audit logs'],
            ],
        ];

        $grouped = [];
        foreach ($modules as $groupName => $meta) {
            $grouped[$groupName] = [
                'icon'        => $meta['icon'],
                'desc'        => $meta['desc'],
                'permissions' => $permissions->whereIn('name', $meta['items'])->values(),
            ];
        }

        // Izin lain yang belum masuk kelompok di atas
        $categorizedNames = collect($modules)->flatMap(fn($m) => $m['items'])->toArray();
        $remaining = $permissions->whereNotIn('name', $categorizedNames)->values();
        if ($remaining->isNotEmpty()) {
            $grouped['Izin Tambahan Lainnya'] = [
                'icon'        => 'shield',
                'desc'        => 'Wewenang sistem tambahan',
                'permissions' => $remaining,
            ];
        }

        return $grouped;
    }
}
