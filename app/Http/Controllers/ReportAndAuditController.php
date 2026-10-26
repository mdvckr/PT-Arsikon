<?php

namespace App\Http\Controllers;

use App\Models\AuditActionPlan;
use App\Models\AuditFinding;
use App\Models\Category;
use App\Models\MonthlyAuditReport;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAndAuditController extends Controller
{
    public function __construct(private ReportService $service) {}

    /**
     * Menampilkan halaman utama Laporan & Audit beserta ringkasan KPI
     */
    public function index(Request $request)
    {
        $this->authorize('view reports');

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $latestMonthlyReport = MonthlyAuditReport::latest('report_period')->first();

        $today = Carbon::today();
        $auditSummary = [
            'open_count'        => AuditFinding::whereIn('status', ['Open', 'In Progress'])->count(),
            'overdue_count'     => AuditFinding::where('status', '!=', 'Closed')->whereDate('due_date', '<', $today)->count(),
            'total_exposure'    => (float) AuditFinding::where('status', '!=', 'Closed')->sum('financial_impact'),
            'closed_this_month' => AuditFinding::where('status', 'Closed')->whereMonth('closed_at', now()->month)->whereYear('closed_at', now()->year)->count(),
        ];

        $recentFindings = AuditFinding::with(['pic', 'warehouse', 'actionPlans.user', 'actionPlans.verifier'])
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('warehouses', 'categories', 'auditSummary', 'recentFindings', 'latestMonthlyReport'));
    }

    /**
     * 1. Laporan Stok Material (Posisi stok & estimasi nilai inventori)
     */
    public function materialStock(Request $request)
    {
        $this->authorize('view reports');

        $warehouseId = $request->filled('warehouse_id') && $request->warehouse_id !== 'all'
            ? $request->warehouse_id
            : session('active_warehouse_id');

        $categoryId = $request->filled('category_id') && $request->category_id !== 'all'
            ? $request->category_id
            : null;

        $data = $this->service->stockReport($warehouseId, $categoryId);

        return view('reports.stock', compact('data', 'warehouseId'));
    }

    /**
     * 2. Laporan Mutasi Stok (Arus barang Inbound & Outbound)
     */
    public function stockMutation(Request $request)
    {
        $this->authorize('view reports');

        $warehouseId = $request->filled('warehouse_id') && $request->warehouse_id !== 'all'
            ? $request->warehouse_id
            : session('active_warehouse_id');

        $dateFrom = $request->date_from ?? $request->start_date ?? date('Y-m-01');
        $dateTo = $request->date_to ?? $request->end_date ?? date('Y-m-d');

        $data = $this->service->mutationReport($warehouseId, $dateFrom, $dateTo);

        return view('reports.mutation', compact('data', 'warehouseId', 'dateFrom', 'dateTo'));
    }

    /**
     * 3. Laporan Diskrepansi Opname (Audit Stok Sistem vs Fisik)
     */
    public function opnameDiscrepancy(Request $request)
    {
        $this->authorize('view reports');

        $warehouseId = $request->filled('warehouse_id') && $request->warehouse_id !== 'all'
            ? $request->warehouse_id
            : session('active_warehouse_id');

        $data = $this->service->discrepancyReport($warehouseId);

        return view('reports.discrepancy', compact('data', 'warehouseId'));
    }

    /**
     * 4. Laporan Status Alat (Kondisi & ketersediaan inventori peralatan)
     */
    public function toolStatus(Request $request)
    {
        $this->authorize('view reports');

        $warehouseId = $request->filled('warehouse_id') && $request->warehouse_id !== 'all'
            ? $request->warehouse_id
            : null;

        $projectWarehouses = Warehouse::where('is_central', false)->orderBy('name')->get();
        $data = $this->service->toolReport();
        $centralToolsInProjects = $this->service->centralToolsInProjectsReport($warehouseId);

        return view('reports.tools', compact('data', 'centralToolsInProjects', 'projectWarehouses', 'warehouseId'));
    }

    /**
     * 5. Laporan Audit Pengadaan & Keuangan (PO vs Realisasi Penerimaan)
     */
    public function procurementAudit(Request $request)
    {
        $this->authorize('view reports');

        $query = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('warehouses as w', 'po.delivery_warehouse_id', '=', 'w.id')
            ->leftJoin('users as u', 'po.created_by', '=', 'u.id')
            ->select(
                'po.id',
                'po.po_number',
                'po.order_date',
                'po.expected_delivery',
                'po.delivery_warehouse_id',
                'po.supplier_id',
                DB::raw('COALESCE(s.name, po.supplier_name, "Vendor Umum") as supplier_name'),
                'w.name as warehouse_name',
                'po.total_amount',
                'po.paid_amount',
                'po.status',
                'u.name as creator_name',
                DB::raw('CASE WHEN po.expected_delivery IS NOT NULL AND CURDATE() > po.expected_delivery AND po.status NOT IN ("received", "closed", "cancelled") THEN DATEDIFF(CURDATE(), po.expected_delivery) ELSE 0 END as delay_days')
            );

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('po.order_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('po.status', $request->status);
        }

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('po.supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $query->where('po.delivery_warehouse_id', $request->warehouse_id);
        }

        $purchaseOrders = $query->orderBy('po.order_date', 'desc')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.procurement_audit', compact('purchaseOrders', 'suppliers', 'warehouses'));
    }

    /**
     * Submit Tindak Lanjut (Corrective Action Plan) oleh PIC
     */
    public function submitActionPlan(Request $request, AuditFinding $finding)
    {
        $request->validate([
            'corrective_action' => 'required|string|min:10',
            'evidence_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,xlsx,doc,docx|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('audit_evidences', 'public');
        }

        AuditActionPlan::create([
            'audit_finding_id'    => $finding->id,
            'user_id'             => auth()->id(),
            'corrective_action'   => $request->corrective_action,
            'evidence_file'       => $path,
            'verification_status' => 'Pending',
        ]);

        $finding->update(['status' => 'Under Review']);

        return back()->with('success', 'Rencana tindak lanjut dan bukti perbaikan berhasil dikirim. Menunggu verifikasi auditor.');
    }

    /**
     * Verifikasi Tindak Lanjut oleh Auditor / Manajemen
     */
    public function verifyActionPlan(Request $request, AuditActionPlan $actionPlan)
    {
        $request->validate([
            'verification_status' => 'required|in:Approved,Rejected',
            'verifier_notes'      => 'nullable|string',
        ]);

        $actionPlan->update([
            'verification_status' => $request->verification_status,
            'verifier_notes'      => $request->verifier_notes,
            'verified_by'         => auth()->id(),
            'verified_at'         => now(),
        ]);

        $finding = $actionPlan->auditFinding;
        if ($finding) {
            if ($request->verification_status === 'Approved') {
                $finding->update([
                    'status'    => 'Closed',
                    'closed_at' => now(),
                ]);
            } else {
                $finding->update([
                    'status' => 'In Progress',
                ]);
            }
        }

        return back()->with('success', 'Verifikasi tindak lanjut berhasil diperbarui.');
    }

    /**
     * Alias untuk KARTU 4: Laporan Status Alat
     */
    public function equipmentStatus(Request $request)
    {
        return $this->toolStatus($request);
    }

    /**
     * Update Status & Rencana Perbaikan langsung oleh PIC (sesuai spesifikasi)
     */
    public function updateFindingStatus(Request $request, AuditFinding $finding)
    {
        $validated = $request->validate([
            'status'            => 'required|in:Open,In Progress,Closed',
            'corrective_action' => 'required|string|min:10',
        ]);

        $finding->update([
            'status'            => $validated['status'],
            'corrective_action' => $validated['corrective_action'],
            'resolved_at'       => $validated['status'] === 'Closed' ? Carbon::now() : null,
            'closed_at'         => $validated['status'] === 'Closed' ? Carbon::now() : null,
        ]);

        return back()->with('success', "Status temuan {$finding->finding_code} berhasil diperbarui.");
    }
}
