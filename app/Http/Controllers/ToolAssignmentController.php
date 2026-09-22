<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolLoan;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\ToolInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ToolAssignmentController extends Controller
{
    protected ToolInventoryService $toolInvService;

    public function __construct(ToolInventoryService $toolInvService)
    {
        $this->toolInvService = $toolInvService;
    }

    public function index(Request $request)
    {
        $this->authorize('view tool assignments');

        $user = auth()->user();
        $query = ToolLoan::with(['items.tool.category', 'fromWarehouse', 'assignedBy']);

        // Scope to user's authorized warehouses if not Owner/Admin/Admin Gudang Pusat/Admin PO
        if (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            $userWarehouseIds = $user->accessibleWarehouseIds();
            $query->whereIn('from_warehouse_id', $userWarehouseIds);
        }

        if ($request->status) {
            $status = $request->status === 'assigned' ? 'active' : $request->status;
            $query->where('status', $status);
        }

        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('loan_number', 'like', "%{$searchTerm}%")
                  ->orWhere('borrower_name', 'like', "%{$searchTerm}%")
                  ->orWhere('borrower_phone', 'like', "%{$searchTerm}%")
                  ->orWhere('location_name', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhereHas('items.tool', fn($q2) => $q2->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('code', 'like', "%{$searchTerm}%"));
            });
        }

        $loans = $query->latest()->paginate(20)->withQueryString();

        return view('tool-assignments.index', compact('loans'));
    }

    public function create()
    {
        $this->authorize('create tool assignments');

        $user = auth()->user();
        $warehouses = $user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])
            ? Warehouse::orderBy('name')->get()
            : Warehouse::whereIn('id', $user->accessibleWarehouseIds())->orderBy('name')->get();

        $activeWarehouseId = request('warehouse_id') ?? session('active_warehouse_id') ?? $user->activeWarehouse()?->id;
        $selectedWarehouse = Warehouse::find($activeWarehouseId);

        if (!$selectedWarehouse || (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($selectedWarehouse))) {
            $selectedWarehouse = $warehouses->first();
        }

        $selectedWarehouseId = $selectedWarehouse?->id;

        // Ambil alat aktif yang punya stok tersedia di gudang ini, dikelompokkan per kategori
        $toolCategories = Category::where('type', 'tool')
            ->with(['tools' => function ($q) use ($selectedWarehouseId) {
                $q->where('is_active', true)
                  ->whereHas('inventories', function ($iq) use ($selectedWarehouseId) {
                      if ($selectedWarehouseId) {
                          $iq->where('warehouse_id', $selectedWarehouseId)->where('stock_available', '>', 0);
                      } else {
                          $iq->where('stock_available', '>', 0);
                      }
                  })
                  ->with(['inventories' => function ($iq) use ($selectedWarehouseId) {
                      if ($selectedWarehouseId) {
                          $iq->where('warehouse_id', $selectedWarehouseId);
                      }
                  }])
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn($cat) => $cat->tools->isNotEmpty())
            ->values();

        // Alat tanpa kategori yang masih punya stok di gudang terpilih
        $uncategorizedTools = Tool::where('is_active', true)
            ->whereNull('category_id')
            ->whereHas('inventories', function ($iq) use ($selectedWarehouseId) {
                if ($selectedWarehouseId) {
                    $iq->where('warehouse_id', $selectedWarehouseId)->where('stock_available', '>', 0);
                } else {
                    $iq->where('stock_available', '>', 0);
                }
            })
            ->with(['inventories' => function ($iq) use ($selectedWarehouseId) {
                if ($selectedWarehouseId) {
                    $iq->where('warehouse_id', $selectedWarehouseId);
                }
            }])
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        return view('tool-assignments.create', compact('toolCategories', 'uncategorizedTools', 'users', 'warehouses', 'selectedWarehouseId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tool assignments');

        $validated = $request->validate([
            'quantities'         => 'required|array',
            'borrower_name'      => 'required|string|max:255',
            'borrower_phone'     => 'nullable|string|max:50',
            'location_name'      => 'required|string|max:255',
            'warehouse_id'       => 'nullable|exists:warehouses,id',
            'assigned_at'        => 'required|date',
            'expected_return_at' => 'nullable|date',
            'purpose'            => 'nullable|string',
        ]);

        $warehouseId = $validated['warehouse_id'] ?? session('active_warehouse_id') ?? auth()->user()->activeWarehouse()?->id;
        $warehouse   = Warehouse::find($warehouseId) ?? Warehouse::first();
        $assignedBy  = auth()->user();

        // Validasi hak akses user ke gudang ini
        if ($warehouse && !$assignedBy->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$assignedBy->hasAccessToWarehouse($warehouse)) {
            return back()->withInput()->withErrors(['warehouse_id' => 'Anda tidak memiliki akses ke gudang ini.']);
        }

        $borrowerInfoNotes = "Peminjam: {$validated['borrower_name']} | Lokasi: {$validated['location_name']}";
        if (!empty($validated['borrower_phone'])) {
            $borrowerInfoNotes .= " | Kontak: {$validated['borrower_phone']}";
        }
        if (!empty($validated['purpose'])) {
            $borrowerInfoNotes .= " | " . $validated['purpose'];
        }

        $totalAssignedCount = 0;
        $toolLoan = null;

        DB::transaction(function () use ($validated, $warehouse, $assignedBy, $borrowerInfoNotes, &$totalAssignedCount, &$toolLoan) {
            $loanNumber = ToolLoan::generateLoanNumber();

            $toolLoan = ToolLoan::create([
                'loan_number'         => $loanNumber,
                'from_warehouse_id'   => $warehouse->id,
                'assigned_by_user_id' => $assignedBy->id,
                'borrower_name'       => $validated['borrower_name'],
                'borrower_phone'      => $validated['borrower_phone'] ?? null,
                'location_name'       => $validated['location_name'],
                'assigned_at'         => $validated['assigned_at'],
                'expected_return_at'  => $validated['expected_return_at'] ?? null,
                'status'              => 'pending',
                'notes'               => $borrowerInfoNotes,
            ]);

            foreach ($validated['quantities'] as $toolId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;

                $tool = Tool::find($toolId);
                if (!$tool) continue;

                // Cek ketersediaan stok di gudang asal
                $inv = \App\Models\ToolInventory::where('warehouse_id', $warehouse->id)->where('tool_id', $tool->id)->first();
                $availInWh = $inv ? (int)$inv->stock_available : ((int)$tool->stock_available);
                if ($availInWh < $qty) {
                    $qty = $availInWh;
                }

                if ($qty <= 0) continue;

                $assignmentNumber = 'TA-' . strtoupper(substr(uniqid(), -6));
                ToolAssignment::create([
                    'tool_loan_id'        => $toolLoan->id,
                    'assignment_number'   => $assignmentNumber,
                    'tool_id'             => $tool->id,
                    'quantity'            => $qty,
                    'from_warehouse_id'   => $warehouse->id,
                    'assigned_by_user_id' => $assignedBy->id,
                    'borrower_name'       => $validated['borrower_name'],
                    'borrower_phone'      => $validated['borrower_phone'] ?? null,
                    'location_name'       => $validated['location_name'],
                    'assigned_at'         => $validated['assigned_at'],
                    'expected_return_at'  => $validated['expected_return_at'] ?? null,
                    'status'              => 'pending',
                    'notes'               => $borrowerInfoNotes,
                ]);

                $totalAssignedCount += $qty;
            }

            if ($totalAssignedCount === 0) {
                throw new \Exception('Silakan masukkan jumlah min. 1 pada alat yang ingin dipinjam.');
            }
        });

        if (!$toolLoan || $totalAssignedCount === 0) {
            return back()->withInput()->withErrors(['quantities' => 'Silakan masukkan jumlah min. 1 pada alat yang ingin dipinjam.']);
        }

        // Notifikasi ke Approvers (Owner, Admin, Admin Gudang Pusat, dan Admin Gudang Proyek terkait)
        \App\Services\NotificationHelper::notifyApprovers(
            "Pengajuan Peminjaman Alat: #{$toolLoan->loan_number}",
            "{$assignedBy->name} mengajukan peminjaman {$totalAssignedCount} unit alat untuk {$validated['borrower_name']} ({$validated['location_name']}).",
            "approval_needed",
            route('tool-assignments.show', $toolLoan->id),
            $warehouse->id
        );

        return redirect()->route('tool-assignments.index')
            ->with('success', "Pengajuan peminjaman #{$toolLoan->loan_number} ({$totalAssignedCount} unit alat) berhasil dibuat & menunggu persetujuan Admin.");
    }

    public function show($id)
    {
        $this->authorize('view tool assignments');

        // Dukung baik ToolLoan id maupun legacy ToolAssignment id
        $toolLoan = ToolLoan::with(['items.tool.category', 'fromWarehouse', 'assignedBy', 'approvedBy', 'cancelledBy'])->find($id);

        if (!$toolLoan) {
            $legacyAssignment = ToolAssignment::find($id);
            if ($legacyAssignment && $legacyAssignment->tool_loan_id) {
                return redirect()->route('tool-assignments.show', $legacyAssignment->tool_loan_id);
            }
            abort(404, 'Data peminjaman tidak ditemukan.');
        }

        $user = auth()->user();
        if ($toolLoan->fromWarehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($toolLoan->fromWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data peminjaman di gudang ini.');
        }

        return view('tool-assignments.show', compact('toolLoan'));
    }

    public function approve($id)
    {
        $this->authorize('approve tool assignments');

        $toolLoan = ToolLoan::with(['items.tool', 'fromWarehouse', 'assignedBy'])->findOrFail($id);

        $user = auth()->user();
        if ($toolLoan->fromWarehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($toolLoan->fromWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data peminjaman di gudang ini.');
        }

        if ($toolLoan->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus menunggu persetujuan yang dapat disetujui.');
        }

        // Cek ketersediaan stok untuk semua alat di gudang asal
        $warehouse = $toolLoan->fromWarehouse;
        foreach ($toolLoan->items as $item) {
            $tool = $item->tool;
            $inv = $warehouse ? \App\Models\ToolInventory::where('warehouse_id', $warehouse->id)->where('tool_id', $tool->id)->first() : null;
            $available = $inv ? (int) $inv->stock_available : (int) ($tool?->stock_available ?? 0);
            if ($available < $item->quantity) {
                return back()->with('error', "Stok alat '{$tool?->name}' di {$warehouse?->name} tidak cukup (Tersedia: {$available}, Dibutuhkan: {$item->quantity}).");
            }
        }

        DB::transaction(function () use ($toolLoan) {
            $toolLoan->update([
                'status'              => 'active',
                'approved_by_user_id' => auth()->id(),
                'approved_at'         => now(),
            ]);

            foreach ($toolLoan->items as $item) {
                $item->update([
                    'status'              => 'active',
                    'approved_by_user_id' => auth()->id(),
                    'approved_at'         => now(),
                ]);

                $warehouse = $item->fromWarehouse ?? $toolLoan->fromWarehouse ?? $item->tool?->currentWarehouse;
                if ($warehouse && $item->tool) {
                    $this->toolInvService->borrow($warehouse, $item->tool, (int) $item->quantity);
                }
            }
        });

        // Notifikasi ke pemohon
        if ($toolLoan->assignedBy) {
            \App\Services\NotificationHelper::notifyUser(
                $toolLoan->assignedBy,
                "Peminjaman Alat Disetujui: #{$toolLoan->loan_number}",
                "Pengajuan peminjaman alat #{$toolLoan->loan_number} telah disetujui oleh " . auth()->user()->name . ".",
                "success",
                route('tool-assignments.show', $toolLoan->id)
            );
        }

        return back()->with('success', "Pengajuan peminjaman #{$toolLoan->loan_number} berhasil disetujui dan stok alat telah diperbarui.");
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('approve tool assignments');

        $toolLoan = ToolLoan::with(['items', 'fromWarehouse', 'assignedBy'])->findOrFail($id);

        $user = auth()->user();
        if ($toolLoan->fromWarehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($toolLoan->fromWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data peminjaman di gudang ini.');
        }

        if ($toolLoan->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus menunggu persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($toolLoan, $request) {
            $toolLoan->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            $toolLoan->items()->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);
        });

        if ($toolLoan->assignedBy) {
            \App\Services\NotificationHelper::notifyUser(
                $toolLoan->assignedBy,
                "Peminjaman Alat Ditolak: #{$toolLoan->loan_number}",
                "Pengajuan peminjaman alat #{$toolLoan->loan_number} ditolak oleh " . auth()->user()->name . ". Alasan: {$request->rejection_reason}",
                "danger",
                route('tool-assignments.show', $toolLoan->id)
            );
        }

        return back()->with('success', "Pengajuan peminjaman #{$toolLoan->loan_number} ditolak.");
    }

    public function return(Request $request, $id)
    {
        $this->authorize('return tool assignments');

        $toolLoan = ToolLoan::with(['items.tool', 'fromWarehouse'])->findOrFail($id);

        $user = auth()->user();
        if ($toolLoan->fromWarehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($toolLoan->fromWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data peminjaman di gudang ini.');
        }

        if (!in_array($toolLoan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Hanya peminjaman berstatus Aktif atau Terlambat yang dapat dikembalikan.');
        }

        $request->validate([
            'returned_at' => 'required|date',
            'condition'   => 'required|in:good,damaged,under_maintenance',
            'notes'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $toolLoan) {
            foreach ($toolLoan->items as $item) {
                if ($item->status === 'returned') continue;

                $warehouse = $item->fromWarehouse ?? $toolLoan->fromWarehouse ?? $item->tool?->currentWarehouse;
                if ($warehouse && $item->tool) {
                    $this->toolInvService->returnStock($warehouse, $item->tool, (int)$item->quantity, $request->condition);
                }

                $item->update([
                    'returned_at' => $request->returned_at,
                    'status'      => 'returned',
                    'notes'       => $item->notes . ' | Dikembalikan: ' . $request->condition . ($request->notes ? " ({$request->notes})" : ''),
                ]);
            }

            $toolLoan->update([
                'returned_at' => $request->returned_at,
                'status'      => 'returned',
                'notes'       => $toolLoan->notes . ' | Dikembalikan: ' . $request->condition . ($request->notes ? " ({$request->notes})" : ''),
            ]);
        });

        \App\Services\NotificationHelper::notifyAdmins(
            "Pengembalian Peminjaman Alat: #{$toolLoan->loan_number}",
            "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) telah dikembalikan dengan kondisi " . strtoupper($request->condition) . ".",
            "info",
            route('tool-assignments.show', $toolLoan->id)
        );

        return back()->with('success', "Semua alat dalam peminjaman #{$toolLoan->loan_number} berhasil dikembalikan.");
    }

    public function cancel(Request $request, $id)
    {
        $this->authorize('cancel tool assignments');

        $toolLoan = ToolLoan::with(['items.tool', 'fromWarehouse'])->findOrFail($id);

        $user = auth()->user();
        if ($toolLoan->fromWarehouse && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']) && !$user->hasAccessToWarehouse($toolLoan->fromWarehouse)) {
            abort(403, 'Anda tidak memiliki akses ke data peminjaman di gudang ini.');
        }

        if (!in_array($toolLoan->status, ['pending', 'active'])) {
            return back()->with('error', 'Hanya peminjaman berstatus Menunggu Persetujuan atau Aktif yang dapat dibatalkan.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $toolLoan) {
            $isPreviouslyActive = ($toolLoan->status === 'active');

            foreach ($toolLoan->items as $item) {
                if ($isPreviouslyActive && $item->status === 'active') {
                    $warehouse = $item->fromWarehouse ?? $toolLoan->fromWarehouse ?? $item->tool?->currentWarehouse;
                    if ($warehouse && $item->tool) {
                        $this->toolInvService->returnStock($warehouse, $item->tool, (int)$item->quantity, 'good');
                    }
                }

                $item->update([
                    'status'               => 'cancelled',
                    'cancelled_at'         => now(),
                    'cancelled_by_user_id' => auth()->id(),
                    'cancellation_reason'  => $request->cancellation_reason,
                ]);
            }

            $toolLoan->update([
                'status'               => 'cancelled',
                'cancelled_at'         => now(),
                'cancelled_by_user_id' => auth()->id(),
                'cancellation_reason'  => $request->cancellation_reason,
            ]);
        });

        \App\Services\NotificationHelper::notifyAdmins(
            "Pembatalan Peminjaman Alat: #{$toolLoan->loan_number}",
            "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) dibatalkan oleh " . auth()->user()->name . ". Alasan: {$request->cancellation_reason}",
            "warning",
            route('tool-assignments.show', $toolLoan->id)
        );

        return back()->with('success', "Peminjaman #{$toolLoan->loan_number} berhasil dibatalkan." .
            ($toolLoan->getOriginal('status') === 'active' ? ' Stok alat telah dikembalikan ke gudang.' : ''));
    }
}
