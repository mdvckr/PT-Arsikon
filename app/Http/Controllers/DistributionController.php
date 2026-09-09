<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\MaterialRequest;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use App\Services\DistributionService;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    public function __construct(private DistributionService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view distributions');

        $warehouseId = session('active_warehouse_id');

        $query = Distribution::with(['fromWarehouse', 'toWarehouse', 'materialRequest', 'creator', 'items.tool'])
            ->when($warehouseId, fn($q) => $q->where(function ($q2) use ($warehouseId) {
                $q2->where('from_warehouse_id', $warehouseId)
                   ->orWhere('to_warehouse_id', $warehouseId);
            }));

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('distribution_number', 'like', "%{$request->search}%");
        }

        $distributions = $query->latest()->paginate(15)->withQueryString();

        return view('distributions.index', compact('distributions'));
    }

    public function create(Request $request)
    {
        $this->authorize('create distributions');

        // Sumber: Permintaan Material yang disetujui (belum terpenuhi semua)
        $materialRequests = MaterialRequest::with(['items.material.unit', 'fromWarehouse', 'toWarehouse'])
            ->whereIn('status', ['approved', 'partially_fulfilled'])
            ->orderBy('id', 'desc')
            ->get()
            ->filter(fn($mr) => $mr->items->contains(fn($it) => (float) $it->qty_approved - (float) $it->qty_fulfilled > 0))
            ->values();

        // Sumber: Pengajuan peminjaman alat (pending / sudah disetujui)
        $toolAssignments = ToolAssignment::with(['tool', 'fromWarehouse'])
            ->whereIn('status', ['pending', 'active'])
            ->orderBy('id', 'desc')
            ->get();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('distributions.create', compact('materialRequests', 'toolAssignments', 'warehouses'));
    }

    public function store(Request $request)
    {
        $this->authorize('create distributions');

        $validated = $request->validate([
            'from_warehouse_id'    => 'required|exists:warehouses,id',
            'to_warehouse_id'      => 'required|exists:warehouses,id|different:from_warehouse_id',
            'material_request_id'  => 'nullable|exists:material_requests,id',
            'tool_assignment_ids'  => 'nullable|array',
            'tool_assignment_ids.*'=> 'integer|exists:tool_assignments,id',
            'delivery_date'        => 'required|date',
            'driver_name'          => 'nullable|string|max:150',
            'vehicle_number'       => 'nullable|string|max:30',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.type'         => 'sometimes|in:material,tool',
            'items.*.material_id'  => 'required_without:items.*.tool_id|nullable|exists:materials,id',
            'items.*.tool_id'      => 'required_without:items.*.material_id|nullable|exists:tools,id',
            'items.*.tool_assignment_id' => 'nullable|exists:tool_assignments,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
        ]);

        $validated['tool_assignment_ids'] = $validated['tool_assignment_ids'] ?? [];

        $dist = $this->service->create($validated, auth()->id());

        if ($request->wantsJson() || $request->ajax()) {
            $dist->load(['fromWarehouse', 'toWarehouse', 'creator', 'items.material.unit', 'items.tool']);
            return response()->json([
                'success' => true,
                'message' => "Surat Jalan #{$dist->distribution_number} berhasil dibuat.",
                'distribution' => $dist,
                'redirect_url' => route('distributions.show', $dist)
            ]);
        }

        return redirect()->route('distributions.show', $dist)
            ->with('success', "Surat Jalan #{$dist->distribution_number} berhasil dibuat.");
    }

    public function show(Distribution $distribution)
    {
        $this->authorize('view distributions');
        $distribution->load([
            'fromWarehouse', 'toWarehouse', 'materialRequest', 'creator',
            'items.material.unit', 'items.tool', 'items.toolAssignment'
        ]);

        return view('distributions.show', compact('distribution'));
    }

    public function print(Distribution $distribution)
    {
        $this->authorize('view distributions');
        $distribution->load([
            'fromWarehouse', 'toWarehouse', 'materialRequest', 'creator',
            'shippedBy', 'receivedBy',
            'items.material.unit', 'items.tool', 'items.toolAssignment'
        ]);

        return view('distributions.print', compact('distribution'));
    }

    public function ship(Distribution $distribution)
    {
        $this->authorize('ship distributions');
        $this->service->ship($distribution, auth()->id());

        return back()->with('success', 'Surat jalan telah dikirim.');
    }

    public function receive(Request $request, Distribution $distribution)
    {
        $this->authorize('receive distributions');

        $request->validate([
            'items'                          => 'required|array',
            'items.*.distribution_item_id'   => 'required|exists:distribution_items,id',
            'items.*.received_quantity'      => 'required|numeric|min:0',
            'items.*.qty_damaged_or_lost'    => 'nullable|numeric|min:0',
        ]);

        $this->service->receive($distribution, $request->items, auth()->id());

        return back()->with('success', 'Penerimaan distribusi dicatat.');
    }
}