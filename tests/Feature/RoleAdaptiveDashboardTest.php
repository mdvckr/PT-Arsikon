<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Project;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAdaptiveDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'Admin Pengadaan', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pengadaan', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pengadaan Proyek', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pengadaan Proyek', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Logistik Pusat', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Logistik Proyek', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
    }

    public function test_admin_po_sees_procurement_center_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin Pengadaan');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('persona', 'procurement_center');
        $response->assertSee('PO Vendor (Pusat)');
        $response->assertSee('Buku Catatan Proyek');
        $response->assertSee('Pusat Komando Pengadaan Vendor');
    }

    public function test_admin_po_proyek_sees_site_purchasing_dashboard(): void
    {
        $project = Project::firstOrCreate(
            ['name' => 'Proyek UGM Test'],
            ['code' => 'PRJ-UGM-01', 'status' => 'active']
        );

        $warehouse = Warehouse::firstOrCreate(
            ['name' => 'Gudang UGM Test'],
            ['code' => 'GDG-UGM', 'project_id' => $project->id, 'type' => 'project', 'is_active' => true]
        );

        $user = User::factory()->create();
        $user->assignRole('Admin Pengadaan Proyek');
        $user->warehouses()->attach($warehouse->id);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('persona', 'site_purchasing');
        $response->assertSee('Saldo Kas Proyek');
        $response->assertSee('Pencatatan Pembelian Harian');
    }

    public function test_admin_gudang_sees_warehouse_logistics_dashboard(): void
    {
        $warehouse = Warehouse::firstOrCreate(
            ['name' => 'Gudang Pusat Test'],
            ['code' => 'GDG-PST', 'type' => 'central', 'is_central' => true, 'is_active' => true]
        );

        $user = User::factory()->create();
        $user->assignRole('Admin Logistik Pusat');
        $user->warehouses()->attach($warehouse->id);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('persona', 'warehouse');
        $response->assertSee('Jadwal Pengiriman (PO)');
        $response->assertSee('Pusat Komando Logistik');
    }

    public function test_executive_owner_sees_management_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('persona', 'executive');
        $response->assertSee('Total Komitmen Pengadaan');
        $response->assertSee('Matriks Portofolio & Log Harian Proyek');
        $response->assertSee('Status Log Hari Ini');
        $response->assertSee('Log Harian Proyek (Daily Log)');
    }

    public function test_executive_dashboard_shows_daily_log_project_activity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $code = 'PRJ-ASTRA-' . uniqid();
        $project = Project::create([
            'name'                => 'Proyek Menara Astra Test',
            'code'                => $code,
            'location'            => 'Jakarta Pusat',
            'status'              => 'active',
            'operational_balance' => 5000000,
        ]);

        $warehouse = Warehouse::create([
            'project_id' => $project->id,
            'name'       => 'Site Office Menara Astra',
            'code'       => 'W-' . $code,
            'type'       => 'project',
            'is_central' => false,
            'is_active'  => true,
        ]);

        // Create a PurchaseReceipt today
        \App\Models\PurchaseReceipt::create([
            'project_id'     => $project->id,
            'receipt_number' => 'RCP-TEST-' . uniqid(),
            'receipt_date'   => now()->toDateString(),
            'total_amount'   => 1250000,
            'payment_status' => 'paid',
            'payment_source' => 'project_cash',
            'created_by'     => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Proyek Menara Astra Test');
        $response->assertSee($code);
        $response->assertSee('Site Office Menara Astra');
        $response->assertSee('Terisi Hari Ini');
        $response->assertSee('1.250.000');
        $response->assertSee('1 Nota Belanja');
    }
}
