<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view materials');
        $user = Auth::user();
        $accessibleIds = $user->accessibleWarehouseIds();

        $categoryQuery = Category::query()
            ->where('type', 'material')
            ->with(['materials' => function ($q) use ($request, $accessibleIds) {
                $q->with(['unit', 'inventories' => fn($iq) => $iq->whereIn('warehouse_id', $accessibleIds), 'stockMutations', 'supplier']);
                if ($request->search) {
                    $q->where(function ($sub) use ($request) {
                        $sub->where('name', 'like', "%{$request->search}%")
                            ->orWhere('sku', 'like', "%{$request->search}%")
                            ->orWhere('brand', 'like', "%{$request->search}%")
                            ->orWhere('size', 'like', "%{$request->search}%")
                            ->orWhere('type', 'like', "%{$request->search}%")
                            ->orWhere('supplier_name', 'like', "%{$request->search}%")
                            ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$request->search}%"));
                    });
                }
                $q->latest();
            }]);

        if ($request->category_id) {
            $categoryQuery->where('id', $request->category_id);
        }

        if ($request->search) {
            $categoryQuery->whereHas('materials', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('size', 'like', "%{$request->search}%")
                  ->orWhere('type', 'like', "%{$request->search}%")
                  ->orWhere('supplier_name', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$request->search}%"));
            });
        }

        $categoriesData = $categoryQuery->orderBy('name')->get();
        $filterCategories = Category::query()->where('type', 'material')->orderBy('name')->get();

        return view('materials.index', compact('categoriesData', 'filterCategories', 'accessibleIds'));
    }

    protected function resolveUnitFromManual(array $item): ?Unit
    {
        if (!empty($item['unit_id'])) {
            return Unit::find($item['unit_id']);
        }
        $unit = Unit::where('name', 'Pcs')->first();
        if (!$unit) {
            $unit = Unit::create([
                'code' => 'UNT-' . strtoupper(substr(md5(uniqid()), 0, 4)),
                'name' => 'Pcs',
                'is_decimal' => false,
            ]);
        }
        return $unit;
    }

    public function create()
    {
        $this->authorize('create materials');
        $user = Auth::user();
        $categories = Category::query()->where('type', 'material')->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();
        $warehouses = Warehouse::forUser($user)->orderBy('name')->get();

        // Ambil daftar kelompok nama barang (type) per kategori untuk Dropdown 2 bertingkat
        $existingGroups = Material::whereNotNull('type')
            ->where('type', '!=', '')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $accessibleWarehouseIds = $user->accessibleWarehouseIds();
        $singleWarehouse = $warehouses->count() === 1 ? $warehouses->first() : null;

        return view('materials.create', compact('categories', 'units', 'warehouses', 'existingGroups', 'suppliers', 'accessibleWarehouseIds', 'singleWarehouse'));
    }

    public function store(Request $request)
    {
        $this->authorize('create materials');
        $user = Auth::user();
        $accessibleIds = $user->accessibleWarehouseIds();

        // Clean up manual_items if present but empty or invalid
        if ($request->has('manual_items') && is_array($request->manual_items)) {
            $filteredManual = array_values(array_filter($request->manual_items, function ($item) {
                return is_array($item) && !empty($item['name']) && !empty($item['sku']);
            }));
            $request->merge(['manual_items' => !empty($filteredManual) ? $filteredManual : null]);
        }

        // Validasi keberadaan kategori
        if (!$request->filled('category_id') && !$request->filled('new_category')) {
            return back()->withErrors(['category_id' => 'Kategori wajib dipilih atau diisi pada kolom Kategori Baru.'])->withInput();
        }

        $validated = $request->validate([
            'sku'                  => 'required|string|max:50|unique:materials,sku',
            'name'                 => 'required|string|max:255',
            'brand'                => 'nullable|string|max:255',
            'size'                 => 'nullable|string|max:255',
            'type'                 => 'nullable|string|max:255',
            'category_id'          => 'nullable|exists:categories,id',
            'supplier'             => 'nullable|string|max:255',
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'new_category'         => 'nullable|string|max:255',
            'unit_id'              => 'nullable|string',
            'new_unit'             => 'nullable|string|max:100',
            'description'          => 'nullable|string',
            'warehouse_id'         => 'required|integer|exists:warehouses,id',
            'initial_stock'        => 'nullable|numeric|min:0',
            'min_stock'            => 'nullable|numeric|min:0',
            'incoming_stages'      => 'nullable|array',
            'manual_items'         => 'nullable|array',
            'manual_items.*.name'  => 'required|string|max:255',
            'manual_items.*.sku'   => 'required|string|max:50|unique:materials,sku',
            'manual_items.*.unit_id' => 'nullable|string',
            'manual_items.*.quantity' => 'nullable|numeric|min:0',
            'manual_items.*.warehouse_id' => 'required|integer|exists:warehouses,id',
            'manual_items.*.min_stock' => 'nullable|numeric|min:0',
            'manual_items.*.description' => 'nullable|string',
        ]);

        // Validasi warehouse_id — admin harus memilih gudang yang valid
        if (empty($validated['warehouse_id']) || !is_numeric($validated['warehouse_id'])) {
            return back()->withErrors(['warehouse_id' => 'Gudang wajib dipilih.'])->withInput();
        }
        if (!in_array((int)$validated['warehouse_id'], $accessibleIds)) {
            return back()->withErrors(['warehouse_id' => 'Anda tidak memiliki akses ke gudang ini.'])->withInput();
        }

        $category = $this->resolveCategory($request);
        $validated['category_id'] = $category?->id;

        $unit = $this->resolveUnit($request);
        if (!$unit) {
            return back()->withErrors(['unit_id' => 'Satuan wajib dipilih atau diisi pada kolom Satuan Baru.'])->withInput();
        }
        $validated['unit_id'] = $unit->id;

        $supplier = $this->resolveSupplier($request->supplier ?? null);
        $validated['supplier_id'] = $supplier?->id;
        $validated['supplier_name'] = !empty($request->supplier) ? trim($request->supplier) : null;

        $incomingStages = $this->parseIncomingStages($request);
        $validated['incoming_stages'] = $incomingStages;

        // Jika initial_stock belum diisi manual tapi ada tahap berstatus 'received', otomatis sinkronkan
        $receivedStagesQty = $incomingStages ? collect($incomingStages)->where('status', 'received')->sum('qty') : 0;
        if ((empty($validated['initial_stock']) || $validated['initial_stock'] == 0) && $receivedStagesQty > 0) {
            $validated['initial_stock'] = $receivedStagesQty;
        }

        // Pastikan kelompok barang (type) tidak kosong
        $typeVal = !empty($validated['type']) ? trim($validated['type']) : null;
        if (empty($typeVal) && !empty($validated['name'])) {
            if (!empty($validated['size']) && str_ends_with($validated['name'], $validated['size'])) {
                $typeVal = trim(substr($validated['name'], 0, -strlen($validated['size'])));
            }
        }
        if (empty($typeVal)) {
            $typeVal = $category?->name ?? 'Lainnya';
        }
        $validated['type'] = $typeVal;
        $validated['min_stock_central'] = $validated['min_stock'] ?? 0;

        $material = Material::create($validated);

        if (!empty($validated['warehouse_id']) && isset($validated['initial_stock']) && $validated['initial_stock'] > 0) {
            $inventory = Inventory::create([
                'warehouse_id' => $validated['warehouse_id'],
                'material_id'  => $material->id,
                'quantity'      => $validated['initial_stock'],
                'min_stock'     => $validated['min_stock'] ?? 0,
            ]);

            StockMutation::create([
                'material_id'        => $material->id,
                'warehouse_id'       => $validated['warehouse_id'],
                'qty_change'         => $validated['initial_stock'],
                'qty_balance_after'  => $validated['initial_stock'],
                'reference_type'     => 'Initial Stock',
                'created_by_user_id' => auth()->id(),
                'notes'              => 'Stok awal saat pendaftaran material' . ($receivedStagesQty > 0 ? ' (dari akumulasi tahap T-masuk)' : ''),
            ]);
        }

        // Handle manual items
        $manualCreatedCount = 0;
        if (!empty($validated['manual_items'])) {
            foreach ($validated['manual_items'] as $manualItem) {
                if (!in_array($manualItem['warehouse_id'], $accessibleIds)) {
                    continue;
                }
                $manualUnit = $this->resolveUnitFromManual($manualItem);
                if (!$manualUnit) {
                    continue;
                }

                $manualMaterial = Material::create([
                    'sku'             => $manualItem['sku'],
                    'name'            => $manualItem['name'],
                    'unit_id'         => $manualUnit->id,
                    'category_id'     => $category?->id,
                    'supplier_id'     => $supplier?->id,
                    'supplier_name'   => $validated['supplier_name'],
                    'brand'           => $validated['brand'] ?? null,
                    'size'            => $validated['size'] ?? null,
                    'type'            => $typeVal,
                    'description'     => $manualItem['description'] ?? null,
                    'is_active'       => true,
                ]);

                if (!empty($manualItem['quantity']) && $manualItem['quantity'] > 0) {
                    Inventory::create([
                        'warehouse_id' => $manualItem['warehouse_id'],
                        'material_id'  => $manualMaterial->id,
                        'quantity'     => $manualItem['quantity'],
                        'min_stock'    => $manualItem['min_stock'] ?? 0,
                    ]);
                }
                $manualCreatedCount++;
            }
        }

        $successMsg = "Material '{$validated['name']}' berhasil ditambahkan.";
        if ($manualCreatedCount > 0) {
            $successMsg .= " {$manualCreatedCount} item manual juga berhasil dibuat.";
        }

        return redirect()->route('materials.index')
            ->with('success', $successMsg);
    }

    public function show(Material $material)
    {
        $this->authorize('view materials');
        $material->load(['category', 'unit', 'supplier', 'inventories.warehouse', 'stockMutations' => fn($q) => $q->with('warehouse')->latest()->limit(20)]);

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $this->authorize('edit materials');
        $user = Auth::user();
        $categories = Category::query()->where('type', 'material')->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();
        $warehouses = Warehouse::forUser($user)->orderBy('name')->get();

        // Ambil daftar kelompok nama barang (type) per kategori untuk Dropdown 2 bertingkat
        $existingGroups = Material::whereNotNull('type')
            ->where('type', '!=', '')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('materials.edit', compact('material', 'categories', 'units', 'warehouses', 'existingGroups', 'suppliers'));
    }

    public function update(Request $request, Material $material)
    {
        $this->authorize('edit materials');

        $validated = $request->validate([
            'sku'             => "required|string|max:50|unique:materials,sku,{$material->id}",
            'name'            => 'required|string|max:255',
            'brand'           => 'nullable|string|max:255',
            'size'            => 'nullable|string|max:255',
            'type'            => 'nullable|string|max:255',
            'category_id'     => 'nullable|exists:categories,id',
            'supplier'        => 'nullable|string|max:255',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'new_category'    => 'nullable|string|max:255',
            'unit_id'         => 'nullable|string',
            'new_unit'        => 'nullable|string|max:100',
            'description'     => 'nullable|string',
            'incoming_stages' => 'nullable|array',
        ]);

        $category = $this->resolveCategory($request);
        $validated['category_id'] = $category?->id;

        $unit = $this->resolveUnit($request);
        if (!$unit) {
            return back()->withErrors(['unit_id' => 'Satuan wajib dipilih atau diisi pada kolom Satuan Baru.'])->withInput();
        }
        $validated['unit_id'] = $unit->id;

        $supplier = $this->resolveSupplier($request->supplier ?? null);
        $validated['supplier_id'] = $supplier?->id;
        $validated['supplier_name'] = !empty($request->supplier) ? trim($request->supplier) : null;
        $validated['incoming_stages'] = $this->parseIncomingStages($request);

        // Pertahankan kelompok barang (type) jika tidak sengaja terkirim kosong saat edit
        $typeVal = !empty($validated['type']) ? trim($validated['type']) : null;
        if (empty($typeVal) && !empty($material->type)) {
            $typeVal = $material->type;
        }
        if (empty($typeVal) && !empty($validated['name'])) {
            if (!empty($validated['size']) && str_ends_with($validated['name'], $validated['size'])) {
                $typeVal = trim(substr($validated['name'], 0, -strlen($validated['size'])));
            }
        }
        if (empty($typeVal)) {
            $typeVal = $category?->name ?? 'Lainnya';
        }
        $validated['type'] = $typeVal;

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', "Material '{$material->name}' berhasil diperbarui.");
    }

    /**
     * Selesaikan satuan terpilih atau buat satuan baru jika user menginputkan satuan baru on-the-fly.
     */
    protected function resolveUnit(Request $request): ?Unit
    {
        if ($request->filled('new_unit')) {
            $name = trim($request->new_unit);
            $unit = Unit::where('name', $name)->orWhere('code', strtolower($name))->first();

            if (! $unit) {
                $clean = preg_replace('/[^a-zA-Z0-9]/', '', $name);
                $code  = strtolower(substr($clean, 0, 4)) ?: 'unt';
                $base  = $code;
                $i     = 1;
                while (Unit::where('code', $code)->exists()) {
                    $code = $base . $i++;
                }

                $unit = Unit::create([
                    'code'       => $code,
                    'name'       => $name,
                    'is_decimal' => true,
                ]);
            }

            return $unit;
        }

        if ($request->filled('unit_id') && $request->unit_id !== '__new__') {
            return Unit::find($request->unit_id);
        }

        return null;
    }

    private function parseIncomingStages(Request $request): ?array
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

    public function destroy(Material $material)
    {
        $this->authorize('delete materials');

        if ($material->inventories()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Tidak dapat menghapus material yang masih memiliki stok aktif.');
        }

        $name = $material->name;
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', "Material '{$name}' berhasil dihapus.");
    }

    /**
     * Selesaikan kategori terpilih. Jika user mengisi kategori baru (new_category),
     * otomatis buat kategori material baru dan kembalikan instance-nya.
     */
    protected function resolveCategory(Request $request): ?Category
    {
        if ($request->filled('new_category')) {
            $name = trim($request->new_category);

            $category = Category::where('type', 'material')->where('name', $name)->first();

            if (! $category) {
                $code = 'MAT-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
                $base  = $code;
                $i     = 1;
                while (Category::where('code', $code)->exists()) {
                    $code = $base . '-' . $i++;
                }

                $category = Category::create([
                    'code' => $code,
                    'name' => $name,
                    'type' => 'material',
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
     * Selesaikan supplier terpilih dari input teks user.
     * Jika supplier belum terdaftar, otomatis daftarkan supplier baru ke master data.
     */
    protected function resolveSupplier(?string $supplierName): ?Supplier
    {
        if (empty($supplierName)) {
            return null;
        }

        $name = trim($supplierName);
        $supplier = Supplier::where('name', $name)->first();

        if (! $supplier) {
            $code = 'SUP-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6));
            $base = $code;
            $i = 1;
            while (Supplier::where('code', $code)->exists()) {
                $code = $base . '-' . $i++;
            }

            $supplier = Supplier::create([
                'code' => $code,
                'name' => $name,
                'is_active' => true,
            ]);
        }

        return $supplier;
    }
}
