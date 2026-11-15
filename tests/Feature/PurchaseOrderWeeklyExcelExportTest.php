<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderWeeklyExcelExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Warehouse $warehouse;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Permission::firstOrCreate(['name' => 'view purchase orders']);
        Permission::firstOrCreate(['name' => 'view purchase order prices']);

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
        $this->user->givePermissionTo(['view purchase orders', 'view purchase order prices']);

        $this->project = Project::create([
            'name' => 'Proyek Uji Coba PO',
            'code' => 'PRJ-TEST',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Proyek Test',
            'code' => 'GDG-TEST',
            'type' => 'project',
            'project_id' => $this->project->id,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PT Mitra Material Utama',
            'phone' => '08123456789',
        ]);
    }

    public function test_can_export_weekly_purchase_orders_to_excel(): void
    {
        Storage::fake('private');
        $file = UploadedFile::fake()->image('po_lampiran.jpg', 400, 300);
        $path = $file->store('purchase_orders', 'private');

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->startOfWeek()->format('Y-m-d'),
            'status' => 'approved',
            'attachment_path' => $path,
            'total_amount' => 500000,
        ]);

        $unit = Unit::create(['name' => 'Zak', 'code' => 'zak']);
        $cat = Category::create(['name' => 'Semen & Pasir', 'code' => 'SP']);
        $mat = Material::create([
            'name' => 'Semen Portland 50kg',
            'sku' => 'MAT-SMN-01',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $mat->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        Excel::fake();

        $response = $this->actingAs($this->user)
            ->get(route('purchase-orders.export-weekly-excel', [
                'date' => Carbon::now()->format('Y-m-d'),
            ]));

        $response->assertOk();
        Excel::assertDownloaded('Rekap-PO-Mingguan-' . Carbon::now()->format('Y-m') . '.xlsx', function (\App\Exports\WeeklyPurchaseOrderExport $export) {
            return true;
        });
    }

    public function test_blind_mode_masks_prices_in_export(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-BLIND',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'total_amount' => 750000,
        ]);

        Excel::fake();

        $response = $this->actingAs($this->user)
            ->get(route('purchase-orders.export-weekly-excel', [
                'date' => Carbon::now()->format('Y-m-d'),
                'mode' => 'blind',
            ]));

        $response->assertOk();
        Excel::assertDownloaded('Rekap-PO-Mingguan-' . Carbon::now()->format('Y-m') . '.xlsx');
    }

    public function test_drawing_handles_valid_image_and_skips_pdf(): void
    {
        Storage::fake('private');
        $imgFile = UploadedFile::fake()->image('bukti.png', 200, 200);
        $imgPath = $imgFile->store('purchase_orders', 'private');

        $pdfFile = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');
        $pdfPath = $pdfFile->store('purchase_orders', 'private');

        $poWithImg = PurchaseOrder::create([
            'po_number' => 'PO-IMG-01',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'attachment_path' => $imgPath,
            'total_amount' => 100000,
        ]);

        $poWithPdf = PurchaseOrder::create([
            'po_number' => 'PO-PDF-01',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'attachment_path' => $pdfPath,
            'total_amount' => 200000,
        ]);

        $export = new \App\Exports\WeeklyPurchaseOrderExport(
            collect([$poWithImg, $poWithPdf]),
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
            $this->project,
            false
        );

        $drawings = $export->drawings();
        $this->assertIsArray($drawings);
        $this->assertCount(1, $drawings); // Hanya gambar yang dimasukkan ke Drawing
        $this->assertEquals('Foto PO PO-IMG-01', $drawings[0]->getName());
    }

    public function test_export_filters_by_project_and_status(): void
    {
        $otherProject = Project::create([
            'name' => 'Proyek Lain',
            'code' => 'PRJ-OTHER',
            'status' => 'active',
        ]);
        $otherWarehouse = Warehouse::create([
            'name' => 'Gudang Lain',
            'code' => 'GDG-OTHER',
            'type' => 'project',
            'project_id' => $otherProject->id,
            'is_active' => true,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-FILTER-TARGET',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'received',
            'total_amount' => 300000,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-FILTER-IGNORED',
            'supplier_id' => $this->supplier->id,
            'delivery_warehouse_id' => $otherWarehouse->id,
            'created_by' => $this->user->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'draft',
            'total_amount' => 400000,
        ]);

        Excel::fake();

        $response = $this->actingAs($this->user)
            ->get(route('purchase-orders.export-weekly-excel', [
                'date' => Carbon::now()->format('Y-m-d'),
                'project_id' => $this->project->id,
                'status' => 'received',
            ]));

        $response->assertOk();
        Excel::assertDownloaded('Rekap-PO-Mingguan-' . Carbon::now()->format('Y-m') . '.xlsx');
    }

    public function test_weekly_report_format_excel_downloads_file(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)
            ->get(route('purchase-orders.weekly-report', [
                'format' => 'excel',
                'date' => Carbon::now()->format('Y-m-d'),
            ]));

        $response->assertOk();
        Excel::assertDownloaded('Rekap-PO-Minggu-' . Carbon::now()->weekOfMonth . '-' . Carbon::now()->year . '.xlsx');
    }

    public function test_po_index_renders_export_modal_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));
        $response->assertOk();
        $response->assertSee('Export Excel PO');
        $response->assertSee('id="modalExportExcelPo"', false);
    }

    public function test_weekly_report_renders_excel_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.weekly-report'));
        $response->assertOk();
        $response->assertSee('Unduh Excel');
        $response->assertSee('format=excel', false);
    }
}
