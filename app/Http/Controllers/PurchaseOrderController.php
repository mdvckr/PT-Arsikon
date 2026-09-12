<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProcurementRequest;
use App\Models\Supplier;
use App\Models\Material;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier','creator','items']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('po_number', 'like', "%{$request->search}%");

        $pos = $query->latest()->paginate(15)->withQueryString();
        return view('purchase-orders.index', compact('pos'));
    }

    public function create(Request $request)
    {
        $suppliers    = Supplier::orderBy('name')->get();
        $materials    = Material::with('unit')->orderBy('name')->get();
        $approvedPRs  = ProcurementRequest::where('status','approved')->with('items.material')->get();
        $selectedPR   = $request->pr_id ? ProcurementRequest::with('items.material.unit')->find($request->pr_id) : null;
        return view('purchase-orders.create', compact('suppliers','materials','approvedPRs','selectedPR'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'procurement_request_id'=> 'nullable|exists:procurement_requests,id',
            'order_date'           => 'required|date',
            'expected_delivery'    => 'nullable|date|after_or_equal:order_date',
            'terms'                => 'nullable|string',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.material_id'  => 'required|exists:materials,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id'           => $validated['supplier_id'],
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
            $subtotal = $item['quantity'] * $item['unit_price'];
            $total += $subtotal;
            $po->items()->create([
                'material_id' => $item['material_id'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'subtotal'    => $subtotal,
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
        $purchaseOrder->load(['supplier','creator','approver','items.material.unit','payments.creator','procurementRequest']);
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
