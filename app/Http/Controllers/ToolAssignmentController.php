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
            'returns'     => 'nullable|array',
            'returns.*.returned_good'    => 'nullable|integer|min:0',
            'returns.*.returned_damaged' => 'nullable|integer|min:0',
            'returns.*.returned_lost'    => 'nullable|integer|min:0',
            'returns.*.notes'            => 'nullable|string|max:500',
            'condition'   => 'nullable|in:good,damaged,under_maintenance',
            'notes'       => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $toolLoan) {
                $totalGoodAll = 0;
                $totalDamagedAll = 0;
                $totalLostAll = 0;

                foreach ($toolLoan->items as $item) {
                    if ($item->status === 'returned' || $item->status === 'lost') continue;

                    $warehouse = $item->fromWarehouse ?? $toolLoan->fromWarehouse ?? $item->tool?->currentWarehouse;
                    $itemQty = (int) $item->quantity;

                    if ($request->has('returns') && isset($request->returns[$item->id])) {
                        $retData = $request->returns[$item->id];
                        $qtyGood = isset($retData['returned_good']) ? (int)$retData['returned_good'] : 0;
                        $qtyDamaged = isset($retData['returned_damaged']) ? (int)$retData['returned_damaged'] : 0;
                        $qtyLost = isset($retData['returned_lost']) ? (int)$retData['returned_lost'] : 0;
                        $itemNotes = $retData['notes'] ?? null;

                        $sum = $qtyGood + $qtyDamaged + $qtyLost;
                        if ($sum !== $itemQty) {
                            $toolName = $item->tool?->name ?? 'Alat';
                            throw new \Exception("Total pengembalian alat '{$toolName}' ({$qtyGood} Baik + {$qtyDamaged} Rusak + {$qtyLost} Hilang = {$sum}) harus sama dengan jumlah pinjam ({$itemQty} Unit).");
                        }

                        if ($warehouse && $item->tool) {
                            $this->toolInvService->returnStockDetailed($warehouse, $item->tool, $qtyGood, $qtyDamaged, $qtyLost);
                        }

                        $condSummary = [];
                        if ($qtyGood > 0) $condSummary[] = "{$qtyGood} Baik";
                        if ($qtyDamaged > 0) $condSummary[] = "{$qtyDamaged} Rusak";
                        if ($qtyLost > 0) $condSummary[] = "{$qtyLost} Hilang";

                        $overallCond = 'good';
                        if ($qtyLost > 0 && $qtyGood === 0 && $qtyDamaged === 0) {
                            $overallCond = 'lost';
                        } elseif ($qtyDamaged > 0 && $qtyGood === 0 && $qtyLost === 0) {
                            $overallCond = 'damaged';
                        } elseif ($qtyDamaged > 0 || $qtyLost > 0) {
                            $overallCond = 'partial';
                        }

                        $item->update([
                            'returned_at'      => $request->returned_at,
                            'returned_good'    => $qtyGood,
                            'returned_damaged' => $qtyDamaged,
                            'returned_lost'    => $qtyLost,
                            'condition'        => $overallCond,
                            'return_notes'     => $itemNotes,
                            'status'           => ($qtyLost === $itemQty) ? 'lost' : 'returned',
                            'notes'            => trim(($item->notes ?? '') . ' | Dikembalikan: ' . implode(', ', $condSummary) . ($itemNotes ? " ({$itemNotes})" : '')),
                        ]);

                        $totalGoodAll += $qtyGood;
                        $totalDamagedAll += $qtyDamaged;
                        $totalLostAll += $qtyLost;
                    } else {
                        // Fallback legacy
                        $cond = $request->condition ?? 'good';
                        $qtyGood = ($cond === 'good') ? $itemQty : 0;
                        $qtyDamaged = in_array($cond, ['damaged', 'under_maintenance']) ? $itemQty : 0;
                        $qtyLost = 0;

                        if ($warehouse && $item->tool) {
                            $this->toolInvService->returnStockDetailed($warehouse, $item->tool, $qtyGood, $qtyDamaged, $qtyLost);
                        }

                        $item->update([
                            'returned_at'      => $request->returned_at,
                            'returned_good'    => $qtyGood,
                            'returned_damaged' => $qtyDamaged,
                            'returned_lost'    => $qtyLost,
                            'condition'        => $cond,
                            'return_notes'     => $request->notes,
                            'status'           => 'returned',
                            'notes'            => trim(($item->notes ?? '') . ' | Dikembalikan: ' . $cond . ($request->notes ? " ({$request->notes})" : '')),
                        ]);

                        $totalGoodAll += $qtyGood;
                        $totalDamagedAll += $qtyDamaged;
                        $totalLostAll += $qtyLost;
                    }
                }

                $summaryParts = [];
                if ($totalGoodAll > 0) $summaryParts[] = "{$totalGoodAll} Baik";
                if ($totalDamagedAll > 0) $summaryParts[] = "{$totalDamagedAll} Rusak";
                if ($totalLostAll > 0) $summaryParts[] = "{$totalLostAll} Hilang";

                $toolLoan->update([
                    'returned_at'      => $request->returned_at,
                    'returned_good'    => $totalGoodAll,
                    'returned_damaged' => $totalDamagedAll,
                    'returned_lost'    => $totalLostAll,
                    'return_notes'     => $request->notes,
                    'status'           => ($totalLostAll > 0 && $totalGoodAll === 0 && $totalDamagedAll === 0) ? 'lost' : 'returned',
                    'notes'            => trim(($toolLoan->notes ?? '') . ' | Dikembalikan: ' . implode(', ', $summaryParts) . ($request->notes ? " ({$request->notes})" : '')),
                ]);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $wh = $toolLoan->fromWarehouse;
        $summaryText = [];
        if ($toolLoan->returned_good > 0) $summaryText[] = "{$toolLoan->returned_good} Baik";
        if ($toolLoan->returned_damaged > 0) $summaryText[] = "{$toolLoan->returned_damaged} Rusak";
        if ($toolLoan->returned_lost > 0) $summaryText[] = "{$toolLoan->returned_lost} Hilang";
        $summaryStr = !empty($summaryText) ? implode(', ', $summaryText) : 'Lengkap';

        if ($wh && !$wh->is_central) {
            \App\Services\NotificationHelper::notifyProjectWarehouseAdmins(
                $wh->id,
                "Pengembalian Peminjaman Alat: #{$toolLoan->loan_number}",
                "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) telah dikembalikan ({$summaryStr}).",
                "info",
                route('tool-assignments.show', $toolLoan->id)
            );
        } else {
            \App\Services\NotificationHelper::notifyCentralWarehouseAdmins(
                "Pengembalian Peminjaman Alat: #{$toolLoan->loan_number}",
                "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) telah dikembalikan ({$summaryStr}).",
                "info",
                route('tool-assignments.show', $toolLoan->id)
            );
        }

        return back()->with('success', "Pengembalian peminjaman #{$toolLoan->loan_number} berhasil dicatat ({$summaryStr}) dan stok inventaris gudang telah disinkronkan.");
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

        $wh = $toolLoan->fromWarehouse;
        if ($wh && !$wh->is_central) {
            \App\Services\NotificationHelper::notifyProjectWarehouseAdmins(
                $wh->id,
                "Pembatalan Peminjaman Alat: #{$toolLoan->loan_number}",
                "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) dibatalkan oleh " . auth()->user()->name . ". Alasan: {$request->cancellation_reason}",
                "warning",
                route('tool-assignments.show', $toolLoan->id)
            );
        } else {
            \App\Services\NotificationHelper::notifyCentralWarehouseAdmins(
                "Pembatalan Peminjaman Alat: #{$toolLoan->loan_number}",
                "Peminjaman alat #{$toolLoan->loan_number} ({$toolLoan->borrower_name}) dibatalkan oleh " . auth()->user()->name . ". Alasan: {$request->cancellation_reason}",
                "warning",
                route('tool-assignments.show', $toolLoan->id)
            );
        }

        return back()->with('success', "Peminjaman #{$toolLoan->loan_number} berhasil dibatalkan." .
            ($toolLoan->getOriginal('status') === 'active' ? ' Stok alat telah dikembalikan ke gudang.' : ''));
    }
}
