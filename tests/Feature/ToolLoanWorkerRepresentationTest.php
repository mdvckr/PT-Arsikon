<?php

namespace Tests\Feature;

use App\Models\ToolLoan;
use App\Models\ToolAssignment;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolLoanWorkerRepresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_tool_loan_supports_worker_representation_fields(): void
    {
        $warehouse = Warehouse::firstOrFail();
        $admin = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $worker = User::factory()->create(['name' => 'Tukang Bejo']);

        $loan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $warehouse->id,
            'assigned_by_user_id' => $admin->id,
            'borrower_user_id'    => $worker->id,
            'is_representative'   => true,
            'borrower_name'       => $worker->name,
            'location_name'       => 'Zona Lapangan 1',
            'assigned_at'         => now(),
            'status'              => 'active',
        ]);

        $this->assertTrue((bool)$loan->is_representative);
        $this->assertEquals($worker->id, $loan->borrower_user_id);
        $this->assertEquals('Tukang Bejo', $loan->borrowerUser?->name);
    }

    public function test_admin_proyek_can_borrow_for_worker_with_instant_self_approval(): void
    {
        $warehouse = Warehouse::where('is_central', false)->firstOrFail();
        $admin = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $category = Category::firstOrCreate(
            ['code' => 'CAT-TEST-REP'],
            ['name' => 'Kategori Test', 'type' => 'tool']
        );
        $tool = Tool::create([
            'code'                 => 'TOOL-REP-01',
            'name'                 => 'Mesin Las Inverter',
            'brand'                => 'Lakoni',
            'type'                 => 'Las',
            'category_id'          => $category->id,
            'current_warehouse_id' => $warehouse->id,
            'owner_warehouse_id'   => $warehouse->id,
            'stock_total'          => 5,
            'stock_available'      => 5,
            'stock_borrowed'       => 0,
            'is_active'            => true,
        ]);
        ToolInventory::create([
            'warehouse_id'    => $warehouse->id,
            'tool_id'         => $tool->id,
            'stock_total'     => 5,
            'stock_available' => 5,
            'stock_borrowed'  => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('tool-assignments.store'), [
            'warehouse_id'       => $warehouse->id,
            'borrower_name'      => 'Pak Joko (Mandor)',
            'borrower_phone'     => '08123456789',
            'is_representative'  => '1',
            'self_approve'       => '1',
            'location_name'      => 'Gedung Rektorat Lt 2',
            'assigned_at'        => now()->toDateString(),
            'quantities'         => [$tool->id => 2],
        ]);

        $response->assertRedirect(route('tool-assignments.index'));

        $loan = ToolLoan::where('borrower_name', 'Pak Joko (Mandor)')->firstOrFail();
        $this->assertEquals('active', $loan->status);
        $this->assertEquals($admin->id, $loan->assigned_by_user_id);
        $this->assertEquals($admin->id, $loan->approved_by_user_id);
        $this->assertTrue((bool)$loan->is_representative);

        // Verify stock deducted
        $inv = ToolInventory::where('warehouse_id', $warehouse->id)->where('tool_id', $tool->id)->first();
        $this->assertEquals(3, $inv->stock_available);
        $this->assertEquals(2, $inv->stock_borrowed);
    }

    public function test_admin_proyek_can_approve_own_submitted_loan_for_worker(): void
    {
        $warehouse = Warehouse::where('is_central', false)->firstOrFail();
        $admin = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $category = Category::firstOrCreate(
            ['code' => 'CAT-TEST-REP2'],
            ['name' => 'Kategori Test 2', 'type' => 'tool']
        );
        $tool = Tool::create([
            'code'                 => 'TOOL-REP-02',
            'name'                 => 'Gerinda Tangan',
            'brand'                => 'Bosch',
            'type'                 => 'Gerinda',
            'category_id'          => $category->id,
            'current_warehouse_id' => $warehouse->id,
            'owner_warehouse_id'   => $warehouse->id,
            'stock_total'          => 5,
            'stock_available'      => 5,
            'stock_borrowed'       => 0,
            'is_active'            => true,
        ]);
        ToolInventory::create([
            'warehouse_id'    => $warehouse->id,
            'tool_id'         => $tool->id,
            'stock_total'     => 5,
            'stock_available' => 5,
            'stock_borrowed'  => 0,
        ]);

        $loan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $warehouse->id,
            'assigned_by_user_id' => $admin->id,
            'borrower_name'       => 'Sdr. Slamet',
            'is_representative'   => true,
            'location_name'       => 'Zona Lapangan',
            'assigned_at'         => now(),
            'status'              => 'pending',
        ]);
        ToolAssignment::create([
            'tool_loan_id'        => $loan->id,
            'assignment_number'   => 'TA-TEST-001',
            'tool_id'             => $tool->id,
            'quantity'            => 1,
            'from_warehouse_id'   => $warehouse->id,
            'assigned_by_user_id' => $admin->id,
            'borrower_name'       => 'Sdr. Slamet',
            'is_representative'   => true,
            'location_name'       => 'Zona Lapangan',
            'status'              => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('tool-assignments.approve', $loan->id));
        $response->assertSessionHas('success');
        $this->assertEquals('active', $loan->fresh()->status);
        $this->assertEquals($admin->id, $loan->fresh()->approved_by_user_id);
    }
}

