<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\ToolManagementService;
use Illuminate\Http\Request;

class ToolAssignmentController extends Controller
{
    public function __construct(private ToolManagementService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view tool assignments');

        $query = ToolAssignment::with(['tool', 'assignedTo', 'fromWarehouse']);

        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('status', 'assigned');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->search) {
            $query->whereHas('tool', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"));
        }

        $assignments = $query->latest()->paginate(15)->withQueryString();

        return view('tool-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $this->authorize('create tool assignments');

        $activeWarehouseId = request('warehouse_id') ?? session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;

        $rawToolsQuery = Tool::with(['category', 'currentWarehouse'])
            ->where('status', 'available')
            ->where('is_active', true);

        if ($activeWarehouseId) {
            $rawToolsQuery->where('current_warehouse_id', $activeWarehouseId);
        }

        $rawTools = $rawToolsQuery->get();

        // Fallback: If no available tools found in the specific active warehouse, show all available tools
        if ($rawTools->isEmpty()) {
            $rawTools = Tool::with(['category', 'currentWarehouse'])
                ->where('status', 'available')
                ->where('is_active', true)
                ->get();
        }
        
        // Group by Tool Name to get grouped stock availability
        $groupedTools = $rawTools->groupBy('name')->map(function ($group) {
            return [
                'name'               => $group->first()->name,
                'category'           => $group->first()->category?->name ?? 'Lainnya',
                'current_warehouse'  => $group->first()->currentWarehouse?->name ?? 'Gudang Utama',
                'stock_available'    => $group->count(),
                'tools'              => $group->values(),
            ];
        })->values();

        $users      = User::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $selectedWarehouseId = $activeWarehouseId;

        return view('tool-assignments.create', compact('groupedTools', 'users', 'warehouses', 'selectedWarehouseId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tool assignments');

        $validated = $request->validate([
            'quantities'   => 'required|array',
            'borrower_name'=> 'required|string|max:255',
            'location_name'=> 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'assigned_at'  => 'required|date',
            'expected_return_at' => 'nullable|date|after_or_equal:assigned_at',
            'purpose'      => 'nullable|string',
        ]);

        // Find or fallback warehouse & user
        $warehouseId = $validated['warehouse_id'] ?? session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;
        $warehouse  = Warehouse::find($warehouseId) ?? Warehouse::first();
        $assignedBy = auth()->user();

        // Include manual borrower name and location name in purpose/notes
        $borrowerInfoNotes = "Peminjam Manual: {$validated['borrower_name']} | Lokasi Site: {$validated['location_name']}";
        if (!empty($validated['purpose'])) {
            $borrowerInfoNotes .= " | " . $validated['purpose'];
        }

        $totalAssignedCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $warehouse, $assignedBy, $borrowerInfoNotes, &$totalAssignedCount) {
            foreach ($validated['quantities'] as $toolName => $qty) {
                $qty = (int) $qty;
                if ($qty > 0) {
                    $availableTools = Tool::where('name', $toolName)
                        ->where('status', 'available')
                        ->where('is_active', true)
                        ->limit($qty)
                        ->get();

                    foreach ($availableTools as $tool) {
                        $this->service->assignTool(
                            $tool,
                            $warehouse,
                            $assignedBy,
                            null,
                            null,
                            $validated['expected_return_at'] ?? null,
                            $borrowerInfoNotes
                        );
                        $totalAssignedCount++;
                    }
                }
            }
        });

        if ($totalAssignedCount === 0) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada alat yang ingin dipinjam.']);
        }

        return redirect()->route('tool-assignments.index')
            ->with('success', "Berhasil meminjamkan total {$totalAssignedCount} unit alat.");
    }

    public function show(ToolAssignment $toolAssignment)
    {
        $this->authorize('view tool assignments');
        $toolAssignment->load(['tool', 'assignedTo', 'fromWarehouse', 'assignedBy']);

        return view('tool-assignments.show', compact('toolAssignment'));
    }

    public function return(Request $request, ToolAssignment $toolAssignment)
    {
        $this->authorize('return tool assignments');

        $request->validate([
            'returned_at' => 'required|date',
            'condition'   => 'required|in:good,damaged,under_maintenance',
            'notes'       => 'nullable|string',
        ]);

        $this->service->returnTool(
            $toolAssignment,
            auth()->user(),
            $request->condition,
            $request->notes
        );

        return back()->with('success', 'Alat berhasil dikembalikan.');
    }
}
