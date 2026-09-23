<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\Warehouse;
use App\Services\MaterialUsageService;
use Exception;
use Illuminate\Http\Request;

class MaterialUsageController extends Controller
{
    protected MaterialUsageService $usageService;

    public function __construct(MaterialUsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function index(Request $request)
    {
        $this->authorize('view material usages');

        $user = auth()->user();
        $query = MaterialUsage::with(['warehouse', 'project', 'issuedBy', 'items.material.unit']);

        // Scope to user's authorized warehouses if not Owner/Admin/Admin Gudang Pusat/Admin PO
        if (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            $userWarehouseIds = $user->accessibleWarehouseIds();
            $query->whereIn('warehouse_id', $userWarehouseIds);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('usage_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('job_section', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('usage_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('usage_date', '<=', $request->end_date);
        }

        $usages = $query->latest('usage_date')->latest('id')->paginate(15)->withQueryString();

        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])
            ? Warehouse::orderBy('name')->get()
            : Warehouse::whereIn('id', $user->accessibleWarehouseIds())->orderBy('name')->get();

        return view('material-usages.index', compact('usages', 'warehouses'));
    }

    public function create()
    {
        $this->authorize('create material usages');

        $user = auth()->user();
        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])
            ? Warehouse::orderBy('name')->get()
            : Warehouse::whereIn('id', $user->accessibleWarehouseIds())->orderBy('name')->get();

        $activeWarehouseId = request('warehouse_id') ?? session('active_warehouse_id') ?? $user->activeWarehouse()?->id;
        $selectedWarehouse = Warehouse::find($activeWarehouseId);

        if (!$selectedWarehouse || (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($selectedWarehouse))) {
            $selectedWarehouse = $warehouses->first();
        }

        // Get all active materials with their stock in this warehouse
        $materialsData = [];
        $materialsGrouped = collect();
        if ($selectedWarehouse) {
            $stocks = Inventory::where('warehouse_id', $selectedWarehouse->id)
                ->pluck('quantity', 'material_id')
                ->toArray();

            $materials = Material::with(['unit', 'category'])->where('is_active', true)->orderBy('name')->get();

            $materialsData = $materials->map(function ($m) use ($stocks) {
                $stock = isset($stocks[$m->id]) ? (float) $stocks[$m->id] : 0.0;
                return [
                    'id'       => $m->id,
                    'name'     => $m->name,
                    'code'     => $m->code ?? $m->sku ?? '',
                    'stock'    => $stock,
                    'unit'     => $m->unit?->abbreviation ?? 'unit',
                    'category' => $m->category?->name ?? 'Umum',
                ];
            })->values()->toArray();

            $materialsGrouped = collect($materialsData)->groupBy('category')->sortKeys();
        }

        $mrQuery = \App\Models\MaterialRequest::whereIn('status', ['approved', 'partially_fulfilled'])
            ->with(['items.material.unit', 'items.material.category', 'fromWarehouse', 'toWarehouse', 'requestedBy', 'approvedBy']);

        if ($selectedWarehouse) {
            $mrQuery->where(function ($q) use ($selectedWarehouse) {
                $q->where('from_warehouse_id', $selectedWarehouse->id)
                  ->orWhere('to_warehouse_id', $selectedWarehouse->id);
            });
        }

        $approvedMRs = $mrQuery->orderBy('id', 'desc')->get();

        return view('material-usages.create', compact('warehouses', 'selectedWarehouse', 'materialsData', 'materialsGrouped', 'approvedMRs'));
    }

    public function getMRDetails(Request $request, \App\Models\MaterialRequest $materialRequest)
    {
        $warehouseId = $request->query('warehouse_id');

        $materialRequest->load([
            'items.material.unit',
            'items.material.category',
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
        ]);

        $stocks = [];
        if ($warehouseId) {
            $materialIds = $materialRequest->items->pluck('material_id')->toArray();
            $stocks = Inventory::where('warehouse_id', $warehouseId)
                ->whereIn('material_id', $materialIds)
                ->pluck('quantity', 'material_id')
                ->toArray();
        }

        $items = $materialRequest->items->map(function ($item) use ($stocks) {
            $approved = (float) $item->qty_approved;
            $fulfilled = (float) $item->qty_fulfilled;
            $remaining = max(0, $approved - $fulfilled);
            $stock = isset($stocks[$item->material_id]) ? (float) $stocks[$item->material_id] : 0.0;

            return [
                'id'            => $item->id,
                'material_id'   => $item->material_id,
                'name'          => $item->material?->name ?? '-',
                'code'          => $item->material?->code ?? '-',
                'category'      => $item->material?->category?->name ?? 'Umum',
                'unit'          => $item->material?->unit?->abbreviation ?? 'unit',
                'qty_requested' => (float) $item->qty_requested,
                'qty_approved'  => $approved,
                'qty_fulfilled' => $fulfilled,
                'qty_remaining' => $remaining,
                'stock'         => $stock,
                'max_allowed'   => min($remaining, $stock),
                'notes'         => $item->notes,
            ];
        });

        return response()->json([
            'id'             => $materialRequest->id,
            'request_number' => $materialRequest->request_number,
            'status'         => $materialRequest->status,
            'requested_by'   => $materialRequest->requestedBy?->name ?? '-',
            'approved_by'    => $materialRequest->approvedBy?->name ?? '-',
            'from_warehouse' => $materialRequest->fromWarehouse?->name ?? '-',
            'to_warehouse'   => $materialRequest->toWarehouse?->name ?? '-',
            'created_at'     => $materialRequest->created_at ? $materialRequest->created_at->format('d/m/Y') : '-',
            'notes'          => $materialRequest->notes,
            'items'          => $items,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create material usages');

        $validated = $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'material_request_id' => 'nullable|exists:material_requests,id',
            'recipient_name'      => 'required|string|max:255',
            'job_section'         => 'nullable|string|max:255',
            'usage_date'          => 'required|date',
            'notes'               => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.material_id'      => 'nullable|exists:materials,id',
            'items.*.custom_item_name' => 'required_if:items.*.material_id,null|nullable|string|max:255',
            'items.*.custom_item_unit' => 'nullable|string|max:50',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.notes'            => 'nullable|string|max:255',
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        // Check user access to this warehouse
        if (!auth()->user()->hasAccessToWarehouse($warehouse)) {
            return back()->withInput()->withErrors(['warehouse_id' => 'Anda tidak memiliki akses ke gudang ini.']);
        }

        try {
            $usage = $this->usageService->createUsage($warehouse, auth()->user(), $validated);

            return redirect()->route('material-usages.show', $usage)
                ->with('success', "Pengeluaran material #{$usage->usage_number} berhasil dicatat & stok telah diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(MaterialUsage $materialUsage)
    {
        $this->authorize('view material usages');

        $user = auth()->user();
        if ($materialUsage->warehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($materialUsage->warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data pemakaian material di gudang ini.');
        }

        $materialUsage->load(['warehouse', 'project', 'issuedBy', 'materialRequest.requestedBy', 'materialRequest.approvedBy', 'items.material.unit', 'cancelledBy']);

        return view('material-usages.show', compact('materialUsage'));
    }

    public function print(MaterialUsage $materialUsage)
    {
        $this->authorize('view material usages');

        $user = auth()->user();
        if ($materialUsage->warehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($materialUsage->warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data pemakaian material di gudang ini.');
        }

        $materialUsage->load(['warehouse', 'project', 'issuedBy', 'materialRequest.requestedBy', 'materialRequest.approvedBy', 'items.material.unit']);

        return view('material-usages.print', compact('materialUsage'));
    }

    public function cancel(Request $request, MaterialUsage $materialUsage)
    {
        $this->authorize('cancel material usages');

        $user = auth()->user();
        if ($materialUsage->warehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($materialUsage->warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data pemakaian material di gudang ini.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            $this->usageService->cancelUsage(
                $materialUsage,
                auth()->user(),
                $request->cancellation_reason
            );

            return back()->with('success', "Pengeluaran material #{$materialUsage->usage_number} berhasil dibatalkan. Stok telah dikembalikan ke gudang.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
