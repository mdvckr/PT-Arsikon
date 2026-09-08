<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\MaterialRequest;
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

        $query = Distribution::with(['fromWarehouse', 'toWarehouse', 'materialRequest', 'creator'])
            ->when($warehouseId, fn($q) => $q->where(function ($q2) use ($warehouseId) {
                $q2->where('from_warehouse_id', $warehouseId)
                   ->orWhere('to_warehouse_id', $warehouseId);
            }));

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where('delivery_number', 'like', "%{$request->search}%");
        }

        $distributions = $query->latest()->paginate(15)->withQueryString();

        return view('distributions.index', compact('distributions'));
    }

    public function create(Request $request)
    {
        $this->authorize('create distributions');

        $mrId       = $request->material_request_id;
        $mr         = $mrId ? MaterialRequest::with(['items.material.unit', 'warehouse'])->findOrFail($mrId) : null;
        $warehouses = Warehouse::orderBy('name')->get();

        return view('distributions.create', compact('mr', 'warehouses'));
    }

    public function store(Request $request)
    {
        $this->authorize('create distributions');

        $validated = $request->validate([
            'from_warehouse_id'   => 'required|exists:warehouses,id',
            'to_warehouse_id'     => 'required|exists:warehouses,id|different:from_warehouse_id',
            'material_request_id' => 'nullable|exists:material_requests,id',
            'delivery_date'       => 'required|date',
            'driver_name'         => 'nullable|string|max:150',
            'vehicle_number'      => 'nullable|string|max:30',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ]);

        $dist = $this->service->create($validated, auth()->id());

        return redirect()->route('distributions.show', $dist)
            ->with('success', "Distribusi #{$dist->delivery_number} berhasil dibuat.");
    }

    public function show(Distribution $distribution)
    {
        $this->authorize('view distributions');
        $distribution->load(['fromWarehouse', 'toWarehouse', 'materialRequest', 'creator', 'items.material.unit']);

        return view('distributions.show', compact('distribution'));
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
            'items'                    => 'required|array',
            'items.*.distribution_item_id' => 'required|exists:distribution_items,id',
            'items.*.received_quantity'    => 'required|numeric|min:0',
        ]);

        $this->service->receive($distribution, $request->items, auth()->id());

        return back()->with('success', 'Penerimaan distribusi dicatat.');
    }
}
