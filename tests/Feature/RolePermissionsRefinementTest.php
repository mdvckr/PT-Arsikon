<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_proyek_has_access_to_technical_po_lapangan_inventory_and_daily_log_only(): void
    {
        $role = Role::findByName('Admin Proyek');

        // Boleh: PO Lapangan (material requests), inventory, daily log (reports)
        $this->assertTrue($role->hasPermissionTo('view material requests'), 'Admin Proyek harus bisa melihat PO Lapangan');
        $this->assertTrue($role->hasPermissionTo('create material requests'), 'Admin Proyek harus bisa membuat PO Lapangan');
        $this->assertTrue($role->hasPermissionTo('view inventory'), 'Admin Proyek harus bisa melihat inventory');
        $this->assertTrue($role->hasPermissionTo('view reports'), 'Admin Proyek harus bisa melihat daily log / reports');

        // Tidak Boleh: Logistik fisik operasional lapangan
        $this->assertFalse($role->hasPermissionTo('create goods receipts'), 'Admin Proyek tidak boleh membuat penerimaan barang');
        $this->assertFalse($role->hasPermissionTo('create tool assignments'), 'Admin Proyek tidak boleh membuat peminjaman alat');
        $this->assertFalse($role->hasPermissionTo('create material usages'), 'Admin Proyek tidak boleh membuat pemakaian material');
        $this->assertFalse($role->hasPermissionTo('create distributions'), 'Admin Proyek tidak boleh membuat surat jalan');
    }

    public function test_gudang_proyek_has_access_strictly_to_logistics_only(): void
    {
        $role = Role::findByName('Admin Gudang Proyek');

        // Boleh: Surat Jalan (distributions), Penerimaan (goods receipts), Peminjaman alat, Pemakaian material, Inventory, Material, Alat
        $this->assertTrue($role->hasPermissionTo('view distributions'), 'Gudang Proyek harus bisa melihat surat jalan');
        $this->assertTrue($role->hasPermissionTo('create distributions'), 'Gudang Proyek harus bisa membuat surat jalan');
        $this->assertTrue($role->hasPermissionTo('view goods receipts'), 'Gudang Proyek harus bisa melihat penerimaan barang');
        $this->assertTrue($role->hasPermissionTo('create goods receipts'), 'Gudang Proyek harus bisa membuat penerimaan barang');
        $this->assertTrue($role->hasPermissionTo('view tool assignments'), 'Gudang Proyek harus bisa melihat peminjaman alat');
        $this->assertTrue($role->hasPermissionTo('view material usages'), 'Gudang Proyek harus bisa melihat pemakaian material');
        $this->assertTrue($role->hasPermissionTo('view inventory'), 'Gudang Proyek harus bisa melihat inventory');
        $this->assertTrue($role->hasPermissionTo('view materials'), 'Gudang Proyek harus bisa melihat master material');
        $this->assertTrue($role->hasPermissionTo('view tools'), 'Gudang Proyek harus bisa melihat master alat');

        // Tidak Boleh: PO Lapangan teknis & Modul Retur / Stock Opname yang dihapus
        $this->assertFalse($role->hasPermissionTo('create material requests'), 'Gudang Proyek tidak boleh membuat PO Lapangan');
        $this->assertFalse($role->hasPermissionTo('view returns'), 'Gudang Proyek tidak boleh memiliki akses retur');
        $this->assertFalse($role->hasPermissionTo('view stock opname'), 'Gudang Proyek tidak boleh memiliki akses stock opname');
    }
}

