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
        $this->authorizeDailyLog();

        $data = $this->compileDailyData($request);

        return view('daily-log.index', $data);
    }

    /**
     * Printable version of the Daily Site Log.
     */
    public function print(Request $request)
    {
        $this->authorizeDailyLog();

        $data = $this->compileDailyData($request);

        return view('daily-log.print', $data);
    }

    /**
     * Otorisasi akses Log Harian Proyek untuk Manajemen, Gudang Pusat, dan Tim Teknis Lapangan.
     */
    protected function authorizeDailyLog(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Site Manager', 'Site Engineer', 'Project Manager']) && !$user->can('view daily logs')) || $user->hasAnyRole(['Admin Gudang Pusat', 'Admin Gudang Proyek', 'Admin Logistik Pusat', 'Admin Logistik Proyek'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Log Harian Proyek.');
        }
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

        $warehouses = $user->hasAnyRole(['Owner', 'Admin Pusat'])
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
