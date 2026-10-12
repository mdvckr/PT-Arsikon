<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\TransitLoss;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DistributionService;
use App\Services\StockService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionStockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPusat;
    protected User $adminGudangPusat;
    protected User $adminProyek;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $material;
    protected DistributionService $distributionService;
    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPusat = User::firstOrCreate(
            ['email' => 'admin.dist@example.com'],
            ['name' => 'Admin Dist', 'password' => bcrypt('password')]
        );
        $this->adminPusat->syncRoles(['Admin Pusat']);

        $this->adminGudangPusat = User::firstOrCreate(
            ['email' => 'gudang.pusat@example.com'],
            ['name' => 'Admin Gudang Pusat', 'password' => bcrypt('password')]
        );
        $this->adminGudangPusat->syncRoles(['Admin Gudang Pusat']);

        $this->adminProyek = User::firstOrCreate(
            ['email' => 'gudang.proyek@example.com'],
            ['name' => 'Admin Gudang Proyek', 'password' => bcrypt('password')]
        );
        $this->adminProyek->syncRoles(['Admin Gudang Proyek']);

        $this->centralWarehouse = Warehouse::where('is_central', true)->first() ?? Warehouse::create([
            'name' => 'Gudang Sentral Test',
            'code' => 'WH-SNT-TEST',
            'is_central' => true,
        ]);

        $this->projectWarehouse = Warehouse::where('is_central', false)->first() ?? Warehouse::create([
            'name' => 'Gudang Proyek Test',
            'code' => 'WH-PRY-TEST',
            'is_central' => false,
        ]);

        $this->adminGudangPusat->warehouses()->syncWithoutDetaching([$this->centralWarehouse->id]);
        $this->adminProyek->warehouses()->syncWithoutDetaching([$this->projectWarehouse->id]);

        $category = Category::where('type', 'material')->first();
        $unit = Unit::first();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-DST-01'],
            [
                'name' => 'Besi Beton 10mm',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_central' => 10,
            ]
        );

        $this->stockService = app(StockService::class);
        $this->distributionService = app(DistributionService::class);

        // Berikan initial stock 100 di gudang pusat
        $this->stockService->addStock(
            $this->centralWarehouse,
            $this->material,
            100,
            'initial_seed',
            null,
            $this->adminPusat->id
        );
    }

    public function test_cannot_create_distribution_for_unapproved_mr(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-SUBMITTED',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'submitted', // Belum approved!
        ]);

        $this->expectException(Exception::class);
        $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 10,
                ]
            ],
        ], $this->adminPusat->id);
    }

    public function test_cannot_create_distribution_for_purchase_fulfillment_item(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-PURCHASE',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 20,
            'qty_approved' => 20,
            'fulfillment' => 'purchase', // Jalur PO, bukan stok!
        ]);

        $this->expectException(Exception::class);
        $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 10,
                ]
            ],
        ], $this->adminPusat->id);
    }

    public function test_cannot_create_distribution_for_custom_manual_item(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-CUSTOM',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $this->expectException(Exception::class);
        $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'type' => 'custom',
                    'custom_item_name' => 'Barang Custom Spesial',
                    'custom_item_unit' => 'pcs',
                    'quantity' => 5,
                ]
            ],
        ], $this->adminPusat->id);
    }

    public function test_cannot_ship_exceeding_distribution_quota(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-QUOTA',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 10,
            'qty_approved' => 10,
            'qty_in_transit' => 4,
            'qty_received' => 2,
            'fulfillment' => 'stock',
        ]);
        // Sisa kuota kirim: 10 - 4 - 2 = 4

        $this->expectException(Exception::class);
        $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 5, // Melebihi kuota 4!
                ]
            ],
        ], $this->adminPusat->id);
    }

    public function test_distribution_stock_transfer_flow_and_mr_status(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-FLOW',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 20,
            'qty_approved' => 20,
            'fulfillment' => 'stock',
        ]);

        // Buat Surat Jalan untuk 20
        $dist = $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 20,
                ]
            ],
        ], $this->adminPusat->id);

        $this->assertEquals('draft', $dist->status);

        // 1. Ship
        $this->distributionService->ship($dist, $this->adminPusat->id);

        // Stok asal berkurang 20 (dari 100 menjadi 80)
        $centralStock = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity');
        $this->assertEquals(80, (float) $centralStock);

        // Stok tujuan BELUM bertambah (masih 0)
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity') ?? 0;
        $this->assertEquals(0, (float) $projectStock);

        // MR item in_transit bertambah menjadi 20
        $this->assertEquals(20, (float) $mrItem->fresh()->qty_in_transit);

        // Status MR masih approved (karena belum received!)
        $this->assertEquals('approved', $mr->fresh()->status);

        // 2. Receive
        $distItem = $dist->items()->first();
        $this->distributionService->receive($dist, [
            [
                'distribution_item_id' => $distItem->id,
                'received_quantity' => 20,
            ]
        ], $this->adminProyek->id);

        // Stok tujuan sekarang bertambah 20
        $projectStockAfter = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity');
        $this->assertEquals(20, (float) $projectStockAfter);

        // MR item in_transit menjadi 0, received menjadi 20
        $this->assertEquals(0, (float) $mrItem->fresh()->qty_in_transit);
        $this->assertEquals(20, (float) $mrItem->fresh()->qty_received);

        // Status MR sekarang fulfilled
        $this->assertEquals('fulfilled', $mr->fresh()->status);
    }

    public function test_receive_with_discrepancy_records_transit_losses_and_partially_fulfilled(): void
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-TEST-DISCREPANCY',
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'requested_by_user_id' => $this->adminProyek->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->adminPusat->id,
            'approved_at' => now(),
        ]);

        $mrItem = MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material->id,
            'qty_requested' => 10,
            'qty_approved' => 10,
            'fulfillment' => 'stock',
        ]);

        $dist = $this->distributionService->create([
            'material_request_id' => $mr->id,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 10,
                ]
            ],
        ], $this->adminPusat->id);

        $this->distributionService->ship($dist, $this->adminPusat->id);

        // Terima dengan barang kurang: dikirim 10, diterima hanya 7 (kurang 3)
        $distItem = $dist->items()->first();
        $this->distributionService->receive($dist, [
            [
                'distribution_item_id' => $distItem->id,
                'received_quantity' => 7,
                'qty_lost' => 3,
            ]
        ], $this->adminProyek->id);

        // Stok tujuan bertambah 7
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->value('quantity');
        $this->assertEquals(7, (float) $projectStock);

        // Selisih 3 tercatat di transit_losses
        $loss = TransitLoss::where('distribution_id', $dist->id)->first();
        $this->assertNotNull($loss);
        $this->assertEquals(3, (float) $loss->lost_qty);

        // Sisa in_transit di-zero-out
        $this->assertEquals(0, (float) $mrItem->fresh()->qty_in_transit);
        $this->assertEquals(7, (float) $mrItem->fresh()->qty_received);

        // Status MR partially_fulfilled (karena baru 7 dari 10)
        $this->assertEquals('partially_fulfilled', $mr->fresh()->status);
    }
}
