<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function __construct(private StockOpnameService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view stock opname');

        $warehouseId = session('active_warehouse_id');

        $opnames = StockOpname::with(['warehouse', 'creator', 'approver'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->paginate(15);

        return view('stock-opnames.index', compact('opnames'));
    }

    public function create()
    {
        $this->authorize('create stock opname');

        $warehouseId = session('active_warehouse_id');
        $warehouses  = Warehouse::orderBy('name')->get();
        $inventory   = $warehouseId
            ? Inventory::with(['material.unit'])->where('warehouse_id', $warehouseId)->get()
            : collect();

        return view('stock-opnames.create', compact('warehouses', 'inventory', 'warehouseId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create stock opname');

        $validated = $request->validate([
            'warehouse_id'             => 'required|exists:warehouses,id',
            'opname_date'              => 'required|date',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.inventory_id'     => 'required|exists:inventories,id',
            'items.*.physical_quantity' => 'required|numeric|min:0',
        ]);

        $opname = $this->service->create($validated, auth()->id());

        return redirect()->route('stock-opnames.show', $opname)
            ->with('success', "Stock Opname #{$opname->opname_number} berhasil dibuat.");
    }

    public function show(StockOpname $stockOpname)
    {
        $this->authorize('view stock opname');
        $stockOpname->load(['warehouse', 'creator', 'approver', 'items.inventory.material.unit']);

        return view('stock-opnames.show', compact('stockOpname'));
    }

    public function approve(StockOpname $stockOpname)
    {
        $this->authorize('approve stock opname');
        $this->service->approve($stockOpname, auth()->id());

        return back()->with('success', 'Stock Opname disetujui dan stok disesuaikan.');
    }

    public function reject(Request $request, StockOpname $stockOpname)
    {
        $this->authorize('approve stock opname');
        $request->validate(['rejection_reason' => 'required|string']);
        $this->service->reject($stockOpname, auth()->id(), $request->rejection_reason);

        return back()->with('success', 'Stock Opname ditolak.');
    }
}
