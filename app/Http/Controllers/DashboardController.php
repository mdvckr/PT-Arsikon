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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $warehouseId = session('active_warehouse_id');

        // KPI Cards
        $totalMaterials   = Material::count();
        $totalTools       = Tool::count();
        $pendingRequests  = MaterialRequest::where('status', 'pending')->count();
        $lowStockItems    = Inventory::where('warehouse_id', $warehouseId)
                                ->where('quantity', '<=', DB::raw('min_stock'))
                                ->count();

        // Recent Material Requests
        $recentRequests = MaterialRequest::with(['requestedBy', 'toWarehouse', 'fromWarehouse'])
            ->latest()
            ->limit(5)
            ->get();

        // Stock Mutations (last 7 days)
        $mutations = StockMutation::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, reference_type as type, SUM(qty_change) as total')
            ->groupBy('date', 'reference_type')
            ->orderBy('date')
            ->get();

        // Tool Status Summary
        $toolStats = ToolAssignment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Stock Opname status
        $openOpname = StockOpname::where('status', 'open')
            ->where('warehouse_id', $warehouseId)
            ->count();

        // Inventory value by category
        $inventoryByCategory = Inventory::with(['material.category'])
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->groupBy(fn($i) => $i->material?->category?->name ?? 'Lainnya')
            ->map(fn($items) => $items->sum('quantity'));

        return view('dashboard', compact(
            'totalMaterials', 'totalTools', 'pendingRequests', 'lowStockItems',
            'recentRequests', 'mutations', 'toolStats',
            'openOpname', 'inventoryByCategory'
        ));
    }
}
