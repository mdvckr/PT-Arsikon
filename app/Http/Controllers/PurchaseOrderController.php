<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProcurementRequest;
use App\Models\MaterialRequestItem;
use App\Models\Supplier;
use App\Models\Material;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || (!$user->hasAnyRole(['Owner', 'Super Admin', 'Admin Gudang Pusat', 'Admin', 'Admin PO']) && !$user->can('view purchase orders'))) {
                abort(403, 'Akses ditolak: Hanya Admin Gudang Pusat dan Admin PO yang berhak mengakses Purchase Order.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'creator',
            'items.material.unit',
            'items.materialRequestItem',
            'receipts'
        ]);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('po_number', 'like', "%{$request->search}%")
                  ->orWhere('supplier_name', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('items', function ($it) use ($request) {
                      $it->where('custom_item_name', 'like', "%{$request->search}%")
                         ->orWhereHas('material', fn($m) => $m->where('name', 'like', "%{$request->search}%"));
                  });
            });
        }

        if ($request->date) {
            $query->whereDate('order_date', $request->date);
        }

        // Urutkan berdasarkan tanggal order terbaru, lalu ID terbaru
        $pos = $query->orderBy('order_date', 'desc')->orderBy('id', 'desc')->paginate(30)->withQueryString();

        // Kelompokkan koleksi halaman ini berdasarkan tanggal order (format 'Y-m-d')
        $groupedPOs = $pos->getCollection()->groupBy(function ($po) {
            return $po->order_date ? $po->order_date->format('Y-m-d') : 'Tanpa Tanggal';
        });

        return view('purchase-orders.index', compact('pos', 'groupedPOs'));
    }

    public function create(Request $request)
    {
        $suppliers    = Supplier::orderBy('name')->get();
        $materials    = Material::with('unit')->orderBy('name')->get();
        $approvedPRs  = ProcurementRequest::where('status','approved')->with('items.material')->get();
        $selectedPR   = $request->pr_id ? ProcurementRequest::with('items.material.unit')->find($request->pr_id) : null;
        
        $selectedMR = null;
        if ($request->from_mr_id) {
            $mr = \App\Models\MaterialRequest::with(['items.material.unit', 'fromWarehouse'])->find($request->from_mr_id);
            if ($mr) {
                // Hanya boleh membuat PO jika terdapat item manual yang tidak ada di inventori
                $customItemsCount = $mr->items->whereNull('material_id')->count();
                if ($customItemsCount === 0) {
                    return redirect()->route('material-requests.show', $mr)
                        ->with('error', "Permintaan #{$mr->request_number} tidak memiliki barang manual. Seluruh barang tersedia di master inventori dan langsung diproses melalui Surat Jalan.");
                }
                $selectedMR = $mr;
            }
        }

        // Ambil item custom dari Permintaan Material (MR) yang belum dibatalkan/ditolak
        $mrCustomItems = MaterialRequestItem::whereNull('material_id')
            ->whereHas('materialRequest', function ($q) {
                $q->whereIn('status', ['submitted', 'approved', 'partially_fulfilled']);
            })
            ->with(['materialRequest.fromWarehouse'])
            ->latest()
            ->get();

        return view('purchase-orders.create', compact('suppliers', 'materials', 'approvedPRs', 'selectedPR', 'selectedMR', 'mrCustomItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'                      => 'nullable|exists:suppliers,id',
            'supplier_name'                    => 'nullable|string|max:255',
            'procurement_request_id'           => 'nullable|exists:procurement_requests,id',
            'order_date'                       => 'required|date',
            'expected_delivery'                => 'nullable|date|after_or_equal:order_date',
            'terms'                            => 'nullable|string',
            'notes'                            => 'nullable|string',
            'items'                            => 'required|array|min:1',
            'items.*.material_id'              => 'nullable|exists:materials,id',
            'items.*.custom_item_name'         => 'nullable|string|max:255',
            'items.*.custom_item_unit'         => 'nullable|string|max:50',
            'items.*.material_request_item_id' => 'nullable|exists:material_request_items,id',
            'items.*.quantity'                 => 'required|numeric|min:0.01',
            'items.*.unit_price'               => 'required|numeric|min:0',
        ]);

        $supplierId = !empty($validated['supplier_id']) ? $validated['supplier_id'] : null;
        $supplierName = !empty(trim($validated['supplier_name'] ?? '')) ? trim($validated['supplier_name']) : null;

        if (!$supplierId && !$supplierName) {
            return back()->withInput()->withErrors([
                'supplier_id' => 'Silakan pilih supplier terdaftar atau ketik nama supplier baru.'
            ]);
        }

        if ($supplierId && !$supplierName) {
            $regSupplier = Supplier::find($supplierId);
            $supplierName = $regSupplier?->name;
        }

        // Validasi: tiap baris wajib memilih material master ATAU mengisi nama barang baru/custom
        foreach ($request->input('items', []) as $idx => $item) {
            $hasMaterial = !empty($item['material_id']);
            $hasCustomName = !empty(trim($item['custom_item_name'] ?? ''));
            if (!$hasMaterial && !$hasCustomName) {
                return back()->withInput()->withErrors([
                    "items.{$idx}.material_id" => "Baris ke-" . ($idx + 1) . ": Silakan pilih material dari master atau tentukan barang baru/custom."
                ]);
            }
        }

        $po = PurchaseOrder::create([
            'supplier_id'           => $supplierId,
            'supplier_name'         => $supplierName,
            'procurement_request_id'=> $validated['procurement_request_id'] ?? null,
            'created_by'            => auth()->id(),
            'status'                => 'draft',
            'order_date'            => $validated['order_date'],
            'expected_delivery'     => $validated['expected_delivery'] ?? null,
            'terms'                 => $validated['terms'] ?? null,
            'notes'                 => $validated['notes'] ?? null,
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = (float)$item['quantity'] * (float)$item['unit_price'];
            $total += $subtotal;

            $customName = !empty($item['custom_item_name']) ? trim($item['custom_item_name']) : null;
            $customUnit = !empty($item['custom_item_unit']) ? trim($item['custom_item_unit']) : null;
            $materialId = !empty($item['material_id']) ? $item['material_id'] : null;
            $mrItemId   = !empty($item['material_request_item_id']) ? $item['material_request_item_id'] : null;

            $po->items()->create([
                'material_id'              => $materialId,
                'custom_item_name'         => $customName,
                'custom_item_unit'         => $customUnit,
                'material_request_item_id' => $mrItemId,
                'quantity'                 => $item['quantity'],
                'unit_price'               => $item['unit_price'],
                'subtotal'                 => $subtotal,
                'unit'                     => $customUnit,
            ]);
        }
        $po->update(['total_amount' => $total]);

        if ($po->procurement_request_id) {
            ProcurementRequest::find($po->procurement_request_id)->update(['status' => 'po_created']);
        }

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "PO #{$po->po_number} berhasil dibuat.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'creator',
            'approver',
            'items.material.unit',
            'items.materialRequestItem.materialRequest.fromWarehouse',
            'payments.creator',
            'procurementRequest',
            'receipts.creator'
        ]);
        $printTemplate = \App\Models\PrintTemplate::activeForPO();
        $allTemplates  = \App\Models\PrintTemplate::latest()->get();
        return view('purchase-orders.show', compact('purchaseOrder', 'printTemplate', 'allTemplates'));
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'sent']);
        return back()->with('success', "PO #{$purchaseOrder->po_number} dikirim ke supplier.");
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'cancelled']);
        return back()->with('success', "PO #{$purchaseOrder->po_number} dibatalkan.");
    }
}
