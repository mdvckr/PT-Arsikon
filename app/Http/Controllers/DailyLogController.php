<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\DailyLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyLogController extends Controller
{
    public function __construct(private DailyLogService $dailyLogService) {}

    /**
     * Display Daily Site Material & Activity Summary.
     */
    public function index(Request $request)
    {
        $this->authorize('view reports');

        if (auth()->user()->hasRole('Karyawan')) {
            abort(403, 'Akses ditolak.');
        }

        $data = $this->compileDailyData($request);

        return view('daily-log.index', $data);
    }

    /**
     * Printable version of the Daily Site Log.
     */
    public function print(Request $request)
    {
        $this->authorize('view reports');

        if (auth()->user()->hasRole('Karyawan')) {
            abort(403, 'Akses ditolak.');
        }

        $data = $this->compileDailyData($request);

        return view('daily-log.print', $data);
    }

    /**
     * Compile incoming, usage, tools, and stock balance for a specific warehouse and date.
     * Gudang Pusat & Proyek terpisah — semua agregasi filter by warehouse_id.
     * Semua mutasi stok (surat jalan, pemakaian, peminjaman, goods receipt, retur, opname) otomatis masuk via StockService/ToolInventoryService.
     */
    protected function compileDailyData(Request $request): array
    {
        $user = auth()->user();
        $dateStr = $request->input('date', date('Y-m-d'));
        $date = Carbon::parse($dateStr)->toDateString();

        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat'])
            ? Warehouse::orderBy('name')->get()
            : $user->warehouses;

        $activeWarehouseId = $request->warehouse_id ?? session('active_warehouse_id') ?? $user->activeWarehouse()?->id;
        $selectedWarehouse = Warehouse::find($activeWarehouseId) ?? $warehouses->first();

        if ($selectedWarehouse && !$user->hasAccessToWarehouse($selectedWarehouse)) {
            $selectedWarehouse = $warehouses->first();
        }

        if (!$selectedWarehouse) {
            abort(403, 'Tidak ada gudang/workspace yang dapat diakses.');
        }

        // Delegate agregasi ke DailyLogService (StockService ledger terintegrasi untuk semua mutasi: surat jalan, pemakaian, peminjaman, GR, retur, opname)
        $compiled = $this->dailyLogService->compile($request, $selectedWarehouse, $date);

        return array_merge(compact('warehouses', 'selectedWarehouse', 'date'), $compiled);
    }
}
