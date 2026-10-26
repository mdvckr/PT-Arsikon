<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderWeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPo;
    protected User $poLapangan;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        // Pastikan role po_lapangan terdaftar
        $poLapanganRole = Role::firstOrCreate(['name' => 'po_lapangan', 'guard_name' => 'web']);
        $poLapanganRole->givePermissionTo('view purchase orders');

        $this->adminPo = User::firstOrCreate(
            ['email' => 'admin.po.weekly@example.com'],
            ['name' => 'Admin PO Weekly', 'password' => bcrypt('password')]
        );
        $this->adminPo->syncRoles(['Admin PO']);

        $this->poLapangan = User::firstOrCreate(
            ['email' => 'po.lapangan.weekly@example.com'],
            ['name' => 'PO Lapangan Weekly', 'password' => bcrypt('password')]
        );
        $this->poLapangan->syncRoles(['po_lapangan']);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'PT Supplier Bahan Bangunan',
            'phone' => '081234567890',
        ]);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'Gudang Utama',
            'code' => 'WH-UTAMA-01',
            'is_central' => true,
            'is_active' => true,
        ]);

        $category = Category::where('type', 'material')->first() ?? Category::firstOrCreate(['name' => 'Bahan Pokok', 'type' => 'material']);
        $unit = Unit::first() ?? Unit::firstOrCreate(['name' => 'Sak', 'symbol' => 'sak']);

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-SMN-01'],
            [
                'name' => 'Semen Portland 50kg',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]
        );
    }

    public function test_model_auto_populates_weekly_grouping_columns_on_save(): void
    {
        $dt = Carbon::parse('2026-10-21'); // Rabu

        $po = PurchaseOrder::create([
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $dt->format('Y-m-d'),
            'total_amount'          => 15000000,
            'status'                => 'draft',
        ]);

        $this->assertEquals($dt->weekOfMonth, $po->week_number);
        $this->assertEquals(10, $po->month);
        $this->assertEquals(2026, $po->year);
        $this->assertEquals($dt->isoWeek(), $po->iso_week);
    }

    public function test_admin_po_can_view_weekly_report_filtered_by_current_week(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $mon = $now->copy()->startOfWeek(Carbon::MONDAY);

        // PO 1: Dalam minggu ini
        $poInWeek = PurchaseOrder::create([
            'po_number'             => 'PO-2026-INWEEK',
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $mon->copy()->addDays(1)->format('Y-m-d'),
            'total_amount'          => 7500000,
            'status'                => 'approved',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $poInWeek->id,
            'material_id'       => $this->material->id,
            'quantity'          => 100,
            'unit_price'        => 75000,
            'subtotal'          => 7500000,
            'total_price'       => 7500000,
        ]);

        // PO 2: Di luar minggu ini (3 minggu yang lalu)
        $poOld = PurchaseOrder::create([
            'po_number'             => 'PO-2026-OLDWEEK',
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $mon->copy()->subWeeks(3)->format('Y-m-d'),
            'total_amount'          => 3200000,
            'status'                => 'approved',
        ]);

        $response = $this->actingAs($this->adminPo)->get(route('purchase-orders.weekly-report'));

        $response->assertStatus(200);
        $response->assertSee('PO-2026-INWEEK');
        $response->assertDontSee('PO-2026-OLDWEEK');
        // Admin PO berhak melihat harga
        $response->assertSee('7.500.000');
    }

    public function test_po_lapangan_receives_blind_report_with_masked_prices(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $mon = $now->copy()->startOfWeek(Carbon::MONDAY);

        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-LAPANGAN',
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $mon->copy()->addDays(2)->format('Y-m-d'),
            'total_amount'          => 28500000,
            'status'                => 'sent',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 500,
            'unit_price'        => 57000,
            'subtotal'          => 28500000,
            'total_price'       => 28500000,
        ]);

        $response = $this->actingAs($this->poLapangan)->get(route('purchase-orders.weekly-report'));

        $response->assertStatus(200);
        $response->assertSee('PO-2026-LAPANGAN');
        // Harga disembunyikan / Rp 0
        $response->assertDontSee('28.500.000');
        $response->assertSee('Rp 0');
        $response->assertSee('LAPORAN LAPANGAN (BLIND)');
    }

    public function test_can_filter_weekly_report_by_specific_date(): void
    {
        $targetDate = '2026-08-12'; // Rabu di minggu ke-2 Agustus 2026

        PurchaseOrder::create([
            'po_number'             => 'PO-2026-AUG',
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $targetDate,
            'total_amount'          => 12000000,
            'status'                => 'approved',
        ]);

        $response = $this->actingAs($this->adminPo)
            ->get(route('purchase-orders.weekly-report', ['date' => $targetDate]));

        $response->assertStatus(200);
        $response->assertSee('PO-2026-AUG');
        $response->assertSee('Agustus');
    }

    public function test_can_export_weekly_report_to_pdf_via_dompdf(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $mon = $now->copy()->startOfWeek(Carbon::MONDAY);

        PurchaseOrder::create([
            'po_number'             => 'PO-2026-PDF',
            'supplier_id'           => $this->supplier->id,
            'created_by'            => $this->adminPo->id,
            'delivery_warehouse_id' => $this->warehouse->id,
            'order_date'            => $mon->copy()->addDay()->format('Y-m-d'),
            'total_amount'          => 4500000,
            'status'                => 'sent',
        ]);

        $response = $this->actingAs($this->adminPo)
            ->get(route('purchase-orders.weekly-report', ['format' => 'pdf']));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
