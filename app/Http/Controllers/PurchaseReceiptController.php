<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\Supplier;
use App\Models\Material;
use App\Services\SecureUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view purchase receipts');

        $query = PurchaseReceipt::with(['purchaseOrder', 'project', 'supplier', 'creator', 'items']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhere('day_label', 'like', "%{$request->search}%");
            });
        }

        if ($request->po_id) {
            $query->where('purchase_order_id', $request->po_id);
        }

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->date) {
            $query->whereDate('receipt_date', $request->date);
        }

        // Scope to user's assigned projects if Admin PO Proyek
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasRole('Admin PO Proyek') && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        if ($isRoleAdminPOProyek) {
            if (!empty($userProjectIds)) {
                $query->whereIn('project_id', $userProjectIds);
                $projects = Project::whereIn('id', $userProjectIds)->orderBy('name')->get();
            } else {
                $query->where('created_by', $user->id);
                $projects = Project::where('id', 0)->get();
            }
        } else {
            $projects = Project::orderBy('name')->get();
        }

        $receipts = $query->latest('receipt_date')->latest('id')->paginate(30)->withQueryString();

        // Group per minggu, di dalamnya group per hari
        $groupedWeeks = $receipts->getCollection()->groupBy(function ($item) {
            if (!$item->receipt_date) return 'no-date';
            $year = $item->receipt_date->isoWeekYear();
            $week = str_pad($item->receipt_date->isoWeek(), 2, '0', STR_PAD_LEFT);
            return "{$year}-W{$week}";
        })->map(function ($weekItems, $weekKey) {
            $firstItem = $weekItems->first();
            $date = $firstItem->receipt_date;

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
                return $item->receipt_date ? $item->receipt_date->format('Y-m-d') : 'no-date';
            })->map(function ($dayItems, $dayKey) {
                $firstDayItem = $dayItems->first();
                $dayDate = $firstDayItem->receipt_date;

                return (object) [
                    'date_key'   => $dayKey,
                    'date_label' => $dayDate ? $dayDate->translatedFormat('l, d F Y') : 'Tanpa Tanggal',
                    'day_name'   => $dayDate ? $dayDate->translatedFormat('l') : '-',
                    'total'      => (float) $dayItems->sum('total_amount'),
                    'count'      => $dayItems->count(),
                    'receipts'   => $dayItems,
                ];
            });

            return (object) [
                'week_key'    => $weekKey,
                'week_number' => $weekNumber,
                'week_label'  => $weekLabel,
                'start_date'  => $startOfWeek,
                'end_date'    => $endOfWeek,
                'total'       => (float) $weekItems->sum('total_amount'),
                'count'       => $weekItems->count(),
                'days'        => $days,
            ];
        });

        $pos = collect();

        return view('purchase-receipts.index', compact('receipts', 'groupedWeeks', 'projects', 'pos'));
    }

    public function create(Request $request)
    {
        $this->authorize('create purchase receipts');

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasRole('Admin PO Proyek') && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        if ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $projects = Project::whereIn('id', $userProjectIds)->orderBy('name')->get();
        } else {
            $projects = Project::orderBy('name')->get();
        }

        $selectedProject = null;
        if ($request->project_id) {
            $selectedProject = $projects->firstWhere('id', $request->project_id);
        }
        if (!$selectedProject && $projects->count() === 1) {
            $selectedProject = $projects->first();
        }
        if (!$selectedProject && $user->activeWarehouse()?->project) {
            $selectedProject = $user->activeWarehouse()->project;
        }
        if (!$selectedProject && $projects->isNotEmpty()) {
            $selectedProject = $projects->first();
        }

        $currentBalance = (float) ($selectedProject?->operational_balance ?? 0);
        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::with('unit')->orderBy('name')->get();

        return view('purchase-receipts.create', compact(
            'projects',
            'selectedProject',
            'currentBalance',
            'suppliers',
            'materials'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create purchase receipts');

        $validated = $request->validate([
            'project_id'         => 'nullable|exists:projects,id',
            'project_name'       => 'nullable|string|max:255',
            'supplier_name'      => 'nullable|string|max:255',
            'receipt_number'     => 'nullable|string|max:100',
            'receipt_date'       => 'required|date',
            'image'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:2048'],
            'notes'              => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.material_id'   => 'nullable|exists:materials,id',
            'items.*.material_name' => 'nullable|string|max:255',
            'items.*.item_name'     => 'nullable|string|max:255',
            'items.*.unit_name'     => 'nullable|string|max:50',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.notes'         => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasRole('Admin PO Proyek') && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        // Project identification
        $projectId = $validated['project_id'] ?? null;
        if (empty($projectId) && !empty($userProjectIds)) {
            $projectId = $userProjectIds[0];
        }
        if (empty($projectId)) {
            $projectId = $user->warehouses()->whereNotNull('project_id')->value('project_id');
        }

        if ($isRoleAdminPOProyek && !empty($userProjectIds) && !in_array($projectId, $userProjectIds)) {
            return back()->withInput()->withErrors(['project_id' => 'Anda hanya berwenang mencatat nota untuk proyek yang ditugaskan kepada Anda.']);
        }

        $project = $projectId ? Project::find($projectId) : null;
        if (!$project) {
            return back()->withInput()->withErrors(['project_id' => 'Proyek wajib dipilih untuk pemotongan saldo kas operasional proyek.']);
        }

        // Calculate total amount from items first
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = (float)$item['quantity'] * (float)$item['unit_price'];
            $totalAmount += $subtotal;
        }

        if ($totalAmount <= 0) {
            return back()->withInput()->withErrors(['items' => 'Total nominal nota harus lebih besar dari Rp 0.']);
        }

        DB::beginTransaction();
        try {
            // Lock project for update & deduct balance
            $project = Project::where('id', $project->id)->lockForUpdate()->first();
            $balanceBefore = (float) $project->operational_balance;

            if ($balanceBefore < ($totalAmount - 0.001)) {
                $curSaldo = number_format($balanceBefore, 0, ',', '.');
                $needSaldo = number_format($totalAmount, 0, ',', '.');
                throw new \DomainException("Saldo kas operasional proyek '{$project->name}' tidak mencukupi (Tersedia: Rp {$curSaldo}, Dibutuhkan: Rp {$needSaldo}). Silakan lakukan Top-Up Saldo terlebih dahulu.");
            }

            $balanceAfter = $balanceBefore - $totalAmount;
            $project->update(['operational_balance' => $balanceAfter]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                SecureUploadService::validateSafeFile($request->file('image'), 'image');
                $imagePath = $this->handleReceiptUpload($request->file('image'));
            }

            $days = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $dayEnglish = date('l', strtotime($validated['receipt_date']));
            $dayLabel = $days[$dayEnglish] ?? $dayEnglish;

            $receipt = PurchaseReceipt::create([
                'purchase_order_id' => null,
                'project_id'        => $project->id,
                'project_name'      => $project->name,
                'supplier_name'     => $validated['supplier_name'] ?? null,
                'receipt_number'    => $validated['receipt_number'] ?? null,
                'receipt_date'      => $validated['receipt_date'],
                'day_label'         => $dayLabel,
                'payment_status'    => 'paid',
                'image_path'        => $imagePath,
                'notes'             => $validated['notes'] ?? null,
                'created_by'        => auth()->id(),
                'total_amount'      => $totalAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $material = !empty($item['material_id']) ? Material::find($item['material_id']) : null;
                $materialName = !empty($item['material_name']) ? trim($item['material_name']) : ($material ? $material->name : null);
                $itemName = !empty($item['item_name']) ? trim($item['item_name']) : ($materialName ?? 'Item Nota');
                if (empty($materialName)) {
                    $materialName = $itemName;
                }
                $unitName = !empty($item['unit_name']) ? trim($item['unit_name']) : ($material?->unit?->symbol ?? 'Bh');
                $subtotal = (float)$item['quantity'] * (float)$item['unit_price'];

                $receipt->items()->create([
                    'material_id'   => $item['material_id'] ?? null,
                    'material_name' => $materialName,
                    'item_name'     => $itemName,
                    'unit_name'     => $unitName,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'subtotal'      => $subtotal,
                    'notes'         => $item['notes'] ?? null,
                ]);
            }

            // Record log into ProjectBalanceLog
            ProjectBalanceLog::create([
                'project_id'       => $project->id,
                'user_id'          => auth()->id(),
                'type'             => 'payment_deduction',
                'amount'           => $totalAmount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $balanceAfter,
                'reference_number' => $receipt->receipt_number ?? "NOTA-{$receipt->id}",
                'notes'            => 'Pembayaran Instan Nota Harian #' . ($receipt->receipt_number ?? $receipt->id) . ($receipt->supplier_name ? ' - Toko: ' . $receipt->supplier_name : ''),
            ]);

            DB::commit();

            $formattedTotal = number_format($totalAmount, 0, ',', '.');
            $formattedRem   = number_format($balanceAfter, 0, ',', '.');

            return redirect()->route('purchase-receipts.show', $receipt)
                ->with('success', "⚡ Nota Pembelian Harian berhasil dicatat & LUNAS! Saldo kas operasional proyek '{$project->name}' otomatis terpotong Rp {$formattedTotal}. Sisa saldo: Rp {$formattedRem}.");
        } catch (\DomainException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan nota: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        $purchaseReceipt->load(['purchaseOrder.payments', 'project', 'supplier', 'creator', 'items.material.unit']);
        return view('purchase-receipts.show', compact('purchaseReceipt'));
    }

    public function print(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        $purchaseReceipt->load(['purchaseOrder', 'project', 'supplier', 'creator', 'items.material.unit']);
        return view('purchase-receipts.print', compact('purchaseReceipt'));
    }

    public function printWeekly(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        $date        = $purchaseReceipt->receipt_date ?? now();
        $startOfWeek = $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek   = $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $weekNumber  = $date->isoWeek();

        $project = $purchaseReceipt->project;

        $receipts = PurchaseReceipt::with(['project', 'supplier', 'creator', 'items.material.unit'])
            ->where('project_id', $purchaseReceipt->project_id)
            ->whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('receipt_date')
            ->orderBy('id')
            ->get();

        $bulanId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $bulanStart = $bulanId[$startOfWeek->month] ?? '';
        $bulanEnd   = $bulanId[$endOfWeek->month]   ?? '';

        $periodLabel  = 'Minggu ke-' . $weekNumber . ' (' . $startOfWeek->format('d') . ' ' . $bulanStart . ' — ' . $endOfWeek->format('d') . ' ' . $bulanEnd . ' ' . $endOfWeek->format('Y') . ')';
        $reportTitle  = 'LAPORAN KAS OPERASIONAL MINGGUAN ' . strtoupper($project?->name ?? '') . ' ' . $startOfWeek->format('Y');
        $reportType   = 'weekly';
        $periodYear   = $date->year;

        $backUrl     = route('purchase-receipts.show', $purchaseReceipt);
        $weeklyUrl   = route('purchase-receipts.print-weekly', $purchaseReceipt);
        $monthlyUrl  = route('purchase-receipts.print-monthly', $purchaseReceipt);

        return view('purchase-receipts.print-report', compact(
            'receipts', 'project', 'periodLabel', 'reportTitle', 'reportType',
            'periodYear', 'backUrl', 'weeklyUrl', 'monthlyUrl'
        ));
    }

    public function printMonthly(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        $date  = $purchaseReceipt->receipt_date ?? now();
        $month = $date->month;
        $year  = $date->year;

        $project = $purchaseReceipt->project;

        $receipts = PurchaseReceipt::with(['project', 'supplier', 'creator', 'items.material.unit'])
            ->where('project_id', $purchaseReceipt->project_id)
            ->whereYear('receipt_date', $year)
            ->whereMonth('receipt_date', $month)
            ->orderBy('receipt_date')
            ->orderBy('id')
            ->get();

        $bulanId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $namaBulan = $bulanId[$month] ?? '';

        $periodLabel = $namaBulan . ' ' . $year;
        $reportTitle = 'PENGELUARAN KAS OPERASIONAL ' . strtoupper($project?->name ?? '') . ' BULAN ' . strtoupper($namaBulan) . ' ' . $year;
        $reportType  = 'monthly';
        $periodYear  = $year;

        $backUrl    = route('purchase-receipts.show', $purchaseReceipt);
        $weeklyUrl  = route('purchase-receipts.print-weekly', $purchaseReceipt);
        $monthlyUrl = route('purchase-receipts.print-monthly', $purchaseReceipt);

        return view('purchase-receipts.print-report', compact(
            'receipts', 'project', 'periodLabel', 'reportTitle', 'reportType',
            'periodYear', 'backUrl', 'weeklyUrl', 'monthlyUrl'
        ));
    }

    public function weeklyReport(Request $request)
    {
        $this->authorize('view purchase receipts');

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasRole('Admin PO Proyek') && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $projectId = $request->project_id;
        if (empty($projectId) && !empty($userProjectIds)) {
            $projectId = $userProjectIds[0];
        }

        $project = null;
        if (!empty($projectId)) {
            $project = Project::find($projectId);
        } elseif (!$request->has('project_id')) {
            $project = Project::orderBy('name')->first();
        }

        if ($request->filled('date')) {
            $date = \Carbon\Carbon::parse($request->date);
        } elseif ($request->filled('year') && $request->filled('week')) {
            $date = \Carbon\Carbon::now()->setISODate((int)$request->year, (int)$request->week);
        } else {
            $date = now();
        }

        $startOfWeek = $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek   = $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $weekNumber  = $date->isoWeek();

        $query = PurchaseReceipt::with(['project', 'supplier', 'creator', 'items.material.unit'])
            ->whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);

        if ($project) {
            $query->where('project_id', $project->id);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $query->whereIn('project_id', $userProjectIds);
        }

        $receipts = $query->orderBy('receipt_date')->orderBy('id')->get();

        $bulanId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $bulanStart = $bulanId[$startOfWeek->month] ?? '';
        $bulanEnd   = $bulanId[$endOfWeek->month]   ?? '';

        $periodLabel  = 'Minggu ke-' . $weekNumber . ' (' . $startOfWeek->format('d') . ' ' . $bulanStart . ' — ' . $endOfWeek->format('d') . ' ' . $bulanEnd . ' ' . $endOfWeek->format('Y') . ')';
        $projectNameStr = $project ? strtoupper($project->name) : 'SEMUA PROYEK';
        $reportTitle  = 'LAPORAN KAS OPERASIONAL MINGGUAN ' . $projectNameStr . ' ' . $startOfWeek->format('Y');
        $reportType   = 'weekly';
        $periodYear   = $date->year;

        $backUrl     = route('purchase-receipts.index', array_filter(['project_id' => $project?->id]));
        $weeklyUrl   = route('purchase-receipts.weekly-report', array_filter(['project_id' => $project?->id, 'year' => $startOfWeek->year, 'week' => $weekNumber]));
        $monthlyUrl  = route('purchase-receipts.monthly-report', array_filter(['project_id' => $project?->id, 'year' => $startOfWeek->year, 'month' => $startOfWeek->month]));

        return view('purchase-receipts.print-report', compact(
            'receipts', 'project', 'periodLabel', 'reportTitle', 'reportType',
            'periodYear', 'backUrl', 'weeklyUrl', 'monthlyUrl'
        ));
    }

    public function monthlyReport(Request $request)
    {
        $this->authorize('view purchase receipts');

        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasRole('Admin PO Proyek') && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $projectId = $request->project_id;
        if (empty($projectId) && !empty($userProjectIds)) {
            $projectId = $userProjectIds[0];
        }

        $project = null;
        if (!empty($projectId)) {
            $project = Project::find($projectId);
        } elseif (!$request->has('project_id')) {
            $project = Project::orderBy('name')->first();
        }

        $year  = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? ($request->filled('date') ? \Carbon\Carbon::parse($request->date)->month : now()->month));

        $query = PurchaseReceipt::with(['project', 'supplier', 'creator', 'items.material.unit'])
            ->whereYear('receipt_date', $year)
            ->whereMonth('receipt_date', $month);

        if ($project) {
            $query->where('project_id', $project->id);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $query->whereIn('project_id', $userProjectIds);
        }

        $receipts = $query->orderBy('receipt_date')->orderBy('id')->get();

        $bulanId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $namaBulan = $bulanId[$month] ?? '';

        $periodLabel    = $namaBulan . ' ' . $year;
        $projectNameStr = $project ? strtoupper($project->name) : 'SEMUA PROYEK';
        $reportTitle    = 'PENGELUARAN KAS OPERASIONAL ' . $projectNameStr . ' BULAN ' . strtoupper($namaBulan) . ' ' . $year;
        $reportType     = 'monthly';
        $periodYear     = $year;

        $backUrl    = route('purchase-receipts.index', array_filter(['project_id' => $project?->id]));
        $weeklyUrl  = route('purchase-receipts.weekly-report', array_filter(['project_id' => $project?->id, 'year' => $year, 'week' => now()->isoWeek()]));
        $monthlyUrl = route('purchase-receipts.monthly-report', array_filter(['project_id' => $project?->id, 'year' => $year, 'month' => $month]));

        return view('purchase-receipts.print-report', compact(
            'receipts', 'project', 'periodLabel', 'reportTitle', 'reportType',
            'periodYear', 'backUrl', 'weeklyUrl', 'monthlyUrl'
        ));
    }

    public function updateStatus(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('create purchase receipts');

        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,paid,cancelled',
        ]);

        $purchaseReceipt->update([
            'payment_status' => $validated['payment_status'],
        ]);

        $label = $purchaseReceipt->status_label;
        $noNota = $purchaseReceipt->receipt_number ? "No. {$purchaseReceipt->receipt_number}" : "ID #{$purchaseReceipt->id}";

        return back()->with('success', "Status nota {$noNota} berhasil diubah menjadi: {$label}.");
    }

    public function destroy(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('delete purchase receipts');

        DB::beginTransaction();
        try {
            // Refund balance if paid
            if ($purchaseReceipt->payment_status === 'paid' && (float)$purchaseReceipt->total_amount > 0 && $purchaseReceipt->project_id) {
                $project = Project::find($purchaseReceipt->project_id);
                if ($project) {
                    $before = (float) $project->operational_balance;
                    $after = $before + (float) $purchaseReceipt->total_amount;
                    $project->update(['operational_balance' => $after]);

                    ProjectBalanceLog::create([
                        'project_id'       => $project->id,
                        'user_id'          => auth()->id(),
                        'type'             => 'topup',
                        'amount'           => (float) $purchaseReceipt->total_amount,
                        'balance_before'   => $before,
                        'balance_after'    => $after,
                        'reference_number' => $purchaseReceipt->receipt_number ?? "VOID-{$purchaseReceipt->id}",
                        'notes'            => 'Pengembalian saldo kas atas penghapusan nota harian #' . ($purchaseReceipt->receipt_number ?? $purchaseReceipt->id),
                    ]);
                }
            }

            if ($purchaseReceipt->image_path) {
                if (Storage::disk('private')->exists($purchaseReceipt->image_path)) {
                    Storage::disk('private')->delete($purchaseReceipt->image_path);
                } elseif (Storage::disk('local')->exists($purchaseReceipt->image_path)) {
                    Storage::disk('local')->delete($purchaseReceipt->image_path);
                } elseif (Storage::disk('public')->exists($purchaseReceipt->image_path)) {
                    Storage::disk('public')->delete($purchaseReceipt->image_path);
                }
            }
            $purchaseReceipt->delete();

            DB::commit();

            return redirect()->route('purchase-receipts.index')
                ->with('success', 'Nota Pembelian berhasil dihapus dan saldo kas dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus nota: ' . $e->getMessage());
        }
    }

    /**
     * Handle receipt upload using SecureUploadService (Anti-PHP2Shell, Randomized UUID/Hash, Private Storage).
     */
    protected function handleReceiptUpload($file): string
    {
        return SecureUploadService::processAndStore($file, 'purchase_receipts', 'private');
    }

    /**
     * Tampilkan berkas nota secara aman dari private storage.
     */
    public function file(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        if (!$purchaseReceipt->image_path) {
            abort(404, 'File nota tidak ditemukan.');
        }

        $path = $purchaseReceipt->image_path;
        $disk = null;

        if (Storage::disk('private')->exists($path)) {
            $disk = Storage::disk('private');
        } elseif (Storage::disk('local')->exists($path)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = Storage::disk('public');
        }

        if (!$disk) {
            abort(404, 'Berkas fisik nota tidak ditemukan di server.');
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $fileContents = $disk->get($path);

        return response($fileContents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ]);
    }
}

