<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\ToolLoan;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\MaterialRequestService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentralToolInProjectTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminPusat;
    protected User $adminGudangProyek;
    protected User $karyawan;
    protected Supplier $supplier;
    protected Tool $tool;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminPusat       = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminGudangProyek= User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->karyawan         = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();

        $this->supplier = Supplier::firstOrCreate(
            ['code' => 'SUPP-T14'],
            ['name' => 'PT Mitra Alat Perkasa', 'phone' => '081234567890', 'is_active' => true]
        );

        $this->category = Category::firstOrCreate(
            ['code' => 'CAT-T14'],
            ['name' => 'Peralatan Proyek T14', 'type' => 'tool']
        );

        $this->tool = Tool::create([
            'code'                 => 'TOOL-T14-01',
            'name'                 => 'Jackhammer Elektrik 1500W',
            'brand'                => 'Bosch',
            'type'                 => 'Alat Bobok',
            'category_id'          => $this->category->id,
            'current_warehouse_id' => $this->projectWarehouse->id,
            'owner_warehouse_id'   => null, // Sengaja NULL untuk menguji auto-fill ke pusat
            'stock_total'          => 0,
            'stock_available'      => 0,
            'is_active'            => true,
        ]);
    }

    /**
     * Skenario 1:
     * GR item alat di gudang proyek mengisi tools.owner_warehouse_id = gudang pusat bila masih NULL,
     * dan menambah stok di inventaris gudang proyek via ToolInventoryService::addStock.
     */
    public function test_gr_tool_item_at_project_warehouse_sets_owner_warehouse_id_to_central_if_null(): void
    {
        $this->assertNull($this->tool->owner_warehouse_id);

        $po = PurchaseOrder::create([
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by'            => $this->adminPusat->id,
            'status'                => 'approved',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 10000000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'tool_id'           => $this->tool->id,
            'quantity'          => 2,
            'received_qty'      => 0,
            'unit_price'        => 5000000,
            'subtotal'          => 10000000,
        ]);

        $grService = app(GoodsReceiptService::class);
        $receipt = $grService->create([
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => now()->toDateString(),
            'items'             => [
                [
                    'item_type'              => 'tool',
                    'tool_id'                => $this->tool->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 2,
                    'qty_shipped'            => 2,
                    'qty_received'           => 2,
                    'qty_damaged'            => 0,
                ],
            ],
        ], $this->adminPusat->id);

        $grService->confirm($receipt, $this->adminPusat->id);

        $this->tool->refresh();
        // owner_warehouse_id harus otomatis terisi ID Gudang Pusat
        $this->assertEquals($this->centralWarehouse->id, $this->tool->owner_warehouse_id);
        $this->assertTrue($this->tool->isOwnedByCentral());

        // Stok di gudang proyek harus bertambah 2 unit
        $projectInv = ToolInventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('tool_id', $this->tool->id)
            ->first();
        $this->assertNotNull($projectInv);
        $this->assertEquals(2, $projectInv->stock_total);
        $this->assertEquals(2, $projectInv->stock_available);
    }

    /**
     * Skenario 2:
     * Alat yang diterima lewat GR TIDAK membuat tool_loans/tool_assignments (bukan peminjaman).
     * Alat langsung tersedia di inventaris gudang proyek.
     */
    public function test_gr_tool_receipt_does_not_create_tool_loans_or_assignments(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by'            => $this->adminPusat->id,
            'status'                => 'approved',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 5000000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'tool_id'           => $this->tool->id,
            'quantity'          => 1,
            'received_qty'      => 0,
            'unit_price'        => 5000000,
            'subtotal'          => 5000000,
        ]);

        $grService = app(GoodsReceiptService::class);
        $receipt = $grService->create([
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => now()->toDateString(),
            'items'             => [
                [
                    'item_type'              => 'tool',
                    'tool_id'                => $this->tool->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 1,
                    'qty_shipped'            => 1,
                    'qty_received'           => 1,
                    'qty_damaged'            => 0,
                ],
            ],
        ], $this->adminPusat->id);

        $grService->confirm($receipt, $this->adminPusat->id);

        // Tidak boleh ada entri tool_assignments atau tool_loans yang terbuat
        $this->assertEquals(0, ToolAssignment::where('tool_id', $this->tool->id)->count());
        $this->assertEquals(0, ToolLoan::whereHas('items', fn($q) => $q->where('tool_id', $this->tool->id))->count());
    }

    /**
     * Skenario 3:
     * Laporan baru di Laporan Alat: alat milik pusat yang sedang berada di tiap proyek
     * (alat, qty, gudang proyek, tanggal diterima).
     */
    public function test_central_tools_in_projects_report_returns_expected_data(): void
    {
        // Siapkan penerimaan alat di gudang proyek
        $po = PurchaseOrder::create([
            'supplier_id'           => $this->supplier->id,
            'delivery_warehouse_id' => $this->projectWarehouse->id,
            'created_by'            => $this->adminPusat->id,
            'status'                => 'approved',
            'order_date'            => now()->toDateString(),
            'total_amount'          => 15000000,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'tool_id'           => $this->tool->id,
            'quantity'          => 3,
            'received_qty'      => 0,
            'unit_price'        => 5000000,
            'subtotal'          => 15000000,
        ]);

        $grService = app(GoodsReceiptService::class);
        $receiptDate = now()->subDays(2)->toDateString();
        $receipt = $grService->create([
            'purchase_order_id' => $po->id,
            'supplier_id'       => $this->supplier->id,
            'warehouse_id'      => $this->projectWarehouse->id,
            'received_at'       => $receiptDate,
            'items'             => [
                [
                    'item_type'              => 'tool',
                    'tool_id'                => $this->tool->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity'               => 3,
                    'qty_shipped'            => 3,
                    'qty_received'           => 3,
                    'qty_damaged'            => 0,
                ],
            ],
        ], $this->adminPusat->id);

        $grService->confirm($receipt, $this->adminPusat->id);

        // Panggil service laporan
        $reportService = app(ReportService::class);
        $reportData = $reportService->centralToolsInProjectsReport();

        $this->assertNotEmpty($reportData);
        $item = $reportData->firstWhere('tool_id', $this->tool->id);
        $this->assertNotNull($item, 'Alat harus terdaftar pada laporan alat milik pusat di proyek');
        $this->assertEquals($this->tool->code, $item->tool_code);
        $this->assertEquals($this->tool->name, $item->tool_name);
        $this->assertEquals(3, $item->quantity);
        $this->assertEquals($this->projectWarehouse->name, $item->warehouse_name);
        $this->assertEquals($receiptDate, $item->received_date);

        // Uji HTTP response halaman laporan
        $response = $this->actingAs($this->adminPusat)
            ->get(route('reports.tools'));

        $response->assertOk();
        $response->assertViewHas('centralToolsInProjects');
        $response->assertSee($this->tool->name);
        $response->assertSee('Milik Pusat');
    }

    /**
     * Skenario 4:
     * Validasi: qty alat selalu bilangan bulat di Purchase Order (PO).
     */
    public function test_purchase_order_rejects_non_integer_tool_quantity(): void
    {
        $response = $this->actingAs($this->adminPusat)
            ->post(route('purchase-orders.store'), [
                'supplier_id'           => $this->supplier->id,
                'delivery_warehouse_id' => $this->projectWarehouse->id,
                'order_date'            => now()->toDateString(),
                'items'                 => [
                    [
                        'tool_id'    => $this->tool->id,
                        'quantity'   => 2.5, // Pecahan
                        'unit_price' => 1000000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(['items.0.quantity']);
    }

    /**
     * Skenario 5:
     * Validasi: qty alat selalu bilangan bulat di Goods Receipt (GR).
     */
    public function test_goods_receipt_rejects_non_integer_tool_quantity(): void
    {
        $response = $this->actingAs($this->adminPusat)
            ->post(route('goods-receipts.store'), [
                'supplier_id'  => $this->supplier->id,
                'warehouse_id' => $this->projectWarehouse->id,
                'received_at'  => now()->toDateString(),
                'items'        => [
                    [
                        'item_type'    => 'tool',
                        'tool_id'      => $this->tool->id,
                        'quantity'     => 1.75, // Pecahan
                        'qty_shipped'  => 2,
                        'qty_received' => 1.75,
                        'qty_damaged'  => 0,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Skenario 6:
     * Validasi: qty alat selalu bilangan bulat di Material Request (MR).
     */
    public function test_material_request_rejects_non_integer_tool_quantity(): void
    {
        $mrService = app(MaterialRequestService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('bulat');

        $mrService->createRequest(
            $this->projectWarehouse,
            $this->centralWarehouse,
            $this->karyawan,
            [
                [
                    'item_type'     => 'tool',
                    'tool_id'       => $this->tool->id,
                    'qty_requested' => 1.5, // Pecahan
                ],
            ]
        );
    }
}
