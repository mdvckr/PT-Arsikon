<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\StockMutation;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\Distribution;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeWarehouse = $user->activeWarehouse();
        $warehouseId = $activeWarehouse?->id ?? session('active_warehouse_id');
        $accessibleIds = $user->accessibleWarehouseIds();

        // KPI Cards — difilter berdasarkan warehouse yang dapat diakses user
        $totalMaterials = Material::whereHas('inventories', function ($q) use ($accessibleIds) {
                $q->whereIn('warehouse_id', $accessibleIds);
            })->count();

        $totalTools = Tool::whereHas('inventories', function ($q) use ($accessibleIds) {
                $q->whereIn('warehouse_id', $accessibleIds);
            })->count();

        $pendingRequests = MaterialRequest::where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->where('status', 'pending')
            ->count();

        $lowStockItems = Inventory::where('warehouse_id', $warehouseId)
                            ->where('quantity', '<=', DB::raw('min_stock'))
                            ->where('min_stock', '>', 0)
                            ->count();

        // Recent Material Requests — filtered by accessible warehouses
        $recentRequests = MaterialRequest::with(['requestedBy', 'toWarehouse', 'fromWarehouse'])
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        // Recent Distributions (Surat Jalan)
        $recentDistributions = Distribution::with(['fromWarehouse', 'toWarehouse'])
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        // Stock Mutations (last 7 days) — filtered by active warehouse
        $mutations = StockMutation::where('created_at', '>=', now()->subDays(7))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('DATE(created_at) as date, reference_type as type, SUM(qty_change) as total')
            ->groupBy('date', 'reference_type')
            ->orderBy('date')
            ->get();

        // Tool Status Summary
        $toolsReady = (int) Tool::sum('stock_available');
        $toolsInUse = ToolAssignment::where('status', 'active')
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->count();
        $toolsMaintenance = (int) Tool::sum('stock_maintenance');

        $toolStats = [
            'ready'       => $toolsReady,
            'active'      => $toolsInUse,
            'maintenance' => $toolsMaintenance,
        ];

        // Stock Opname status — filtered by active warehouse
        $openOpname = StockOpname::where('status', 'open')
            ->where('warehouse_id', $warehouseId)
            ->count();

        // Inventory value by category — filtered by active warehouse
        $inventoryByCategory = Inventory::with(['material.category'])
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->groupBy(fn($i) => $i->material?->category?->name ?? 'Lainnya')
            ->map(fn($items) => $items->sum('quantity'));

        return view('dashboard', compact(
            'totalMaterials', 'totalTools', 'pendingRequests', 'lowStockItems',
            'recentRequests', 'recentDistributions', 'mutations', 'toolStats',
            'toolsReady', 'toolsInUse', 'toolsMaintenance',
            'openOpname', 'inventoryByCategory'
        ));
    }
}
