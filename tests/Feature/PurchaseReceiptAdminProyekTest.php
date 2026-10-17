<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseReceipt;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReceiptAdminProyekTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminProyek;
    protected User $karyawan;
    protected Warehouse $projectWarehouse;
    protected Project $project;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);

        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'admin.proyek1@arsikon.co.id']);
        $this->adminProyek->syncRoles(['Admin Gudang Proyek']);

        $this->karyawan = User::where('email', 'karyawan@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'karyawan@arsikon.co.id']);
        $this->karyawan->syncRoles(['Karyawan']);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first()
            ?? Warehouse::create(['name' => 'Gudang Uji', 'code' => 'WH-TEST', 'type' => 'project', 'is_central' => false]);
        $this->project = Project::firstOrCreate(['code' => 'PRJ-TEST'], ['name' => 'Proyek Uji Coba', 'status' => 'active']);
        $this->material = Material::first() ?? Material::create([
            'sku' => 'TEST-MAT-PR',
            'name' => 'Batu Split Uji',
            'category_id' => \App\Models\Category::first()->id,
            'unit_id' => \App\Models\Unit::first()->id,
        ]);
    }

    public function test_admin_gudang_proyek_cannot_access_purchase_receipts(): void
    {
        $responseIndex = $this->actingAs($this->adminProyek)->get(route('purchase-receipts.index'));
        $responseIndex->assertForbidden();

        $responseCreate = $this->actingAs($this->adminProyek)->get(route('purchase-receipts.create'));
        $responseCreate->assertForbidden();
    }

    public function test_karyawan_cannot_access_purchase_receipts(): void
    {
        $responseIndex = $this->actingAs($this->karyawan)->get(route('purchase-receipts.index'));
        $responseIndex->assertForbidden();

        $responseCreate = $this->actingAs($this->karyawan)->get(route('purchase-receipts.create'));
        $responseCreate->assertForbidden();
    }
}
