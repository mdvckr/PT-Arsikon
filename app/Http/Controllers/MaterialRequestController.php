<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\MaterialRequest;
use App\Models\Material;
use App\Models\Warehouse;
use App\Services\MaterialRequestService;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function __construct(private MaterialRequestService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view material requests');

        $query = MaterialRequest::with(['requestedBy', 'fromWarehouse', 'toWarehouse.project', 'approvedBy', 'items.category']);

        if (auth()->user()->hasRole(['Admin Gudang Proyek', 'Karyawan'])) {
            $query->where('requested_by_user_id', auth()->id());
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('request_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('category_id')) {
            $query->whereHas('items', function ($iq) use ($request) {
                $iq->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->whereNotNull('needed_at')
                  ->whereDate('needed_at', '<', now()->toDateString())
                  ->whereNotIn('status', ['fulfilled', 'rejected', 'cancelled']);
        }

        $sort = in_array($request->sort, ['created_at', 'needed_at']) ? $request->sort : 'created_at';
        $direction = strtolower($request->direction) === 'asc' ? 'asc' : 'desc';

        $requests = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('material-requests.index', compact('requests', 'categories'));
    }

    public function create()
    {
        $this->authorize('create material requests');

        $warehouseId = session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;

        $centralWarehouse = Warehouse::where('is_central', true)->first();

        // Default sumber: Pengadaan / Beli ke Vendor
        $defaultSourceWarehouseId = old('source_warehouse_id', '');

        // Stok material dari gudang sumber (jika Pengadaan, gunakan Gudang Pusat sebagai referensi stok)
        $sourceWarehouseId = $defaultSourceWarehouseId ?: $centralWarehouse?->id;
        $materialCategories = Category::where('type', 'material')
            ->with(['materials' => function ($q) use ($sourceWarehouseId) {
                $q->where('is_active', true)
                  ->with(['unit', 'inventories' => function ($iq) use ($sourceWarehouseId) {
                      if ($sourceWarehouseId) {
                          $iq->where('warehouse_id', $sourceWarehouseId);
                      }
                  }])
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn($cat) => $cat->materials->isNotEmpty())
            ->values();

        // Material tanpa kategori
        $uncategorizedMaterials = Material::where('is_active', true)
            ->whereNull('category_id')
            ->with(['unit', 'inventories' => function ($iq) use ($sourceWarehouseId) {
                if ($sourceWarehouseId) {
                    $iq->where('warehouse_id', $sourceWarehouseId);
                }
            }])
            ->orderBy('name')
            ->get();

        // Gudang tujuan (gudang pemohon = gudang proyek yang boleh diakses pengguna)
        $accessibleWhIds = auth()->user()->accessibleWarehouseIds();
        $warehouses = Warehouse::where('is_central', false)
            ->whereIn('id', $accessibleWhIds)
            ->orderBy('name')
            ->get();

        if ($warehouseId) {
            $selectedWh = Warehouse::find($warehouseId);
            if ($selectedWh && ($selectedWh->is_central || !in_array($selectedWh->id, $accessibleWhIds))) {
                $warehouseId = $warehouses->first()?->id;
            }
        } else {
            $warehouseId = $warehouses->first()?->id;
        }

        // Semua gudang aktif sebagai pilihan sumber (Gudang Pusat tampil pertama)
        $sourceWarehouses = Warehouse::where('is_active', true)
            ->orderByRaw('is_central DESC')
            ->orderBy('name')
            ->get();

        // Daftar kelompok barang per kategori untuk pilihan bertingkat
        $existingGroups = Material::whereNotNull('type')
            ->where('type', '!=', '')
            ->where('type', '!=', 'Material Proyek')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        $toolCategories = Category::where('type', 'tool')->orderBy('name')->get();

        return view('material-requests.create', compact(
            'materialCategories',
            'toolCategories',
            'uncategorizedMaterials',
            'warehouses',
            'warehouseId',
            'centralWarehouse',
            'sourceWarehouses',
            'defaultSourceWarehouseId',
            'existingGroups'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create material requests');

        $validated = $request->validate([
            'warehouse_id'               => 'required|exists:warehouses,id',
            'source_warehouse_id'        => 'nullable|exists:warehouses,id',
            'needed_at'                  => 'nullable|date|after_or_equal:today',
            'requested_as'               => 'nullable|string|max:50',
            'notes'                      => 'nullable|string',
            'quantities'                 => 'nullable|array',
            'specs'                      => 'nullable|array',
            'item_notes'                 => 'nullable|array',
            'item_notes.*'               => 'nullable|string|max:255',
            'usage_locations'            => 'nullable|array',
            'usage_dates'                => 'nullable|array',
            'custom_items'               => 'nullable|array',
            'custom_items.*.name'        => 'required_with:custom_items|string|max:255',
            'custom_items.*.item_type'   => 'nullable|in:material,tool',
            'custom_items.*.category_id' => 'nullable|exists:categories,id',
            'custom_items.*.type'        => 'nullable|string|max:100',
            'custom_items.*.unit'        => 'nullable|string|max:50',
            'custom_items.*.qty'         => 'required_with:custom_items|numeric|min:0.01',
            'custom_items.*.spec'        => 'nullable|string|max:255',
            'custom_items.*.location'    => 'nullable|string|max:255',
            'custom_items.*.notes'       => 'nullable|string|max:255',
            'custom_items.*.date'        => 'nullable|date',
        ]);

        // Validasi: gudang sumber tidak boleh sama dengan gudang tujuan (pemohon)
        if (!empty($validated['source_warehouse_id']) && (int) $validated['warehouse_id'] === (int) $validated['source_warehouse_id']) {
            return back()->withInput()->withErrors([
                'source_warehouse_id' => 'Sumber material tidak boleh sama dengan gudang pemohon.',
                'warehouse_id'        => 'Gudang pemohon tidak boleh sama dengan sumber material.',
            ]);
        }

        // Validasi server: cek hak akses pemohon ke gudang tujuan
        $toWarehouse = Warehouse::findOrFail($validated['warehouse_id']);
        if (!auth()->user()->hasAccessToWarehouse($toWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke gudang proyek ini.');
        }

        if ($toWarehouse->is_central) {
            return back()->withInput()->withErrors(['warehouse_id' => 'PO Lapangan hanya dapat ditujukan ke Gudang Proyek.']);
        }

        $sourceWarehouse = !empty($validated['source_warehouse_id'])
            ? Warehouse::findOrFail($validated['source_warehouse_id'])
            : null;

        // Build items dari quantities[material_id] => qty (master) + custom_items manual
        $itemsData = [];
        if (!empty($validated['quantities'])) {
            foreach ($validated['quantities'] as $materialId => $qty) {
                $qty = (float) $qty;
                if ($qty <= 0) continue;
                $itemNote = trim($request->input("item_notes.{$materialId}") ?? '');
                $itemsData[] = [
                    'material_id'    => $materialId,
                    'qty_requested'  => $qty,
                    'notes'          => $itemNote ?: null,
                    'specification'  => trim($request->input("specs.{$materialId}") ?? ''),
                    'usage_location' => trim($request->input("usage_locations.{$materialId}") ?? ''),
                    'usage_date'     => $request->input("usage_dates.{$materialId}") ?: null,
                ];
            }
        }
        if (!empty($validated['custom_items'])) {
            foreach ($validated['custom_items'] as $cItem) {
                $qty = (float) ($cItem['qty'] ?? 0);
                if ($qty <= 0) continue;
                $name = trim($cItem['name'] ?? '');
                if ($name === '') continue;
                $customNote = trim($cItem['notes'] ?? '');
                $itemType = in_array($cItem['item_type'] ?? '', ['material', 'tool']) ? $cItem['item_type'] : 'material';
                $itemsData[] = [
                    'material_id'      => null,
                    'item_type'        => $itemType,
                    'custom_item_name' => $name,
                    'custom_item_type' => trim($cItem['type'] ?? '') ?: null,
                    'custom_item_unit' => trim($cItem['unit'] ?? ($itemType === 'tool' ? 'unit' : 'pcs')) ?: 'unit',
                    'category_id'      => !empty($cItem['category_id']) ? (int) $cItem['category_id'] : null,
                    'qty_requested'    => $qty,
                    'notes'            => $customNote ?: 'Manual - tidak ada di gudang sumber',
                    'specification'    => trim($cItem['spec'] ?? ''),
                    'usage_location'   => trim($cItem['location'] ?? ''),
                    'usage_date'       => $cItem['date'] ?? null,
                ];
            }
        }

        if (!empty($validated['custom_items'])) {
            foreach ($validated['custom_items'] as $cIdx => $cItem) {
                if (!empty($cItem['category_id'])) {
                    $cat = \App\Models\Category::find($cItem['category_id']);
                    if ($cat && $cat->type === 'tool' && fmod((float)($cItem['qty'] ?? 0), 1) != 0) {
                        return back()->withInput()->withErrors([
                            "custom_items.{$cIdx}.qty" => "Kuantitas alat harus berupa bilangan bulat."
                        ]);
                    }
                }
            }
        }

        if (empty($itemsData)) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada material yang diminta (master atau manual).']);
        }

        try {
            $mr = $this->service->createRequest(
                $toWarehouse,
                $sourceWarehouse,
                auth()->user(),
                $itemsData,
                true,
                $validated['notes'] ?? null,
                $validated['needed_at'] ?? null,
                $validated['requested_as'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'warehouse_id'  => $e->getMessage(),
                'general_error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('material-requests.show', $mr)
            ->with('success', "PO Lapangan #{$mr->request_number} berhasil diajukan.");
    }

    /**
     * AJAX: Ambil data stok semua material untuk gudang tertentu.
     */
    public function getStock(Request $request)
    {
        $request->validate(['warehouse_id' => 'required|exists:warehouses,id']);

        $stocks = Inventory::where('warehouse_id', $request->warehouse_id)
            ->whereNotNull('material_id')
            ->get()
            ->mapWithKeys(fn($inv) => [$inv->material_id => (float) $inv->quantity]);

        return response()->json($stocks);
    }

    public function show(Request $request, MaterialRequest $materialRequest)
    {
        $this->authorize('view material requests');
        $materialRequest->load([
            'requestedBy',
            'fromWarehouse',
            'toWarehouse.project',
            'approvedBy',
            'items.category',
            'items.material.unit',
            'items.material.category',
            'items.purchaseOrderItems.purchaseOrder.goodsReceipts.warehouse',
            'items.purchaseOrderItems.purchaseOrder.supplier',
            'goodsReceipts.warehouse',
            'goodsReceipts.supplier',
            'materialUsages.issuedBy',
            'materialUsages.warehouse',
            'distributions.fromWarehouse',
            'distributions.toWarehouse',
            'distributions.creator',
        ]);

        $sourceWarehouseId = $materialRequest->from_warehouse_id;
        $materialIds = $materialRequest->items->pluck('material_id')->filter()->unique()->values();

        $sourceInventories = collect();
        if ($sourceWarehouseId && $materialIds->isNotEmpty()) {
            $sourceInventories = Inventory::where('warehouse_id', $sourceWarehouseId)
                ->whereIn('material_id', $materialIds)
                ->get()
                ->keyBy('material_id');
        }

        if ($request->boolean('print')) {
            return view('material-requests.print', compact('materialRequest'));
        }

        return view('material-requests.show', compact('materialRequest', 'sourceInventories'));
    }

    public function approve(MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        try {
            $this->service->approveRequest($materialRequest, auth()->user());
            return back()->with('success', "Permintaan #{$materialRequest->request_number} disetujui.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        $request->validate(['rejection_reason' => 'required|string']);

        try {
            $this->service->rejectRequest($materialRequest, auth()->user(), $request->rejection_reason);
            return back()->with('success', "Permintaan #{$materialRequest->request_number} ditolak.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(MaterialRequest $materialRequest)
    {
        $this->authorize('view material requests');

        if (!in_array($materialRequest->status, ['submitted', 'draft'])) {
            abort(403, 'PO Lapangan ini sudah diproses dan tidak dapat diubah.');
        }

        $user = auth()->user();
        if ($materialRequest->requested_by_user_id !== $user->id && !$user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit PO Lapangan ini.');
        }

        $centralWarehouse = Warehouse::where('is_central', true)->first();
        $sourceWarehouseId = old('source_warehouse_id', $materialRequest->from_warehouse_id ?? '');
        $stockWarehouseId = $sourceWarehouseId ?: $centralWarehouse?->id;
        $warehouseId = old('warehouse_id', $materialRequest->to_warehouse_id);

        $materialCategories = Category::where('type', 'material')
            ->with(['materials' => function ($q) use ($stockWarehouseId) {
                $q->where('is_active', true)
                  ->with(['unit', 'inventories' => function ($iq) use ($stockWarehouseId) {
                      if ($stockWarehouseId) {
                          $iq->where('warehouse_id', $stockWarehouseId);
                      }
                  }])
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn($cat) => $cat->materials->isNotEmpty())
            ->values();

        $uncategorizedMaterials = Material::where('is_active', true)
            ->whereNull('category_id')
            ->with(['unit', 'inventories' => function ($iq) use ($sourceWarehouseId) {
                if ($sourceWarehouseId) {
                    $iq->where('warehouse_id', $sourceWarehouseId);
                }
            }])
            ->orderBy('name')
            ->get();

        $accessibleWhIds = $user->accessibleWarehouseIds();
        $warehouses = Warehouse::where('is_central', false)
            ->whereIn('id', $accessibleWhIds)
            ->orderBy('name')
            ->get();

        $sourceWarehouses = Warehouse::where('is_active', true)
            ->orderByRaw('is_central DESC')
            ->orderBy('name')
            ->get();

        $materialRequest->load(['items.material', 'items.category']);

        // Daftar kelompok barang per kategori untuk pilihan bertingkat
        $existingGroups = Material::whereNotNull('type')
            ->where('type', '!=', '')
            ->where('type', '!=', 'Material Proyek')
            ->select('category_id', 'type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('type')->values());

        $toolCategories = Category::where('type', 'tool')->orderBy('name')->get();

        return view('material-requests.edit', compact(
            'materialRequest',
            'materialCategories',
            'toolCategories',
            'uncategorizedMaterials',
            'warehouses',
            'warehouseId',
            'centralWarehouse',
            'sourceWarehouses',
            'sourceWarehouseId',
            'existingGroups'
        ));
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        $this->authorize('view material requests');

        if (!in_array($materialRequest->status, ['submitted', 'draft'])) {
            abort(403, 'PO Lapangan ini sudah diproses dan tidak dapat diubah.');
        }

        $user = auth()->user();
        if ($materialRequest->requested_by_user_id !== $user->id && !$user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit PO Lapangan ini.');
        }

        $originalNeededAt = $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('Y-m-d') : null;
        $neededAtRule = 'nullable|date';
        if ($request->filled('needed_at') && $request->needed_at !== $originalNeededAt) {
            $neededAtRule .= '|after_or_equal:today';
        }

        $validated = $request->validate([
            'warehouse_id'               => 'required|exists:warehouses,id',
            'source_warehouse_id'        => 'nullable|exists:warehouses,id',
            'needed_at'                  => $neededAtRule,
            'requested_as'               => 'nullable|string|max:50',
            'notes'                      => 'nullable|string',
            'quantities'                 => 'nullable|array',
            'specs'                      => 'nullable|array',
            'item_notes'                 => 'nullable|array',
            'item_notes.*'               => 'nullable|string|max:255',
            'usage_locations'            => 'nullable|array',
            'usage_dates'                => 'nullable|array',
            'custom_items'               => 'nullable|array',
            'custom_items.*.name'        => 'required_with:custom_items|string|max:255',
            'custom_items.*.item_type'   => 'nullable|in:material,tool',
            'custom_items.*.category_id' => 'nullable|exists:categories,id',
            'custom_items.*.type'        => 'nullable|string|max:100',
            'custom_items.*.unit'        => 'nullable|string|max:50',
            'custom_items.*.qty'         => 'required_with:custom_items|numeric|min:0.01',
            'custom_items.*.spec'        => 'nullable|string|max:255',
            'custom_items.*.location'    => 'nullable|string|max:255',
            'custom_items.*.notes'       => 'nullable|string|max:255',
            'custom_items.*.date'        => 'nullable|date',
        ]);

        if (!empty($validated['source_warehouse_id']) && (int) $validated['warehouse_id'] === (int) $validated['source_warehouse_id']) {
            return back()->withInput()->withErrors([
                'source_warehouse_id' => 'Sumber material tidak boleh sama dengan gudang pemohon.',
                'warehouse_id'        => 'Gudang pemohon tidak boleh sama dengan sumber material.',
            ]);
        }

        $toWarehouse = Warehouse::findOrFail($validated['warehouse_id']);
        if (!$user->hasAccessToWarehouse($toWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke gudang proyek ini.');
        }

        if ($toWarehouse->is_central) {
            return back()->withInput()->withErrors(['warehouse_id' => 'PO Lapangan hanya dapat ditujukan ke Gudang Proyek.']);
        }

        $sourceWarehouse = !empty($validated['source_warehouse_id'])
            ? Warehouse::findOrFail($validated['source_warehouse_id'])
            : null;

        $itemsData = [];
        if (!empty($validated['quantities'])) {
            foreach ($validated['quantities'] as $materialId => $qty) {
                $qty = (float) $qty;
                if ($qty <= 0) continue;
                $itemNote = trim($request->input("item_notes.{$materialId}") ?? '');
                $itemsData[] = [
                    'material_id'    => $materialId,
                    'qty_requested'  => $qty,
                    'notes'          => $itemNote ?: null,
                    'specification'  => trim($request->input("specs.{$materialId}") ?? ''),
                    'usage_location' => trim($request->input("usage_locations.{$materialId}") ?? ''),
                    'usage_date'     => $request->input("usage_dates.{$materialId}") ?: null,
                ];
            }
        }
        if (!empty($validated['custom_items'])) {
            foreach ($validated['custom_items'] as $cItem) {
                $qty = (float) ($cItem['qty'] ?? 0);
                if ($qty <= 0) continue;
                $name = trim($cItem['name'] ?? '');
                if ($name === '') continue;
                $customNote = trim($cItem['notes'] ?? '');
                $itemType = in_array($cItem['item_type'] ?? '', ['material', 'tool']) ? $cItem['item_type'] : 'material';
                $itemsData[] = [
                    'material_id'      => null,
                    'item_type'        => $itemType,
                    'custom_item_name' => $name,
                    'custom_item_type' => trim($cItem['type'] ?? '') ?: null,
                    'custom_item_unit' => trim($cItem['unit'] ?? ($itemType === 'tool' ? 'unit' : 'pcs')) ?: 'unit',
                    'category_id'      => !empty($cItem['category_id']) ? (int) $cItem['category_id'] : null,
                    'qty_requested'    => $qty,
                    'notes'            => $customNote ?: 'Manual - tidak ada di gudang sumber',
                    'specification'    => trim($cItem['spec'] ?? ''),
                    'usage_location'   => trim($cItem['location'] ?? ''),
                    'usage_date'       => $cItem['date'] ?? null,
                ];
            }
        }

        if (empty($itemsData)) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada material yang diminta (master atau manual).']);
        }

        try {
            $this->service->updateRequest(
                $materialRequest,
                $toWarehouse,
                $sourceWarehouse,
                $user,
                $itemsData,
                $validated['notes'] ?? null,
                $validated['needed_at'] ?? null,
                $validated['requested_as'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'general_error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('material-requests.show', $materialRequest)
            ->with('success', "PO Lapangan #{$materialRequest->request_number} berhasil diperbarui.");
    }
}
