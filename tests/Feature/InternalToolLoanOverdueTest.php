<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\ToolLoan;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DailyLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalToolLoanOverdueTest extends TestCase
{
    use RefreshDatabase;

    protected Warehouse $centralWarehouse;
    protected Warehouse $projectWarehouse;
    protected User $adminPusat;
    protected User $adminProyek;
    protected User $karyawan;
    protected Tool $tool;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();
        $this->projectWarehouse = Warehouse::where('is_central', false)->firstOrFail();
        $this->adminPusat       = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $this->adminProyek      = User::where('email', 'admin.proyek1@arsikon.co.id')->firstOrFail();
        $this->karyawan         = User::where('email', 'user.proyek@arsikon.co.id')->firstOrFail();

        $this->category = Category::firstOrCreate(
            ['code' => 'CAT-T17'],
            ['name' => 'Alat T17', 'type' => 'tool']
        );

        $this->tool = Tool::create([
            'code'                 => 'TOOL-T17-01',
            'name'                 => 'Mesin Las Inverter 200A',
            'brand'                => 'Lakoni',
            'type'                 => 'Alat Pengelasan',
            'category_id'          => $this->category->id,
            'current_warehouse_id' => $this->projectWarehouse->id,
            'owner_warehouse_id'   => $this->centralWarehouse->id,
            'stock_total'          => 5,
            'stock_available'      => 5,
            'stock_borrowed'       => 0,
            'is_active'            => true,
        ]);

        ToolInventory::create([
            'warehouse_id'    => $this->projectWarehouse->id,
            'tool_id'         => $this->tool->id,
            'stock_total'     => 5,
            'stock_available' => 5,
            'stock_borrowed'  => 0,
        ]);
    }

    /**
     * Skenario 1:
     * Pemisahan peran: pengaju peminjaman (assigned_by_user_id) TIDAK boleh menyetujui peminjamannya sendiri.
     */
    public function test_applicant_cannot_approve_own_tool_loan(): void
    {
        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Budi',
            'location_name'       => 'Zona A',
            'assigned_at'         => now(),
            'expected_return_at'  => now()->addDays(3)->toDateString(),
            'status'              => 'pending',
        ]);

        ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 1,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Budi',
            'location_name'       => 'Zona A',
            'status'              => 'pending',
        ]);

        // adminProyek mencoba menyetujui pengajuannya sendiri
        $response = $this->actingAs($this->adminProyek)
            ->post(route('tool-assignments.approve', $toolLoan->id));

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $toolLoan->fresh()->status);
    }

    /**
     * Skenario 2:
     * Pemisahan peran: pengaju peminjaman (assigned_by_user_id) TIDAK boleh menolak peminjamannya sendiri.
     */
    public function test_applicant_cannot_reject_own_tool_loan(): void
    {
        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Budi',
            'location_name'       => 'Zona A',
            'assigned_at'         => now(),
            'expected_return_at'  => now()->addDays(3)->toDateString(),
            'status'              => 'pending',
        ]);

        // adminProyek mencoba menolak pengajuannya sendiri
        $response = $this->actingAs($this->adminProyek)
            ->post(route('tool-assignments.reject', $toolLoan->id), [
                'rejection_reason' => 'Batal sendiri',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $toolLoan->fresh()->status);
    }

    /**
     * Skenario 3:
     * User berwenang yang berbeda (misal Admin Pusat) DAPAT menyetujui peminjaman.
     */
    public function test_different_authorized_user_can_approve_tool_loan(): void
    {
        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Budi',
            'location_name'       => 'Zona A',
            'assigned_at'         => now(),
            'expected_return_at'  => now()->addDays(3)->toDateString(),
            'status'              => 'pending',
        ]);

        ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 1,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Budi',
            'location_name'       => 'Zona A',
            'status'              => 'pending',
        ]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('tool-assignments.approve', $toolLoan->id));

        $response->assertSessionHas('success');
        $this->assertEquals('active', $toolLoan->fresh()->status);
        $this->assertEquals($this->adminPusat->id, $toolLoan->fresh()->approved_by_user_id);
    }

    /**
     * Skenario 4:
     * Scheduled command harian tools:check-overdue-loans menandai peminjaman 'active'
     * dengan expected_return_at lewat tanggal menjadi 'overdue', dan mengirim notifikasi satu kali.
     */
    public function test_scheduled_command_marks_loan_overdue_and_notifies_once(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'approved_by_user_id' => $this->adminPusat->id,
            'borrower_name'       => $this->karyawan->name,
            'borrower_phone'      => '08123456789',
            'location_name'       => 'Zona Proyek B',
            'assigned_at'         => now()->subDays(5),
            'expected_return_at'  => $yesterday,
            'status'              => 'active',
            'overdue_notified_at' => null,
        ]);

        $item = ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 2,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'assigned_to_user_id' => $this->karyawan->id,
            'borrower_name'       => $this->karyawan->name,
            'location_name'       => 'Zona Proyek B',
            'status'              => 'active',
        ]);

        // Jalankan artisan command harian
        $this->artisan('tools:check-overdue-loans')
            ->assertExitCode(0);

        $toolLoan->refresh();
        $item->refresh();

        $this->assertEquals('overdue', $toolLoan->status);
        $this->assertEquals('overdue', $item->status);
        $this->assertNotNull($toolLoan->overdue_notified_at);

        $firstNotifiedAt = $toolLoan->overdue_notified_at;

        // Jalankan command untuk kedua kalinya (harus idempotent dan tidak mengubah timestamp notifikasi)
        $this->artisan('tools:check-overdue-loans')
            ->assertExitCode(0);

        $toolLoan->refresh();
        $this->assertEquals($firstNotifiedAt->toDateTimeString(), $toolLoan->overdue_notified_at->toDateTimeString());
    }

    /**
     * Skenario 5:
     * Peminjaman yang berstatus 'overdue' dapat dikembalikan dan keluar dari 'overdue' menjadi 'returned'.
     */
    public function test_overdue_loan_can_be_returned_and_leaves_overdue(): void
    {
        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'approved_by_user_id' => $this->adminPusat->id,
            'borrower_name'       => 'Pekerja Lapangan',
            'location_name'       => 'Site Area',
            'assigned_at'         => now()->subDays(5),
            'expected_return_at'  => now()->subDays(2)->toDateString(),
            'status'              => 'overdue',
            'overdue_notified_at' => now()->subDay(),
        ]);

        $item = ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 1,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Pekerja Lapangan',
            'status'              => 'overdue',
        ]);

        // Simulasikan stok terpinjam 1
        $inv = ToolInventory::where('warehouse_id', $this->projectWarehouse->id)->where('tool_id', $this->tool->id)->first();
        $inv->update(['stock_available' => 4, 'stock_borrowed' => 1]);
        $this->tool->update(['stock_available' => 4, 'stock_borrowed' => 1]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('tool-assignments.return', $toolLoan->id), [
                'returned_at' => now()->toDateString(),
                'returns'     => [
                    $item->id => [
                        'returned_good'    => 1,
                        'returned_damaged' => 0,
                        'returned_lost'    => 0,
                    ],
                ],
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('returned', $toolLoan->fresh()->status);
        $this->assertEquals('returned', $item->fresh()->status);

        // Stok kembali tersedia
        $this->assertEquals(5, $inv->fresh()->stock_available);
        $this->assertEquals(0, $inv->fresh()->stock_borrowed);
    }

    /**
     * Skenario 6:
     * Peminjaman yang berstatus 'overdue' dapat dibatalkan (cancel) dan mengembalikan stok yang dipinjam.
     */
    public function test_overdue_loan_can_be_cancelled_and_restores_stock(): void
    {
        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'approved_by_user_id' => $this->adminPusat->id,
            'borrower_name'       => 'Pekerja Lapangan',
            'location_name'       => 'Site Area',
            'assigned_at'         => now()->subDays(5),
            'expected_return_at'  => now()->subDays(2)->toDateString(),
            'status'              => 'overdue',
            'overdue_notified_at' => now()->subDay(),
        ]);

        $item = ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 2,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Pekerja Lapangan',
            'status'              => 'overdue',
        ]);

        $inv = ToolInventory::where('warehouse_id', $this->projectWarehouse->id)->where('tool_id', $this->tool->id)->first();
        $inv->update(['stock_available' => 3, 'stock_borrowed' => 2]);
        $this->tool->update(['stock_available' => 3, 'stock_borrowed' => 2]);

        $response = $this->actingAs($this->adminPusat)
            ->post(route('tool-assignments.cancel', $toolLoan->id), [
                'cancellation_reason' => 'Salah catat lokasi proyek',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $toolLoan->fresh()->status);
        $this->assertEquals('cancelled', $item->fresh()->status);

        // Stok terpinjam dipulihkan
        $this->assertEquals(5, $inv->fresh()->stock_available);
        $this->assertEquals(0, $inv->fresh()->stock_borrowed);
    }

    /**
     * Skenario 7:
     * Laporan Harian (DailyLogService) tetap menghitung peminjaman berstatus 'overdue'
     * sebagai bagian dari alat aktif / terpakai.
     */
    public function test_daily_log_service_includes_overdue_assignments_in_active_count(): void
    {
        $today = now()->toDateString();

        $toolLoan = ToolLoan::create([
            'loan_number'         => ToolLoan::generateLoanNumber(),
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'approved_by_user_id' => $this->adminPusat->id,
            'borrower_name'       => 'Mandor Lapangan',
            'location_name'       => 'Blok C',
            'assigned_at'         => now()->subDays(2),
            'expected_return_at'  => now()->subDay()->toDateString(),
            'status'              => 'overdue',
        ]);

        ToolAssignment::create([
            'tool_loan_id'        => $toolLoan->id,
            'assignment_number'   => 'TA-' . strtoupper(substr(uniqid(), -6)),
            'tool_id'             => $this->tool->id,
            'quantity'            => 1,
            'from_warehouse_id'   => $this->projectWarehouse->id,
            'assigned_by_user_id' => $this->adminProyek->id,
            'borrower_name'       => 'Mandor Lapangan',
            'assigned_at'         => now()->subDays(2),
            'expected_return_at'  => now()->subDay()->toDateString(),
            'status'              => 'overdue',
        ]);

        $dailyLogService = app(DailyLogService::class);
        $data = $dailyLogService->compile(request(), $this->projectWarehouse, $today);

        $this->assertGreaterThanOrEqual(1, $data['totalToolsActive']);
    }
}
