<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseReceiptItemizedDiscountAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Supplier $supplier;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view purchase receipts',
            'create purchase receipts',
            'delete purchase receipts',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'admin-po-nota', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-po-nota');

        $this->project = Project::create([
            'code'                => 'PRJ-EXP-TEST',
            'name'                => 'Proyek Uji Rekap Nota',
            'status'              => 'active',
            'operational_balance' => 10000000.00, // Rp 10.000.000
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'TB Sinar Abadi',
            'phone' => '08123456789',
        ]);

        $category = Category::firstOrCreate(['code' => 'CAT-TEST'], ['name' => 'Material Umum']);
        $unit = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak']);

        $this->material = Material::create([
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'sku'         => 'MAT-DISC-01',
            'name'        => 'Semen Gresik 50kg',
        ]);
    }

    public function test_purchase_receipt_calculates_discounts_and_fees_correctly(): void
    {
        // Item 1: 10 Sak @ Rp 60.000 = Rp 600.000 - Disc Rp 50.000 = Net Rp 550.000
        // Item 2: 5 Sak @ Rp 50.000 = Rp 250.000 - Disc Rp 0 = Net Rp 250.000
        // Items Subtotal = Rp 800.000
        // Global Discount = Rp 50.000
        // Shipping Fee = Rp 100.000
        // Additional Fee (Kuli) = Rp 75.000
        // Expected Grand Total = (800.000 - 50.000) + 100.000 + 75.000 = Rp 925.000

        $postData = [
            'project_id'            => $this->project->id,
            'project_name'          => $this->project->name,
            'supplier_name'         => 'TB Sinar Abadi',
            'receipt_number'        => 'BON-001',
            'receipt_date'          => '2026-10-28',
            'payment_source'        => 'personal_advance',
            'paid_by_name'          => 'Mandor Joko',
            'notes'                 => 'Belanja material darurat tahap 1',
            'global_discount'       => 50000,
            'shipping_fee'          => 100000,
            'additional_fee'        => 75000,
            'additional_fee_notes'  => 'Upah kuli bongkar semen 15 sak',
            'items' => [
                [
                    'material_id'   => $this->material->id,
                    'item_type'     => 'Material',
                    'material_name' => 'Semen Gresik 50kg',
                    'item_name'     => 'Semen Gresik 50kg Bon #1',
                    'unit_name'     => 'Sak',
                    'quantity'      => 10,
                    'unit_price'    => 60000,
                    'item_discount' => 50000,
                    'notes'         => 'Diskon toko Rp 5.000/sak min 10 sak',
                ],
                [
                    'material_id'   => null,
                    'item_type'     => 'Konsumsi',
                    'material_name' => 'Nasi Bungkus',
                    'item_name'     => 'Nasi Bungkus Siang Pekerja',
                    'unit_name'     => 'Porsi',
                    'quantity'      => 5,
                    'unit_price'    => 50000,
                    'item_discount' => 0,
                    'notes'         => null,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        $receipt = PurchaseReceipt::where('receipt_number', 'BON-001')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals('personal_advance', $receipt->payment_source);
        $this->assertEquals('Mandor Joko', $receipt->paid_by_name);
        $this->assertTrue($receipt->is_talangan);

        $this->assertEquals(800000.00, (float)$receipt->items_subtotal);
        $this->assertEquals(50000.00, (float)$receipt->global_discount);
        $this->assertEquals(100000.00, (float)$receipt->shipping_fee);
        $this->assertEquals(75000.00, (float)$receipt->additional_fee);
        $this->assertEquals('Upah kuli bongkar semen 15 sak', $receipt->additional_fee_notes);
        $this->assertEquals(925000.00, (float)$receipt->total_amount);

        // Check project balance NOT deducted because it is a personal advance (talangan pribadi)
        $this->project->refresh();
        $this->assertEquals(10000000.00, (float)$this->project->operational_balance);

        // Check items persisted with item_discount & item_type
        $items = $receipt->items;
        $this->assertCount(2, $items);
        $this->assertEquals('Material', $items[0]->item_type);
        $this->assertEquals(50000.00, (float)$items[0]->item_discount);
        $this->assertEquals(550000.00, (float)$items[0]->subtotal);
        $this->assertEquals('Konsumsi', $items[1]->item_type);
        $this->assertEquals(0.00, (float)$items[1]->item_discount);
        $this->assertEquals(250000.00, (float)$items[1]->subtotal);
    }

    public function test_purchase_receipt_allows_negative_balance_as_field_talangan(): void
    {
        // Balance is 10.000.000, expense is 15.000.000 (Field Talangan when HQ funds haven't arrived)
        $postData = [
            'project_id'            => $this->project->id,
            'project_name'          => $this->project->name,
            'supplier_name'         => 'TB Sinar Abadi',
            'receipt_number'        => 'BON-OVER',
            'receipt_date'          => '2026-10-28',
            'global_discount'       => 0,
            'shipping_fee'          => 0,
            'additional_fee'        => 0,
            'items' => [
                [
                    'material_id'   => $this->material->id,
                    'item_type'     => 'Material',
                    'material_name' => 'Semen Gresik 50kg',
                    'item_name'     => 'Semen Gresik Borongan',
                    'unit_name'     => 'Sak',
                    'quantity'      => 300,
                    'unit_price'    => 50000, // 15.000.000
                    'item_discount' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-receipts.store'), $postData);
        $response->assertSessionHasNoErrors();

        // Verify balance became negative (-5.000.000) as field talangan/deficit
        $this->project->refresh();
        $this->assertEquals(-5000000.00, (float)$this->project->operational_balance);

        $receipt = PurchaseReceipt::where('receipt_number', 'BON-OVER')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals(15000000.00, (float)$receipt->total_amount);
    }

    public function test_can_export_weekly_purchase_receipts_to_excel(): void
    {
        // Create 2 receipts
        PurchaseReceipt::create([
            'project_id'           => $this->project->id,
            'project_name'         => $this->project->name,
            'supplier_name'        => 'TB Rejeki',
            'receipt_number'       => 'NOTA-001',
            'receipt_date'         => now()->format('Y-m-d'),
            'items_subtotal'       => 500000,
            'global_discount'      => 20000,
            'shipping_fee'         => 30000,
            'additional_fee'       => 15000,
            'additional_fee_notes' => 'Kuli 1 org',
            'total_amount'         => 525000,
            'payment_status'       => 'paid',
            'created_by'           => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-receipts.export-weekly', [
            'project_id' => $this->project->id,
            'start_date' => now()->startOfWeek()->format('Y-m-d'),
            'end_date'   => now()->endOfWeek()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        $export = new \App\Exports\WeeklyPurchaseReceiptExport(collect());
        $headings = $export->headings();
        $this->assertNotContains('Proyek', $headings);
        $this->assertNotContains('Status', $headings);
        $this->assertContains('Item', $headings);
        $this->assertContains('Nama Barang', $headings);
        $this->assertContains('Jumlah (Vol)', $headings);
        $this->assertContains('Harga Satuan (Rp)', $headings);
        $this->assertContains('Subtotal (Rp)', $headings);
    }
}
