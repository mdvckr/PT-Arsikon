<?php

namespace App\Support;

class RolePermissionDefinitions {
    /**
     * Daftar permission kanonikal (format spasi) sebagai satu-satunya sumber kebenaran.
     */
    public const PERMISSIONS = [
        'view users', 'create users', 'edit users', 'delete users',
        'projects.manage',
        'warehouses.manage',
        'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
        'view materials', 'create materials', 'edit materials', 'delete materials',
        'view tools', 'create tools', 'edit tools', 'delete tools',
        'view categories', 'create categories', 'edit categories', 'delete categories',
        'view goods receipts', 'create goods receipts', 'confirm goods receipts',
        'view material requests', 'create material requests', 'approve material requests',
        'view material usages', 'create material usages', 'cancel material usages',
        'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
        'view tool assignments', 'create tool assignments', 'return tool assignments',
        'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
        'view inventory', 'delete inventory',
        'view procurement', 'create procurement', 'approve procurement',
        'view purchase orders', 'create purchase orders', 'edit purchase orders', 'approve purchase orders', 'send purchase orders', 'cancel purchase orders', 'close purchase orders',
        'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
        'view payments', 'create payments', 'verify payments',
        'view balance requests', 'create balance requests', 'approve balance requests', 'reject balance requests',
        'create transit losses', 'approve transit losses',
        'view daily logs',
        'view reports', 'view audit logs',
    ];

    /**
     * Peta alias legacy -> role kanonikal.
     */
    public const ALIAS_MAP = [
        'Admin' => 'Admin Pusat',
        'User' => 'Site Engineer',
        'Admin Proyek' => 'Site Engineer',
        'Admin Logistik Pusat' => 'Admin Logistik Pusat',
        'Admin Logistik Proyek' => 'Admin Logistik Proyek',
        'Admin Pengadaan' => 'Admin Pengadaan',
        'Admin Pengadaan Proyek' => 'Admin Pengadaan Proyek',
        'admin-po-nota' => 'Admin Pengadaan Proyek',
        'po_nota_harian' => 'Admin Pengadaan Proyek',
        'po_lapangan' => 'Admin Pengadaan Proyek',
        'admin_pusat' => 'Admin Pusat',
        'admin_po' => 'Admin Pengadaan',
        'admin_po_proyek' => 'Admin Pengadaan Proyek',
        'Karyawan' => 'Site Engineer',
        'karyawan' => 'Site Engineer',
    ];

    /**
     * Peta role kanonikal -> permission.
     */
    public const ROLE_PERMISSIONS = [
        'Owner' => null, // semua view %
        'Admin Pusat' => null, // semua permission (full access)
        'Admin Logistik Pusat' => [
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials', 'create materials', 'edit materials', 'delete materials',
            'view tools', 'create tools', 'edit tools', 'delete tools',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material usages', 'create material usages', 'cancel material usages',
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            'view inventory',
            'create transit losses',
        ],
        'Admin Logistik Proyek' => [
            'view distributions', 'create distributions', 'ship distributions', 'receive distributions',
            'view goods receipts', 'create goods receipts', 'confirm goods receipts',
            'view material usages', 'create material usages', 'cancel material usages',
            'view tool assignments', 'create tool assignments', 'return tool assignments',
            'approve tool assignments', 'cancel tool assignments', 'inspect return tool assignments',
            'view inventory',
            'view materials', 'create materials', 'edit materials',
            'view tools', 'create tools', 'edit tools',
            'view categories', 'create categories',
            'view suppliers', 'create suppliers',
            'create transit losses',
        ],
        'Site Engineer' => [
            'view daily logs',
            'view material requests', 'create material requests',
            'view inventory',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ],
        'Site Manager' => [
            'view daily logs',
            'view goods receipts', 'create goods receipts',
            'view material requests', 'create material requests', 'approve material requests',
            'view inventory',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ],
        'Project Manager' => [
            'view daily logs',
            'view goods receipts', 'create goods receipts',
            'view material requests', 'create material requests', 'approve material requests',
            'view inventory',
            'view materials',
            'view tools',
            'view categories',
            'projects.manage',
        ],
        'Admin Pengadaan' => [
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view materials',
            'view tools',
            'view inventory',
            'view material requests', 'approve material requests',
            'view procurement', 'create procurement',
            'view purchase orders', 'create purchase orders', 'edit purchase orders', 'approve purchase orders', 'send purchase orders', 'cancel purchase orders',
            'view purchase receipts', 'create purchase receipts', 'delete purchase receipts',
            'view payments', 'create payments',
            'view balance requests', 'approve balance requests', 'reject balance requests',
        ],
        'Admin Pengadaan Proyek' => [
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
            'view materials',
            'view suppliers',
            'view balance requests',
            'create balance requests',
        ],
    ];

    public static function permissions(): array
    {
        return self::PERMISSIONS;
    }

    public static function aliasMap(): array
    {
        return self::ALIAS_MAP;
    }

    public static function rolePermissions(string $role): ?array
    {
        return self::ROLE_PERMISSIONS[$role] ?? null;
    }
}
