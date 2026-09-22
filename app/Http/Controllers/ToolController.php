<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Category;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Services\ToolInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToolController extends Controller
{
    protected ToolInventoryService $toolInvService;

    public function __construct(ToolInventoryService $toolInvService)
    {
        $this->toolInvService = $toolInvService;
    }

    public function index(Request $request)
    {
        $this->authorize('view tools');
        $user = Auth::user();
        $accessibleIds = $user->accessibleWarehouseIds();

        $categoryQuery = Category::query()
            ->where('type', 'tool')
            ->with(['tools' => function ($q) use ($request, $accessibleIds) {
                $q->with(['currentWarehouse', 'inventories' => fn($iq) => $iq->whereIn('warehouse_id', $accessibleIds)]);
                if ($request->search) {
                    $q->where(function ($qq) use ($request) {
                        $qq->where('name', 'like', "%{$request->search}%")
                           ->orWhere('code', 'like', "%{$request->search}%")
                           ->orWhere('brand', 'like', "%{$request->search}%")
                           ->orWhere('type', 'like', "%{$request->search}%")
                           ->orWhere('size', 'like', "%{$request->search}%");
                    });
                }
                $q->orderBy('type')->orderBy('name');
            }]);

        if ($request->category_id) {
            $categoryQuery->where('id', $request->category_id);
        }

        if ($request->search) {
            $categoryQuery->whereHas('tools', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('type', 'like', "%{$request->search}%")
                  ->orWhere('size', 'like', "%{$request->search}%");
            });
        }

        $categoriesData = $categoryQuery->orderBy('name')->get();
        $categories = $categoriesData;
        $filterCategories = Category::query()->where('type', 'tool')->orderBy('name')->get();

        return view('tools.index', compact('categoriesData', 'categories', 'filterCategories', 'accessibleIds'));
    }

    public function create(Request $request)
    {
        $this->authorize('create tools');
        $user = Auth::user();
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();
        $warehouses = Warehouse::forUser($user)->orderBy('name')->get();

        $selectedCategoryId = $request->query('category_id');
        if ($selectedCategoryId && $categories->contains('id', (int) $selectedCategoryId)) {
            $selectedCategoryId = (int) $selectedCategoryId;
        } else {
            $selectedCategoryId = null;
        }

        $existingGroups = Tool::whereNotNull('type')
            ->where('type', '!=', '')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        $singleWarehouse = $warehouses->count() === 1 ? $warehouses->first() : null;

        return view('tools.create', compact('categories', 'selectedCategoryId', 'existingGroups', 'warehouses', 'singleWarehouse'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tools');
        $user = Auth::user();
        $accessibleIds = $user->accessibleWarehouseIds();

        $validated = $request->validate([
            'code'            => 'required|string|max:50|unique:tools,code',
            'name'            => 'required|string|max:255',
            'type'            => 'nullable|string|max:255',
            'size'            => 'nullable|string|max:255',
            'brand'           => 'nullable|string|max:100',
            'category_id'     => 'nullable|integer|exists:categories,id',
            'new_category'    => 'nullable|string|max:255',
            'warehouse_id'    => 'required|integer|exists:warehouses,id',
            'notes'           => 'nullable|string',
            'stock_total'     => 'nullable|integer|min:0',
            'manual_items'    => 'nullable|array',
            'manual_items.*.code'  => 'required|string|max:50|unique:tools,code',
            'manual_items.*.name'  => 'required|string|max:255',
            'manual_items.*.stock_total' => 'nullable|integer|min:0',
            'manual_items.*.warehouse_id' => 'required|integer|exists:warehouses,id',
        ]);

        // Validate warehouse access
        if (!in_array($validated['warehouse_id'], $accessibleIds)) {
            return back()->withErrors(['warehouse_id' => 'Anda tidak memiliki akses ke gudang ini.'])->withInput();
        }

        $category = $this->resolveCategory($request);
        $incomingStages = $this->parseIncomingStages($request);

        $stockTotal = isset($validated['stock_total']) ? (int) $validated['stock_total'] : 0;

        $typeVal = !empty($validated['type']) ? trim($validated['type']) : null;
        if (empty($typeVal) && !empty($validated['name'])) {
            if (!empty($validated['size']) && str_ends_with($validated['name'], $validated['size'])) {
                $typeVal = trim(substr($validated['name'], 0, -strlen($validated['size'])));
            }
        }
        if (empty($typeVal)) {
            $typeVal = $category?->name ?? 'Lainnya';
        }

        $tool = Tool::create([
            'code'                 => strtoupper(trim($validated['code'])),
            'name'                 => $validated['name'],
            'type'                 => $typeVal,
            'size'                 => $validated['size'] ?? null,
            'brand'                => $validated['brand'] ?? null,
            'category_id'          => $category?->id,
            'current_warehouse_id' => $validated['warehouse_id'],
            'notes'                => $validated['notes'] ?? null,
            'incoming_stages'      => $incomingStages,
            'stock_total'          => 0,
            'stock_available'      => 0,
            'stock_borrowed'       => 0,
            'stock_maintenance'    => 0,
            'stock_damaged'        => 0,
        ]);

        if ($stockTotal > 0) {
            $warehouse = Warehouse::find($validated['warehouse_id']);
            if ($warehouse) {
                $this->toolInvService->addStock($warehouse, $tool, $stockTotal);
                $tool->refresh();
            }
        }

        // Handle manual items
        $manualCreatedCount = 0;
        if (!empty($validated['manual_items'])) {
            foreach ($validated['manual_items'] as $manualItem) {
                if (!in_array($manualItem['warehouse_id'], $accessibleIds)) {
                    continue;
                }
                $manualTool = Tool::create([
                    'code'              => strtoupper(trim($manualItem['code'])),
                    'name'              => $manualItem['name'],
                    'type'              => $typeVal,
                    'size'              => $validated['size'] ?? null,
                    'brand'             => $validated['brand'] ?? null,
                    'category_id'       => $category?->id,
                    'current_warehouse_id' => $manualItem['warehouse_id'],
                    'stock_total'       => (int) ($manualItem['stock_total'] ?? 0),
                    'stock_available'   => (int) ($manualItem['stock_total'] ?? 0),
                    'stock_borrowed'    => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged'     => 0,
                ]);
                if ($manualItem['stock_total'] > 0) {
                    $wh = Warehouse::find($manualItem['warehouse_id']);
                    if ($wh) {
                        $this->toolInvService->addStock($wh, $manualTool, (int) $manualItem['stock_total']);
                    }
                }
                $manualCreatedCount++;
            }
        }

        $successMsg = "Alat '{$validated['name']}' berhasil ditambahkan dengan total stok {$tool->stock_total} unit.";
        if ($manualCreatedCount > 0) {
            $successMsg .= " {$manualCreatedCount} item manual juga berhasil dibuat.";
        }

        return redirect()->route('tools.index')
            ->with('success', $successMsg);
    }

    public function addStock(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $request->validate(['quantity' => 'required|integer|min:1|max:500']);

        $qty = (int) $request->quantity;
        $warehouse = $tool->currentWarehouse ?? Warehouse::where('is_central', true)->first();

        if (!$warehouse) {
            return redirect()->route('tools.edit', $tool)
                ->with('error', 'Gudang untuk alat ini tidak ditemukan.');
        }

        $this->toolInvService->addStock($warehouse, $tool, $qty);
        $tool->refresh();

        return redirect()->route('tools.edit', $tool)
            ->with('success', "Stok '{$tool->name}' berhasil ditambah {$qty} unit. Total stok sekarang: {$tool->stock_total} unit.");
    }

    public function show(Tool $tool)
    {
        $this->authorize('view tools');
        $tool->load(['category', 'currentWarehouse', 'assignments.fromWarehouse', 'assignments.toWarehouse', 'assignments.assignedBy', 'maintenances']);

        return view('tools.show', compact('tool'));
    }

    public function edit(Tool $tool)
    {
        $this->authorize('edit tools');
        $user = Auth::user();
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();
        $warehouses = Warehouse::forUser($user)->orderBy('name')->get();

        $existingGroups = Tool::whereNotNull('type')
            ->where('type', '!=', '')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        return view('tools.edit', compact('tool', 'categories', 'warehouses', 'existingGroups'));
    }

    public function update(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $validated = $request->validate([
            'code'              => "required|string|max:50|unique:tools,code,{$tool->id}",
            'name'              => 'required|string|max:255',
            'type'              => 'nullable|string|max:255',
            'size'              => 'nullable|string|max:255',
            'brand'             => 'nullable|string|max:100',
            'category_id'       => 'nullable|integer|exists:categories,id',
            'new_category'      => 'nullable|string|max:255',
            'warehouse_id'      => 'nullable|integer|exists:warehouses,id',
            'notes'             => 'nullable|string',
            'stock_total'       => 'required|integer|min:0',
            'stock_available'   => 'required|integer|min:0',
            'stock_maintenance' => 'nullable|integer|min:0',
            'stock_damaged'     => 'nullable|integer|min:0',
        ]);

        $category = $this->resolveCategory($request);
        $incomingStages = $this->parseIncomingStages($request);

        // Pertahankan kelompok alat (type) jika tidak sengaja terkirim kosong saat edit
        $typeVal = !empty($validated['type']) ? trim($validated['type']) : null;
        if (empty($typeVal) && !empty($tool->type)) {
            $typeVal = $tool->type;
        }
        if (empty($typeVal) && !empty($validated['name'])) {
            if (!empty($validated['size']) && str_ends_with($validated['name'], $validated['size'])) {
                $typeVal = trim(substr($validated['name'], 0, -strlen($validated['size'])));
            }
        }
        if (empty($typeVal)) {
            $typeVal = $category?->name ?? 'Lainnya';
        }

        $tool->update([
            'code'                 => strtoupper(trim($validated['code'])),
            'name'                 => $validated['name'],
            'type'                 => $typeVal,
            'size'                 => $validated['size'] ?? null,
            'brand'                => $validated['brand'] ?? null,
            'category_id'          => $category?->id,
            'current_warehouse_id' => $validated['warehouse_id'] ?? $tool->current_warehouse_id,
            'notes'                => $validated['notes'] ?? null,
            'incoming_stages'      => $incomingStages,
        ]);

        // Sync aggregate stock snapshot to the active warehouse inventory
        $warehouse = $validated['warehouse_id']
            ? Warehouse::find($validated['warehouse_id'])
            : $tool->currentWarehouse;

        if ($warehouse) {
            $this->toolInvService->syncStock(
                $warehouse,
                $tool,
                (int) $validated['stock_total'],
                (int) $validated['stock_available'],
                0,
                (int) ($validated['stock_maintenance'] ?? 0),
                (int) ($validated['stock_damaged'] ?? 0)
            );
        }

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$tool->name}' berhasil diperbarui.");
    }

    public function destroy(Tool $tool)
    {
        $this->authorize('delete tools');

        if ($tool->stock_borrowed > 0) {
            return back()->with('error', 'Tidak dapat menghapus alat yang sedang dipinjam.');
        }

        $name = $tool->name;
        $tool->delete();

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$name}' berhasil dihapus.");
    }


    /**
     * Selesaikan kategori terpilih. Jika user mengisi kategori baru (new_category),
     * otomatis buat kategori tool baru dan kembalikan instance-nya.
     */
    protected function resolveCategory(Request $request): ?Category
    {
        if ($request->filled('new_category')) {
            $name = trim($request->new_category);

            $category = Category::where('type', 'tool')->where('name', $name)->first();

            if (! $category) {
                $code = 'TOOL-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
                $base  = $code;
                $i     = 1;
                while (Category::where('code', $code)->exists()) {
                    $code = $base . '-' . $i++;
                }

                $category = Category::create([
                    'code' => $code,
                    'name' => $name,
                    'type' => 'tool',
                ]);
            }

            return $category;
        }

        if ($request->filled('category_id')) {
            return Category::find($request->category_id);
        }

        return null;
    }

    /**
     * Parse incoming stages payload from request.
     */
    protected function parseIncomingStages(Request $request): ?array
    {
        if (!$request->has('incoming_stages') || !is_array($request->incoming_stages)) {
            return null;
        }

        $stages = [];
        foreach ($request->incoming_stages as $item) {
            $qty = isset($item['qty']) && $item['qty'] !== '' ? (float) $item['qty'] : 0;
            $stageName = trim($item['stage'] ?? '');
            if ($qty > 0 || $stageName !== '' || !empty($item['date']) || !empty($item['notes'])) {
                $stages[] = [
                    'stage'  => $stageName ?: 'T' . (count($stages) + 1),
                    'date'   => !empty($item['date']) ? $item['date'] : null,
                    'qty'    => $qty,
                    'status' => in_array($item['status'] ?? '', ['received', 'planned']) ? $item['status'] : 'received',
                    'notes'  => trim($item['notes'] ?? ''),
                ];
            }
        }

        return !empty($stages) ? $stages : null;
    }
}
