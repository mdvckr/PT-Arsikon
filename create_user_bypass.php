<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Project;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

echo "Resetting permissions...\n";
app()[PermissionRegistrar::class]->forgetCachedPermissions();

echo "Creating permissions...\n";
$permissions = [
    'users.manage', 'projects.manage', 'warehouses.manage', 'suppliers.manage',
    'materials.manage', 'tools.manage', 'goods_receipts.create', 'material_requests.create',
    'material_requests.approve', 'distributions.create', 'distributions.receive',
    'tools.assign', 'tools.inspect_return', 'stock_opname.create', 'stock_opname.approve',
    'reports.view_all', 'audit_logs.view'
];
foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission]);
}

echo "Creating roles...\n";
$ownerRole = Role::firstOrCreate(['name' => 'Owner']);
$ownerRole->syncPermissions(Permission::all());

echo "Creating warehouses...\n";
$centralWarehouse = Warehouse::firstOrCreate(
    ['code' => 'W-CENTRAL'],
    ['name' => 'Gudang Pusat PT Arsikon', 'type' => 'central', 'is_central' => true, 'address' => 'Jl. Industri Utama']
);

echo "Creating user...\n";
$ownerUser = User::firstOrCreate(
    ['email' => 'owner@arsikon.co.id'],
    ['name' => 'Bapak Owner', 'password' => Hash::make('password123'), 'is_active' => true]
);
$ownerUser->assignRole($ownerRole);
$ownerUser->warehouses()->syncWithoutDetaching([$centralWarehouse->id]);

echo "DONE!\n";
