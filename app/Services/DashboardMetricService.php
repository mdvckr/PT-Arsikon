<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialLedger;
use App\Models\MaterialLedgerEntry;
use App\Models\MaterialRequest;
use App\Models\MaterialUsage;
use App\Models\Payment;
use App\Models\PoDeliverySchedule;
use App\Models\Project;
use App\Models\ProjectToolRental;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\StockMutation;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMetricService {
    /**
     * Metrics for Admin Pengadaan Pusat (Procurement Command Center)
     */
    public function getProcurementCenterMetrics(): array
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $startOfMonth = $now->copy()->startOfMonth();

        // 1. PO Vendor Metrics
        $poActiveQuery = PurchaseOrder::whereIn('status', ['draft', 'submitted', 'approved', 'sent', 'partial_received']);
        $totalPOActive = $poActiveQuery->count();
        $poSubmittedCount = PurchaseOrder::where('status', 'submitted')->count();
        $poWaitingDelivery = PurchaseOrder::whereIn('status', ['approved', 'sent'])->count();
        $poCompletedMonth = PurchaseOrder::whereIn('status', ['received', 'completed'])->where('updated_at', '>=', $startOfMonth)->count();
        $totalPOAmountMonth = (float) PurchaseOrder::where('created_at', '>=', $startOfMonth)->sum('total_amount');

        // 2. PO Lapangan (Permintaan dari Proyek)
        $pendingFieldRequests = MaterialRequest::where('status', 'pending')->count();
        $approvedFieldRequests = MaterialRequest::where('status', 'approved')->count();

        // 3. Buku Catatan Proyek (Material & Sewa Alat)
        $totalLedgersCount = MaterialLedger::where('status', 'active')->count();
        $totalDeliveredVol = (float) MaterialLedger::sum('total_delivered_vol');
        $totalDepositVol   = (float) MaterialLedger::sum('total_deposit_vol');
        $totalRemainingVol = (float) MaterialLedger::sum('remaining_deposit_vol');
        $activeToolRentalsCount = ProjectToolRental::where('status', 'active')->count();
        $totalToolRentalCost    = (float) ProjectToolRental::where('status', 'active')->sum('total_rental_cost');

        // 4. Nota Pembelian Harian Lapangan (Minggu Ini)
        $weeklyReceiptsCount  = PurchaseReceipt::whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])->count();
        $weeklyReceiptsAmount = (float) PurchaseReceipt::whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])->sum('total_amount');

        // 5. 4-Week Spend Comparison (PO Vendor vs Nota Belanja Lapangan)
        $spendTimeline = [];
        for ($w = 3; $w >= 0; $w--) {
            $wStart = $now->copy()->subWeeks($w)->startOfWeek(Carbon::MONDAY);
            $wEnd   = $now->copy()->subWeeks($w)->endOfWeek(Carbon::SUNDAY);
            $wLabel = 'W' . $wStart->isoWeek() . ' (' . $wStart->format('d/m') . ')';

            $poSum = (float) PurchaseOrder::whereBetween('created_at', [$wStart, $wEnd])->sum('total_amount');
            $receiptSum = (float) PurchaseReceipt::whereBetween('receipt_date', [$wStart->format('Y-m-d'), $wEnd->format('Y-m-d')])->sum('total_amount');

            $spendTimeline[] = [
                'week_number' => $wStart->isoWeek(),
                'label'       => $wLabel,
                'po_spend'    => $poSum,
                'receipt_spend' => $receiptSum,
                'total_spend' => $poSum + $receiptSum,
            ];
        }

        // 6. Recent Active POs
        $recentPOs = PurchaseOrder::with(['supplier'])
            ->latest()
            ->limit(5)
            ->get();

        // 7. Recent PO Lapangan (Material Requests)
        $recentFieldRequests = MaterialRequest::with(['requestedBy', 'toWarehouse', 'fromWarehouse'])
            ->latest()
            ->limit(5)
            ->get();

        // 8. Finance / Payment overview
        $unpaidPOAmount = (float) PurchaseOrder::where('status', '!=', 'cancelled')->sum(DB::raw('total_amount - paid_amount'));
        $pendingPaymentsCount = Payment::where('status', 'pending')->count();

        return compact(
            'totalPOActive',
            'poSubmittedCount',
            'poWaitingDelivery',
            'poCompletedMonth',
            'totalPOAmountMonth',
            'pendingFieldRequests',
            'approvedFieldRequests',
            'totalLedgersCount',
            'totalDeliveredVol',
            'totalDepositVol',
            'totalRemainingVol',
            'activeToolRentalsCount',
            'totalToolRentalCost',
            'weeklyReceiptsCount',
            'weeklyReceiptsAmount',
            'spendTimeline',
            'recentPOs',
            'recentFieldRequests',
            'unpaidPOAmount',
            'pendingPaymentsCount'
        );
    }

    /**
     * Metrics for Admin Pengadaan Proyek (Site Purchasing & Notes Command Center)
     */
    public function getSitePurchasingMetrics(User $user): array
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $now->copy()->endOfWeek(Carbon::SUNDAY);

        // Find user's assigned project
        $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();
        $project = !empty($userProjectIds) ? Project::find($userProjectIds[0]) : Project::first();

        $projectId = $project?->id;

        // 1. Saldo Kasbon Operasional Proyek
        $operationalBalance = (float) ($project?->operational_balance ?? 0);

        // 2. Nota Belanja Harian Minggu Ini
        $receiptQuery = PurchaseReceipt::query();
        if ($projectId) {
            $receiptQuery->where('project_id', $projectId);
        }
        $weeklyReceiptsCount  = $receiptQuery->clone()->whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])->count();
        $weeklyReceiptsAmount = (float) $receiptQuery->clone()->whereBetween('receipt_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])->sum('total_amount');
        $totalReceiptsAllTime = (float) $receiptQuery->clone()->sum('total_amount');

        // 3. Buku Catatan Material (Deposit Suplier)
        $ledgerQuery = MaterialLedger::with(['entries', 'supplier']);
        if ($projectId) {
            $ledgerQuery->where('project_id', $projectId);
        }
        $primaryLedger = $ledgerQuery->latest('start_date')->first();
        $totalLedgersCount = $ledgerQuery->count();

        $deliveredVol = $primaryLedger ? (float) $primaryLedger->total_delivered_vol : 0;
        $depositVol   = $primaryLedger ? (float) $primaryLedger->total_deposit_vol : 0;
        $remainingVol = $primaryLedger ? (float) $primaryLedger->remaining_deposit_vol : 0;
        $materialAmountUsed = $primaryLedger ? (float) $primaryLedger->total_used_amount : 0;

        // 4. Sewa Alat Proyek Aktif
        $rentalQuery = ProjectToolRental::query();
        if ($projectId) {
            $rentalQuery->where('project_id', $projectId);
        }
        $activeRentalsCount = $rentalQuery->clone()->where('status', 'active')->count();
        $totalRentalCost    = (float) $rentalQuery->clone()->where('status', 'active')->sum('total_rental_cost');

        // 5. Streams: Recent Receipts & Recent Delivery Tickets
        $recentReceipts = PurchaseReceipt::with(['supplier'])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->latest('receipt_date')
            ->limit(5)
            ->get();

        $recentTickets = MaterialLedgerEntry::with(['ledger'])
            ->whereHas('ledger', fn($q) => $projectId ? $q->where('project_id', $projectId) : $q)
            ->latest('delivery_date')
            ->limit(5)
            ->get();

        $recentRentals = ProjectToolRental::when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->latest('start_date')
            ->limit(5)
            ->get();

        return compact(
            'project',
            'operationalBalance',
            'weeklyReceiptsCount',
            'weeklyReceiptsAmount',
            'totalReceiptsAllTime',
            'primaryLedger',
            'totalLedgersCount',
            'deliveredVol',
            'depositVol',
            'remainingVol',
            'materialAmountUsed',
            'activeRentalsCount',
            'totalRentalCost',
            'recentReceipts',
            'recentTickets',
            'recentRentals'
        );
    }

    /**
     * Metrics for Admin Gudang & Logistik (Preserved from existing Dashboard)
     */
    public function getWarehouseMetrics(User $user, ?int $warehouseId, array $accessibleIds): array
    {
        $totalMaterials = Material::whereHas('inventories', function ($q) use ($accessibleIds) {
            $q->whereIn('warehouse_id', $accessibleIds);
        })->count();

        $totalTools = Tool::whereHas('inventories', function ($q) use ($accessibleIds) {
            $q->whereIn('warehouse_id', $accessibleIds);
        })->count();

        $pendingRequests = MaterialRequest::where(function ($q) use ($accessibleIds) {
            $q->whereIn('from_warehouse_id', $accessibleIds)
              ->orWhereIn('to_warehouse_id', $accessibleIds);
        })->where('status', 'pending')->count();

        $lowStockItems = Inventory::where('warehouse_id', $warehouseId)
            ->where('quantity', '<=', DB::raw('min_stock'))
            ->where('min_stock', '>', 0)
            ->count();

        $recentRequests = MaterialRequest::with(['requestedBy', 'toWarehouse', 'fromWarehouse'])
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        $recentDistributions = Distribution::with(['fromWarehouse', 'toWarehouse'])
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        $mutations = StockMutation::where('created_at', '>=', now()->subDays(7))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('DATE(created_at) as date, reference_type as type, SUM(qty_change) as total')
            ->groupBy('date', 'reference_type')
            ->orderBy('date')
            ->get();

        $toolsReady = (int) Tool::sum('stock_available');
        $toolsInUse = ToolAssignment::where('status', 'active')
            ->where(function ($q) use ($accessibleIds) {
                $q->whereIn('from_warehouse_id', $accessibleIds)
                  ->orWhereIn('to_warehouse_id', $accessibleIds);
            })
            ->count();
        $toolsMaintenance = (int) Tool::sum('stock_maintenance');

        $toolStats = [
            'ready'       => $toolsReady,
            'active'      => $toolsInUse,
            'maintenance' => $toolsMaintenance,
        ];

        $inventoryByCategory = Inventory::with(['material.category'])
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->groupBy(fn($i) => $i->material?->category?->name ?? 'Lainnya')
            ->map(fn($items) => $items->sum('quantity'));

        $incomingTodayCount = PoDeliverySchedule::whereIn('status', ['planned', 'partial'])
            ->whereDate('planned_date', now()->toDateString())
            ->whereRaw('(planned_qty - received_qty) > 0')
            ->whereHas('purchaseOrderItem.purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'sent', 'partial_received']))
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($w) use ($warehouseId) {
                    $w->where('warehouse_id', $warehouseId)
                      ->orWhereHas('purchaseOrderItem.purchaseOrder', fn($poQ) => $poQ->where('delivery_warehouse_id', $warehouseId));
                });
            })
            ->count();

        $incomingLateCount = PoDeliverySchedule::whereIn('status', ['planned', 'partial'])
            ->whereNotNull('planned_date')
            ->whereDate('planned_date', '<', now()->toDateString())
            ->whereRaw('(planned_qty - received_qty) > 0')
            ->whereHas('purchaseOrderItem.purchaseOrder', fn($q) => $q->whereIn('status', ['approved', 'sent', 'partial_received']))
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where(function ($w) use ($warehouseId) {
                    $w->where('warehouse_id', $warehouseId)
                      ->orWhereHas('purchaseOrderItem.purchaseOrder', fn($poQ) => $poQ->where('delivery_warehouse_id', $warehouseId));
                });
            })
            ->count();

        return compact(
            'totalMaterials',
            'totalTools',
            'pendingRequests',
            'lowStockItems',
            'recentRequests',
            'recentDistributions',
            'mutations',
            'toolStats',
            'toolsReady',
            'toolsInUse',
            'toolsMaintenance',
            'inventoryByCategory',
            'incomingTodayCount',
            'incomingLateCount'
        );
    }

    /**
     * Metrics for Owner / Super Admin / Admin Pusat (Executive Bird's Eye Overview)
     */
    public function getExecutiveMetrics(): array
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth();

        $activeProjectsCount = Project::where('status', 'active')->count();
        $totalPOAmount = (float) PurchaseOrder::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalReceiptAmount = (float) PurchaseReceipt::sum('total_amount');
        $totalToolRentalAmount = (float) ProjectToolRental::sum('total_rental_cost');
        $totalProcurementSpend = $totalPOAmount + $totalReceiptAmount + $totalToolRentalAmount;

        $pendingApprovalPOCount = PurchaseOrder::where('status', 'submitted')->count();
        $pendingApprovalPOAmount = (float) PurchaseOrder::where('status', 'submitted')->sum('total_amount');

        // Fetch active projects with warehouse & legacy counts
        $projects = Project::with(['warehouse'])
            ->withCount(['purchaseReceipts', 'materialLedgers', 'toolRentals'])
            ->orderBy('name')
            ->get();

        $warehouseIds = $projects->pluck('warehouse.id')->filter()->unique()->values()->all();
        $projectIds   = $projects->pluck('id')->all();

        // 1. Usages today by warehouse
        $usagesTodayByWarehouse = !empty($warehouseIds)
            ? MaterialUsage::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('usage_date', $today)
                ->where('status', '!=', 'cancelled')
                ->with(['items.material'])
                ->get()
                ->groupBy('warehouse_id')
            : collect();

        // 2. Goods Receipts today by warehouse
        $goodsReceiptsTodayByWarehouse = !empty($warehouseIds)
            ? GoodsReceipt::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('receipt_date', $today)
                ->get()
                ->groupBy('warehouse_id')
            : collect();

        // 3. Purchase Receipts today by project
        $receiptsTodayByProject = !empty($projectIds)
            ? PurchaseReceipt::whereIn('project_id', $projectIds)
                ->whereDate('receipt_date', $today)
                ->get()
                ->groupBy('project_id')
            : collect();

        // 4. Last activity dates per warehouse & project
        $lastUsages = !empty($warehouseIds)
            ? MaterialUsage::whereIn('warehouse_id', $warehouseIds)
                ->where('status', '!=', 'cancelled')
                ->select('warehouse_id', DB::raw('MAX(usage_date) as max_date'))
                ->groupBy('warehouse_id')
                ->pluck('max_date', 'warehouse_id')
                ->all()
            : [];

        $lastReceipts = !empty($projectIds)
            ? PurchaseReceipt::whereIn('project_id', $projectIds)
                ->select('project_id', DB::raw('MAX(receipt_date) as max_date'))
                ->groupBy('project_id')
                ->pluck('max_date', 'project_id')
                ->all()
            : [];

        $lastLedgerEntries = !empty($projectIds)
            ? MaterialLedgerEntry::whereHas('ledger', fn($q) => $q->whereIn('project_id', $projectIds))
                ->select(DB::raw('material_ledgers.project_id'), DB::raw('MAX(material_ledger_entries.delivery_date) as max_date'))
                ->join('material_ledgers', 'material_ledgers.id', '=', 'material_ledger_entries.material_ledger_id')
                ->groupBy('material_ledgers.project_id')
                ->pluck('max_date', 'project_id')
                ->all()
            : [];

        // 5. Active Tools by warehouse & rentals by project
        $activeToolsByWarehouse = !empty($warehouseIds)
            ? ToolAssignment::whereIn('from_warehouse_id', $warehouseIds)
                ->where('status', 'active')
                ->select('from_warehouse_id', DB::raw('COUNT(*) as total'))
                ->groupBy('from_warehouse_id')
                ->pluck('total', 'from_warehouse_id')
                ->all()
            : [];

        $activeRentalsByProject = !empty($projectIds)
            ? ProjectToolRental::whereIn('project_id', $projectIds)
                ->where('status', 'active')
                ->select('project_id', DB::raw('COUNT(*) as total'))
                ->groupBy('project_id')
                ->pluck('total', 'project_id')
                ->all()
            : [];

        // Attach computed metrics to each project
        $projectsLoggedTodayCount = 0;
        foreach ($projects as $proj) {
            $whId = $proj->warehouse?->id;

            // Usages today
            $projectUsages = $whId && isset($usagesTodayByWarehouse[$whId])
                ? $usagesTodayByWarehouse[$whId]
                : collect();
            $todayUsagesCount = $projectUsages->count();

            // Sample material names from today's usage
            $sampleMaterials = [];
            foreach ($projectUsages as $usage) {
                foreach ($usage->items as $item) {
                    if ($item->material?->name && !in_array($item->material->name, $sampleMaterials)) {
                        $sampleMaterials[] = $item->material->name;
                    }
                    if (count($sampleMaterials) >= 2) break 2;
                }
            }

            // Goods receipts today
            $todayGoodsReceiptsCount = $whId && isset($goodsReceiptsTodayByWarehouse[$whId])
                ? $goodsReceiptsTodayByWarehouse[$whId]->count()
                : 0;

            // Receipts today
            $projectReceipts = isset($receiptsTodayByProject[$proj->id])
                ? $receiptsTodayByProject[$proj->id]
                : collect();
            $todayReceiptsCount = $projectReceipts->count();
            $todayReceiptsAmount = (float) $projectReceipts->sum('total_amount');

            // Has log today
            $hasLogToday = ($todayUsagesCount > 0 || $todayReceiptsCount > 0 || $todayGoodsReceiptsCount > 0);
            if ($hasLogToday) {
                $projectsLoggedTodayCount++;
            }

            // Find last activity date
            $candidateDates = array_filter([
                $whId && isset($lastUsages[$whId]) ? $lastUsages[$whId] : null,
                isset($lastReceipts[$proj->id]) ? $lastReceipts[$proj->id] : null,
                isset($lastLedgerEntries[$proj->id]) ? $lastLedgerEntries[$proj->id] : null,
            ]);
            $lastActivityDate = !empty($candidateDates) ? max($candidateDates) : null;

            // Active tools count
            $activeToolsCount = ($whId && isset($activeToolsByWarehouse[$whId]) ? (int) $activeToolsByWarehouse[$whId] : 0)
                + (isset($activeRentalsByProject[$proj->id]) ? (int) $activeRentalsByProject[$proj->id] : 0);

            // Assign attributes to project
            $proj->today_usages_count = $todayUsagesCount;
            $proj->today_sample_materials = $sampleMaterials;
            $proj->today_goods_receipts_count = $todayGoodsReceiptsCount;
            $proj->today_receipts_count = $todayReceiptsCount;
            $proj->today_receipts_amount = $todayReceiptsAmount;
            $proj->has_log_today = $hasLogToday;
            $proj->last_activity_date = $lastActivityDate;
            $proj->active_tools_count = $activeToolsCount;
        }

        $totalActiveProjectsCount = $projects->count();

        return compact(
            'activeProjectsCount',
            'totalPOAmount',
            'totalReceiptAmount',
            'totalToolRentalAmount',
            'totalProcurementSpend',
            'pendingApprovalPOCount',
            'pendingApprovalPOAmount',
            'projects',
            'projectsLoggedTodayCount',
            'totalActiveProjectsCount',
            'today'
        );
    }
}

