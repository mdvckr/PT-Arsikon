<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\MaterialReturn;
use App\Models\MaterialUsage;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * DailyLogService — Single source of truth untuk integrasi stok → Log Harian.
 *
 * KONTRAK INTEGRASI (point 10):
 * - Semua fitur yang mengubah stok WAJIB lewat StockService (addStock/deductStock) atau ToolInventoryService (borrow/returnStock).
 *   StockService otomatis menulis StockMutation dengan reference_type & qty_balance_after.
 * - DailyLog mengagregasi dari model transaksi (Distribution, GoodsReceipt, MaterialUsage, MaterialReturn, StockOpname) + StockMutation.
 *   Custom items (DistributionItem/MaterialUsageItem tanpa material_id) sengaja TIDAK menambah stok — skip agar tidak bingung.
 * - Gudang Pusat & Proyek terpisah: agregasi selalu filter by warehouse_id, tidak ada sum lintas gudang.
 * - Neraca menampilkan hanya material yang dipakai (qty_in>0 || qty_out>0) dan alat yang dipinjam (has_activity), grouped by kategori.
 */
class DailyLogService
{
    /**
     * Compile semua data untuk Log Harian satu gudang + tanggal.
     * Dipindahkan dari DailyLogController agar dapat di-reuse (print, API, export).
     */
    public function compile(Request $request, Warehouse $selectedWarehouse, string $date): array
    {
        // 1. Incoming
        $incomingDistributions = Distribution::with(['fromWarehouse', 'items.material.unit', 'items.tool'])
            ->where('to_warehouse_id', $selectedWarehouse->id)
            ->where(function ($q) use ($date) {
                $q->whereDate('received_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->where('status', 'received')->whereDate('updated_at', $date);
                  });
            })->get();

