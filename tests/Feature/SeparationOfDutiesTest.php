<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Payment;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MaterialRequestService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationOfDutiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $projectUser;
    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected Material $material;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectUser = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();
        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-SEM-001'],
            ['name' => 'Semen Portland 50kg', 'category_id' => \App\Models\Category::first()->id, 'unit_id' => \App\Models\Unit::first()->id]
        );
        $this->supplier = Supplier::firstOrFail();
    }

    public function test_creator_cannot_approve_own_material_request_via_service(): void
    {
        $service = new MaterialRequestService();
        $mr = $service->createRequest(
            $this->projectWarehouse,
            $this->centralWarehouse,
            $this->adminUser,
            [['material_id' => $this->material->id, 'qty_requested' => 10]],
            true
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Pembuat permintaan tidak dapat menyetujui pengajuannya sendiri.');

        $service->approveRequest($mr, $this->adminUser);
    }

    public function test_material_request_controller_catches_exception_and_redirects_with_error(): void
    {
        $service = new MaterialRequestService();
        $mr = $service->createRequest(
            $this->projectWarehouse,
            $this->centralWarehouse,
            $this->adminUser,
            [['material_id' => $this->material->id, 'qty_requested' => 10]],
            true
        );

        // Self-approval via controller
        $response = $this->actingAs($this->adminUser)
            ->from(route('material-requests.show', $mr))
            ->post(route('material-requests.approve', $mr));

        $response->assertRedirect(route('material-requests.show', $mr));
        $response->assertSessionHas('error');
    }

    public function test_creator_cannot_approve_own_procurement_request(): void
    {
        $pr = ProcurementRequest::create([
            'requested_by' => $this->adminUser->id,
            'status'       => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement.approve', $pr));

        $response->assertSessionHas('error');
        $this->assertEquals('submitted', $pr->fresh()->status);
    }

    public function test_creator_cannot_approve_own_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'created_by'  => $this->adminUser->id,
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchase-orders.approve', $po));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $po->fresh()->status);
    }

    public function test_creator_cannot_verify_own_payment(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'created_by'  => $this->projectUser->id,
            'order_date'  => now()->toDateString(),
            'status'      => 'sent',
        ]);

        $payment = Payment::create([
            'purchase_order_id' => $po->id,
            'payment_number'    => 'PAY-TEST-001',
            'amount'            => 50000,
            'payment_date'      => now()->toDateString(),
            'created_by'        => $this->adminUser->id,
            'status'            => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('payments.verify', $payment));

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $payment->fresh()->status);
    }
}
