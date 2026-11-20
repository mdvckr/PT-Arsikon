<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\ProjectBalanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);

        if (!$user->can('view payments') && !($isRoleAdminPOProyek && $user->can('view balance requests'))) {
            $this->authorize('view payments');
        }

        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $query = Payment::with(['purchaseOrder.supplier', 'purchaseOrder.deliveryWarehouse.project', 'project', 'creator']);

        if ($isRoleAdminPOProyek) {
            if (!empty($userProjectIds)) {
                $query->whereIn('project_id', $userProjectIds);
            } else {
                $query->where('created_by', $user->id);
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('bank_account', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%")
                          ->orWhereHas('supplier', function ($supQ) use ($search) {
                              $supQ->where('name', 'like', "%{$search}%");
                          });
                  })
                  ->orWhereHas('project', function ($pQ) use ($search) {
                      $pQ->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->start_date) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();

        // Projects & Saldo metrics for Admin Pengadaan & Admin Pengadaan Proyek
        if ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $projects = Project::whereIn('id', $userProjectIds)->orderBy('name')->get();
            $totalOperationalBalance = (float) Project::whereIn('id', $userProjectIds)->sum('operational_balance');
            $baseMetricQuery = Payment::whereIn('project_id', $userProjectIds);
        } else {
            $projects = Project::orderBy('name')->get();
            $totalOperationalBalance = (float) Project::sum('operational_balance');
            $baseMetricQuery = Payment::query();
        }

        // Query Balance Logs
        $balanceLogQuery = ProjectBalanceLog::with(['project', 'user'])->latest('id');
        if ($isRoleAdminPOProyek) {
            if (!empty($userProjectIds)) {
                $balanceLogQuery->whereIn('project_id', $userProjectIds);
            } else {
                $balanceLogQuery->where('user_id', $user->id);
            }
        }
        if ($request->project_id) {
            $balanceLogQuery->where('project_id', $request->project_id);
        }
        $balanceLogs = $balanceLogQuery->take(100)->get();

        // Group mutasi saldo per 1 minggu, di dalamnya group per hari (mirip daftar nota harian)
        $groupedBalanceWeeks = $balanceLogs->groupBy(function ($item) {
            $date = $item->created_at;
            if (!$date) return 'no-date';
            $year = $date->isoWeekYear();
            $week = str_pad($date->isoWeek(), 2, '0', STR_PAD_LEFT);
            return "{$year}-W{$week}";
        })->map(function ($weekItems, $weekKey) {
            $firstItem = $weekItems->first();
            $date = $firstItem->created_at;

            if ($date) {
                $startOfWeek = $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $endOfWeek   = $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $weekNumber  = $date->isoWeek();
                $weekLabel   = 'Minggu ke-' . $weekNumber . ' (' . $startOfWeek->translatedFormat('d M Y') . ' — ' . $endOfWeek->translatedFormat('d M Y') . ')';
                $weekSubLabel = 'Periode 1 Minggu (' . $startOfWeek->format('d M') . ' s/d ' . $endOfWeek->format('d M Y') . ')';
            } else {
                $startOfWeek = null;
                $endOfWeek   = null;
                $weekNumber  = null;
                $weekLabel   = 'Tanpa Tanggal';
                $weekSubLabel = '';
            }

            // Group by Day (Y-m-d)
            $days = $weekItems->groupBy(function ($item) {
                return $item->created_at ? $item->created_at->format('Y-m-d') : 'no-date';
            })->map(function ($dayItems, $dayKey) {
                $firstDayItem = $dayItems->first();
                $dayDate = $firstDayItem->created_at;

                return (object) [
                    'date_key'        => $dayKey,
                    'date_label'      => $dayDate ? $dayDate->translatedFormat('l, d F Y') : 'Tanpa Tanggal',
                    'day_name'        => $dayDate ? $dayDate->translatedFormat('l') : '-',
                    'total_topup'     => (float) $dayItems->where('type', 'topup')->sum('amount'),
                    'total_deduction' => (float) $dayItems->where('type', 'payment_deduction')->sum('amount'),
                    'count'           => $dayItems->count(),
                    'logs'            => $dayItems,
                ];
            });

            return (object) [
                'week_key'        => $weekKey,
                'week_number'     => $weekNumber,
                'week_label'      => $weekLabel,
                'week_sub_label'  => $weekSubLabel,
                'start_date'      => $startOfWeek,
                'end_date'        => $endOfWeek,
                'total_topup'     => (float) $weekItems->where('type', 'topup')->sum('amount'),
                'total_deduction' => (float) $weekItems->where('type', 'payment_deduction')->sum('amount'),
                'count'           => $weekItems->count(),
                'days'            => $days,
            ];
        });

        $metrics = [
            'total_verified_amount'     => (float) (clone $baseMetricQuery)->where('status', 'verified')->sum('amount'),
            'verified_count'            => (int) (clone $baseMetricQuery)->where('status', 'verified')->count(),
            'pending_count'             => (int) (clone $baseMetricQuery)->where('status', 'pending')->count(),
            'pending_amount'            => (float) (clone $baseMetricQuery)->where('status', 'pending')->sum('amount'),
            'total_count'               => (int) (clone $baseMetricQuery)->count(),
            'rejected_count'            => (int) (clone $baseMetricQuery)->where('status', 'rejected')->count(),
            'total_operational_balance' => $totalOperationalBalance,
            'instant_count'             => (int) (clone $baseMetricQuery)->where('payment_type', 'instant')->count(),
            'waiting_count'             => (int) (clone $baseMetricQuery)->where('payment_type', 'waiting')->count(),
            'instant_verified_amount'   => (float) (clone $baseMetricQuery)->where('payment_type', 'instant')->where('status', 'verified')->sum('amount'),
        ];

        if ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $balanceRequests = ProjectBalanceRequest::whereIn('project_id', $userProjectIds)->with(['project', 'requester', 'approver', 'items'])->latest('id')->take(50)->get();
        } else {
            $balanceRequests = ProjectBalanceRequest::with(['project', 'requester', 'approver', 'items'])->latest('id')->take(50)->get();
        }
        $pendingBalanceRequestsCount = ProjectBalanceRequest::where('status', 'pending')->count();

        // Group pengajuan kas per bulan kalender operasional
        $groupedBalanceRequestMonths = $balanceRequests->groupBy(function ($item) {
            $date = $item->request_date ?? $item->created_at;
            if (!$date) return 'no-date';
            return \Carbon\Carbon::parse($date)->format('Y-m');
        })->map(function ($monthItems, $monthKey) {
            $firstItem = $monthItems->first();
            $date = \Carbon\Carbon::parse($firstItem->request_date ?? $firstItem->created_at);

            return (object) [
                'month_key'        => $monthKey,
                'month_label'      => $monthKey === 'no-date' ? 'Tanpa Tanggal' : $date->translatedFormat('F Y'),
                'is_current_month' => $monthKey !== 'no-date' && $date->isCurrentMonth(),
                'has_pending'      => $monthItems->where('status', 'pending')->count() > 0,
                'pending_count'    => $monthItems->where('status', 'pending')->count(),
                'approved_count'   => $monthItems->where('status', 'approved')->count(),
                'total_amount'     => (float) $monthItems->sum('amount'),
                'approved_amount'  => (float) $monthItems->where('status', 'approved')->sum('amount'),
                'items'            => $monthItems,
            ];
        });
        $groupedBalanceRequestWeeks = $groupedBalanceRequestMonths;

        $assignedProject = null;
        if (!empty($userProjectIds)) {
            $assignedProject = Project::find($userProjectIds[0]);
        }
        if (!$assignedProject && $user->activeWarehouse()?->project) {
            $assignedProject = $user->activeWarehouse()->project;
        }
        if (!$assignedProject && $projects->isNotEmpty()) {
            $assignedProject = $projects->first();
        }

        return view('payments.index', compact('payments', 'metrics', 'projects', 'balanceLogs', 'groupedBalanceWeeks', 'balanceRequests', 'groupedBalanceRequestWeeks', 'pendingBalanceRequestsCount', 'isRoleAdminPOProyek', 'assignedProject'));
    }

    public function create(Request $request)
    {
        return redirect()->route('payments.index');
    }

    public function store(Request $request)
    {
        $this->authorize('create payments');

        $validated = $request->validate([
            'purchase_order_id'     => 'required|exists:purchase_orders,id',
            'payment_type'          => 'nullable|in:waiting,instant',
            'project_id'            => 'nullable|exists:projects,id',
            'amount'                => 'required|numeric|min:1',
            'payment_method'        => 'required|string',
            'payment_date'          => 'required|date',
            'bank_account'          => 'nullable|string',
            'reference_number'      => 'nullable|string',
            'notes'                 => 'nullable|string',
            'mark_receipts_as_paid' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        if ($isRoleAdminPOProyek) {
            $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();
            if (!empty($userProjectIds)) {
                if (empty($validated['project_id'])) {
                    $validated['project_id'] = $userProjectIds[0];
                } elseif (!in_array($validated['project_id'], $userProjectIds)) {
                    return back()->withErrors(['project_id' => 'Anda hanya berwenang memproses pembayaran untuk proyek yang ditugaskan kepada Anda.'])->withInput();
                }
            }
        }

        $markReceiptsAsPaid = $request->boolean('mark_receipts_as_paid');
        $paymentType = $validated['payment_type'] ?? 'waiting';
        $validated['payment_type'] = $paymentType;
        unset($validated['mark_receipts_as_paid']);

        try {
            $payment = DB::transaction(function () use ($validated, $markReceiptsAsPaid, $paymentType) {
                $po = PurchaseOrder::where('id', $validated['purchase_order_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $po->hasPayableProof()) {
                    throw new \DomainException('Pembayaran hanya untuk Purchase Order yang memiliki minimal satu Penerimaan Barang (GR) terkonfirmasi atau bukti Nota Pembelian.');
                }

                $maxPayable = $po->payable_remaining_amount;
                if ((float) $validated['amount'] > ($maxPayable + 0.001)) {
                    $formattedMax = number_format($maxPayable, 0, ',', '.');
                    throw new \DomainException("Jumlah pembayaran melebihi batas nilai sah yang dapat dibayar (Maksimal: Rp {$formattedMax}).");
                }

                $targetProjectId = $validated['project_id'] ?? $po->project_id;

                if ($paymentType === 'instant') {
                    if (!$targetProjectId) {
                        throw new \DomainException('Proyek tidak terdeteksi untuk pemotongan saldo kas. Silakan pilih Proyek pada form pengajuan pembayaran.');
                    }

                    $project = Project::where('id', $targetProjectId)->lockForUpdate()->firstOrFail();
                    $amount = (float) $validated['amount'];

                    if ((float) $project->operational_balance < ($amount - 0.001)) {
                        $curSaldo = number_format($project->operational_balance, 0, ',', '.');
                        $needSaldo = number_format($amount, 0, ',', '.');
                        throw new \DomainException("Saldo kas operasional proyek '{$project->name}' tidak mencukupi (Tersedia: Rp {$curSaldo}, Dibutuhkan: Rp {$needSaldo}). Silakan lakukan Top-Up Saldo terlebih dahulu.");
                    }

                    $balanceBefore = (float) $project->operational_balance;
                    $balanceAfter = $balanceBefore - $amount;
                    $project->update(['operational_balance' => $balanceAfter]);

                    // Otomatis tandai nota pembelian harian sebagai sudah dibayar
                    $po->receipts()->where('payment_status', 'unpaid')->update(['payment_status' => 'paid']);

                    $payment = Payment::create([
                        ...$validated,
                        'project_id'     => $project->id,
                        'payment_type'   => 'instant',
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceAfter,
                        'created_by'     => auth()->id(),
                        'verified_by'    => auth()->id(),
                        'verified_at'    => now(),
                        'status'         => 'verified',
                    ]);

                    // Catat ke log mutasi saldo proyek
                    ProjectBalanceLog::create([
                        'project_id'       => $project->id,
                        'user_id'          => auth()->id(),
                        'payment_id'       => $payment->id,
                        'type'             => 'payment_deduction',
                        'amount'           => $amount,
                        'balance_before'   => $balanceBefore,
                        'balance_after'    => $balanceAfter,
                        'reference_number' => $validated['reference_number'] ?? $payment->payment_number,
                        'notes'            => 'Pembayaran Instan (Nota Harian) PO ' . $po->po_number . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
                    ]);

                    return $payment;
                } else {
                    // Pembayaran PO Proyek Reguler (Menunggu Verifikasi Keuangan)
                    if ($markReceiptsAsPaid) {
                        $po->receipts()->where('payment_status', 'unpaid')->update(['payment_status' => 'paid']);
                    }

                    return Payment::create([
                        ...$validated,
                        'project_id'   => $targetProjectId,
                        'payment_type' => 'waiting',
                        'created_by'   => auth()->id(),
                        'status'       => 'pending',
                    ]);
                }
            });

            if ($payment->isInstant()) {
                return redirect()->route('payments.show', $payment)
                    ->with('success', "⚡ Pembayaran Instan #{$payment->payment_number} berhasil diproses langsung! Saldo kas proyek telah dipotong & status pembayaran langsung terverifikasi.");
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', "⏳ Pembayaran #{$payment->payment_number} berhasil diajukan dan menunggu verifikasi bagian keuangan.");
        } catch (\DomainException $e) {
            $msg = $e->getMessage();
            $field = str_contains($msg, 'GR') ? 'purchase_order_id' : (str_contains($msg, 'Saldo') ? 'amount' : 'amount');
            return back()->withErrors([$field => $msg])->withInput();
        }
    }

    public function topupBalance(Request $request)
    {
        $this->authorize('create payments');

        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'amount'           => 'required|numeric|min:1000',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        if ($isRoleAdminPOProyek) {
            return back()->with('error', 'Admin Pengadaan Proyek tidak dapat melakukan top-up langsung. Pengisian saldo kas operasional proyek harus diajukan ke Admin Pengadaan Pusat.');
        }

        $project = DB::transaction(function () use ($validated) {
            $p = Project::where('id', $validated['project_id'])->lockForUpdate()->firstOrFail();
            $before = (float) $p->operational_balance;
            $amount = (float) $validated['amount'];
            $after  = $before + $amount;

            $p->update(['operational_balance' => $after]);

            ProjectBalanceLog::create([
                'project_id'       => $p->id,
                'user_id'          => auth()->id(),
                'type'             => 'topup',
                'amount'           => $amount,
                'balance_before'   => $before,
                'balance_after'    => $after,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes'            => (!empty($validated['notes']) ? $validated['notes'] : 'Top-up saldo operasional kas proyek untuk Admin Pengadaan'),
            ]);

            return $p;
        });

        $formattedAmount = number_format($validated['amount'], 0, ',', '.');
        $formattedAfter  = number_format($project->operational_balance, 0, ',', '.');

        return back()->with('success', "Top-up saldo kas proyek '{$project->name}' sebesar Rp {$formattedAmount} berhasil disimpan! Saldo saat ini: Rp {$formattedAfter}.");
    }

    public function requestBalance(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('create payments') && !$user->can('create balance requests')) {
            $this->authorize('create payments');
        }

        $validated = $request->validate([
            'project_id'          => 'nullable|exists:projects,id',
            'request_date'        => 'nullable|date',
            'amount'              => 'nullable|numeric|min:0',
            'purpose'             => 'nullable|string|max:1000',
            'urgency'             => 'nullable|in:normal,urgent',
            'signer_requester'    => 'nullable|string|max:150',
            'signer_site_manager' => 'nullable|string|max:150',
            'items'               => 'nullable|array',
            'items.*.item_name'   => 'required_with:items|string|max:255',
            'items.*.quantity'    => 'required_with:items|numeric|min:0.01',
            'items.*.unit'        => 'nullable|string|max:50',
            'items.*.unit_price'  => 'required_with:items|numeric|min:0',
            'items.*.notes'       => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $projectId = $validated['project_id'] ?? null;
        if (empty($projectId) && !empty($userProjectIds)) {
            $projectId = $userProjectIds[0];
        }
        if (empty($projectId)) {
            $projectId = $user->warehouses()->whereNotNull('project_id')->value('project_id')
                ?? $user->activeWarehouse()?->project_id
                ?? Project::value('id');
        }

        if ($isRoleAdminPOProyek && !empty($userProjectIds) && !in_array($projectId, $userProjectIds)) {
            return back()->with('error', 'Anda hanya dapat mengajukan permintaan saldo kas untuk proyek yang ditugaskan kepada Anda.');
        }

        $project = Project::findOrFail($projectId);

        // Hitung total dari baris rincian item jika ada
        $calculatedAmount = 0;
        $itemsToSave = [];

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (empty(trim($item['item_name'] ?? ''))) continue;

                $qty = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $subtotal = $qty * $price;
                $calculatedAmount += $subtotal;

                $itemsToSave[] = [
                    'item_name'   => trim($item['item_name']),
                    'quantity'    => $qty,
                    'unit'        => $item['unit'] ?? null,
                    'unit_price'  => $price,
                    'total_price' => $subtotal,
                    'notes'       => $item['notes'] ?? null,
                ];
            }
        }

        $finalAmount = ($calculatedAmount > 0) ? $calculatedAmount : (float) ($validated['amount'] ?? 0);

        if ($finalAmount <= 0) {
            return back()->withErrors(['amount' => 'Total pengajuan kas lapangan wajib lebih dari Rp 0. Masukkan rincian kebutuhan belanja dengan harga yang valid.'])->withInput();
        }

        $itemCount = count($itemsToSave);
        $purpose = !empty($validated['purpose'])
            ? $validated['purpose']
            : ($itemCount > 0 ? "Pengajuan Kebutuhan Kas Lapangan ({$itemCount} item barang)" : "Pengajuan Kebutuhan Kas Lapangan");
        $urgency = $validated['urgency'] ?? 'normal';

        $balanceRequest = DB::transaction(function () use ($validated, $user, $project, $finalAmount, $purpose, $urgency, $itemsToSave) {
            $req = ProjectBalanceRequest::create([
                'request_date'        => $validated['request_date'] ?? now()->toDateString(),
                'project_id'          => $project->id,
                'requested_by'        => $user->id,
                'amount'              => $finalAmount,
                'purpose'             => $purpose,
                'urgency'             => $urgency,
                'status'              => 'pending',
                'signer_requester'    => !empty($validated['signer_requester']) ? $validated['signer_requester'] : $user->name,
                'signer_site_manager' => $validated['signer_site_manager'] ?? null,
            ]);

            foreach ($itemsToSave as $it) {
                $req->items()->create($it);
            }

            return $req;
        });

        $formattedAmount = number_format($balanceRequest->amount, 0, ',', '.');
        return back()->with('success', "Pengajuan Kas Lapangan #{$balanceRequest->request_number} sebesar Rp {$formattedAmount} untuk '{$project->name}' berhasil diajukan ke Kantor Pusat / Owner! Anda dapat mencetak dokumen pengajuannya.");
    }

    public function balanceRequestsIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('view payments') && !$user->can('view balance requests')) {
            $this->authorize('view payments');
        }

        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $projects = Project::orderBy('name')->get();

        $query = ProjectBalanceRequest::with(['project', 'requester', 'approver', 'items'])->latest('id');

        if ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $query->whereIn('project_id', $userProjectIds);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $balanceRequests = $query->get();

        // Group pengajuan kas per bulan kalender operasional
        $groupedBalanceRequestMonths = $balanceRequests->groupBy(function ($item) {
            $date = $item->request_date ?? $item->created_at;
            if (!$date) return 'no-date';
            return \Carbon\Carbon::parse($date)->format('Y-m');
        })->map(function ($monthItems, $monthKey) {
            $firstItem = $monthItems->first();
            $date = \Carbon\Carbon::parse($firstItem->request_date ?? $firstItem->created_at);

            return (object) [
                'month_key'        => $monthKey,
                'month_label'      => $monthKey === 'no-date' ? 'Tanpa Tanggal' : $date->translatedFormat('F Y'),
                'is_current_month' => $monthKey !== 'no-date' && $date->isCurrentMonth(),
                'has_pending'      => $monthItems->where('status', 'pending')->count() > 0,
                'pending_count'    => $monthItems->where('status', 'pending')->count(),
                'approved_count'   => $monthItems->where('status', 'approved')->count(),
                'total_amount'     => (float) $monthItems->sum('amount'),
                'approved_amount'  => (float) $monthItems->where('status', 'approved')->sum('amount'),
                'items'            => $monthItems,
            ];
        });
        $groupedBalanceRequestWeeks = $groupedBalanceRequestMonths;

        $metrics = [
            'pending_count'   => $balanceRequests->where('status', 'pending')->count(),
            'approved_count'  => $balanceRequests->where('status', 'approved')->count(),
            'total_amount'    => (float) $balanceRequests->sum('amount'),
            'approved_amount' => (float) $balanceRequests->where('status', 'approved')->sum('amount'),
            'total_count'     => $balanceRequests->count(),
        ];

        return view('payments.balance-requests', compact(
            'balanceRequests',
            'groupedBalanceRequestMonths',
            'groupedBalanceRequestWeeks',
            'projects',
            'metrics',
            'isRoleAdminPOProyek'
        ));
    }

    public function showBalanceRequest(ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        if (!$user->can('view payments') && !$user->can('view balance requests')) {
            $this->authorize('view payments');
        }

        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        if ($isRoleAdminPOProyek) {
            $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();
            if (!empty($userProjectIds) && !in_array($balanceRequest->project_id, $userProjectIds) && $balanceRequest->requested_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan kas proyek lain.');
            }
        }

        $balanceRequest->load(['project', 'requester', 'approver', 'items']);

        return view('balance-requests.show', compact('balanceRequest', 'isRoleAdminPOProyek'));
    }

    public function printBalanceRequest(ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        if (!$user->can('view payments') && !$user->can('view balance requests')) {
            $this->authorize('view payments');
        }

        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        if ($isRoleAdminPOProyek) {
            $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();
            if (!empty($userProjectIds) && !in_array($balanceRequest->project_id, $userProjectIds) && $balanceRequest->requested_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan kas proyek lain.');
            }
        }

        $balanceRequest->load(['project', 'requester', 'approver', 'items']);

        return view('balance-requests.print', compact('balanceRequest'));
    }

    public function approveBalanceRequest(Request $request, ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Super Admin'])) {
            abort(403, 'Wewenang persetujuan pengajuan kas lapangan berada pada Admin Pengadaan Pusat. Admin Pusat dan Owner hanya memiliki hak akses melihat (read-only).');
        }

        if ($balanceRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan saldo ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'payment_date'     => 'nullable|date',
            'payment_method'   => 'nullable|string|in:cash,bank_transfer,other',
            'bank_account'     => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'proof_file'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'            => 'nullable|string|max:500',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');
        }

        DB::transaction(function () use ($balanceRequest, $validated, $user, $proofPath) {
            $project = Project::where('id', $balanceRequest->project_id)->lockForUpdate()->firstOrFail();
            $before  = (float) $project->operational_balance;
            $amount  = (float) $balanceRequest->amount;
            $after   = $before + $amount;

            $project->update(['operational_balance' => $after]);

            $ref = (!empty($validated['reference_number'])) ? $validated['reference_number'] : "REQ-BAL-{$balanceRequest->id}";
            $requesterName = $balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek';
            $noteText = (!empty($validated['notes'])) ? $validated['notes'] : $balanceRequest->purpose;

            $paymentDate = !empty($validated['payment_date']) ? $validated['payment_date'] : now()->toDateString();
            $paymentMethod = !empty($validated['payment_method']) ? $validated['payment_method'] : 'bank_transfer';
            $bankAccount = $validated['bank_account'] ?? null;

            // Catat transaksi pembayaran kas operasional resmi
            $paymentNumber = 'PAY-BAL-' . date('Ymd') . '-' . str_pad($balanceRequest->id, 4, '0', STR_PAD_LEFT);
            $payment = Payment::create([
                'payment_number'   => $paymentNumber,
                'project_id'       => $project->id,
                'created_by'       => $user->id,
                'verified_by'      => $user->id,
                'status'           => 'verified',
                'payment_type'     => 'disbursement_kas',
                'amount'           => $amount,
                'balance_before'   => $before,
                'balance_after'    => $after,
                'payment_method'   => $paymentMethod,
                'payment_date'     => $paymentDate,
                'bank_account'     => $bankAccount,
                'reference_number' => $ref,
                'proof_file'       => $proofPath,
                'notes'            => "Pencairan Pengajuan Kas #{$balanceRequest->request_number}: " . $noteText,
                'verified_at'      => now(),
            ]);

            ProjectBalanceLog::create([
                'project_id'       => $project->id,
                'user_id'          => $user->id,
                'payment_id'       => $payment->id,
                'type'             => 'topup',
                'amount'           => $amount,
                'balance_before'   => $before,
                'balance_after'    => $after,
                'reference_number' => $ref,
                'notes'            => "Disetujui dari Pengajuan Kas #{$balanceRequest->request_number} oleh {$requesterName}: " . $noteText,
            ]);

            $balanceRequest->update([
                'status'           => 'approved',
                'approved_by'      => $user->id,
                'approved_at'      => now(),
                'reference_number' => $ref,
                'signer_approver'  => $balanceRequest->signer_approver ?: $user->name,
            ]);
        });

        $formattedAmount = number_format($balanceRequest->amount, 0, ',', '.');
        return redirect()->route('payments.index')->with('success', "Pengajuan kas lapangan #{$balanceRequest->request_number} sebesar Rp {$formattedAmount} berhasil disetujui & dicairkan! Saldo proyek otomatis bertambah.");
    }

    public function rejectBalanceRequest(Request $request, ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Super Admin'])) {
            abort(403, 'Wewenang penolakan pengajuan kas lapangan berada pada Admin Pengadaan Pusat. Admin Pusat dan Owner hanya memiliki hak akses melihat (read-only).');
        }

        if ($balanceRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan saldo ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $balanceRequest->update([
            'status'           => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', "Permintaan saldo kas sebesar Rp " . number_format($balanceRequest->amount, 0, ',', '.') . " telah ditolak.");
    }

    public function show(Payment $payment)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);

        if (!$user->can('view payments') && !($isRoleAdminPOProyek && $user->can('view balance requests'))) {
            $this->authorize('view payments');
        }
        if ($isRoleAdminPOProyek) {
            $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();
            if (!empty($userProjectIds) && !in_array($payment->project_id, $userProjectIds) && $payment->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data pembayaran proyek lain.');
            }
        }

        $payment->load([
            'purchaseOrder.supplier',
            'purchaseOrder.items.material',
            'purchaseOrder.items.tool',
            'purchaseOrder.goodsReceipts.items',
            'purchaseOrder.payments',
            'purchaseOrder.receipts.items',
            'project.balanceLogs',
            'creator',
            'verifier',
            'items'
        ]);
        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        $this->authorize('verify payments');

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Status tidak valid untuk diverifikasi.');
        }

        $payment->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $po = $payment->purchaseOrder;
        if ($po) {
            $totalPaid = $po->payments()->where('status','verified')->sum('amount');
            $po->update(['paid_amount' => $totalPaid]);
        }

        return back()->with('success', "Pembayaran #{$payment->payment_number} terverifikasi.");
    }

    public function reject(Request $request, Payment $payment)
    {
        $this->authorize('verify payments');

        $request->validate(['rejection_reason' => 'required|string']);
        $payment->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
        ]);
        return back()->with('success', "Pembayaran #{$payment->payment_number} ditolak.");
    }
}
