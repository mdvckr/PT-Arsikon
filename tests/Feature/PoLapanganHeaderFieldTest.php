<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoLapanganHeaderFieldTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminProyek;
    protected User $adminPusat;
    protected Warehouse $centralWh;
    protected Warehouse $projectWh1;
    protected Warehouse $projectWh2;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->centralWh = Warehouse::where('is_central', true)->firstOrFail();

        $prj1 = Project::create(['name' => 'Proyek Alpha', 'code' => 'PRJ-A', 'status' => 'active']);
        $prj2 = Project::create(['name' => 'Proyek Beta', 'code' => 'PRJ-B', 'status' => 'active']);

        $this->projectWh1 = Warehouse::create([
            'name' => 'Gudang Site Alpha',
            'code' => 'WH-A',
            'type' => 'project',
            'is_central' => false,
            'is_active' => true,
            'project_id' => $prj1->id,
        ]);

        $this->projectWh2 = Warehouse::create([
            'name' => 'Gudang Site Beta',
            'code' => 'WH-B',
            'type' => 'project',
            'is_central' => false,
            'is_active' => true,
            'project_id' => $prj2->id,
        ]);

        // User adminProyek hanya punya akses ke projectWh1
        $this->adminProyek->warehouses()->sync([$this->projectWh1->id]);

        $cat = Category::first() ?? Category::create(['name' => 'Umum', 'code' => 'UMM', 'type' => 'material']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Pcs', 'code' => 'pcs', 'is_decimal' => false]);

        $this->material = Material::create([
            'name' => 'Paku Beton 5cm',
            'sku' => 'MAT-PKU-01',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_warehouse_submission_is_rejected(): void
    {
        // adminProyek mencoba membuat pengajuan untuk projectWh2 (di luar hak aksesnya)
        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh2->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->addDays(3)->format('Y-m-d'),
            'quantities'          => [$this->material->id => 10],
        ]);

        // Harus ditolak dengan status 403 atau error validasi akses
        $this->assertTrue(
            $response->status() === 403 || $response->isInvalid() || session('errors') !== null,
            'Pengajuan ke gudang di luar hak akses harus ditolak.'
        );
    }

    public function test_source_warehouse_same_as_target_warehouse_is_rejected(): void
    {
        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh1->id,
            'source_warehouse_id' => $this->projectWh1->id, // sama!
            'needed_at'           => now()->addDays(3)->format('Y-m-d'),
            'quantities'          => [$this->material->id => 10],
        ]);

        $response->assertSessionHasErrors(['source_warehouse_id', 'warehouse_id']);
    }

    public function test_needed_date_before_order_date_is_rejected(): void
    {
        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh1->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => now()->subDay()->format('Y-m-d'), // kemarin (sebelum hari ini)
            'quantities'          => [$this->material->id => 10],
        ]);

        $response->assertSessionHasErrors('needed_at');
    }

    public function test_valid_header_fields_saved_and_order_date_needed_date_separated(): void
    {
        $targetDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh1->id,
            'source_warehouse_id' => $this->centralWh->id,
            'needed_at'           => $targetDate,
            'quantities'          => [$this->material->id => 5],
        ]);

        $response->assertSessionHasNoErrors();
        $mr = MaterialRequest::latest()->first();
        $this->assertNotNull($mr);
        $this->assertEquals($targetDate, $mr->needed_at ? \Carbon\Carbon::parse($mr->needed_at)->format('Y-m-d') : null);

        // Halaman detail harus memuat label Tgl Dibutuhkan
        $showRes = $this->actingAs($this->adminProyek)->get(route('material-requests.show', $mr));
        $showRes->assertStatus(200);
        $showRes->assertSee('Tgl Dibutuhkan');
    }

    public function test_overdue_indicator_is_computed_on_display_when_needed_date_passed(): void
    {
        // Buat MR yang dibutuhkan 3 hari yang lalu dan status masih submitted
        $pastDate = now()->subDays(3)->format('Y-m-d');
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-OVERDUE-01',
            'from_warehouse_id'    => $this->centralWh->id,
            'to_warehouse_id'      => $this->projectWh1->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => $pastDate,
        ]);

        $response = $this->actingAs($this->adminPusat)->get(route('material-requests.show', $mr));
        $response->assertStatus(200);
        // Harus ada badge / penanda Lewat Tanggal / Terlambat
        $this->assertTrue(
            str_contains($response->getContent(), 'Terlambat') ||
            str_contains($response->getContent(), 'Lewat') ||
            str_contains($response->getContent(), 'Overdue'),
            'Penanda lewat tanggal dibutuhkan harus tampil saat needed_at lewat.'
        );
    }

    public function test_create_page_defaults_kepada_to_pengadaan(): void
    {
        $response = $this->actingAs($this->adminProyek)->get(route('material-requests.create'));
        $response->assertStatus(200);

        // Opsi '— Pengadaan —' harus terpilih secara default
        $response->assertSee('<option value="" selected>— Pengadaan —</option>', false);
    }

    public function test_can_submit_material_request_to_pengadaan_without_source_warehouse(): void
    {
        $targetDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->adminProyek)->post(route('material-requests.store'), [
            'warehouse_id'        => $this->projectWh1->id,
            'source_warehouse_id' => '', // Pengadaan
            'needed_at'           => $targetDate,
            'quantities'          => [$this->material->id => 12],
        ]);

        $response->assertSessionHasNoErrors();
        $mr = MaterialRequest::latest()->first();
        $this->assertNotNull($mr);
        $this->assertNull($mr->from_warehouse_id);
    }

    public function test_print_pdf_kepada_shows_pengadaan_without_beli_ke_vendor(): void
    {
        $mr = MaterialRequest::create([
            'request_number'       => 'MR-PRINT-01',
            'from_warehouse_id'    => null, // Pengadaan
            'to_warehouse_id'      => $this->projectWh1->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status'               => 'submitted',
            'needed_at'            => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->adminProyek)->get(route('material-requests.show', $mr) . '?print=1');
        $response->assertStatus(200);
        $response->assertSee('<span class="hf-val">Pengadaan</span>', false);
        $response->assertDontSee('Pengadaan / Beli ke Vendor');
        $response->assertDontSee('Beli ke Vendor');
    }
}
