<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Tool;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $user = auth()->user();

        // Non-admin users: force scope ke warehouse mereka sendiri
        $isAdmin = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']);

        if ($isAdmin) {
            $warehouseId = session('active_warehouse_id');
        } else {
            // Untuk Admin Proyek / Karyawan: paksa ke active warehouse user,
            // atau warehouse pertama yang dimiliki jika sesi belum di-set.
            $warehouseId = session('active_warehouse_id') ?? $user->activeWarehouse()?->id;

            // Pastikan warehouseId yang dipilih memang milik user ini
            $userWhIds = $user->accessibleWarehouseIds();
            if ($warehouseId && !in_array($warehouseId, $userWhIds)) {
                $warehouseId = $userWhIds[0] ?? null;
            }
        }
        $itemType    = $request->query('item_type'); // 'material', 'tool', or empty (semua)
        $categoryId  = $request->query('category_id');
        $type        = $request->query('type');
        $size        = $request->query('size');
        $search      = $request->query('search');
        $stockLevel  = $request->query('stock_level');

        // 1. Build Cascading Data structure for 4 dropdowns
        // Material
        $materialCats = Category::where('type', 'material')->orderBy('name')->get();
        $materialData = Material::select('category_id', 'type', 'size')
            ->whereNotNull('category_id')
            ->get();

        $materialCascading = [];
        foreach ($materialCats as $cat) {
            $catItems = $materialData->where('category_id', $cat->id);
            $typesMap = [];
            $groupedByType = $catItems->groupBy(fn($m) => $m->type ?: 'Umum');
            foreach ($groupedByType as $typeName => $items) {
                $sizes = $items->pluck('size')->filter()->unique()->values()->all();
                $typesMap[$typeName] = $sizes;
            }
            $materialCascading[] = [
                'id'    => (string) $cat->id,
                'name'  => $cat->name,
                'types' => $typesMap,
            ];
        }

        // Tool
        $toolCats = Category::where('type', 'tool')->orderBy('name')->get();
        $toolData = Tool::select('category_id', 'type', 'size')
            ->whereNotNull('category_id')
            ->get();

        $toolCascading = [];
        foreach ($toolCats as $cat) {
            $catItems = $toolData->where('category_id', $cat->id);
            $typesMap = [];
            $groupedByType = $catItems->groupBy(fn($t) => $t->type ?: 'Umum');
            foreach ($groupedByType as $typeName => $items) {
                $sizes = $items->pluck('size')->filter()->unique()->values()->all();
                $typesMap[$typeName] = $sizes;
            }
            $toolCascading[] = [
                'id'    => (string) $cat->id,
                'name'  => $cat->name,
                'types' => $typesMap,
            ];
        }

        $cascadingData = [
            'material' => $materialCascading,
            'tool'     => $toolCascading,
        ];

        // 2. Fetch Material Inventories if applicable
        $categoriesData = collect();
        if (!$itemType || $itemType === 'material') {
            $catQuery = Category::query()
                ->where('type', 'material')
                ->with(['materials' => function ($q) use ($request, $warehouseId, $search, $type, $size) {
                    $q->with(['unit', 'inventories' => function ($invQ) use ($warehouseId) {
                        if ($warehouseId) {
                            $invQ->where('warehouse_id', $warehouseId);
                        }
                        $invQ->with('warehouse');
                    }]);

                    if ($search) {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%")
                                ->orWhere('size', 'like', "%{$search}%");
                        });
                    }

                    if ($type) {
                        $q->where('type', $type);
                    }

                    if ($size) {
                        $q->where('size', $size);
                    }
                }]);

            if ($categoryId) {
                $catQuery->where('id', $categoryId);
            }

            if ($search) {
                $catQuery->whereHas('materials', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('size', 'like', "%{$search}%");
                });
            }

            if ($type) {
                $catQuery->whereHas('materials', fn($q) => $q->where('type', $type));
            }

            if ($size) {
                $catQuery->whereHas('materials', fn($q) => $q->where('size', $size));
            }

            if ($stockLevel === 'low') {
                $catQuery->whereHas('materials.inventories', function ($q) use ($warehouseId) {
                    if ($warehouseId) $q->where('warehouse_id', $warehouseId);
                    $q->whereColumn('quantity', '<=', 'min_stock')->where('quantity', '>', 0);
                });
            } elseif ($stockLevel === 'out') {
                $catQuery->whereHas('materials.inventories', function ($q) use ($warehouseId) {
                    if ($warehouseId) $q->where('warehouse_id', $warehouseId);
                    $q->where('quantity', '<=', 0);
                });
            }

            $categoriesData = $catQuery->orderBy('name')->get()
                ->each(function ($cat) {
                    $cat->setRelation('materials', $cat->materials->filter(fn($m) => $m->inventories->isNotEmpty()));
                })
                ->filter(fn($cat) => $cat->materials->isNotEmpty())
                ->values();
        }

        // 3. Fetch Tool Inventories if applicable
        $toolsCategoriesData = collect();
        if (!$itemType || $itemType === 'tool') {
            $toolCatQuery = Category::query()
                ->where('type', 'tool')
                ->with(['tools' => function ($q) use ($request, $warehouseId, $search, $type, $size, $stockLevel) {
                    $q->with('currentWarehouse');

                    if ($warehouseId) {
                        $q->where('current_warehouse_id', $warehouseId);
                    }

                    if ($search) {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%")
                                ->orWhere('size', 'like', "%{$search}%");
                        });
                    }

                    if ($type) {
                        $q->where('type', $type);
                    }

                    if ($size) {
                        $q->where('size', $size);
                    }

                    if ($stockLevel === 'low') {
                        $q->where('stock_available', '<=', 2)->where('stock_available', '>', 0);
                    } elseif ($stockLevel === 'out') {
                        $q->where('stock_available', '<=', 0);
                    }
                }]);

            if ($categoryId) {
                $toolCatQuery->where('id', $categoryId);
            }

            if ($search) {
                $toolCatQuery->whereHas('tools', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('size', 'like', "%{$search}%");
                });
            }

            if ($type) {
                $toolCatQuery->whereHas('tools', fn($q) => $q->where('type', $type));
            }

            if ($size) {
                $toolCatQuery->whereHas('tools', fn($q) => $q->where('size', $size));
            }

            if ($stockLevel === 'low') {
                $toolCatQuery->whereHas('tools', fn($q) => $q->where('stock_available', '<=', 2)->where('stock_available', '>', 0));
            } elseif ($stockLevel === 'out') {
                $toolCatQuery->whereHas('tools', fn($q) => $q->where('stock_available', '<=', 0));
            }

            $toolsCategoriesData = $toolCatQuery->orderBy('name')->get()
                ->filter(fn($cat) => $cat->tools->isNotEmpty())
                ->values();
        }

        $filterCategories = Category::orderBy('name')->get();
        $warehouses = $this->accessibleWarehouses();

        return view('inventory.index', compact(
            'categoriesData',
            'toolsCategoriesData',
            'cascadingData',
            'filterCategories',
            'warehouses',
            'warehouseId',
            'itemType',
            'categoryId',
            'type',
            'size',
            'search',
            'stockLevel'
        ));
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view inventory');
        $inventory->load(['material.unit', 'material.category', 'warehouse',
            'stockMutations' => fn($q) => $q->latest()->limit(30)]);

        return view('inventory.show', compact('inventory'));
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('delete inventory');

        if ($inventory->quantity > 0) {
            return back()->with('error', 'Tidak dapat menghapus inventori yang masih memiliki stok aktif.');
        }

        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Data inventori berhasil dihapus.');
    }

    // ── Helper ────────────────────────────────────────────────────────────

    protected function accessibleWarehouses()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::orderBy('name')->get();
        }

        return $user->warehouses()->orderBy('name')->get();
    }
}
