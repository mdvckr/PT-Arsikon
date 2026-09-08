<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Material;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $warehouseId = session('active_warehouse_id');

        $query = Inventory::with(['material.category', 'material.unit', 'warehouse'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

        if ($request->search) {
            $query->whereHas('material', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"));
        }

        if ($request->category_id) {
            $query->whereHas('material', fn($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->stock_level === 'low') {
            $query->whereColumn('quantity', '<=', 'min_stock');
        } elseif ($request->stock_level === 'out') {
            $query->where('quantity', 0);
        }

        $inventories = $query->orderBy('quantity')->paginate(20)->withQueryString();
        $warehouses  = Warehouse::orderBy('name')->get();

        return view('inventory.index', compact('inventories', 'warehouses', 'warehouseId'));
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view inventory');
        $inventory->load(['material.unit', 'material.category', 'warehouse',
            'stockMutations' => fn($q) => $q->latest()->limit(30)]);

        return view('inventory.show', compact('inventory'));
    }
}
