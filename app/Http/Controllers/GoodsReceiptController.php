<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Material;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $service) {}

    // ── Index ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('view goods receipts');

        $user        = auth()->user();
        $warehouseId = $request->warehouse_id ?? session('active_warehouse_id');

        $query = GoodsReceipt::with(['supplier', 'warehouse', 'creator'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

        // Batasi ke gudang yang boleh diakses user (kecuali Owner/Admin)
        if (!$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            $allowedIds = $user->warehouses->pluck('id');
            $query->whereIn('warehouse_id', $allowedIds);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhere('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $receipts   = $query->latest()->paginate(15)->withQueryString();
        $warehouses = $this->accessibleWarehouses();

        return view('goods-receipts.index', compact('receipts', 'warehouses', 'warehouseId'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create goods receipts');

        $suppliers  = Supplier::orderBy('name')->get();
        $warehouses = $this->accessibleWarehouses();
        $materials  = Material::with(['unit', 'category'])->orderBy('name')->get();

        $materialsJson = $materials->map(fn($m) => [
            'id'            => $m->id,
            'code'          => $m->code,
            'name'          => $m->name,
            'type'          => $m->type,
            'category_name' => $m->category?->name ?? 'Lainnya',
            'abbr'          => $m->unit?->abbreviation,
            'price'         => $m->unit_price,
        ]);

        // PO yang statusnya bisa diterima barangnya (sent atau partial_received)
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.material'])
            ->whereIn('status', ['sent', 'partial_received'])
            ->orderBy('po_number')
            ->get();

        return view('goods-receipts.create', compact(
            'suppliers', 'warehouses', 'materials', 'materialsJson', 'purchaseOrders'
        ));
    }

    // ── AJAX: Load PO Items untuk di-prefill ke form ──────────────────────

    public function getPoItems(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('create goods receipts');

        $purchaseOrder->load(['items.material.unit', 'items.material.category', 'supplier']);

        $items = $purchaseOrder->items->map(fn($item) => [
            'purchase_order_item_id' => $item->id,
            'material_id'            => $item->material_id,
            'material_name'          => $item->material?->name ?? $item->custom_item_name,
            'material_code'          => $item->material?->code ?? 'NON-MASTER',
            'category_name'          => $item->material?->category?->name ?? 'Material Khusus Proyek',
            'unit_abbr'              => $item->material?->unit?->abbreviation ?? $item->custom_item_unit ?? $item->unit ?? 'unit',
            'qty_ordered'            => (float) $item->quantity,
            'qty_received'           => (float) $item->received_qty,
            'qty_remaining'          => max(0, (float) $item->quantity - (float) $item->received_qty),
            'unit_price'             => (float) $item->unit_price,
        ])->filter(fn($i) => $i['qty_remaining'] > 0)->values();

        return response()->json([
            'supplier_id'   => $purchaseOrder->supplier_id,
            'supplier_name' => $purchaseOrder->supplier?->name,
            'items'         => $items,
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorize('create goods receipts');

        // Auto-register custom items from PO if material_id is not yet created
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $v) {
                if (empty($v['material_id']) && !empty($v['purchase_order_item_id'])) {
                    $poItem = \App\Models\PurchaseOrderItem::find($v['purchase_order_item_id']);
                    if ($poItem && !empty($poItem->custom_item_name)) {
                        $mat = Material::autoRegisterCustom($poItem->custom_item_name, $poItem->custom_item_unit ?? $poItem->unit);
                        $items[$k]['material_id'] = $mat->id;
                        $poItem->update(['material_id' => $mat->id]);
                        if ($poItem->material_request_item_id) {
                            $poItem->materialRequestItem?->update(['material_id' => $mat->id]);
                        }
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'purchase_order_id'          => 'nullable|exists:purchase_orders,id',
            'supplier_id'                => 'required|exists:suppliers,id',
            'warehouse_id'               => 'required|exists:warehouses,id',
            'received_at'                => 'required|date',
            'invoice_number'             => 'nullable|string|max:100',
            'notes'                      => 'nullable|string|max:2000',
            'items'                      => 'required|array|min:1',
            'items.*.material_id'            => 'required|exists:materials,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.quantity'               => 'required|numeric|min:0.01',
            'items.*.unit_price'             => 'required|numeric|min:0',
        ]);

        $receipt = $this->service->create($validated, auth()->id());

        return redirect()->route('goods-receipts.show', $receipt)
            ->with('success', "Penerimaan barang #{$receipt->receipt_number} berhasil disimpan sebagai draft.");
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('view goods receipts');
        $goodsReceipt->load([
            'supplier', 'warehouse', 'creator', 'receivedBy',
            'confirmedBy', 'purchaseOrder', 'items.material.unit', 'items.material.category',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    // ── Confirm ───────────────────────────────────────────────────────────

    public function confirm(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('confirm goods receipts');

        try {
            $this->service->confirm($goodsReceipt, auth()->id());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penerimaan barang dikonfirmasi. Stok gudang diperbarui.');
    }

    // ── Helper ────────────────────────────────────────────────────────────

    protected function accessibleWarehouses()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::where('is_active', true)
                ->orWhere('is_central', true)
                ->orderBy('name')
                ->get();
        }

        return $user->warehouses()->orderBy('name')->get();
    }
}
