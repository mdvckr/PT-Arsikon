<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $warehouseId = session('active_warehouse_id');

        $categoryQuery = Category::query()
            ->where('type', 'material')
            ->with(['materials' => function ($q) use ($request, $warehouseId) {
                $q->with(['unit', 'inventories' => function ($invQ) use ($warehouseId) {
                    if ($warehouseId) {
                        $invQ->where('warehouse_id', $warehouseId);
                    }
                    $invQ->with('warehouse');
                }]);

                if ($request->search) {
                    $q->where(function ($sub) use ($request) {
                        $sub->where('materials.name', 'like', "%{$request->search}%")
                            ->orWhere('materials.sku', 'like', "%{$request->search}%");
                    });
                }
            }]);

        if ($request->category_id) {
            $categoryQuery->where('id', $request->category_id);
        }

        if ($request->search) {
            $categoryQuery->whereHas('materials', function ($q) use ($request) {
                $q->where('materials.name', 'like', "%{$request->search}%")
                  ->orWhere('materials.sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->stock_level === 'low') {
            $categoryQuery->whereHas('materials.inventories', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
                $q->whereColumn('quantity', '<=', 'min_stock')->where('quantity', '>', 0);
            });
        } elseif ($request->stock_level === 'out') {
            $categoryQuery->whereHas('materials.inventories', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
                $q->where('quantity', '<=', 0);
            });
        }

        $categoriesData = $categoryQuery->orderBy('name')->get()
            ->each(function ($cat) {
                $cat->setRelation('materials', $cat->materials->filter(fn($m) => $m->inventories->isNotEmpty()));
            })
            ->filter(fn($cat) => $cat->materials->isNotEmpty())
            ->values();

        $filterCategories = Category::query()->where('type', 'material')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.index', compact('categoriesData', 'filterCategories', 'warehouses', 'warehouseId'));
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view inventory');
        $inventory->load(['material.unit', 'material.category', 'warehouse',
            'stockMutations' => fn($q) => $q->latest()->limit(30)]);

        return view('inventory.show', compact('inventory'));
    }
}
