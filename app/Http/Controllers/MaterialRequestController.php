<?php

namespace App\Http\Controllers;

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

        if (auth()->user()->hasRole('User')) {
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

        $warehouseId = session('active_warehouse_id');
        $materials   = Material::with('unit')->orderBy('name')->get();
        $warehouses  = Warehouse::orderBy('name')->get();

        return view('material-requests.create', compact('materials', 'warehouses', 'warehouseId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create material requests');

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'needed_at'    => 'required|date|after_or_equal:today',
            'notes'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.notes'       => 'nullable|string',
        ]);

        $mr = $this->service->create($validated, auth()->id());

        return redirect()->route('material-requests.show', $mr)
            ->with('success', "Permintaan material #{$mr->request_number} berhasil diajukan.");
    }

    public function show(MaterialRequest $materialRequest)
    {
        $this->authorize('view material requests');
        $materialRequest->load(['requester', 'warehouse', 'approver', 'items.material.unit']);

        return view('material-requests.show', compact('materialRequest'));
    }

    public function approve(MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        $this->service->approve($materialRequest, auth()->id());

        return back()->with('success', "Permintaan #{$materialRequest->request_number} disetujui.");
    }

    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        $this->authorize('approve material requests');

        $request->validate(['rejection_reason' => 'required|string']);
        $this->service->reject($materialRequest, auth()->id(), $request->rejection_reason);

        return back()->with('success', "Permintaan #{$materialRequest->request_number} ditolak.");
    }
}
