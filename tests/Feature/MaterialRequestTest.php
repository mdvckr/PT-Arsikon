<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MaterialRequestService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialRequestTest extends TestCase
{
    use RefreshDatabase;

    protected MaterialRequestService $requestService;
    protected Warehouse $projectWarehouse;
    protected Warehouse $centralWarehouse;
    protected User $projectUser;
    protected User $adminUser;
    protected Material $semenMaterial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->requestService = new MaterialRequestService();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->semenMaterial = Material::where('sku', 'MAT-SEM-001')->firstOrFail();
    }

    public function test_project_user_can_create_and_submit_material_request(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_requested' => 50],
            ],
            true, // submit immediately
            'Kebutuhan Pengecoran Lantai 2'
        );

        $this->assertNotNull($request);
        $this->assertStringStartsWith('REQ-', $request->request_number);
        $this->assertEquals('submitted', $request->status);
        $this->assertCount(1, $request->items);
    }

    public function test_admin_can_approve_material_request(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_requested' => 50],
            ]
        );

        $approvedRequest = $this->requestService->approveRequest(
            $request,
            $this->adminUser
        );

        $this->assertEquals('approved', $approvedRequest->status);
        $this->assertEquals($this->adminUser->id, $approvedRequest->approved_by_user_id);
    }

    public function test_admin_can_reject_material_request_with_reason(): void
    {
        $request = $this->requestService->createRequest(
            $this->projectWarehouse,
            $this->projectUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_requested' => 5000],
            ]
        );

        $rejectedRequest = $this->requestService->rejectRequest(
            $request,
            $this->adminUser,
            'Kuantitas terlalu besar melebihi alokasi proyek'
        );

        $this->assertEquals('rejected', $rejectedRequest->status);
        $this->assertEquals('Kuantitas terlalu besar melebihi alokasi proyek', $rejectedRequest->rejection_reason);
    }

    public function test_cannot_create_request_from_central_warehouse(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('hanya boleh dibuat oleh Gudang Proyek');

        $this->requestService->createRequest(
            $this->centralWarehouse, // Invalid source for request
            $this->adminUser,
            [
                ['material_id' => $this->semenMaterial->id, 'qty_requested' => 10],
            ]
        );
    }
}