        $incomingGoodsReceipts = GoodsReceipt::with(['supplier', 'items.material.unit'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('receipt_date', $date)
            ->get();

        $materialReturns = MaterialReturn::with(['fromWarehouse', 'items.material.unit'])
            ->where('to_warehouse_id', $selectedWarehouse->id)
            ->where('status', 'received')
            ->whereDate('received_at', $date)
            ->get();

        // Stock Opname approvals hari ini (penyesuaian stok)
        $opnameAdjustments = StockOpname::with(['items.material.unit', 'items.material.category'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->where('status', 'approved')
            ->whereDate('updated_at', $date)
            ->get();

        // 2. Outgoing
        $usages = MaterialUsage::with(['issuedBy', 'items.material.unit'])
            ->where('warehouse_id', $selectedWarehouse->id)
            ->whereDate('usage_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $outgoingDistributions = Distribution::with(['toWarehouse', 'items.material.unit'])
            ->where('from_warehouse_id', $selectedWarehouse->id)
            ->where(function ($q) use ($date) {
                $q->whereDate('shipped_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->where('status', 'received')->whereDate('updated_at', $date);
                  });
            })->get();

        // 3. Tools
        $toolAssignments = ToolAssignment::with(['tool.category', 'assignedBy'])
            ->where('from_warehouse_id', $selectedWarehouse->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($date) {
                $q->whereDate('assigned_at', $date)
                  ->orWhereDate('returned_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->whereDate('assigned_at', '<=', $date)
                         ->where(function ($q3) use ($date) {
                             $q3->whereNull('returned_at')->orWhereDate('returned_at', '>=', $date);
                         })->whereIn('status', ['active', 'returned', 'overdue']);
                  });
            })->latest('assigned_at')->get();

        // 4. Rekonstruksi Neraca (include opname)
        $usagesAfterDate = MaterialUsage::with('items')->where('warehouse_id', $selectedWarehouse->id)->whereDate('usage_date', '>', $date)->where('status', '!=', 'cancelled')->get();
        $distInAfterDate = Distribution::with('items')->where('to_warehouse_id', $selectedWarehouse->id)->where(function($q) use ($date) { $q->whereDate('received_at', '>', $date)->orWhere(fn($q2) => $q2->where('status', 'received')->whereDate('updated_at', '>', $date)); })->get();
        $grInAfterDate = GoodsReceipt::with('items')->where('warehouse_id', $selectedWarehouse->id)->whereDate('receipt_date', '>', $date)->get();
        $outgoingAfterDate = Distribution::with('items')->where('from_warehouse_id', $selectedWarehouse->id)->where(function($q) use ($date) { $q->whereDate('shipped_at', '>', $date)->orWhere(fn($q2) => $q2->where('status', 'received')->whereDate('updated_at', '>', $date)); })->get();
        $returnsAfterDate = MaterialReturn::with('items')->where('to_warehouse_id', $selectedWarehouse->id)->where('status', 'received')->whereDate('received_at', '>', $date)->get();
        $opnameAfterDate = StockOpname::with('items')->where('warehouse_id', $selectedWarehouse->id)->where('status', 'approved')->whereDate('updated_at', '>', $date)->get();

        $inventories = Inventory::with('material.unit', 'material.category')->where('warehouse_id', $selectedWarehouse->id)->get();

        $sumQtyByMaterial = function ($transactions, string $valueKey) {
            return collect($transactions)->flatMap(fn($t) => $t->items ?? collect())->filter(fn($i) => isset($i->material_id) && $i->material_id !== null)->groupBy('material_id')->map(fn($group) => (float) $group->sum($valueKey));
        };
        // Opname qty_difference positif = masuk, negatif = keluar — pisahkan
        $sumOpnameIn = collect($opnameAdjustments)->flatMap(fn($o) => $o->items ?? collect())->filter(fn($i) => $i->qty_difference > 0)->groupBy('material_id')->map(fn($g) => (float) $g->sum('qty_difference'));
        $sumOpnameOut = collect($opnameAdjustments)->flatMap(fn($o) => $o->items ?? collect())->filter(fn($i) => $i->qty_difference < 0)->groupBy('material_id')->map(fn($g) => (float) abs($g->sum('qty_difference')));
        $sumOpnameInAfter = collect($opnameAfterDate)->flatMap(fn($o) => $o->items ?? collect())->filter(fn($i) => $i->qty_difference > 0)->groupBy('material_id')->map(fn($g) => (float) $g->sum('qty_difference'));
        $sumOpnameOutAfter = collect($opnameAfterDate)->flatMap(fn($o) => $o->items ?? collect())->filter(fn($i) => $i->qty_difference < 0)->groupBy('material_id')->map(fn($g) => (float) abs($g->sum('qty_difference')));

        $qtyOutMat = $sumQtyByMaterial($usages, 'quantity');
        $qtyOutDistMat = $sumQtyByMaterial($outgoingDistributions, 'qty_shipped');
        $qtyInDistMat = $sumQtyByMaterial($incomingDistributions, 'qty_received');
        $qtyInGrMat = $sumQtyByMaterial($incomingGoodsReceipts, 'quantity_received');
        $qtyInReturnMat = $sumQtyByMaterial($materialReturns, 'received_qty');
        $qtyOutAfterMat = $sumQtyByMaterial($usagesAfterDate, 'quantity');
        $qtyOutDistAfterMat = $sumQtyByMaterial($outgoingAfterDate, 'qty_shipped');
        $qtyInDistAfterMat = $sumQtyByMaterial($distInAfterDate, 'qty_received');
        $qtyInGrAfterMat = $sumQtyByMaterial($grInAfterDate, 'quantity_received');
        $qtyInReturnAfterMat = $sumQtyByMaterial($returnsAfterDate, 'received_qty');

        $stockBalance = $inventories->map(function ($inv) use ($qtyOutMat,$qtyOutDistMat,$qtyInDistMat,$qtyInGrMat,$qtyInReturnMat,$qtyOutAfterMat,$qtyOutDistAfterMat,$qtyInDistAfterMat,$qtyInGrAfterMat,$qtyInReturnAfterMat,$sumOpnameIn,$sumOpnameOut,$sumOpnameInAfter,$sumOpnameOutAfter) {
            $matId = $inv->material_id;
            $qtyOut = ($qtyOutMat[$matId] ?? 0) + ($qtyOutDistMat[$matId] ?? 0) + ($sumOpnameOut[$matId] ?? 0);
            $qtyIn  = ($qtyInDistMat[$matId] ?? 0) + ($qtyInGrMat[$matId] ?? 0) + ($qtyInReturnMat[$matId] ?? 0) + ($sumOpnameIn[$matId] ?? 0);
            $qtyOutAfter = ($qtyOutAfterMat[$matId] ?? 0) + ($qtyOutDistAfterMat[$matId] ?? 0) + ($sumOpnameOutAfter[$matId] ?? 0);
            $qtyInAfter  = ($qtyInDistAfterMat[$matId] ?? 0) + ($qtyInGrAfterMat[$matId] ?? 0) + ($qtyInReturnAfterMat[$matId] ?? 0) + ($sumOpnameInAfter[$matId] ?? 0);
            $currentStock = (float) $inv->quantity;
            $closingStock = $currentStock + $qtyOutAfter - $qtyInAfter;
            $openingStock = $closingStock - $qtyIn + $qtyOut;
            return (object) [
                'material_id'   => $matId,
                'material_name' => $inv->material?->name ?? '-',
                'material_code' => $inv->material?->sku ?? $inv->material?->code ?? '-',
                'category_name' => $inv->material?->category?->name ?? 'Umum',
                'unit'          => $inv->material?->unit?->code ?? $inv->material?->unit?->name ?? 'Unit',
                'opening_stock' => $openingStock,
                'qty_in'        => $qtyIn,
                'qty_out'       => $qtyOut,
                'closing_stock' => $closingStock,
                'has_activity'  => ($qtyIn > 0 || $qtyOut > 0),
            ];
        })->filter(fn($s) => $s->has_activity)->sortBy([['category_name','asc'],['material_name','asc']])->values();

        $stockBalanceGrouped = $stockBalance->groupBy('category_name')->sortKeys();

        // 5. Tool balance (same as controller)
        $toolInventories = \App\Models\ToolInventory::with(['tool.category'])->where('warehouse_id', $selectedWarehouse->id)->get();
        $inventoryToolIds = $toolInventories->pluck('tool_id')->all();
        $otherTools = \App\Models\Tool::with('category')->whereNotIn('id', $inventoryToolIds)->where(function ($q) use ($selectedWarehouse) {
            $q->where('current_warehouse_id', $selectedWarehouse->id)->orWhereHas('assignments', fn($qa) => $qa->where('from_warehouse_id', $selectedWarehouse->id));
        })->get();
        $allToolEntities = collect();
        foreach ($toolInventories as $ti) { if ($ti->tool) $allToolEntities->push((object)['tool'=>$ti->tool,'inventory'=>$ti,'stock_total'=>(int)$ti->stock_total,'stock_avail'=>(int)$ti->stock_available,'stock_borr'=>(int)$ti->stock_borrowed,'stock_maint'=>(int)($ti->stock_maintenance+$ti->stock_damaged)]); }
        foreach ($otherTools as $ot) { $allToolEntities->push((object)['tool'=>$ot,'inventory'=>null,'stock_total'=>(int)$ot->stock_total,'stock_avail'=>(int)$ot->stock_available,'stock_borr'=>0,'stock_maint'=>(int)($ot->stock_maintenance+$ot->stock_damaged)]); }
        $toolBalance = $allToolEntities->map(function ($item) use ($toolAssignments, $date) {
            $tool = $item->tool;
            $toolActiveAssignments = $toolAssignments->where('tool_id', $tool->id)->filter(function ($ta) use ($date) {
                $assignDate = Carbon::parse($ta->assigned_at)->toDateString();
                if ($assignDate > $date) return false;
                if ($ta->returned_at && Carbon::parse($ta->returned_at)->toDateString() < $date) return false;
                return in_array($ta->status, ['active','returned','overdue']);
            });
            $borrowers = $toolActiveAssignments->map(fn($ta) => ($ta->borrower_display ?: 'Pekerja Lapangan').' ('.(int)($ta->quantity ?? 1).' Unit'.($ta->location_display ? ' — '.$ta->location_display : '').')')->values()->all();
            $activeQty = (int) $toolActiveAssignments->sum(fn($ta) => $ta->quantity ?? 1);
            $borrowedCount = $activeQty;
            $totalCount = max($item->stock_total, $borrowedCount);
            $maintCount = $item->stock_maint;
            $availableCount = max(0, $totalCount - $borrowedCount - $maintCount);
            $borrowedToday = (int) $toolAssignments->where('tool_id', $tool->id)->filter(fn($ta) => Carbon::parse($ta->assigned_at)->toDateString() === $date)->sum(fn($ta) => $ta->quantity ?? 1);
            $returnedToday = (int) $toolAssignments->where('tool_id', $tool->id)->filter(fn($ta) => $ta->returned_at && Carbon::parse($ta->returned_at)->toDateString() === $date)->sum(fn($ta) => $ta->quantity ?? 1);
            return (object)[
                'tool_id'=>$tool->id,'tool_name'=>$tool->name,'tool_code'=>$tool->code ?? '-','brand'=>$tool->brand ?? '-','category_name'=>$tool->category?->name ?? 'Alat Kerja',
                'stock_total'=>$totalCount,'stock_available'=>$availableCount,'stock_borrowed'=>$borrowedCount,'stock_maintenance'=>$maintCount,'borrowers'=>$borrowers,'borrowed_today'=>$borrowedToday,'returned_today'=>$returnedToday,'has_activity'=>($borrowedCount>0 || $borrowedToday>0 || $returnedToday>0),
            ];
        })->filter(fn($t) => $t->has_activity)->sortBy([['category_name','asc'],['tool_name','asc']])->values();
        $toolBalanceGrouped = $toolBalance->groupBy('category_name')->sortKeys();

        // 6. KPIs
        $totalItemsIn = 0;
        foreach ($incomingDistributions as $dist) $totalItemsIn += $dist->items->where('type','material')->count();
        foreach ($incomingGoodsReceipts as $gr) $totalItemsIn += $gr->items->count();
        foreach ($materialReturns as $r) $totalItemsIn += $r->items->count();
        $totalItemsOut = $usages->sum(fn($u) => $u->items->count()) + $outgoingDistributions->sum(fn($d) => $d->items->count());
        $totalToolsActive = $toolAssignments->where('status','active')->count();
        $totalRecipients = $usages->pluck('recipient_name')->unique()->count();
        $totalToolsReady = $toolBalance->sum('stock_available');
        $totalToolsInUse = $toolBalance->sum('stock_borrowed');
        $totalToolsDamaged = $toolBalance->sum('stock_maintenance');

        return compact('incomingDistributions','incomingGoodsReceipts','materialReturns','opnameAdjustments','outgoingDistributions','usages','toolAssignments','stockBalance','stockBalanceGrouped','toolBalance','toolBalanceGrouped','totalItemsIn','totalItemsOut','totalToolsActive','totalRecipients','totalToolsReady','totalToolsInUse','totalToolsDamaged');
    }
}
