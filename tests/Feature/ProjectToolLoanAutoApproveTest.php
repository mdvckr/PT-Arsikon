<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\ToolLoan;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectToolLoanAutoApproveTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminPusat;
    protected User $adminProyek;
    protected Category $category;
    protected Tool $projectTool;
    protected Tool $centralTool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminPusat       = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyek      = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();

        $this->category = Category::firstOrCreate(
            ['code' => 'CAT-TEST-TOOL'],
            ['name' => 'Kategori Uji Alat', 'type' => 'tool']
        );

        // 1. Alat Milik Gudang Proyek Sendiri
        $this->projectTool = Tool::create([
            'code'                 => 'TOOL-PROJ-01',
            'name'                 => 'Genset Proyek 5000W',
            'brand'                => 'Yamaha',
            'type'                 => 'Genset',
            'category_id'          => $this->category->id,
            'current_warehouse_id' => $this->projectWarehouse->id,
            'owner_warehouse_id'   => $this->projectWarehouse->id, // Milik Proyek!
            'stock_total'          => 3,
            'stock_available'      => 3,
            'stock_borrowed'       => 0,
            'is_active'            => true,
        ]);

        ToolInventory::create([
            'warehouse_id'    => $this->projectWarehouse->id,
            'tool_id'         => $this->projectTool->id,
            'stock_total'     => 3,
            'stock_available' => 3,
            'stock_borrowed'  => 0,
        ]);

        // 2. Alat Milik Gudang Pusat yang berada di Gudang Proyek
        $this->centralTool = Tool::create([
            'code'                 => 'TOOL-CENTRAL-01',
            'name'                 => 'Theodolite Digital Pusat',
            'brand'                => 'Topcon',
            'type'                 => 'Surveying',
            'category_id'          => $this->category->id,
            'current_warehouse_id' => $this->projectWarehouse->id,
            'owner_warehouse_id'   => $this->centralWarehouse->id, // Milik Pusat!
            'stock_total'          => 2,
            'stock_available'      => 2,
            'stock_borrowed'       => 0,
            'is_active'            => true,
        ]);

        ToolInventory::create([
            'warehouse_id'    => $this->projectWarehouse->id,
            'tool_id'         => $this->centralTool->id,
            'stock_total'     => 2,
            'stock_available' => 2,
            'stock_borrowed'  => 0,
        ]);
    }

    /**
     * Skenario 1:
     * Admin Gudang Proyek meminjamkan alat milik proyek sendiri:
     * TIDAK PERLU aproval Admin Pusat, status langsung 'active', stok otomatis berkurang.
     */
    public function test_admin_proyek_borrowing_own_project_tool_does_not_need_central_approval(): void
    {
        $this->assertFalse($this->projectTool->isOwnedByCentral());

        $response = $this->actingAs($this->adminProyek)
            ->post(route('tool-assignments.store'), [
                'warehouse_id'       => $this->projectWarehouse->id,
                'borrower_name'      => 'Mandor Supri',
                'borrower_phone'     => '081299998888',
                'location_name'      => 'Zona Lapangan 1',
                'assigned_at'        => now()->toDateString(),
                'expected_return_at' => now()->addDays(5)->toDateString(),
                'purpose'            => 'Pekerjaan pengecoran malam',
                'quantities'         => [
                    $this->projectTool->id => 1,
                ],
            ]);

        $response->assertRedirect(route('tool-assignments.index'));
        $response->assertSessionHas('success');

        // Pastikan ToolLoan langsung berstatus active (bukan pending)
        $loan = ToolLoan::where('borrower_name', 'Mandor Supri')->first();
        $this->assertNotNull($loan);
        $this->assertEquals('active', $loan->status);
        $this->assertEquals($this->adminProyek->id, $loan->approved_by_user_id);
        $this->assertNotNull($loan->approved_at);

        // Pastikan ToolAssignment item juga langsung berstatus active
        $item = ToolAssignment::where('tool_loan_id', $loan->id)->first();
        $this->assertNotNull($item);
        $this->assertEquals('active', $item->status);
        $this->assertEquals(1, $item->quantity);

        // Pastikan stok inventaris gudang proyek otomatis berkurang (borrowed bertambah)
        $inv = ToolInventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('tool_id', $this->projectTool->id)
            ->first();
        $this->assertEquals(2, $inv->stock_available);
        $this->assertEquals(1, $inv->stock_borrowed);
    }

    /**
     * Skenario 2:
     * Admin Gudang Proyek meminjamkan alat milik Pusat yang ada di proyek:
     * TETAP PERLU persetujuan Admin Pusat (status 'pending').
     */
    public function test_admin_proyek_borrowing_central_owned_tool_still_requires_central_approval(): void
    {
        $this->assertTrue($this->centralTool->isOwnedByCentral());

        $response = $this->actingAs($this->adminProyek)
            ->post(route('tool-assignments.store'), [
                'warehouse_id'       => $this->projectWarehouse->id,
                'borrower_name'      => 'Surveyor Dani',
                'borrower_phone'     => '081277776666',
                'location_name'      => 'Gedung Tower 2',
                'assigned_at'        => now()->toDateString(),
                'expected_return_at' => now()->addDays(3)->toDateString(),
                'purpose'            => 'Pengukuran elevasi',
                'quantities'         => [
                    $this->centralTool->id => 1,
                ],
            ]);

        $response->assertRedirect(route('tool-assignments.index'));

        // Karena alat milik pusat, status harus tetap pending
        $loan = ToolLoan::where('borrower_name', 'Surveyor Dani')->first();
        $this->assertNotNull($loan);
        $this->assertEquals('pending', $loan->status);
        $this->assertNull($loan->approved_by_user_id);

        // Stok belum berkurang selama pending
        $inv = ToolInventory::where('warehouse_id', $this->projectWarehouse->id)
            ->where('tool_id', $this->centralTool->id)
            ->first();
        $this->assertEquals(2, $inv->stock_available);
        $this->assertEquals(0, $inv->stock_borrowed);

        // Admin Pusat kemudian menyetujui pengajuan
        $approveResponse = $this->actingAs($this->adminPusat)
            ->post(route('tool-assignments.approve', $loan->id));

        $approveResponse->assertSessionHas('success');
        $this->assertEquals('active', $loan->fresh()->status);
        $this->assertEquals($this->adminPusat->id, $loan->fresh()->approved_by_user_id);

        // Setelah disetujui pusat, stok berkurang
        $inv->refresh();
        $this->assertEquals(1, $inv->stock_available);
        $this->assertEquals(1, $inv->stock_borrowed);
    }
}
