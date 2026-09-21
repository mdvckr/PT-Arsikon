<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

        $query = MaterialRequest::with(['requestedBy', 'fromWarehouse', 'toWarehouse', 'approvedBy']);

        if (auth()->user()->hasRole(['User', 'Admin Gudang Proyek', 'Karyawan'])) {
            $query->where('requested_by_user_id', auth()->id());
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('request_number', 'like', "%{$request->search}%");
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        return view('material-requests.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize('create material requests');

        $warehouseId = session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;

        $centralWarehouse = Warehouse::where('is_central', true)->first();
        $centralWarehouseId = $centralWarehouse?->id;

        // Material dikelompokkan per kategori, stok ditampilkan terpisah Pusat vs Proyek (gudang tidak saling terhubung)
        $materialCategories = Category::where('type', 'material')
            ->with(['materials' => function ($q) use ($centralWarehouseId) {
                $q->where('is_active', true)
                  ->with(['unit', 'inventories' => function ($iq) use ($centralWarehouseId) {
                      if ($centralWarehouseId) {
                          $iq->where('warehouse_id', $centralWarehouseId);
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
            ->with(['unit', 'inventories' => function ($iq) use ($centralWarehouseId) {
                if ($centralWarehouseId) {
                    $iq->where('warehouse_id', $centralWarehouseId);
                }
            }])
            ->orderBy('name')
            ->get();

        // Gudang pemohon harus merupakan Gudang Proyek (bukan Gudang Pusat) — stok terpisah
        $warehouses = Warehouse::where('is_central', false)->orderBy('name')->get();
        if ($warehouseId) {
            $selectedWh = Warehouse::find($warehouseId);
            if ($selectedWh && $selectedWh->is_central) {
                $warehouseId = $warehouses->first()?->id;
            }
        } else {
            $warehouseId = $warehouses->first()?->id;
        }

        return view('material-requests.create', compact('materialCategories', 'uncategorizedMaterials', 'warehouses', 'warehouseId', 'centralWarehouse'));
    }

    public function store(Request $request)
    {
        $this->authorize('create material requests');

        $validated = $request->validate([
            'warehouse_id'              => 'required|exists:warehouses,id',
            'needed_at'                 => 'nullable|date|after_or_equal:today',
            'notes'                     => 'nullable|string',
            'quantities'                => 'nullable|array',
            'custom_items'              => 'nullable|array',
            'custom_items.*.name'       => 'required_with:custom_items|string|max:255',
            'custom_items.*.unit'       => 'nullable|string|max:50',
            'custom_items.*.qty'        => 'required_with:custom_items|numeric|min:0.01',
        ]);

        // Build items dari quantities[material_id] => qty (master) + custom_items manual
        $itemsData = [];
        if (!empty($validated['quantities'])) {
            foreach ($validated['quantities'] as $materialId => $qty) {
                $qty = (float) $qty;
                if ($qty <= 0) continue;
                $itemsData[] = [
                    'material_id'   => $materialId,
                    'qty_requested' => $qty,
                    'notes'         => null,
                ];
            }
        }
        if (!empty($validated['custom_items'])) {
            foreach ($validated['custom_items'] as $cItem) {
                $qty = (float) ($cItem['qty'] ?? 0);
                if ($qty <= 0) continue;
                $name = trim($cItem['name'] ?? '');
                if ($name === '') continue;
                $itemsData[] = [
                    'material_id'      => null,
                    'custom_item_name' => $name,
                    'custom_item_unit' => trim($cItem['unit'] ?? 'unit') ?: 'unit',
                    'qty_requested'    => $qty,
                    'notes'            => 'Manual dari Gudang Proyek - tidak ada di Pusat',
                ];
            }
        }

        if (empty($itemsData)) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada material yang diminta (master atau manual).']);
        }

        $fromWarehouse = Warehouse::findOrFail($validated['warehouse_id']);

        if ($fromWarehouse->is_central) {
            return back()->withInput()->withErrors(['warehouse_id' => 'Permintaan material hanya dapat diajukan dari Gudang Proyek.']);
        }

        try {
            $mr = $this->service->createRequest(
                $fromWarehouse,
                auth()->user(),
                $itemsData,
                true,
                $validated['notes'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['warehouse_id' => $e->getMessage()]);
        }

        return redirect()->route('material-requests.show', $mr)
            ->with('success', "Permintaan material #{$mr->request_number} berhasil diajukan.");
    }

    public function show(MaterialRequest $materialRequest)
    {
        $this->authorize('view material requests');
        $materialRequest->load([
            'requestedBy', 
            'fromWarehouse', 
            'toWarehouse', 
            'approvedBy', 
            'items.material.unit', 
            'items.material.category',
            'materialUsages.issuedBy',
            'materialUsages.warehouse'
        ]);

        return view('material-requests.show', compact('materialRequest'));
    }

    public function approve(MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        $this->service->approveRequest($materialRequest, auth()->user());

        return back()->with('success', "Permintaan #{$materialRequest->request_number} disetujui.");
    }

    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        $request->validate(['rejection_reason' => 'required|string']);
        $this->service->rejectRequest($materialRequest, auth()->user(), $request->rejection_reason);

        return back()->with('success', "Permintaan #{$materialRequest->request_number} ditolak.");
    }
}
