<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MaterialRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoLapanganCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminProyek;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh;
    protected Category $categorySemen;
    protected Category $categoryBesi;
    protected Material $materialSemen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $project = Project::create([
            'name' => 'Proyek Uji Kategori',
            'code' => 'PRJ-CAT-01',
            'status' => 'active',
        ]);

        $this->projectWh = Warehouse::create([
            'name' => 'Gudang Site Kategori',
            'code' => 'WH-CAT-01',
            'type' => 'project',
            'is_central' => false,
            'is_active' => true,
            'project_id' => $project->id,
        ]);
        $this->adminProyek->warehouses()->attach($this->projectWh->id);

        $this->categorySemen = Category::create([
            'name' => 'Semen & Mortar',
            'code' => 'CAT-SMN',
            'type' => 'material',
        ]);

        $this->categoryBesi = Category::create([
            'name' => 'Besi & Baja',
            'code' => 'CAT-BSI',
            'type' => 'material',
        ]);

        $unit = \App\Models\Unit::first() ?? \App\Models\Unit::create(['name' => 'Sak', 'code' => 'sak', 'is_decimal' => false]);

        $this->materialSemen = Material::create([
            'name' => 'Semen Tiga Roda 50kg',
            'sku' => 'MAT-SMN-01',
            'category_id' => $this->categorySemen->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
    }

    public function test_master_item_saves_category_from_material_ignoring_client_input(): void
    {
        $service = app(MaterialRequestService::class);

        // Klien mencoba mengirim category_id = categoryBesi padahal materialSemen adalah categorySemen
        $mr = $service->createRequest(
            $this->projectWh,
            $this->centralWh,
            $this->adminProyek,
            [
                [
                    'material_id'   => $this->materialSemen->id,
                    'category_id'   => $this->categoryBesi->id, // harus diabaikan!
                    'qty_requested' => 10,
                ],
            ]
        );

        $item = $mr->items()->first();
        $this->assertNotNull($item);
        $this->assertEquals($this->categorySemen->id, $item->category_id);
    }

    public function test_custom_item_with_invalid_category_is_rejected(): void
    {
        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(2)->format('Y-m-d'),
            'custom_items'        => [
                [
                    'name'        => 'Item Custom Khusus',
                    'qty'         => 5,
                    'category_id' => 999999, // tidak valid
                ],
            ],
        ]);

        $response->assertSessionHasErrors('custom_items.0.category_id');
    }

    public function test_custom_item_with_valid_category_is_saved(): void
    {
        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(2)->format('Y-m-d'),
            'custom_items'        => [
                [
                    'name'        => 'Kawat Las Khusus',
                    'qty'         => 5,
                    'unit'        => 'box',
                    'category_id' => $this->categoryBesi->id,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $mr = MaterialRequest::latest()->first();
        $item = $mr->items()->first();
        $this->assertEquals($this->categoryBesi->id, $item->category_id);
    }

    public function test_legacy_data_with_null_category_renders_properly(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-LEGACY-001',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'approved',
        ]);

        $item = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id'         => null,
            'custom_item_name'    => 'Barang Lama Tanpa Kategori',
            'qty_requested'       => 2,
            'qty_approved'        => 2,
            'category_id'         => null,
        ]);

        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));
        $response->assertStatus(200);
        $response->assertSee('Barang Lama Tanpa Kategori');
        $this->assertEquals('-', $item->displayCategoryName());
    }

    public function test_category_filter_in_material_request_index(): void
    {
        $service = app(MaterialRequestService::class);

        // MR 1: Semen
        $mr1 = $service->createRequest(
            $this->projectWh,
            $this->centralWh,
            $this->adminProyek,
            [
                ['material_id' => $this->materialSemen->id, 'qty_requested' => 5],
            ]
        );

        // MR 2: Custom Besi
        $mr2 = $service->createRequest(
            $this->projectWh,
            $this->centralWh,
            $this->adminProyek,
            [
                [
                    'material_id'      => null,
                    'custom_item_name' => 'Baja WF Manual',
                    'category_id'      => $this->categoryBesi->id,
                    'qty_requested'    => 2,
                ],
            ]
        );

        // Filter category semen
        $resSemen = $this->actingAs($this->adminPusat)->get(route('material-requests.index', [
            'category_id' => $this->categorySemen->id,
        ]));
        $resSemen->assertStatus(200);
        $resSemen->assertSee($mr1->request_number);
        $resSemen->assertDontSee($mr2->request_number);

        // Filter category besi
        $resBesi = $this->actingAs($this->adminPusat)->get(route('material-requests.index', [
            'category_id' => $this->categoryBesi->id,
        ]));
        $resBesi->assertStatus(200);
        $resBesi->assertSee($mr2->request_number);
        $resBesi->assertDontSee($mr1->request_number);
    }
}
