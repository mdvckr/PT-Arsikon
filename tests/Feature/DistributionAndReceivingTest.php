<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Services\DistributionService;
use App\Services\MaterialRequestService;
use App\Services\StockService;
use App\Services\ToolInventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionAndReceivingTest extends TestCase
{
    use RefreshDatabase;

    protected DistributionService $distributionService;
    protected MaterialRequestService $requestService;
    protected StockService $stockService;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminUser;
    protected User $projectUser;
    protected User $adminPOUser;
    protected Supplier $supplier;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->stockService = new StockService();
        $toolInventoryService = new ToolInventoryService();
        $this->distributionService = new DistributionService($this->stockService, $toolInventoryService);
        $this->requestService = new MaterialRequestService();

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->adminPOUser = User::where('email', 'admin.po@arsikon.co.id')->firstOrFail();
        
        // Create supplier since not seeded
        $this->supplier = Supplier::firstOrCreate(
            ['code' => 'SUP-TEST'],
            ['code' => 'SUP-TEST', 'name' => 'Supplier Test', 'contact_person' => 'Test', 'phone' => '08123456789', 'address' => 'Test Address', 'is_active' => true]
        );
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();

        // Stock Central with 500 bags of Semen
        $this->stockService->addStock(
            $this->centralWarehouse,
            $this->semenMaterial,
            500.0,
            'goods_receipt',
            1,
            $this->adminUser->id,
            'Penerimaan dari Supplier Tiga Roda'
        );
    }

    public function test_full_distribution_and_project_receiving_flow(): void
    {
        // 1. Project User creates request for 200 bags
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 200]]
        );

        // 2. Admin approves request
        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        // 3. Admin creates + ships 200 bags
        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 200],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status);

        // Central stock: seeder adds 500, setUp adds 500 = 1000 initial, minus 200 shipped = 800
        $centralStock = Inventory::where('warehouse_id', $this->centralWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertEquals(800, $centralStock);

        // Project in_transit stock should be 200
        $projectInTransit = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(200, $projectInTransit);

        // 4. Project User receives 195 bags good, 5 bags damaged (partial receive)
        $completedDistribution = $this->distributionService->receive(
            $distribution,
            [
                ['distribution_item_id' => $distribution->items->first()->id, 'received_quantity' => 195, 'qty_damaged_or_lost' => 5]
            ],
            $this->projectUser->id,
            'DST-TEST-001' // Pass surat_jalan number
        );

        $this->assertEquals('completed', $completedDistribution->status);

        // Verify surat_jalan is populated after receive
        $completedDistribution->refresh();
        $this->assertNotNull($completedDistribution->surat_jalan,
            'surat_jalan number must be filled after receive');
        $this->assertStringStartsWith('DST-', $completedDistribution->surat_jalan,
            'surat_jalan number should use DST- prefix');

        // Project stock on hand: seeder adds ~38, plus 195 received = 233
        $projectStock = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('quantity');
        $this->assertEquals(233, $projectStock);

        // Project in_transit stock should now be 0
        $projectInTransitAfter = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->semenMaterial->id)
            ->value('qty_in_transit');
        $this->assertEquals(0, $projectInTransitAfter);
    }

    public function test_print_surat_jalan_page_displayed_for_in_transit(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $response = $this->actingAs($this->adminUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
        $response->assertSee('Surat Jalan');
        $response->assertSee($distribution->distribution_number);
        $response->assertSee($this->semenMaterial->name);
    }

    public function test_print_surat_jalan_page_accessible_for_viewer(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        // projectUser memiliki view distributions, jadi harus bisa mengakses halaman cetak
        $response = $this->actingAs($this->projectUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
    }

    public function test_admin_po_can_access_print_surat_jalan_page(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);

        $this->assertTrue($this->adminPOUser->can('view distributions'));

        $response = $this->actingAs($this->adminPOUser)
            ->get(route('distributions.print', $distribution));

        $response->assertOk();
    }

    public function test_central_warehouse_admin_can_ship_distribution_via_http(): void
    {
        $distribution = $this->distributionService->create([
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Supir Pengiriman Pusat',
            'vehicle_number'       => 'B 9999 ACK',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 50],
            ],
        ], $this->adminUser->id);

        $this->assertEquals('draft', $distribution->status);

        // Admin Pusat ships the distribution via HTTP POST
        $response = $this->actingAs($this->adminUser)
            ->post(route('distributions.ship', $distribution));

        $response->assertRedirect(route('distributions.show', $distribution));
        $response->assertSessionHas('success');

        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status);
        $this->assertEquals($this->adminUser->id, $distribution->shipped_by_user_id);
    }

    public function test_can_create_and_receive_distribution_with_custom_item(): void
    {
        $postData = [
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id'   => $this->projectWarehouse->id,
            'delivery_date'     => now()->toDateString(),
            'driver_name'       => 'Pak Joko Driver',
            'vehicle_number'    => 'B 5555 XYZ',
            'notes'             => 'Pengiriman item custom non-master',
            'items'             => [
                [
                    'type'             => 'custom',
                    'custom_item_name' => 'Kabel Roll Ekstra 50m',
                    'custom_item_unit' => 'roll',
                    'quantity'         => 2,
                ],
            ],
        ];

        // 1. Create distribution with custom item via HTTP POST
        $response = $this->actingAs($this->adminUser)->post(route('distributions.store'), $postData);
        $response->assertSessionHasNoErrors();

        $distribution = \App\Models\Distribution::where('vehicle_number', 'B 5555 XYZ')->first();
        $this->assertNotNull($distribution);
        $response->assertRedirect(route('distributions.show', $distribution));

        $item = $distribution->items()->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->isCustom());
        $this->assertEquals('Kabel Roll Ekstra 50m', $item->custom_item_name);
        $this->assertEquals('roll', $item->custom_item_unit);
        $this->assertEquals('Kabel Roll Ekstra 50m', $item->name());
        $this->assertEquals('roll', $item->unitAbbr());
        $this->assertEquals(2.0, (float) $item->qty_shipped);

        // 2. Ship distribution
        $shipResponse = $this->actingAs($this->adminUser)->post(route('distributions.ship', $distribution));
        $shipResponse->assertSessionHasNoErrors();
        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status);

        // 3. Check show & print pages render the custom item
        $showRes = $this->actingAs($this->adminUser)->get(route('distributions.show', $distribution));
        $showRes->assertOk();
        $showRes->assertSee('Kabel Roll Ekstra 50m');

        $printRes = $this->actingAs($this->adminUser)->get(route('distributions.print', $distribution));
        $printRes->assertOk();
        $printRes->assertSee('Kabel Roll Ekstra 50m');

        // 4. Project warehouse receives custom item
        $receiveData = [
            'items' => [
                [
                    'distribution_item_id' => $item->id,
                    'received_quantity'    => 2,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];
        $receiveRes = $this->actingAs($this->projectUser)->post(route('distributions.receive', $distribution), $receiveData);
        $receiveRes->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertEquals(2.0, (float) $item->qty_received);
        $distribution->refresh();
        $this->assertEquals('completed', $distribution->status);
    }

    public function test_auto_approves_mr_when_creating_surat_jalan(): void
    {
        // 1. Create MR with status submitted
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 200]]
        );

        // 2. Verify initial status
        $this->assertEquals('submitted', $request->status);

        // 3. Create Surat Jalan from that MR
        $distribution = $this->distributionService->create([
            'material_request_id'  => $request->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 200],
            ],
        ], $this->adminUser->id);

        // 4. Key assertions
        $request->refresh();
        $this->assertEquals('approved', $request->status,
            'MR should be auto-approved when creating Surat Jalan');
        $this->assertNotNull($request->approved_by_user_id,
            'MR should have approved_by_user_id');
        $this->assertEquals($this->adminUser->id, $request->approved_by_user_id,
            'approved_by_user_id should match user who created SJ');

        // 5. Verify distribution status
        $this->assertEquals('draft', $distribution->status);
    }

    public function test_full_employee_to_admin_surat_jalan_journey(): void
    {
        // 1. Employee creates request MR
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser, // using projectUser as employee proxy
            [['material_id' => $this->semenMaterial->id, 'qty_requested' => 100]]
        );

        // 2. Admin approves MR
        $approvedRequest = $this->requestService->approveRequest($request, $this->adminUser);

        // 3. Employee (or admin) creates Surat Jalan
        $distribution = $this->distributionService->create([
            'material_request_id'  => $approvedRequest->id,
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Budi',
            'vehicle_number'       => 'B 1234 CD',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 100],
            ],
        ], $this->adminUser->id);

        // 4. Admin ships the distribution
        $this->distributionService->ship($distribution, $this->adminUser->id);
        $this->assertEquals('in_transit', $distribution->status);

        // 5. Admin receives with surat_jalan number
        $receiveData = [
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 100,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];
        $this->distributionService->receive(
            $distribution,
            $receiveData['items'],
            $this->adminUser->id,
            'DST-TEST-002' // Pass surat_jalan number
        );

        // 6. Assertions
        $distribution->refresh();
        $this->assertEquals('completed', $distribution->status);
        $this->assertNotNull($distribution->surat_jalan,
            'surat_jalan number must be filled after receive');
        $this->assertStringStartsWith('DST-', $distribution->surat_jalan,
            'surat_jalan number should use DST- prefix');
    }

    public function test_can_create_ship_receive_distribution_with_tool_assignment(): void
    {
        // 1. Create tool and tool assignment pending
        $category = \App\Models\Category::firstOrCreate(['code' => 'TOOL-CAT', 'name' => 'Peralatan Kerja']);
        $tool = Tool::create([
            'name' => 'Test Tool',
            'code' => 'TOOL-TEST',
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        // Create tool inventory in central warehouse
        \App\Models\ToolInventory::create([
            'tool_id' => $tool->id,
            'warehouse_id' => $this->centralWarehouse->id,
            'stock_total' => 10,
            'stock_available' => 10,
            'stock_borrowed' => 0,
            'stock_maintenance' => 0,
            'stock_damaged' => 0,
        ]);
        $ta = ToolAssignment::create([
            'tool_loan_id' => null,
            'assignment_number' => ToolAssignment::generateAssignmentNumber(),
            'tool_id' => $tool->id,
            'quantity' => 1,
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id' => $this->projectWarehouse->id,
            'borrower_name' => 'Test Borrower',
            'location_name' => 'Test Location',
            'assigned_by_user_id' => $this->adminUser->id,
            'assigned_at' => now(),
            'status' => 'pending',
        ]);

        // 2. Create distribution from tool assignment
        $distribution = $this->distributionService->create([
            'tool_assignment_ids' => [$ta->id],
            'from_warehouse_id'   => $this->centralWarehouse->id,
            'to_warehouse_id'     => $this->projectWarehouse->id,
            'delivery_date'       => now()->toDateString(),
            'driver_name'         => 'Budi',
            'vehicle_number'      => 'B 1234 CD',
            'items' => [
                ['type' => 'tool', 'tool_id' => $tool->id, 'tool_assignment_id' => $ta->id, 'quantity' => 1],
            ],
        ], $this->adminUser->id);

        // 3. Assertions
        $this->assertEquals('draft', $distribution->status);
        $this->assertCount(1, $distribution->items);

        // 4. Ship - should auto-approve TA and borrow tool
        $this->distributionService->ship($distribution, $this->adminUser->id);
        $this->assertEquals('in_transit', $distribution->status);

        // 5. Verify TA status changed to active
        $ta->refresh();
        $this->assertEquals('active', $ta->status);

        // 6. Receive
        $receiveData = [
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 1,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];
        $this->distributionService->receive(
            $distribution,
            $receiveData['items'],
            $this->adminUser->id,
            'DST-TEST-003' // Pass surat_jalan number
        );

        $distribution->refresh();
        $this->assertEquals('completed', $distribution->status);
        $this->assertNotNull($distribution->surat_jalan);
    }

    public function test_receive_endpoint_with_surat_jalan_field(): void
    {
        $distribution = $this->distributionService->create([
            'from_warehouse_id'    => $this->centralWarehouse->id,
            'to_warehouse_id'      => $this->projectWarehouse->id,
            'delivery_date'        => now()->toDateString(),
            'driver_name'          => 'Supir Pengiriman Pusat',
            'vehicle_number'       => 'B 9999 ACK',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 50],
            ],
        ], $this->adminUser->id);

        // Ship first
        $this->distributionService->ship($distribution, $this->adminUser->id);

        // POST to receive with surat_jalan
        $receiveData = [
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 50,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
            'surat_jalan' => 'DST-TEST-0004',
        ];
        $response = $this->actingAs($this->adminUser)
            ->post(route('distributions.receive', $distribution), $receiveData);

        $response->assertSessionHasNoErrors();

        $distribution->refresh();
        $this->assertEquals('DST-TEST-0004', $distribution->surat_jalan);
    }

    public function test_only_destination_warehouse_admin_can_receive_distribution(): void
    {
        // 1. Setup two project warehouses: Warehouse A and Warehouse B
        $warehouseA = $this->projectWarehouse; // projectUser (admin.proyek1) is assigned here
        $warehouseB = Warehouse::where('is_central', false)->where('id', '!=', $warehouseA->id)->first();
        if (!$warehouseB) {
            $warehouseB = Warehouse::create([
                'name' => 'Gudang Proyek Lain B',
                'code' => 'WHS-PRJ-B',
                'is_central' => false,
                'is_active' => true,
            ]);
        }

        // Create an admin strictly assigned to Warehouse B
        $adminProyekB = User::factory()->create();
        $adminProyekB->syncRoles(['Admin Gudang Proyek']);
        $adminProyekB->warehouses()->sync([$warehouseB->id]);

        // 2. Create distribution destined for Warehouse A
        $distribution = $this->distributionService->create([
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id'   => $warehouseA->id,
            'delivery_date'     => now()->toDateString(),
            'items'             => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 20],
            ],
        ], $this->adminUser->id);

        $this->distributionService->ship($distribution, $this->adminUser->id);
        $this->assertEquals('in_transit', $distribution->status);

        $receiveData = [
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 20,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];

        // 3. Admin of Warehouse B attempts to receive for Warehouse A -> Rejected!
        $this->assertFalse($distribution->canUserReceive($adminProyekB));
        $responseForbidden = $this->actingAs($adminProyekB)
            ->post(route('distributions.receive', $distribution), $receiveData);
        $responseForbidden->assertSessionHas('error');
        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status, 'Status must remain in_transit when unauthorized user attempts receive');

        // 4. Admin of Warehouse A (the destination warehouse) receives -> Accepted!
        $this->assertTrue($distribution->canUserReceive($this->projectUser));
        $responseSuccess = $this->actingAs($this->projectUser)
            ->post(route('distributions.receive', $distribution), $receiveData);
        $responseSuccess->assertSessionHas('success');
        $distribution->refresh();
        $this->assertEquals('completed', $distribution->status);
    }

    public function test_can_create_distribution_with_null_string_material_id_for_custom_item(): void
    {
        $postData = [
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id'   => $this->projectWarehouse->id,
            'delivery_date'     => now()->toDateString(),
            'driver_name'       => 'Pak Joko Driver',
            'vehicle_number'    => 'B 9999 HYB',
            'notes'             => 'Pengiriman item campuran inventori dan custom',
            'items'             => [
                [
                    'type'        => 'material',
                    'material_id' => $this->semenMaterial->id,
                    'quantity'    => 5,
                ],
                [
                    // Simulasi kiriman dari form HTML yang mengirim material_id string "null" atau empty
                    'type'             => 'custom',
                    'material_id'      => 'null',
                    'custom_item_name' => 'Baut Anchor Khusus M16',
                    'custom_item_unit' => 'Pcs',
                    'quantity'         => 50,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('distributions.store'), $postData);
        $response->assertSessionHasNoErrors();

        $distribution = \App\Models\Distribution::where('vehicle_number', 'B 9999 HYB')->first();
        $this->assertNotNull($distribution);
        $this->assertCount(2, $distribution->items);

        $customItem = $distribution->items->where('custom_item_name', 'Baut Anchor Khusus M16')->first();
        $this->assertNotNull($customItem);
        $this->assertTrue($customItem->isCustom());
        $this->assertNull($customItem->material_id);

        // 2. Ship the distribution
        $this->actingAs($this->adminUser)->post(route('distributions.ship', $distribution));
        $distribution->refresh();
        $this->assertEquals('in_transit', $distribution->status);

        // 3. Receive at destination project warehouse
        $receiveData = [
            'surat_jalan' => 'SJ-AUTO-001',
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 5,
                    'qty_damaged_or_lost'  => 0,
                ],
                [
                    'distribution_item_id' => $customItem->id,
                    'received_quantity'    => 50,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];

        $receiveResponse = $this->actingAs($this->projectUser)->post(route('distributions.receive', $distribution), $receiveData);
        $receiveResponse->assertSessionHasNoErrors();
        $distribution->refresh();
        $this->assertEquals('completed', $distribution->status);

        // 4. Verify custom item was automatically registered in Master Materials
        $autoMaterial = \App\Models\Material::where('name', 'Baut Anchor Khusus M16')->first();
        $this->assertNotNull($autoMaterial, 'Custom item must be auto-registered in master materials');
        $this->assertStringStartsWith('MAT-AUTO-', $autoMaterial->sku);

        // 5. Verify stock was automatically added to Project Warehouse Inventory
        $projectInventory = \App\Models\Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $autoMaterial->id)
            ->first();
        $this->assertNotNull($projectInventory, 'Inventory record must be created in project warehouse');
        $this->assertEquals(50.0, (float) $projectInventory->quantity);

        // 6. Verify distribution item has material_id linked
        $this->assertEquals($autoMaterial->id, $customItem->fresh()->material_id);
    }

    public function test_confirming_surat_jalan_dispatches_notifications_to_approvers_and_parties(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        // 1. Create distribution
        $distribution = $this->distributionService->create([
            'from_warehouse_id' => $this->centralWarehouse->id,
            'to_warehouse_id'   => $this->projectWarehouse->id,
            'delivery_date'     => now()->toDateString(),
            'driver_name'       => 'Pak Supir',
            'vehicle_number'    => 'B 9999 NOTIF',
            'items' => [
                ['type' => 'material', 'material_id' => $this->semenMaterial->id, 'quantity' => 10],
            ],
        ], $this->adminUser->id);

        // 2. Ship distribution
        $this->distributionService->ship($distribution, $this->adminUser->id);

        // 3. Confirm receipt (Receive) by projectUser
        $receiveData = [
            'surat_jalan' => 'SJ-CONFIRM-TEST',
            'items' => [
                [
                    'distribution_item_id' => $distribution->items->first()->id,
                    'received_quantity'    => 10,
                    'qty_damaged_or_lost'  => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->projectUser)->post(route('distributions.receive', $distribution), $receiveData);
        $response->assertSessionHasNoErrors();

        // 4. Assert notifications were dispatched to receiver and adminUser (shipper/central admin)
        \Illuminate\Support\Facades\Notification::assertSentTo($this->projectUser, \App\Notifications\SystemNotification::class);
        \Illuminate\Support\Facades\Notification::assertSentTo($this->adminUser, \App\Notifications\SystemNotification::class);
    }
}

