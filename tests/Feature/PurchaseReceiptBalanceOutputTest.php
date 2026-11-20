<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\PurchaseReceipt;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseReceiptBalanceOutputTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected PurchaseReceipt $receipt;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view purchase receipts',
            'create purchase receipts',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'Admin Pengadaan Proyek', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('Admin Pengadaan Proyek');

        $this->project = Project::create([
            'code'                => 'PRJ-BAL-OUT',
            'name'                => 'Proyek Uji Saldo Output',
            'status'              => 'active',
            'operational_balance' => 4500000.00,
        ]);

        $wh = Warehouse::create([
            'code'       => 'WH-BAL-01',
            'name'       => 'Gudang Uji Saldo',
            'project_id' => $this->project->id,
            'is_central' => false,
        ]);
        $this->user->warehouses()->attach($wh->id);

        $this->receipt = PurchaseReceipt::create([
            'project_id'     => $this->project->id,
            'project_name'   => $this->project->name,
            'supplier_name'  => 'TB Sinar Berkah',
            'receipt_number' => 'NOTA-BAL-101',
            'receipt_date'   => now()->toDateString(),
            'payment_status' => 'paid',
            'payment_source' => 'project_cash',
            'total_amount'   => 500000.00,
            'items_subtotal' => 500000.00,
            'created_by'     => $this->user->id,
        ]);

        $this->receipt->items()->create([
            'material_name' => 'Pasir Pasang',
            'item_name'     => 'Pasir Pasang',
            'unit_name'     => 'Truk',
            'quantity'      => 1,
            'unit_price'    => 500000.00,
            'subtotal'      => 500000.00,
        ]);

        ProjectBalanceLog::create([
            'project_id'       => $this->project->id,
            'user_id'          => $this->user->id,
            'type'             => 'payment_deduction',
            'amount'           => 500000.00,
            'balance_before'   => 5000000.00,
            'balance_after'    => 4500000.00,
            'reference_number' => 'NOTA-BAL-101',
            'notes'            => 'Pembayaran Instan Nota Harian #NOTA-BAL-101',
        ]);
    }

    public function test_print_single_receipt_renders_initial_and_remaining_balance(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-receipts.print', $this->receipt));
        $response->assertOk();
        $response->assertSee('Saldo Awal');
        $response->assertSee('Sisa Saldo');
        $response->assertSee('5.000.000');
        $response->assertSee('4.500.000');
    }

    public function test_print_report_renders_initial_and_remaining_balance(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-receipts.print-weekly', $this->receipt));
        $response->assertOk();
        $response->assertSee('Saldo Awal Kas Proyek');
        $response->assertSee('Sisa Saldo Kas Proyek');
        $response->assertSee('4.500.000');
    }

    public function test_show_detail_view_renders_initial_and_remaining_balance(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-receipts.show', $this->receipt));
        $response->assertOk();
        $response->assertSee('Saldo Awal:');
        $response->assertSee('Sisa Saldo:');
        $response->assertSee('5.000.000');
        $response->assertSee('4.500.000');
    }
}
