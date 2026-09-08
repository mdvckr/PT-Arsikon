<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ToolManagementService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolManagementTest extends TestCase
{
    use RefreshDatabase;

    protected ToolManagementService $toolService;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminUser;
    protected User $projectUser;
    protected Tool $gensetTool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->toolService = new ToolManagementService();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->gensetTool = Tool::where('code', 'TOOL-GEN-01')->firstOrFail();
    }

    public function test_can_assign_available_tool_to_project(): void
    {
        $assignment = $this->toolService->assignTool(
            $this->gensetTool,
            $this->centralWarehouse,
            $this->adminUser,
            $this->projectWarehouse,
            $this->projectUser,
            now()->addDays(7)->toDateString(),
            'Peminjaman Genset untuk site proyek A'
        );

        $this->assertNotNull($assignment);
        $this->assertEquals('assigned', $this->gensetTool->fresh()->status);
        $this->assertEquals('active', $assignment->status);
    }

    public function test_cannot_assign_tool_that_is_already_assigned(): void
    {
        $this->toolService->assignTool(
            $this->gensetTool,
            $this->centralWarehouse,
            $this->adminUser,
            $this->projectWarehouse
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('tidak dapat dipinjamkan');

        // Try assigning second time
        $this->toolService->assignTool(
            $this->gensetTool->fresh(),
            $this->centralWarehouse,
            $this->adminUser,
            $this->projectWarehouse
        );
    }

    public function test_return_tool_with_damaged_condition_triggers_maintenance(): void
    {
        $assignment = $this->toolService->assignTool(
            $this->gensetTool,
            $this->centralWarehouse,
            $this->adminUser,
            $this->projectWarehouse
        );

        $inspection = $this->toolService->returnTool(
            $assignment,
            $this->adminUser,
            'damaged',
            'Oli bocor dan mesin sulit dinyalakan'
        );

        $this->assertEquals('damaged', $inspection->condition);
        $this->assertEquals('maintenance', $this->gensetTool->fresh()->status);
        $this->assertDatabaseHas('maintenances', [
            'tool_id' => $this->gensetTool->id,
            'status' => 'in_progress',
        ]);
    }
}
