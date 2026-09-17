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

        // Scope to user's authorized warehouses if not Owner/Admin/Admin Gudang Pusat
        if (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat'])) {
            $userWarehouseIds = $user->warehouses->pluck('id')->toArray();
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

        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat'])
            ? Warehouse::orderBy('name')->get()
            : $user->warehouses;

        return view('material-usages.index', compact('usages', 'warehouses'));
    }

    public function create()
    {
        $this->authorize('create material usages');

        $user = auth()->user();
        $activeWarehouseId = request('warehouse_id') ?? session('active_warehouse_id') ?? $user->activeWarehouse()?->id;

        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat'])
            ? Warehouse::orderBy('name')->get()
            : $user->warehouses;

        $selectedWarehouse = Warehouse::find($activeWarehouseId) ?? $warehouses->first();

        // Get materials that have stock > 0 in this warehouse
        $materialsData = [];
        if ($selectedWarehouse) {
            $inventories = Inventory::with(['material.unit', 'material.category'])
                ->where('warehouse_id', $selectedWarehouse->id)
                ->where('quantity', '>', 0)
                ->get();

            $materialsData = $inventories->map(function ($inv) {
                return [
                    'id'       => $inv->material_id,
                    'name'     => $inv->material?->name,
                    'code'     => $inv->material?->code,
                    'stock'    => (float) $inv->quantity,
                    'unit'     => $inv->material?->unit?->abbreviation ?? 'unit',
                    'category' => $inv->material?->category?->name ?? 'Umum',
                ];
            })->values()->toArray();
        }

        return view('material-usages.create', compact('warehouses', 'selectedWarehouse', 'materialsData'));
    }

    public function store(Request $request)
    {
        $this->authorize('create material usages');

        $validated = $request->validate([
            'warehouse_id'   => 'required|exists:warehouses,id',
            'recipient_name' => 'required|string|max:255',
            'job_section'    => 'nullable|string|max:255',
            'usage_date'     => 'required|date',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.notes'       => 'nullable|string|max:255',
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

        $materialUsage->load(['warehouse', 'project', 'issuedBy', 'items.material.unit']);

        return view('material-usages.show', compact('materialUsage'));
    }

    public function print(MaterialUsage $materialUsage)
    {
        $this->authorize('view material usages');

        $materialUsage->load(['warehouse', 'project', 'issuedBy', 'items.material.unit']);

        return view('material-usages.print', compact('materialUsage'));
    }

    public function cancel(Request $request, MaterialUsage $materialUsage)
    {
        $this->authorize('cancel material usages');

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
