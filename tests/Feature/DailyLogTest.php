<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\MaterialUsageItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $siteEngineer;
    protected User $logistikProyek;
    protected User $adminPusat;
    protected Warehouse $projectWarehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->siteEngineer = User::where('email', 'engineer.prj1@arsikon.co.id')->firstOrFail();
        $this->logistikProyek = User::where('email', 'logistik.proyek@arsikon.co.id')->firstOrFail();
        $this->adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
    }

    public function test_site_engineer_can_view_daily_log(): void
    {
        $response = $this->actingAs($this->siteEngineer)
            ->get(route('daily-log.index', [
                'warehouse_id' => $this->projectWarehouse->id,
                'date' => date('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSeeText('Log Harian Proyek');
        $response->assertSeeText('Pemakaian Material Lapangan Hari Ini');
        $response->assertSeeText('Neraca Sisa Stok Material Hari Ini');
        $response->assertSeeText('Neraca Posisi');
        $response->assertSeeText('Tool Availability');
        $response->assertDontSeeText('Penyesuaian Fisik Stock Opname');
        $response->assertDontSeeText('No. Opname');
    }

    public function test_logistik_proyek_can_view_daily_log(): void
    {
        $response = $this->actingAs($this->logistikProyek)
            ->get(route('daily-log.index', [
                'warehouse_id' => $this->projectWarehouse->id,
                'date' => date('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSeeText('Log Harian Proyek');
        $response->assertSeeText('Pemakaian Material Lapangan Hari Ini');
        $response->assertSeeText('Neraca Sisa Stok Material Hari Ini');
    }

    public function test_user_can_print_daily_log(): void
    {
        // Seed a sample usage on today
        $cat = Category::first() ?? Category::create(['name' => 'Struktur', 'code' => 'STR']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Sak', 'code' => 'SAK', 'symbol' => 'sak']);

        $material = Material::firstOrCreate(
            ['sku' => 'MAT-SEM-001'],
            ['name' => 'Semen Portland 50kg', 'category_id' => $cat->id, 'unit_id' => $unit->id]
        );

        $usage = MaterialUsage::create([
            'usage_number'      => 'USG-TODAY-01',
            'warehouse_id'      => $this->projectWarehouse->id,
            'project_id'        => $this->projectWarehouse->project_id,
            'issued_by_user_id' => $this->siteEngineer->id,
            'recipient_name'    => 'Mandor Bambang Daily',
            'job_section'       => 'Pengecoran Kolom Lt. 3',
            'usage_date'        => date('Y-m-d'),
            'status'            => 'completed',
        ]);

        MaterialUsageItem::create([
            'material_usage_id' => $usage->id,
            'material_id'       => $material->id,
            'quantity'          => 25,
            'notes'             => 'Pengecoran sore',
        ]);

        $response = $this->actingAs($this->siteEngineer)
            ->get(route('daily-log.print', [
                'warehouse_id' => $this->projectWarehouse->id,
                'date' => date('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSeeText('LAPORAN HARIAN LOGISTIK DAN MATERIAL PROYEK');
        $response->assertSeeText('Mandor Bambang Daily');
        $response->assertSeeText('Pengecoran Kolom Lt. 3');
        $response->assertSeeText('USG-TODAY-01');
        $response->assertSeeText('Neraca Posisi');
        $response->assertSeeText('Tool Availability');
        $response->assertDontSeeText('Penyesuaian Fisik Stock Opname');
        $response->assertDontSeeText('No. Opname');
    }

    public function test_admin_pusat_can_view_daily_log(): void
    {
        $response = $this->actingAs($this->adminPusat)
            ->get(route('daily-log.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Log Harian Proyek');
    }
}
