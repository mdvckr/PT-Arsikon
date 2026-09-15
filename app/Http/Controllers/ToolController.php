<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Category;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view tools');

        // Kategori beserta alat di dalamnya (untuk tabel berjenjang Kategori -> Kelompok Alat -> Varian)
        $categoryQuery = Category::query()
            ->where('type', 'tool')
            ->with(['tools' => function ($q) use ($request) {
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
        // Alias untuk kompatibilitas
        $categories = $categoriesData;

        // Kategori untuk dropdown filter
        $filterCategories = Category::query()->where('type', 'tool')->orderBy('name')->get();

        return view('tools.index', compact('categoriesData', 'categories', 'filterCategories'));
    }

    public function create(Request $request)
    {
        $this->authorize('create tools');
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Kategori yang dipilih lewat query (mis. klik "Tambah Alat" pada baris kategori)
        $selectedCategoryId = $request->query('category_id');
        if ($selectedCategoryId && $categories->contains('id', (int) $selectedCategoryId)) {
            $selectedCategoryId = (int) $selectedCategoryId;
        } else {
            $selectedCategoryId = null;
        }

        // Ambil daftar kelompok nama alat (type) per kategori untuk Dropdown bertingkat
        $existingGroups = Tool::whereNotNull('type')
            ->where('type', '!=', '')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        return view('tools.create', compact('categories', 'selectedCategoryId', 'existingGroups', 'warehouses'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tools');

        $validated = $request->validate([
            'code'            => 'required|string|max:50|unique:tools,code',
            'name'            => 'required|string|max:255',
            'type'            => 'nullable|string|max:255',
            'size'            => 'nullable|string|max:255',
            'brand'           => 'nullable|string|max:100',
            'category_id'     => 'nullable|integer|exists:categories,id',
            'new_category'    => 'nullable|string|max:255',
            'warehouse_id'    => 'nullable|integer|exists:warehouses,id',
            'notes'           => 'nullable|string',
            'stock_total'     => 'nullable|integer|min:0',
            'incoming_stages' => 'nullable|array',
        ]);

        $category = $this->resolveCategory($request);
        $incomingStages = $this->parseIncomingStages($request);

        $stockTotal = isset($validated['stock_total']) ? (int) $validated['stock_total'] : 0;
        $receivedStagesQty = $incomingStages ? (int) collect($incomingStages)->where('status', 'received')->sum('qty') : 0;

        // Jika stock_total belum diisi manual tapi ada tahap berstatus 'received', otomatis sinkronkan
        if ($stockTotal <= 0 && $receivedStagesQty > 0) {
            $stockTotal = $receivedStagesQty;
        }

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
            'current_warehouse_id' => $validated['warehouse_id'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'incoming_stages'      => $incomingStages,
            'stock_total'          => $stockTotal,
            'stock_available'      => $stockTotal,
            'stock_borrowed'       => 0,
            'stock_maintenance'    => 0,
            'stock_damaged'        => 0,
        ]);

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$tool->name}' berhasil ditambahkan dengan total stok {$tool->stock_total} unit.");
    }

    public function addStock(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $request->validate(['quantity' => 'required|integer|min:1|max:500']);

        $qty = (int) $request->quantity;
        $tool->addStock($qty);

        return redirect()->route('tools.edit', $tool)
            ->with('success', "Stok '{$tool->name}' berhasil ditambah {$qty} unit. Total stok sekarang: {$tool->fresh()->stock_total} unit.");
    }

    public function show(Tool $tool)
    {
        $this->authorize('view tools');
        $tool->load(['category', 'currentWarehouse', 'assignments.project', 'maintenances']);

        return view('tools.show', compact('tool'));
    }

    public function edit(Tool $tool)
    {
        $this->authorize('edit tools');
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

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
            'incoming_stages'   => 'nullable|array',
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
            'stock_total'          => $validated['stock_total'],
            'stock_available'      => $validated['stock_available'],
            'stock_maintenance'    => $validated['stock_maintenance'] ?? 0,
            'stock_damaged'        => $validated['stock_damaged'] ?? 0,
        ]);

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

    private function parseIncomingStages(Request $request): ?array
    {
        if (!$request->has('incoming_stages') || !is_array($request->incoming_stages)) {
            return null;
        }

        $stages = [];
        foreach ($request->incoming_stages as $item) {
            $qty = isset($item['qty']) && $item['qty'] !== '' ? (int) $item['qty'] : 0;
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
}
