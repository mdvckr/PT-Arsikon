<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\MaterialRequest;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use App\Models\Tool;
use App\Models\ToolInventory;
use App\Services\DistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributionController extends Controller
{
    public function __construct(private DistributionService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view distributions');

        $user        = auth()->user();
        $warehouseId = session('active_warehouse_id');

        $query = Distribution::with(['fromWarehouse', 'toWarehouse', 'materialRequest', 'creator', 'items.tool']);

        // Non-admin: wajib scope ke warehouse yang dimiliki user
        if (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            $userWhIds = $user->accessibleWarehouseIds();
            $query->where(function ($q) use ($userWhIds) {
                $q->whereIn('from_warehouse_id', $userWhIds)
                  ->orWhereIn('to_warehouse_id', $userWhIds);
            });
        } elseif ($warehouseId) {
            // Admin: filter opsional berdasarkan active warehouse session
            $query->where(function ($q2) use ($warehouseId) {
                $q2->where('from_warehouse_id', $warehouseId)
                   ->orWhere('to_warehouse_id', $warehouseId);
            });
        }

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
        $user = Auth::user();
        $warehouses = $this->accessibleWarehouses();

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

        $materials = \App\Models\Material::with('unit', 'category')->orderBy('name')->get();

        // Filter tools based on user role
        $accessibleWarehouseIds = $user->accessibleWarehouseIds();
        $tools = Tool::with('inventories')
            ->whereHas('inventories', fn($q) => $q->whereIn('warehouse_id', $accessibleWarehouseIds))
            ->orderBy('name')
            ->get()
            ->unique('id');

        // Tools available for dropdown (available stock > 0)
        $toolsForDropdown = $tools->filter(fn($t) => $t->inventories->where('stock_available', '>', 0)->isNotEmpty())
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name . ' · Sisa stok: ' . $t->inventories->sum('stock_available') . ' unit',
                'code' => $t->code,
                'available' => (int) $t->inventories->sum('stock_available'),
            ])
            ->values();

        return view('distributions.create', compact('materialRequests', 'toolAssignments', 'warehouses', 'materials', 'tools', 'toolsForDropdown'));
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
            'items'                      => 'required|array|min:1',
            'items.*.type'               => 'sometimes|in:material,tool,custom',
            'items.*.material_id'        => 'nullable|exists:materials,id',
            'items.*.tool_id'            => 'nullable|exists:tools,id',
            'items.*.tool_assignment_id' => 'nullable|exists:tool_assignments,id',
            'items.*.custom_item_name'   => 'required_if:items.*.type,custom|nullable|string|max:255',
            'items.*.custom_item_unit'   => 'nullable|string|max:50',
            'items.*.quantity'           => 'required|numeric|min:0.01',
        ]);

        $validated['tool_assignment_ids'] = $validated['tool_assignment_ids'] ?? [];

        try {
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
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
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

        if ($distribution->status !== 'draft') {
            return redirect()->route('distributions.show', $distribution)
                ->with('info', "Surat Jalan #{$distribution->distribution_number} sudah dikirim atau tidak berstatus draft.");
        }

        // Gudang Pusat & Proyek sudah terpisah — ship harus dari gudang asal yang terdaftar, bukan session
        if (!$distribution->fromWarehouse || !$distribution->toWarehouse) {
            return redirect()->route('distributions.show', $distribution)
                ->with('error', 'Gudang asal/tujuan surat jalan tidak ditemukan. Periksa data gudang.');
        }

        if ($distribution->from_warehouse_id === $distribution->to_warehouse_id) {
            return redirect()->route('distributions.show', $distribution)
                ->with('error', 'Gudang asal dan tujuan tidak boleh sama.');
        }

        try {
            $this->service->ship($distribution, auth()->id());

            return redirect()->route('distributions.show', $distribution)
                ->with('success', "Surat jalan #{$distribution->distribution_number} berhasil dikirim dari {$distribution->fromWarehouse->name} ke {$distribution->toWarehouse->name}. Stok Pusat telah dikurangi.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Distribution ship failed: '.$e->getMessage(), ['distribution_id' => $distribution->id, 'user_id' => auth()->id()]);
            return redirect()->route('distributions.show', $distribution)
                ->with('error', $e->getMessage());
        }
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

    // ── Helper ────────────────────────────────────────────────────────────

    protected function accessibleWarehouses()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::orderBy('name')->get();
        }

        return Warehouse::whereIn('id', $user->accessibleWarehouseIds())->orderBy('name')->get();
    }
}