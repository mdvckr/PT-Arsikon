<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialRequestViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Pusat');

        $this->centralWarehouse = Warehouse::create([
            'name'       => 'Gudang Pusat',
            'code'       => 'WH-CENTRAL',
            'is_central' => true,
            'is_active'  => true,
        ]);

        $this->project = Project::create([
            'name' => 'Proyek Test UI',
            'code' => 'PRJ-TEST',
            'status' => 'active',
        ]);

        $this->projectWarehouse = Warehouse::create([
            'name'       => 'Gudang Proyek Test UI',
            'code'       => 'WH-PRJ-01',
            'is_central' => false,
            'is_active'  => true,
            'project_id' => $this->project->id,
        ]);

        $this->user->warehouses()->sync([$this->centralWarehouse->id, $this->projectWarehouse->id]);
    }

    public function test_create_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('material-requests.create'));
        $response->assertStatus(200);
        $response->assertSee('Buat PO Lapangan');
        $response->assertDontSee('3. Daftar Material Tersedia (Gudang Terpilih)');
        $response->assertSee('Rincian Item / Material yang Diminta');
        $response->assertSee('Ajukan PO Lapangan');
    }

    public function test_show_page_renders_successfully(): void
    {
        $category = Category::create(['name' => 'Semen & Pasir', 'code' => 'CAT-SMP', 'type' => 'material']);
        $unit = Unit::create(['name' => 'Sak', 'code' => 'sak']);
        $material = Material::create([
            'name' => 'Semen Gresik 40kg',
            'sku' => 'SKU-SMN-01',
            'unit_id' => $unit->id,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $mr = MaterialRequest::create([
            'request_number' => 'REQ-20261013-0001',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->user->id,
            'status' => 'approved',
            'needed_at' => now()->addDays(2),
            'notes' => 'Catatan tes UI',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $material->id,
            'qty_requested' => 100,
            'qty_approved' => 100,
            'specification' => 'Type 1',
            'usage_location' => 'Zona 1',
            'notes' => 'Catatan item',
        ]);

        $response = $this->actingAs($this->user)->get(route('material-requests.show', $mr));
        $response->assertStatus(200);
        $response->assertSee('REQ-20261013-0001');
        $response->assertSee('Disetujui');
        $response->assertSee('Proyek Pemohon');
        $response->assertSee('Rincian Item PO Lapangan');
        $response->assertSee('Semen Gresik 40kg');
    }

    public function test_edit_page_renders_successfully(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'REQ-20261013-0002',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->user->id,
            'status' => 'draft',
            'needed_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->user)->get(route('material-requests.edit', $mr));
        $response->assertStatus(200);
        $response->assertSee('Edit PO Lapangan');
        $response->assertSee('Simpan Perubahan PO Lapangan');
    }

    public function test_can_submit_po_lapangan_with_items_table(): void
    {
        $cat = Category::create(['name' => 'Besi', 'code' => 'CAT-BSI', 'type' => 'material']);
        $unit = Unit::create(['name' => 'Batang', 'code' => 'btg']);
        $material = Material::create([
            'name' => 'Besi 10 Ulir',
            'sku' => 'SKU-BSI-10',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $postData = [
            'warehouse_id' => $this->projectWarehouse->id,
            'source_warehouse_id' => '',
            'requested_as' => 'Site Engineer',
            'notes' => 'Kebutuhan mendesak',
            'custom_items' => [
                [
                    'material_id' => $material->id,
                    'name' => $material->name,
                    'item_type' => 'material',
                    'spec' => '10mm',
                    'unit' => 'btg',
                    'qty' => 50,
                    'location' => 'Zona 1',
                    'notes' => 'Urat cor',
                ],
                [
                    'material_id' => null,
                    'name' => 'Triplek Cor 9mm',
                    'item_type' => 'material',
                    'spec' => '1.2 x 2.4',
                    'unit' => 'lbr',
                    'qty' => 20,
                    'location' => 'Zona 2',
                    'notes' => 'Bekisting',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('material-requests.store'), $postData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $mr = MaterialRequest::where('to_warehouse_id', $this->projectWarehouse->id)->latest()->first();
        $this->assertNotNull($mr);
        $this->assertCount(2, $mr->items);

        $masterItem = $mr->items->firstWhere('material_id', $material->id);
        $this->assertNotNull($masterItem);
        $this->assertEquals(50, (float)$masterItem->qty_requested);

        $customItem = $mr->items->firstWhere('material_id', null);
        $this->assertNotNull($customItem);
        $this->assertEquals('Triplek Cor 9mm', $customItem->custom_item_name);
        $this->assertEquals(20, (float)$customItem->qty_requested);
    }

    public function test_can_update_po_lapangan_items(): void
    {
        $cat = Category::create(['name' => 'Semen', 'code' => 'CAT-SMN-UPD', 'type' => 'material']);
        $unit = Unit::create(['name' => 'Sak', 'code' => 'sak']);
        $material = Material::create([
            'name' => 'Semen Padang 50kg',
            'sku' => 'SKU-SMN-PDG',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $mr = MaterialRequest::create([
            'request_number' => 'REQ-20261013-0003',
            'from_warehouse_id' => null,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->user->id,
            'status' => 'draft',
            'needed_at' => now()->addDays(2),
        ]);

        $item1 = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $material->id,
            'qty_requested' => 10,
        ]);

        $updateData = [
            'warehouse_id' => $this->projectWarehouse->id,
            'source_warehouse_id' => '',
            'requested_as' => 'Site Manager',
            'notes' => 'Catatan revisi',
            'custom_items' => [
                [
                    'material_id' => $material->id,
                    'name' => $material->name,
                    'item_type' => 'material',
                    'qty' => 25,
                    'unit' => 'sak',
                ],
                [
                    'material_id' => null,
                    'name' => 'Kawat Bendrat',
                    'item_type' => 'material',
                    'qty' => 5,
                    'unit' => 'roll',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->put(route('material-requests.update', $mr), $updateData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('material-requests.show', $mr));

        $mr->refresh();
        $this->assertEquals('Site Manager', $mr->requested_as);
        $this->assertEquals('Catatan revisi', $mr->notes);
        $this->assertCount(2, $mr->items);

        $updatedItem1 = $mr->items->firstWhere('material_id', $material->id);
        $this->assertEquals(25, (float)$updatedItem1->qty_requested);

        $newItem = $mr->items->firstWhere('material_id', null);
        $this->assertNotNull($newItem);
        $this->assertEquals('Kawat Bendrat', $newItem->custom_item_name);
        $this->assertEquals(5, (float)$newItem->qty_requested);
    }
}
