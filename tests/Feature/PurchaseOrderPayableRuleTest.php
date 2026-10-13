<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\TransitLoss;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\TransitLossService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderPayableRuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $financeUser;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected Supplier $supplier;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        // Permissions & Roles
        Permission::firstOrCreate(['name' => 'create purchase orders', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'approve purchase orders', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'close purchase orders', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create payments', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'verify payments', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create transit losses', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'approve transit losses', 'guard_name' => 'web']);

        $adminRole = Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $this->adminUser = User::factory()->create(['name' => 'Admin User']);
        $this->adminUser->assignRole($adminRole);

        $this->financeUser = User::factory()->create(['name' => 'Finance Staff']);
        $this->financeUser->givePermissionTo(['create payments', 'verify payments']);

        $this->centralWarehouse = Warehouse::create([
            'name' => 'Gudang Pusat',
            'code' => 'WH-PST-01',
            'type' => 'central',
            'is_active' => true,
        ]);

        $this->projectWarehouse = Warehouse::create([
            'name' => 'Gudang Proyek A',
            'code' => 'WH-PRJ-01',
            'type' => 'project',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Vendor Test PT',
            'contact_person' => 'Budi',
            'phone' => '08123456789',
        ]);

        $cat = Category::firstOrCreate(['code' => 'BB01'], ['name' => 'Bahan Bangunan', 'type' => 'material']);
        $unit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs']);

        $this->material = Material::create([
            'name' => 'Bata Ringan',
            'sku' => 'MAT-BATA-001',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'type' => 'material',
            'is_active' => true,
        ]);
    }

    public function test_cannot_create_payment_for_po_without_confirmed_goods_receipt(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9001',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'status'                => 'sent',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 0,
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        // Attempt to create payment via store endpoint
        $response = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 500000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('purchase_order_id');
        $this->assertEquals(0, Payment::count());
    }

    public function test_payment_is_capped_by_nilai_sah_received_qty_times_unit_price(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9002',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'status'                => 'partial_received',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 900, // 900 diterima baik
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        // Buat GR terkonfirmasi untuk PO ini
        $gr = GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9001',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'purchase_order_item_id' => $poItem->id,
            'material_id'            => $this->material->id,
            'qty_shipped'            => 1000,
            'qty_received'           => 900,
            'qty_damaged'            => 0,
            'qty_missing'            => 100,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        // Nilai sah = 900 * 1000 = 900.000. Mencoba bayar 1.000.000 harus DITOLAK
        $responseFail = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 1000000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $responseFail->assertSessionHasErrors('amount');
        $this->assertEquals(0, Payment::count());

        // Membayar 900.000 (sesuai nilai sah) harus BERHASIL
        $responseOk = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 900000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $responseOk->assertSessionHasNoErrors();
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(900000, Payment::first()->amount);
    }

    public function test_pending_and_verified_payments_count_towards_max_payable(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9003',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'status'                => 'partial_received',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 900,
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9002',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        // Bayar termin 1: 500.000 (status pending)
        $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 500000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $this->assertEquals(1, Payment::count());
        $this->assertEquals(400000, $po->fresh()->payable_remaining_amount);

        // Coba bayar lagi 500.000 (total jadi 1.000.000 > nilai_sah 900.000) -> DITOLAK
        $responseFail = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 500000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $responseFail->assertSessionHasErrors('amount');
        $this->assertEquals(1, Payment::count());

        // Bayar 400.000 (pas sisa nilai sah) -> BERHASIL
        $responseOk = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 400000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $responseOk->assertSessionHasNoErrors();
        $this->assertEquals(2, Payment::count());
        $this->assertEquals(0, $po->fresh()->payable_remaining_amount);
    }

    public function test_vendor_risk_lost_qty_does_not_increase_nilai_sah_even_when_confirmed_lost(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9004',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor', // Risiko vendor
            'status'                => 'partial_received',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 900,
            'lost_qty'          => 100, // 100 confirmed lost
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9003',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        // Karena risk_bearer = vendor, lost_qty TIDAK menambah nilai_sah
        $this->assertEquals(900000, $po->fresh()->nilai_sah);

        $responseFail = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 1000000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $responseFail->assertSessionHasErrors('amount');
    }

    public function test_buyer_risk_lost_qty_increases_nilai_sah_once_confirmed_lost(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9005',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'buyer', // Risiko pembeli PT Arsikon
            'status'                => 'partial_received',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 900,
            'lost_qty'          => 0, // Awalnya belum confirmed lost
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9004',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        // Sebelum confirmed lost, nilai sah = 900.000
        $this->assertEquals(900000, $po->fresh()->nilai_sah);

        // Setelah kehilangan dikonfirmasi (lost_qty = 100)
        $poItem->update(['lost_qty' => 100]);

        // Karena risk_bearer = buyer, nilai sah menjadi (900 + 100) * 1000 = 1.000.000
        $this->assertEquals(1000000, $po->fresh()->nilai_sah);

        // Pembayaran penuh 1.000.000 kini DIIZINKAN
        $response = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 1000000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1000000, Payment::first()->amount);
    }

    public function test_short_closed_po_can_be_paid_up_to_nilai_sah_received(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9006',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'status'                => 'closed', // PO di-short close
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 900,
            'short_closed_qty'  => 100, // sisa 100 ditutup
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9005',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        // Nilai sah = 900.000
        $this->assertEquals(900000, $po->fresh()->nilai_sah);
        $this->assertEquals(900000, $po->fresh()->payable_remaining_amount);

        // Bayar 900.000 berhasil
        $response = $this->actingAs($this->financeUser)
            ->post(route('payments.store'), [
                'purchase_order_id' => $po->id,
                'amount'            => 900000,
                'payment_method'    => 'bank_transfer',
                'payment_date'      => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(1, Payment::count());
    }

    public function test_financial_summary_displays_nilai_sah_and_withheld_reasons(): void
    {
        $po = PurchaseOrder::create([
            'po_number'             => 'PO-2026-9007',
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'risk_bearer'           => 'vendor',
            'status'                => 'partial_received',
            'created_by'            => $this->adminUser->id,
            'total_amount'          => 1000000,
            'paid_amount'           => 0,
            'order_date'            => now()->toDateString(),
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id'       => $this->material->id,
            'quantity'          => 1000,
            'received_qty'      => 800,
            'unit_price'        => 1000,
            'subtotal'          => 1000000,
            'total_price'       => 1000000,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number'    => 'GR-20261012-9006',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_by_user_id' => $this->adminUser->id,
            'receipt_date'      => now()->toDateString(),
            'status'            => 'confirmed',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id'       => $gr->id,
            'purchase_order_item_id' => $poItem->id,
            'material_id'            => $this->material->id,
            'qty_shipped'            => 900,
            'qty_received'           => 800,
            'qty_damaged'            => 50,
            'qty_missing'            => 50,
            'missing_status'         => 'unresolved',
            'unit_price'             => 1000,
        ]);

        $summary = $po->fresh()->withheld_summary;

        $this->assertNotEmpty($summary['items']);
        $this->assertGreaterThan(0, $summary['total_withheld']);

        // Akses halaman PO show
        $responsePo = $this->actingAs($this->adminUser)
            ->get(route('purchase-orders.show', $po));

        $responsePo->assertStatus(200);
        $responsePo->assertSee('Nilai Sah');
        $responsePo->assertSee('Nilai Tertahan');

        // Akses halaman create payment dengan po_id
        $responsePay = $this->actingAs($this->financeUser)
            ->get(route('payments.create', ['po_id' => $po->id]));

        $responsePay->assertStatus(200);
        $responsePay->assertSee('Nilai Sah');
    }
}
