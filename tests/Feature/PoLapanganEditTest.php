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
use Tests\TestCase;

class PoLapanganEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminProyek;
    protected User $otherAdminProyek;
    protected User $adminPusat;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh;
    protected Material $material1;
    protected Material $material2;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->otherAdminProyek = User::where('email', 'karyawan@arsikon.co.id')->firstOrFail();
        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $project = Project::create(['name' => 'Proyek Delta', 'code' => 'PRJ-DLT', 'status' => 'active']);
        $this->projectWh = Warehouse::create([
            'name'       => 'Gudang Delta',
            'code'       => 'WH-DLT',
            'type'       => 'project',
            'is_central' => false,
            'is_active'  => true,
            'project_id' => $project->id,
        ]);
        $this->adminProyek->warehouses()->sync([$this->projectWh->id]);

        $this->category = Category::first() ?? Category::create(['name' => 'Umum', 'code' => 'UMM', 'type' => 'material']);
        $this->unit = Unit::first() ?? Unit::create(['name' => 'Pcs', 'code' => 'pcs', 'is_decimal' => false]);

        $this->material1 = Material::create([
            'name'        => 'Besi Beton 10mm',
            'sku'         => 'MAT-BSI-10',
            'category_id' => $this->category->id,
            'unit_id'     => $this->unit->id,
            'is_active'   => true,
        ]);

        $this->material2 = Material::create([
            'name'        => 'Besi Beton 12mm',
            'sku'         => 'MAT-BSI-12',
            'category_id' => $this->category->id,
            'unit_id'     => $this->unit->id,
            'is_active'   => true,
        ]);
    }

    public function test_creator_can_view_edit_and_update_when_submitted(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-EDIT-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'requested_as'         => 'Admin Proyek',
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(5)->format('Y-m-d'),
            'notes'                => 'Catatan awal',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->material1->id,
            'qty_requested'       => 10,
        ]);

        // 1. Tampilkan form edit
        $resEdit = $this->actingAs($this->adminProyek)->get(route('material-requests.edit', $mr));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Edit PO Lapangan');

        // 2. Submit update
        $resUpdate = $this->actingAs($this->adminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(7)->format('Y-m-d'),
            'requested_as'        => 'Site Manager',
            'notes'               => 'Catatan diperbarui',
            'quantities'          => [$this->material1->id => 20],
        ]);

        $resUpdate->assertRedirect(route('material-requests.show', $mr));

        $mr->refresh();
        $this->assertEquals('Catatan diperbarui', $mr->notes);
        $this->assertEquals('Site Manager', $mr->requested_as);
        $this->assertEquals(20, (float) $mr->items->first()->qty_requested);
    }

    public function test_unauthorized_user_cannot_edit_and_gets_403(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-UNAUTH-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
        ]);

        $resEdit = $this->actingAs($this->otherAdminProyek)->get(route('material-requests.edit', $mr));
        $resEdit->assertStatus(403);

        $resUpdate = $this->actingAs($this->otherAdminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id' => $this->projectWh->id,
            'notes'        => 'Hacked notes',
        ]);
        $resUpdate->assertStatus(403);
    }

    public function test_cannot_edit_when_status_already_approved(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-APPROVED-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'approved',
        ]);

        // Edit form harus 403
        $resEdit = $this->actingAs($this->adminProyek)->get(route('material-requests.edit', $mr));
        $resEdit->assertStatus(403);

        // Update put harus 403
        $resUpdate = $this->actingAs($this->adminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id' => $this->projectWh->id,
            'notes'        => 'Should fail',
        ]);
        $resUpdate->assertStatus(403);

        // Di halaman show tombol edit tidak boleh tampil
        $resShow = $this->actingAs($this->adminProyek)->get(route('material-requests.show', $mr));
        $resShow->assertStatus(200);
        $resShow->assertDontSee(route('material-requests.edit', $mr));
    }

    public function test_admin_pusat_can_edit_submitted_request(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-PUSAT-EDIT-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(4)->format('Y-m-d'),
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->material1->id,
            'qty_requested'       => 10,
        ]);

        $resEdit = $this->actingAs($this->adminPusat)->get(route('material-requests.edit', $mr));
        $resEdit->assertStatus(200);

        $resUpdate = $this->actingAs($this->adminPusat)->put(route('material-requests.update', $mr), [
            'warehouse_id' => $this->projectWh->id,
            'notes'        => 'Diedit oleh Pusat',
            'quantities'   => [$this->material1->id => 12],
        ]);
        $resUpdate->assertRedirect(route('material-requests.show', $mr));
    }

    public function test_add_modify_and_remove_items_during_edit(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-ITEM-MUTATE-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(5)->format('Y-m-d'),
        ]);

        // Awalnya punya material1 (qty: 5)
        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->material1->id,
            'qty_requested'       => 5,
        ]);

        // Edit: ubah material1 jadi 8, tambahkan material2 qty 15, tambahkan custom item
        $response = $this->actingAs($this->adminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'quantities'          => [
                $this->material1->id => 8,
                $this->material2->id => 15,
            ],
            'custom_items'        => [
                [
                    'name' => 'Kawat Tali Pengikat',
                    'qty'  => 3,
                    'unit' => 'rol',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $mr->refresh();
        $this->assertCount(3, $mr->items);
        $this->assertEquals(8, (float) $mr->items->where('material_id', $this->material1->id)->first()->qty_requested);
        $this->assertEquals(15, (float) $mr->items->where('material_id', $this->material2->id)->first()->qty_requested);
        $this->assertNotNull($mr->items->where('custom_item_name', 'Kawat Tali Pengikat')->first());
    }

    public function test_needed_at_in_the_past_does_not_fail_if_not_modified(): void
    {
        $pastDate = now()->subDays(2)->format('Y-m-d');
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-PAST-DATE-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => $pastDate,
            'notes'                => 'Catatan lama',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->material1->id,
            'qty_requested'       => 5,
        ]);

        // Update tanpa mengubah needed_at (mengirim nilai yang sama)
        $response = $this->actingAs($this->adminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => $pastDate, // tanggal lama yang sudah lewat
            'notes'               => 'Hanya ubah catatan',
            'quantities'          => [$this->material1->id => 5],
        ]);

        $response->assertSessionHasNoErrors();
        $mr->refresh();
        $this->assertEquals('Hanya ubah catatan', $mr->notes);
        $this->assertEquals($pastDate, $mr->needed_at->format('Y-m-d'));
    }

    public function test_qty_approved_cannot_be_injected_via_edit(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-INJECT-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(5)->format('Y-m-d'),
        ]);

        $item = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'item_type'           => 'material',
            'material_id'         => $this->material1->id,
            'qty_requested'       => 10,
            'qty_approved'        => 0,
        ]);

        // Coba injeksi qty_approved lewat edit request
        $this->actingAs($this->adminProyek)->put(route('material-requests.update', $mr), [
            'warehouse_id' => $this->projectWh->id,
            'quantities'   => [$this->material1->id => 10],
            'qty_approved' => [$this->material1->id => 100], // upaya injeksi
        ]);

        $item->refresh();
        $this->assertEquals(0, (float) $item->qty_approved, 'qty_approved tidak boleh berubah lewat form edit pemohon.');
    }
}
