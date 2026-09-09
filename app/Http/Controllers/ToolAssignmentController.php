<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ToolAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view tool assignments');

        $query = ToolAssignment::with(['tool', 'assignedTo', 'fromWarehouse']);

        if ($request->status) {
            // Normalisasi 'assigned' (legacy UI) menjadi 'active'
            $status = $request->status === 'assigned' ? 'active' : $request->status;
            $query->where('status', $status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('tool', fn($q2) => $q2->where('name', 'like', "%{$request->search}%")
                      ->orWhere('code', 'like', "%{$request->search}%"))
                  ->orWhere('notes', 'like', "%{$request->search}%");
            });
        }

        $assignments = $query->latest()->paginate(15)->withQueryString();

        return view('tool-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $this->authorize('create tool assignments');

        $activeWarehouseId = request('warehouse_id') ?? session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;

        // Ambil semua alat aktif yang punya stok tersedia
        $toolsQuery = Tool::where('is_active', true)->where('stock_available', '>', 0);

        $groupedTools = $toolsQuery->get()->map(function ($tool) {
            return [
                'id'                => $tool->id,
                'name'              => $tool->name,
                'code'              => $tool->code,
                'category'          => $tool->type ?? 'Lainnya',
                'current_warehouse' => $tool->currentWarehouse?->name ?? 'Gudang Utama',
                'stock_available'   => $tool->stock_available,
            ];
        });

        $users      = User::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $selectedWarehouseId = $activeWarehouseId;

        return view('tool-assignments.create', compact('groupedTools', 'users', 'warehouses', 'selectedWarehouseId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tool assignments');

        $validated = $request->validate([
            'quantities'         => 'required|array',
            'borrower_name'      => 'required|string|max:255',
            'location_name'      => 'required|string|max:255',
            'warehouse_id'       => 'nullable|exists:warehouses,id',
            'assigned_at'        => 'required|date',
            'expected_return_at' => 'nullable|date|after_or_equal:assigned_at',
            'purpose'            => 'nullable|string',
        ]);

        $warehouseId = $validated['warehouse_id'] ?? session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;
        $warehouse   = Warehouse::find($warehouseId) ?? Warehouse::first();
        $assignedBy  = auth()->user();

        $borrowerInfoNotes = "Peminjam: {$validated['borrower_name']} | Lokasi: {$validated['location_name']}";
        if (!empty($validated['purpose'])) {
            $borrowerInfoNotes .= " | " . $validated['purpose'];
        }

        $totalAssignedCount = 0;

        DB::transaction(function () use ($validated, $warehouse, $assignedBy, $borrowerInfoNotes, &$totalAssignedCount) {
            foreach ($validated['quantities'] as $toolId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;

                $tool = Tool::find($toolId);
                if (!$tool) continue;

                // Cek apakah stok tersedia cukup
                if ($tool->stock_available < $qty) {
                    $qty = $tool->stock_available; // Pinjam sebanyak yang tersedia
                }

                if ($qty <= 0) continue;

                // Buat 1 record assignment per tool (status: menunggu persetujuan admin)
                $assignmentNumber = 'TA-' . strtoupper(substr(uniqid(), -6));
                ToolAssignment::create([
                    'assignment_number'   => $assignmentNumber,
                    'tool_id'             => $tool->id,
                    'quantity'            => $qty,
                    'from_warehouse_id'   => $warehouse->id,
                    'assigned_by_user_id' => $assignedBy->id,
                    'assigned_at'         => $validated['assigned_at'],
                    'expected_return_at'  => $validated['expected_return_at'] ?? null,
                    'status'              => 'pending',
                    'notes'               => $borrowerInfoNotes,
                ]);

                // Stok BELUM dikurangi; menunggu persetujuan Admin
                $totalAssignedCount += $qty;
            }
        });

        if ($totalAssignedCount === 0) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada alat yang ingin dipinjam.']);
        }

        return redirect()->route('tool-assignments.index')
            ->with('success', "Pengajuan pinjam {$totalAssignedCount} unit alat berhasil dibuat & menunggu persetujuan Admin.");
    }

    public function show(ToolAssignment $toolAssignment)
    {
        $this->authorize('view tool assignments');
        $toolAssignment->load(['tool', 'assignedTo', 'fromWarehouse', 'assignedBy', 'approvedBy']);

        return view('tool-assignments.show', compact('toolAssignment'));
    }

    public function approve(ToolAssignment $toolAssignment)
    {
        $this->authorize('approve tool assignments');

        if ($toolAssignment->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus menunggu persetujuan yang dapat disetujui.');
        }

        // Pastikan stok tersedia masih cukup saat persetujuan
        $tool      = $toolAssignment->tool;
        $available = (int) $tool->stock_available;
        if ($available < $toolAssignment->quantity) {
            return back()->with('error', "Stok tersedia ({$available}) kurang dari jumlah pinjam ({$toolAssignment->quantity}).");
        }

        DB::transaction(function () use ($toolAssignment, $tool) {
            $toolAssignment->update([
                'status'              => 'active',
                'approved_by_user_id' => auth()->id(),
                'approved_at'         => now(),
            ]);

            // Kurangi stok tersedia hanya setelah disetujui
            $tool->borrow($toolAssignment->quantity);
        });

        return back()->with('success', 'Pengajuan peminjaman alat disetujui & stok alat dikurangi.');
    }

    public function reject(Request $request, ToolAssignment $toolAssignment)
    {
        $this->authorize('approve tool assignments');

        if ($toolAssignment->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus menunggu persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $toolAssignment->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Pengajuan peminjaman alat ditolak.');
    }

    public function return(Request $request, ToolAssignment $toolAssignment)
    {
        $this->authorize('return tool assignments');

        $request->validate([
            'returned_at' => 'required|date',
            'condition'   => 'required|in:good,damaged,under_maintenance',
            'notes'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $toolAssignment) {
            $qty  = $toolAssignment->quantity ?? 1;
            $tool = $toolAssignment->tool;

            // Kembalikan stok sesuai kondisi
            $tool->returnStock($qty, $request->condition);

            // Update status assignment
            $toolAssignment->update([
                'returned_at' => $request->returned_at,
                'status'      => 'returned',
                'notes'       => $toolAssignment->notes . ' | Dikembalikan: ' . $request->condition . ($request->notes ? " ({$request->notes})" : ''),
            ]);
        });

        return back()->with('success', 'Alat berhasil dikembalikan.');
    }
}
