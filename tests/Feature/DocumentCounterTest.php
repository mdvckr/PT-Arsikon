<?php

namespace Tests\Feature;

use App\Models\DocumentCounter;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocumentCounterTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->supplier = Supplier::firstOrFail();
    }

    public function test_document_counters_generates_sequential_po_and_pr_numbers(): void
    {
        $currentYear = (int) date('Y');

        $poNum1 = DocumentNumberService::generate('PO', $currentYear);
        $poNum2 = DocumentNumberService::generate('PO', $currentYear);

        $this->assertEquals("PO-{$currentYear}-0001", $poNum1);
        $this->assertEquals("PO-{$currentYear}-0002", $poNum2);

        $prNum1 = DocumentNumberService::generate('PR', $currentYear);
        $prNum2 = DocumentNumberService::generate('PR', $currentYear);

        $this->assertEquals("PR-{$currentYear}-0001", $prNum1);
        $this->assertEquals("PR-{$currentYear}-0002", $prNum2);

        $counterPO = DocumentCounter::where('type', 'PO')->where('year', $currentYear)->first();
        $this->assertNotNull($counterPO);
        $this->assertEquals(2, $counterPO->last_number);
    }

    public function test_counter_initializes_from_highest_existing_number(): void
    {
        $currentYear = (int) date('Y');

        // Insert legacy PO with number PO-YYYY-0007
        DB::table('purchase_orders')->insert([
            'po_number'    => "PO-{$currentYear}-0007",
            'supplier_id'  => $this->supplier->id,
            'created_by'   => $this->adminUser->id,
            'status'       => 'draft',
            'order_date'   => now()->toDateString(),
            'total_amount' => 100000,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $nextPo = DocumentNumberService::generate('PO', $currentYear);
        $this->assertEquals("PO-{$currentYear}-0008", $nextPo);

        $counter = DocumentCounter::where('type', 'PO')->where('year', $currentYear)->first();
        $this->assertEquals(8, $counter->last_number);
    }

    public function test_creating_purchase_order_and_procurement_request_uses_document_counters(): void
    {
        $currentYear = (int) date('Y');

        $pr = ProcurementRequest::create([
            'requested_by' => $this->adminUser->id,
            'status'       => 'draft',
        ]);
        $this->assertEquals("PR-{$currentYear}-0001", $pr->pr_number);

        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'created_by'  => $this->adminUser->id,
            'order_date'  => now()->toDateString(),
            'status'      => 'draft',
        ]);
        $this->assertEquals("PO-{$currentYear}-0001", $po->po_number);
    }
}
