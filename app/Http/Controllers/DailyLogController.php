<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\StockMutation;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyLogController extends Controller
{
    /**
     * Display Daily Site Material & Activity Summary.
     */
    public function index(Request $request)
    {
        $this->authorize('view reports');

        $data = $this->compileDailyData($request);

        return view('daily-log.index', $data);
    }

    /**
     * Printable version of the Daily Site Log.
     */
    public function print(Request $request)
    {
        $this->authorize('view reports');

        $data = $this->compileDailyData($request);

        return view('daily-log.print', $data);
    }

    /**
     * Compile incoming, usage, tools, and stock balance for a specific warehouse and date.
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

        // 1. Material Masuk Hari Ini (Incoming)
        // A. Dari Distribusi (Surat Jalan yang statusnya 'received' pada tanggal ini ke gudang tujuan)
        $incomingDistributions = Distribution::with(['fromWarehouse', 'items.material.unit', 'items.tool'])
            ->where('to_warehouse_id', $selectedWarehouse->id)
            ->where(function ($q) use ($date) {
                $q->whereDate('received_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->where('status', 'received')
                         ->whereDate('updated_at', $date);
                  });
            })
            ->get();

        // B. Dari Goods Receipt (Penerimaan PO Supplier pada tanggal ini)
        $incomingGoodsReceipts = GoodsReceipt::with(['supplier', 'items.material.unit'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('receipt_date', $date)
            ->get();

        // 2. Pemakaian Material Lapangan Hari Ini (Usage / Outgoing)
        $usages = MaterialUsage::with(['issuedBy', 'items.material.unit'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('usage_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        // 3. Status Alat Kerja Hari Ini (Tools Activity)
        // Alat yang dipinjam pada tanggal ini, dikembalikan pada tanggal ini, atau sedang aktif beroperasi pada tanggal ini
        $toolAssignments = ToolAssignment::with(['tool.category', 'assignedBy'])
            ->where('from_warehouse_id', $selectedWarehouse->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($date) {
                $q->whereDate('assigned_at', $date)
                  ->orWhereDate('returned_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->whereDate('assigned_at', '<=', $date)
                         ->where(function ($q3) use ($date) {
                             $q3->whereNull('returned_at')
                                ->orWhereDate('returned_at', '>=', $date);
                         })
                         ->whereIn('status', ['active', 'returned', 'overdue']);
                  });
            })
            ->latest('assigned_at')
            ->get();

        // 4. Neraca Pergerakan Stok Material Hari Ini (Stock Balance)
        // Usages strictly after $date (untuk rekonstruksi sisa stok masa lalu/masa depan)
        $usagesAfterDate = MaterialUsage::with('items')
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('usage_date', '>', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        // Incoming strictly after $date
        $distInAfterDate = Distribution::with('items')
            ->where('to_warehouse_id', $selectedWarehouse->id)
            ->where(function($q) use ($date) {
                $q->whereDate('received_at', '>', $date)
                  ->orWhere(fn($q2) => $q2->where('status', 'received')->whereDate('updated_at', '>', $date));
            })->get();

        $grInAfterDate = GoodsReceipt::with('items')
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('receipt_date', '>', $date)
            ->get();

        $inventories = Inventory::with('material.unit')
            ->where('warehouse_id', $selectedWarehouse->id)
            ->get();

        $stockBalance = $inventories->map(function ($inv) use ($usages, $usagesAfterDate, $incomingDistributions, $incomingGoodsReceipts, $distInAfterDate, $grInAfterDate) {
            $matId = $inv->material_id;

            // Qty Out on date
            $qtyOut = 0;
            foreach ($usages as $u) {
                $qtyOut += (float) $u->items->where('material_id', $matId)->sum('quantity');
            }

            // Qty In on date
            $qtyIn = 0;
            foreach ($incomingDistributions as $d) {
                $qtyIn += (float) $d->items->where('material_id', $matId)->sum('quantity');
            }
            foreach ($incomingGoodsReceipts as $g) {
                $qtyIn += (float) $g->items->where('material_id', $matId)->sum('quantity_received');
            }

            // Qty Out after date
            $qtyOutAfter = 0;
            foreach ($usagesAfterDate as $u) {
                $qtyOutAfter += (float) $u->items->where('material_id', $matId)->sum('quantity');
            }

            // Qty In after date
            $qtyInAfter = 0;
            foreach ($distInAfterDate as $d) {
                $qtyInAfter += (float) $d->items->where('material_id', $matId)->sum('quantity');
            }
            foreach ($grInAfterDate as $g) {
                $qtyInAfter += (float) $g->items->where('material_id', $matId)->sum('quantity_received');
            }

            $currentStock = (float) $inv->quantity;
            $closingStock = $currentStock + $qtyOutAfter - $qtyInAfter;
            $openingStock = $closingStock - $qtyIn + $qtyOut;

            return (object) [
                'material_id'   => $matId,
                'material_name' => $inv->material?->name ?? '-',
                'material_code' => $inv->material?->sku ?? $inv->material?->code ?? '-',
                'unit'          => $inv->material?->unit?->code ?? $inv->material?->unit?->name ?? 'Unit',
                'opening_stock' => $openingStock,
                'qty_in'        => $qtyIn,
                'qty_out'       => $qtyOut,
                'closing_stock' => $closingStock,
                'has_activity'  => ($qtyIn > 0 || $qtyOut > 0),
            ];
        })->sortByDesc('has_activity')->values();

        // 5. Neraca Posisi & Kesiapan Alat Kerja (Tool Availability & Condition Balance)
        $toolInventories = \App\Models\ToolInventory::with(['tool.category'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->get();

        $inventoryToolIds = $toolInventories->pluck('tool_id')->all();

        $otherTools = \App\Models\Tool::with('category')
            ->whereNotIn('id', $inventoryToolIds)
            ->where(function ($q) use ($selectedWarehouse) {
                $q->where('current_warehouse_id', $selectedWarehouse->id)
                  ->orWhereHas('assignments', fn($qa) => $qa->where('from_warehouse_id', $selectedWarehouse->id));
            })
            ->get();

        $allToolEntities = collect();
        foreach ($toolInventories as $ti) {
            if ($ti->tool) {
                $allToolEntities->push((object)[
                    'tool'        => $ti->tool,
                    'inventory'   => $ti,
                    'stock_total' => (int) $ti->stock_total,
                    'stock_avail' => (int) $ti->stock_available,
                    'stock_borr'  => (int) $ti->stock_borrowed,
                    'stock_maint' => (int) ($ti->stock_maintenance + $ti->stock_damaged),
                ]);
            }
        }
        foreach ($otherTools as $ot) {
            $allToolEntities->push((object)[
                'tool'        => $ot,
                'inventory'   => null,
                'stock_total' => (int) $ot->stock_total,
                'stock_avail' => (int) $ot->stock_available,
                'stock_borr'  => 0,
                'stock_maint' => (int) ($ot->stock_maintenance + $ot->stock_damaged),
            ]);
        }

        $toolBalance = $allToolEntities->map(function ($item) use ($toolAssignments, $date) {
            $tool = $item->tool;
            // Tool active on $date
            $toolActiveAssignments = $toolAssignments->where('tool_id', $tool->id)
                ->filter(function ($ta) use ($date) {
                    $assignDate = Carbon::parse($ta->assigned_at)->toDateString();
                    if ($assignDate > $date) {
                        return false;
                    }
                    if ($ta->returned_at && Carbon::parse($ta->returned_at)->toDateString() < $date) {
                        return false;
                    }
                    return in_array($ta->status, ['active', 'returned', 'overdue']);
                });

            // Collect mandor / borrowers who are currently holding this tool on $date
            $borrowers = $toolActiveAssignments->map(function ($ta) {
                $name = $ta->borrower_display ?: 'Pekerja Lapangan';
                $qty = (int) ($ta->quantity ?? 1);
                $loc = $ta->location_display ?: '';
                return $name . ' (' . $qty . ' Unit' . ($loc ? ' — ' . $loc : '') . ')';
            })->values()->all();

            $activeQtyFromAssignments = (int) $toolActiveAssignments->sum(fn($ta) => $ta->quantity ?? 1);
            $borrowedCount = $activeQtyFromAssignments;
            $totalCount = max($item->stock_total, $borrowedCount);
            $maintCount = $item->stock_maint;
            $availableCount = max(0, $totalCount - $borrowedCount - $maintCount);

            $borrowedToday = (int) $toolAssignments->where('tool_id', $tool->id)
                ->filter(fn($ta) => Carbon::parse($ta->assigned_at)->toDateString() === $date)
                ->sum(fn($ta) => $ta->quantity ?? 1);

            $returnedToday = (int) $toolAssignments->where('tool_id', $tool->id)
                ->filter(fn($ta) => $ta->returned_at && Carbon::parse($ta->returned_at)->toDateString() === $date)
                ->sum(fn($ta) => $ta->quantity ?? 1);

            return (object) [
                'tool_id'           => $tool->id,
                'tool_name'         => $tool->name,
                'tool_code'         => $tool->code ?? '-',
                'brand'             => $tool->brand ?? '-',
                'category_name'     => $tool->category?->name ?? 'Alat Kerja',
                'stock_total'       => $totalCount,
                'stock_available'   => $availableCount,
                'stock_borrowed'    => $borrowedCount,
                'stock_maintenance' => $maintCount,
                'borrowers'         => $borrowers,
                'borrowed_today'    => $borrowedToday,
                'returned_today'    => $returnedToday,
                'has_activity'      => ($borrowedCount > 0 || $borrowedToday > 0 || $returnedToday > 0),
            ];
        })->sortByDesc('has_activity')->values();

        // 6. KPI Ringkasan Cepat
        $totalItemsIn = 0;
        foreach ($incomingDistributions as $dist) {
            $totalItemsIn += $dist->items->where('type', 'material')->count();
        }
        foreach ($incomingGoodsReceipts as $gr) {
            $totalItemsIn += $gr->items->count();
        }

        $totalItemsOut = $usages->sum(fn($u) => $u->items->count());
        $totalToolsActive = $toolAssignments->where('status', 'active')->count();
        $totalRecipients = $usages->pluck('recipient_name')->unique()->count();

        $totalToolsReady = $toolBalance->sum('stock_available');
        $totalToolsInUse = $toolBalance->sum('stock_borrowed');
        $totalToolsDamaged = $toolBalance->sum('stock_maintenance');

        return compact(
            'warehouses',
            'selectedWarehouse',
            'date',
            'incomingDistributions',
            'incomingGoodsReceipts',
            'usages',
            'toolAssignments',
            'stockBalance',
            'toolBalance',
            'totalItemsIn',
            'totalItemsOut',
            'totalToolsActive',
            'totalRecipients',
            'totalToolsReady',
            'totalToolsInUse',
            'totalToolsDamaged'
        );
    }
}
