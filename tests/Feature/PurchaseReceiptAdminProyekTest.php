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

    public function test_admin_gudang_proyek_can_access_purchase_receipts_index_and_create(): void
    {
        $responseIndex = $this->actingAs($this->adminProyek)->get(route('purchase-receipts.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Nota Pembelian Harian');

        $responseCreate = $this->actingAs($this->adminProyek)->get(route('purchase-receipts.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Catat Nota Pembelian Harian');
    }

    public function test_admin_gudang_proyek_can_store_purchase_receipt(): void
    {
        $payload = [
            'project_name'   => 'Proyek Uji Coba Lapangan',
            'supplier_name'  => 'Toko Material Sentosa Jaya',
            'receipt_number' => 'NOTA-SITE-001',
            'receipt_date'   => now()->toDateString(),
            'payment_status' => 'paid',
            'notes'          => 'Pembelian semen darurat di toko lokal',
            'items'          => [
                [
                    'material_id' => $this->material->id,
                    'item_name'   => $this->material->name,
                    'quantity'    => 5,
                    'unit_price'  => 65000,
                    'notes'       => 'Urgent lantai 1',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminProyek)->post(route('purchase-receipts.store'), $payload);

        $receipt = PurchaseReceipt::where('receipt_number', 'NOTA-SITE-001')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals(325000, (float) $receipt->total_amount);
        $this->assertEquals($this->adminProyek->id, $receipt->created_by);

        $response->assertRedirect(route('purchase-receipts.show', $receipt));
    }

    public function test_karyawan_cannot_access_purchase_receipts(): void
    {
        $responseIndex = $this->actingAs($this->karyawan)->get(route('purchase-receipts.index'));
        $responseIndex->assertForbidden();

        $responseCreate = $this->actingAs($this->karyawan)->get(route('purchase-receipts.create'));
        $responseCreate->assertForbidden();
    }
}
