<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\StockMutation;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MaterialUsageService;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialUsageTest extends TestCase
{
    use RefreshDatabase;

    protected MaterialUsageService $usageService;
    protected StockService $stockService;
    protected Warehouse $projectWarehouse;
    protected User $adminProyek;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->stockService = new StockService();
        $this->usageService = new MaterialUsageService($this->stockService);
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->material = Material::firstOrFail();
    }

    public function test_can_record_material_usage_and_deducts_stock(): void
    {
        $initialInventory = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->first();
        $baseQty = $initialInventory ? (float) $initialInventory->quantity : 0;

        // Add 100 units of initial stock to project warehouse
        $this->stockService->addStock(
            $this->projectWarehouse,
            $this->material,
            100,
            'initial_test',
            null,
            $this->adminProyek->id,
            'Stok awal pengujian'
        );

        $afterAddInventory = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->first();

        $this->assertEquals($baseQty + 100, $afterAddInventory->quantity);

        // Record material usage (35 units taken by Mandor)
        $usage = $this->usageService->createUsage(
            $this->projectWarehouse,
            $this->adminProyek,
            [
                'recipient_name' => 'Pak Bambang (Mandor Cor)',
                'job_section'    => 'Pengecoran Balok Lantai 2',
                'usage_date'     => now()->toDateString(),
                'notes'          => 'Bon pengeluaran batch 1',
                'items'          => [
                    [
                        'material_id' => $this->material->id,
                        'quantity'    => 35,
                        'notes'       => 'Pekerjaan struktur',
                    ],
                ],
            ]
        );

        $this->assertNotNull($usage);
        $this->assertStringStartsWith('USG-', $usage->usage_number);
        $this->assertEquals('Pak Bambang (Mandor Cor)', $usage->recipient_name);
        $this->assertEquals('completed', $usage->status);
        $this->assertCount(1, $usage->items);

        // Verify inventory stock deducted by 35
        $updatedInventory = Inventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('material_id', $this->material->id)
            ->first();

        $this->assertEquals($baseQty + 100 - 35, $updatedInventory->quantity);

        // Verify stock mutation logged
        $mutation = StockMutation::where('reference_type', 'material_usage')
            ->where('reference_id', $usage->id)
            ->first();

        $this->assertNotNull($mutation);
        $this->assertEquals(-35, $mutation->qty_change);
        $this->assertEquals($baseQty + 100 - 35, $mutation->qty_balance_after);
    }

    public function test_cannot_record_usage_exceeding_available_stock(): void
    {
        // Ensure stock is only 10
        Inventory::updateOrCreate(
            ['warehouse_id' => $this->projectWarehouse->id, 'material_id' => $this->material->id],
            ['quantity' => 10, 'min_stock' => 0]
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('tidak mencukupi');

        $this->usageService->createUsage(
            $this->projectWarehouse,
            $this->adminProyek,
            [
                'recipient_name' => 'Mandor Joko',
                'items' => [
                    ['material_id' => $this->material->id, 'quantity' => 50],
                ],
            ]
        );
    }

    public function test_user_can_view_material_usages_web_routes(): void
    {
        $response = $this->actingAs($this->adminProyek)->get(route('material-usages.index'));
        $response->assertStatus(200);
        $response->assertSee('Pemakaian Material Lapangan');

        $createResponse = $this->actingAs($this->adminProyek)->get(route('material-usages.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Catat Pengeluaran Baru');

        // Create sample usage and check show & print
        $usage = MaterialUsage::create([
            'usage_number' => 'USG-TEST-0001',
            'warehouse_id' => $this->projectWarehouse->id,
            'project_id' => $this->projectWarehouse->project_id,
            'issued_by_user_id' => $this->adminProyek->id,
            'recipient_name' => 'Pak Mandor Test',
            'job_section' => 'Plumbing Lantai 1',
            'usage_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $showResponse = $this->actingAs($this->adminProyek)->get(route('material-usages.show', $usage));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('USG-TEST-0001');
        $showResponse->assertSee('Pak Mandor Test');

        $printResponse = $this->actingAs($this->adminProyek)->get(route('material-usages.print', $usage));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('BUKTI PENGELUARAN MATERIAL');
    }

    public function test_tool_assignment_stores_borrower_name_and_phone(): void
    {
        $tool = Tool::first();
        if (!$tool) {
            $tool = Tool::create([
                'name' => 'Bor Listrik Makita',
                'code' => 'TL-MAK-01',
                'category_id' => null,
                'is_active' => true,
            ]);
        }

        // Ensure ToolInventory has available stock in projectWarehouse
        \App\Models\ToolInventory::updateOrCreate(
            ['tool_id' => $tool->id, 'warehouse_id' => $this->projectWarehouse->id],
            ['stock_total' => 10, 'stock_available' => 10, 'stock_borrowed' => 0, 'stock_maintenance' => 0, 'stock_damaged' => 0]
        );

        $postData = [
            'warehouse_id'   => $this->projectWarehouse->id,
            'borrower_name'  => 'Pak Joko Santoso',
            'borrower_phone' => '081234567890',
            'location_name'  => 'Lantai 3 Gedung A',
            'assigned_at'    => now()->toDateString(),
            'quantities'     => [
                $tool->id => 1,
            ],
        ];

        $response = $this->actingAs($this->adminProyek)->post(route('tool-assignments.store'), $postData);
        $response->assertRedirect(route('tool-assignments.index'));

        $assignment = ToolAssignment::where('borrower_name', 'Pak Joko Santoso')->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('081234567890', $assignment->borrower_phone);
        $this->assertEquals('Lantai 3 Gedung A', $assignment->location_name);
        $this->assertEquals('Pak Joko Santoso', $assignment->borrower_display);
    }
}
