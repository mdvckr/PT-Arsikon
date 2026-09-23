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
        $originWarehouse = $warehouses->firstWhere('is_central', true) ?? $warehouses->first();

        // 1. Permintaan Material (MR) dari user / proyek (submitted, approved, partially_fulfilled)
        $materialRequests = \App\Models\MaterialRequest::with([
            'items.material.unit',
            'items.material.category',
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
        ])
        ->whereIn('status', ['submitted', 'approved', 'partially_fulfilled'])
        ->orderBy('id', 'desc')
        ->get()
        ->filter(function ($mr) {
            return $mr->items->contains(function ($it) {
                $approved = (float) $it->qty_approved > 0 ? (float) $it->qty_approved : (float) $it->qty_requested;
                return $approved - (float) $it->qty_fulfilled > 0;
            });
        })
        ->values();

        $mrsFormatted = $materialRequests->map(function ($mr) {
            $items = [];
            foreach ($mr->items as $it) {
                $approved = (float) $it->qty_approved > 0 ? (float) $it->qty_approved : (float) $it->qty_requested;
                $fulfilled = (float) $it->qty_fulfilled;
                $remaining = max(0, $approved - $fulfilled);
                if ($remaining <= 0) continue;

                $items[] = [
                    'material_id'      => $it->material_id,
                    'name'             => $it->displayName(),
                    'code'             => $it->material?->code ?? $it->material?->sku ?? '',
                    'unit'             => $it->displayUnit(),
                    'category'         => $it->material?->category?->name ?? 'Material Umum',
                    'qty_approved'     => $approved,
                    'qty_fulfilled'    => $fulfilled,
                    'remaining'        => $remaining,
                    'is_custom'        => $it->isCustom(),
                    'custom_item_name' => $it->custom_item_name,
                    'custom_item_unit' => $it->custom_item_unit,
                ];
            }

            $statusLabels = [
                'submitted'           => 'Menunggu Persetujuan',
                'approved'            => 'Disetujui',
                'partially_fulfilled' => 'Terkirim Sebagian',
            ];

            return [
                'id'                => $mr->id,
                'number'            => $mr->request_number,
                'requester'         => $mr->requestedBy?->name ?? 'User Proyek',
                'from_warehouse_id' => $mr->to_warehouse_id,   // Asal kirim = gudang penyedia (Pusat)
                'to_warehouse_id'   => $mr->from_warehouse_id, // Tujuan = gudang pemohon (Proyek)
                'from_warehouse'    => $mr->toWarehouse?->name ?? 'Gudang Pusat',
                'to_warehouse'      => $mr->fromWarehouse?->name ?? 'Gudang Proyek',
                'status'            => $mr->status,
                'status_label'      => $statusLabels[$mr->status] ?? $mr->status,
                'date'              => $mr->created_at?->format('d/m/Y') ?? '',
                'items'             => $items,
            ];
        })->values();

        // 2. Peminjaman Alat (ToolLoan / ToolAssignment) aktif / disetujui
        $toolLoans = \App\Models\ToolLoan::with([
            'items.tool.category',
            'fromWarehouse',
            'assignedBy',
            'approvedBy',
        ])
        ->whereIn('status', ['pending', 'active'])
        ->orderBy('id', 'desc')
        ->get();

        $tasFormatted = [];
        foreach ($toolLoans as $loan) {
            $items = [];
            foreach ($loan->items as $item) {
                $items[] = [
                    'id'               => $item->id,
                    'tool_id'          => $item->tool_id,
                    'tool_name'        => $item->tool?->name ?? 'Alat Kerja',
                    'tool_code'        => $item->tool?->code ?? '',
                    'category'         => $item->tool?->category?->name ?? 'Peralatan Kerja',
                    'quantity'         => (int) $item->quantity,
                    'from_warehouse_id'=> $loan->from_warehouse_id,
                    'from_warehouse'   => $loan->fromWarehouse?->name ?? 'Gudang',
                ];
            }
            $tasFormatted[] = [
                'id'                => $loan->id,
                'number'            => $loan->loan_number,
                'borrower'          => $loan->borrower_name,
                'location'          => $loan->location_name,
                'from_warehouse_id' => $loan->from_warehouse_id,
                'from_warehouse'    => $loan->fromWarehouse?->name ?? 'Gudang Pusat',
                'status'            => $loan->status,
                'date'              => $loan->created_at?->format('d/m/Y') ?? '',
                'items'             => $items,
            ];
        }
        $tasFormatted = collect($tasFormatted)->values();

        // URL Query Pre-selections
        $selectedMrId = $request->query('material_request_id');
        $selectedLoanId = $request->query('tool_loan_id');
        if (!$selectedLoanId && $request->query('tool_assignment_id')) {
            $assignment = \App\Models\ToolAssignment::find($request->query('tool_assignment_id'));
            $selectedLoanId = $assignment?->tool_loan_id;
        }

        // Eager load category, unit, inventories for materials
        $materials = \App\Models\Material::with(['unit', 'category', 'inventories'])
            ->orderBy('name')
            ->get();

        $materialsData = $materials->map(function ($m) {
            $stocks = [];
            foreach ($m->inventories as $inv) {
                $stocks[$inv->warehouse_id] = (float) $inv->quantity;
            }
            return [
                'id'          => $m->id,
                'name'        => $m->name . ($m->type ? " [{$m->type}]" : ''),
                'code'        => $m->code ?? $m->sku ?? '',
                'unit'        => $m->unit?->abbreviation ?? 'pcs',
                'category'    => $m->category?->name ?? 'Material Umum',
                'stocks'      => $stocks,
                'total_stock' => (float) $m->inventories->sum('quantity'),
            ];
        })->sortBy([['category', 'asc'], ['name', 'asc']])->values();

        $materialsGrouped = $materialsData->groupBy('category')->sortKeys();

        // Eager load category and inventories for tools
        $accessibleWarehouseIds = $user->accessibleWarehouseIds();
        $tools = Tool::with(['category', 'inventories'])
            ->whereHas('inventories', fn($q) => $q->whereIn('warehouse_id', $accessibleWarehouseIds))
            ->orderBy('name')
            ->get()
            ->unique('id');

        if ($tools->isEmpty()) {
            $tools = Tool::with(['category', 'inventories'])->where('is_active', true)->orderBy('name')->get();
        }

        $toolsData = $tools->map(function ($t) {
            $stocks = [];
            foreach ($t->inventories as $inv) {
                $stocks[$inv->warehouse_id] = (int) $inv->stock_available;
            }
            return [
                'id'          => $t->id,
                'name'        => $t->name . ($t->serial_number ? " (S/N: {$t->serial_number})" : ''),
                'code'        => $t->code ?? '',
                'unit'        => 'unit',
                'category'    => $t->category?->name ?? 'Peralatan Kerja',
                'stocks'      => $stocks,
                'total_stock' => (int) ($t->stock_available ?? 0),
            ];
        })->sortBy([['category', 'asc'], ['name', 'asc']])->values();

        $toolsGrouped = $toolsData->groupBy('category')->sortKeys();
        $toolsForDropdown = $toolsData;

        return view('distributions.create', compact(
            'materialRequests', 'warehouses', 'originWarehouse', 'materials',
            'tools', 'toolsForDropdown', 'materialsData', 'materialsGrouped',
            'toolsData', 'toolsGrouped', 'mrsFormatted', 'tasFormatted',
            'selectedMrId', 'selectedLoanId'
        ));
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

        $this->service->receive($distribution, $request->items, auth()->id(), $request->filled('surat_jalan') ? $request->surat_jalan : null);

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