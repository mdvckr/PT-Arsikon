<?php

namespace Tests\Feature;

use App\Models\AuditActionPlan;
use App\Models\AuditFinding;
use App\Models\Category;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Mail\AuditDeadlineReminderMail;
use App\Mail\AuditFindingAssignedMail;
use App\Mail\MonthlyAuditExecutiveSummaryMail;
use App\Services\AuditAutomationService;
use App\Services\GoodsReceiptService;
use App\Services\StockOpnameService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAndAuditAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $ownerUser;
    protected Warehouse $warehouse;
    protected Material $material;
    protected StockOpnameService $opnameService;
    protected GoodsReceiptService $goodsReceiptService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $stockService = new StockService();
        $this->opnameService = new StockOpnameService($stockService);
        $this->goodsReceiptService = new GoodsReceiptService($stockService);

        $this->adminUser = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->ownerUser = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
        $this->warehouse = Warehouse::where('is_central', true)->firstOrFail();

        $category = Category::first() ?? Category::create(['code' => 'CAT-TEST', 'name' => 'Bahan Baku']);
        $unit = Unit::first() ?? Unit::create(['code' => 'SAK', 'name' => 'Sak']);

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-TEST-001'],
            ['name' => 'Semen Portland Test', 'category_id' => $category->id, 'unit_id' => $unit->id]
        );
    }

    public function test_audit_automation_service_creates_finding_and_sends_email(): void
    {
        Mail::fake();

        $service = new AuditAutomationService();
        $finding = $service->createFromOpnameDiscrepancy($this->warehouse, 3000000, $this->adminUser->id);

        $this->assertDatabaseHas('audit_findings', [
            'id'               => $finding->id,
            'source_type'      => 'opname_discrepancy',
            'financial_impact' => 3000000,
            'risk_level'       => 'High',
            'pic_id'           => $this->adminUser->id,
            'status'           => 'Open',
        ]);

        Mail::assertSent(AuditFindingAssignedMail::class, function ($mail) {
            return $mail->hasTo($this->adminUser->email);
        });
    }

    public function test_stock_opname_approval_with_discrepancy_auto_triggers_audit_finding(): void
    {
        Mail::fake();

        // 1. Initial stock via goods receipt
        $supplier = Supplier::firstOrFail();
        $this->goodsReceiptService->processGoodsReceipt(
            $supplier,
            $this->warehouse,
            $this->adminUser,
            [['material_id' => $this->material->id, 'qty_received' => 50]]
        );

        // 2. Create stock opname
        $opname = $this->opnameService->createOpname(
            $this->warehouse,
            $this->adminUser,
            now()->toDateString(),
            'Opname Test Discrepancy'
        );

        // 3. Submit physical counts with discrepancy (physical 40 vs system 50 -> diff -10)
        $submittedOpname = $this->opnameService->submitPhysicalCounts(
            $opname,
            [['material_id' => $this->material->id, 'qty_physical' => 40, 'notes' => '10 sak hilang/rusak']]
        );

        // 4. Approve opname
        $this->opnameService->approveOpname($submittedOpname, $this->ownerUser);

        // 5. Verify audit_findings table has the auto-generated ticket
        $this->assertDatabaseHas('audit_findings', [
            'source_type'    => 'opname_discrepancy',
            'auditable_type' => get_class($opname),
            'auditable_id'   => $opname->id,
            'warehouse_id'   => $this->warehouse->id,
            'status'         => 'Open',
        ]);

        $createdFinding = AuditFinding::where('auditable_type', get_class($opname))
            ->where('auditable_id', $opname->id)
            ->first();

        $this->assertNotNull($createdFinding);
        $this->assertEquals('opname_discrepancy', $createdFinding->source_type);
        $this->assertEquals('Open', $createdFinding->status);
    }

    public function test_reports_index_displays_kpi_and_audit_findings(): void
    {
        AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'manual',
            'warehouse_id'     => $this->warehouse->id,
            'title'            => 'Pemeriksaan Lapangan Bulanan',
            'description'      => 'Kondisi palet di gudang utama perlu perbaikan.',
            'financial_impact' => 500000,
            'risk_level'       => 'Low',
            'pic_id'           => $this->adminUser->id,
            'created_by'       => $this->ownerUser->id,
            'due_date'         => Carbon::now()->addDays(5),
            'status'           => 'Open',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan & Audit Terpadu', false);
        $response->assertSee('Pemeriksaan Lapangan Bulanan', false);
        $response->assertSee('Audit Pengadaan & PO', false);
    }

    public function test_procurement_audit_report_view_renders_correctly(): void
    {
        $supplier = Supplier::firstOrFail();

        $po = PurchaseOrder::create([
            'po_number'             => 'PO-TEST-001',
            'supplier_id'           => $supplier->id,
            'supplier_name'         => $supplier->name,
            'delivery_warehouse_id' => $this->warehouse->id,
            'created_by'            => $this->adminUser->id,
            'status'                => 'sent',
            'order_date'            => now()->subDays(10)->toDateString(),
            'expected_delivery'     => now()->subDays(3)->toDateString(),
            'total_amount'          => 15000000,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reports.procurement-audit'));

        $response->assertStatus(200);
        $response->assertSee('PO-TEST-001');
        $response->assertSee('Terlambat 3 hari');
    }

    public function test_pic_can_submit_action_plan_and_auditor_can_verify(): void
    {
        Storage::fake('public');

        $finding = AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'opname_discrepancy',
            'warehouse_id'     => $this->warehouse->id,
            'title'            => 'Diskrepansi Besi Beton',
            'description'      => 'Selisih 20 batang besi beton.',
            'financial_impact' => 2000000,
            'risk_level'       => 'Medium',
            'pic_id'           => $this->adminUser->id,
            'created_by'       => $this->ownerUser->id,
            'due_date'         => Carbon::now()->addDays(3),
            'status'           => 'Open',
        ]);

        $file = UploadedFile::fake()->create('berita_acara.pdf', 500, 'application/pdf');

        // 1. Submit Action Plan
        $response = $this->actingAs($this->adminUser)->post(
            route('reports.submit-action', $finding),
            [
                'corrective_action' => 'Telah dilakukan pengecekan ulang dan ditemukan salah input pada penerimaan vendor.',
                'evidence_file'     => $file,
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('audit_action_plans', [
            'audit_finding_id'    => $finding->id,
            'user_id'             => $this->adminUser->id,
            'verification_status' => 'Pending',
        ]);

        $this->assertEquals('Under Review', $finding->fresh()->status);

        // 2. Auditor Verifies and Approves
        $actionPlan = AuditActionPlan::where('audit_finding_id', $finding->id)->firstOrFail();

        $verifyResponse = $this->actingAs($this->adminUser)->post(
            route('reports.verify-action', $actionPlan),
            [
                'verification_status' => 'Approved',
                'verifier_notes'      => 'Bukti telah diverifikasi dan valid.',
            ]
        );

        $verifyResponse->assertRedirect();
        $this->assertEquals('Approved', $actionPlan->fresh()->verification_status);
        $this->assertEquals('Closed', $finding->fresh()->status);
        $this->assertNotNull($finding->fresh()->closed_at);
    }

    public function test_artisan_audit_automate_daily_and_monthly_commands(): void
    {
        Mail::fake();

        // 1. Create upcoming and overdue findings
        AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'stock_mutation',
            'warehouse_id'     => $this->warehouse->id,
            'title'            => 'Upcoming Audit Finding',
            'description'      => 'Due in 2 days.',
            'financial_impact' => 1000000,
            'risk_level'       => 'Medium',
            'pic_id'           => $this->adminUser->id,
            'due_date'         => Carbon::today()->addDays(2),
            'status'           => 'Open',
        ]);

        AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'po_finance',
            'warehouse_id'     => $this->warehouse->id,
            'title'            => 'Overdue Audit Finding',
            'description'      => 'Due 3 days ago.',
            'financial_impact' => 5000000,
            'risk_level'       => 'High',
            'pic_id'           => $this->adminUser->id,
            'due_date'         => Carbon::today()->subDays(3),
            'status'           => 'In Progress',
        ]);

        // Run daily reminder command
        $this->artisan('audit:automate', ['--type' => 'daily'])
            ->assertSuccessful();

        Mail::assertSent(AuditDeadlineReminderMail::class, 2);

        // Run monthly executive summary command
        $this->artisan('audit:automate', ['--type' => 'monthly'])
            ->assertSuccessful();

        Mail::assertSent(MonthlyAuditExecutiveSummaryMail::class);
    }

    public function test_audit_automation_service_record_methods_and_monthly_report_command(): void
    {
        Mail::fake();

        $service = new AuditAutomationService();

        // 1. Test recordOpnameDiscrepancy
        $finding1 = $service->recordOpnameDiscrepancy(
            101,
            $this->warehouse->id,
            'Semen Portland',
            100,
            90,
            75000,
            $this->adminUser->id
        );

        $this->assertNotNull($finding1);
        $this->assertEquals('inventory_discrepancy', $finding1->source_type);
        $this->assertEquals(750000, (float) $finding1->financial_impact);
        $this->assertDatabaseHas('audit_findings', ['id' => $finding1->id, 'stock_opname_id' => 101]);

        // 2. Test recordPoDelay
        $finding2 = $service->recordPoDelay(
            202,
            'PO-202610-001',
            'PT Semen Nusantara',
            12000000,
            $this->adminUser->id,
            5
        );

        $this->assertNotNull($finding2);
        $this->assertEquals('po_delay', $finding2->source_type);
        $this->assertEquals(12000000, (float) $finding2->financial_impact);
        $this->assertDatabaseHas('audit_findings', ['id' => $finding2->id, 'purchase_order_id' => 202]);

        // 3. Test audit:daily-reminders command
        $this->artisan('audit:daily-reminders')->assertSuccessful();

        // 4. Test audit:monthly-report command & MonthlyAuditReport database record
        $this->artisan('audit:monthly-report')->assertSuccessful();

        $lastMonthString = Carbon::now()->subMonth()->format('Y-m');
        $this->assertDatabaseHas('monthly_audit_reports', [
            'report_period' => $lastMonthString,
        ]);
    }

    public function test_pic_can_update_finding_status_directly(): void
    {
        $finding = AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'manual_audit',
            'warehouse_id'     => $this->warehouse->id,
            'title'            => 'Pengecekan Panel Listrik',
            'description'      => 'Kabel terbuka di gudang selatan.',
            'financial_impact' => 250000,
            'risk_level'       => 'Medium',
            'pic_id'           => $this->adminUser->id,
            'due_date'         => Carbon::today()->addDays(4),
            'status'           => 'Open',
        ]);

        $response = $this->actingAs($this->adminUser)->patch(
            route('findings.update-status', $finding),
            [
                'status'            => 'Closed',
                'corrective_action' => 'Kabel telah diperbaiki dan dibungkus conduit pelindung standar.',
            ]
        );

        $response->assertRedirect();
        $this->assertEquals('Closed', $finding->fresh()->status);
        $this->assertNotNull($finding->fresh()->resolved_at);
        $this->assertNotNull($finding->fresh()->closed_at);
        $this->assertEquals('Kabel telah diperbaiki dan dibungkus conduit pelindung standar.', $finding->fresh()->corrective_action);
    }
}
