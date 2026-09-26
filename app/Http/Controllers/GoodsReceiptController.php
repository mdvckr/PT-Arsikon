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

        $query = GoodsReceipt::with(['supplier', 'warehouse', 'creator'])->withCount('items')
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
        $materials  = Material::with(['unit', 'category', 'supplier'])->orderBy('name')->get();

        $materialsJson = $materials->map(function ($m) {
            $stages = collect($m->incoming_stages ?? []);
            $planned = $stages->where('status', 'planned')->values();
            // supplier_name: dari field langsung, atau dari relasi jika ada
            $supplierName = $m->supplier_name ?: ($m->supplier?->name);
            return [
                'id'              => $m->id,
                'code'            => $m->sku ?? $m->code,
                'sku'             => $m->sku ?? $m->code,
                'name'            => $m->name,
                'brand'           => $m->brand,
                'type'            => $m->type,
                'size'            => $m->size,
                'category_name'   => $m->category?->name ?? 'Lainnya',
                'abbr'            => $m->unit?->abbreviation ?? $m->unit?->name ?? 'Unit',
                'price'           => $m->unit_price ?? 0,
                'supplier_id'     => $m->supplier_id ?? null,
                'supplier_name'   => $supplierName,
                'incoming_stages' => $stages->toArray(),
                'planned_stages'  => $planned->toArray(),
                'has_stages'      => $stages->isNotEmpty(),
            ];
        });

        $tools = \App\Models\Tool::with(['category'])->where('is_active', true)->orderBy('name')->get();

        $toolsJson = $tools->map(function ($t) {
            $stages = collect($t->incoming_stages ?? []);
            $planned = $stages->where('status', 'planned')->values();
            return [
                'id'              => $t->id,
                'code'            => $t->code,
                'sku'             => $t->code,
                'name'            => $t->name,
                'brand'           => $t->brand,
                'type'            => $t->type,
                'size'            => $t->size,
                'category_name'   => $t->category?->name ?? 'Alat / Mesin',
                'abbr'            => 'Unit',
                'price'           => 0,
                'incoming_stages' => $stages->toArray(),
                'planned_stages'  => $planned->toArray(),
                'has_stages'      => $stages->isNotEmpty(),
            ];
        });

        // PO yang statusnya bisa diterima barangnya (sent atau partial_received)
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.material'])
            ->whereIn('status', ['sent', 'partial_received'])
            ->orderBy('po_number')
            ->get();

        return view('goods-receipts.create', compact(
            'suppliers', 'warehouses', 'materials', 'materialsJson', 'tools', 'toolsJson', 'purchaseOrders'
        ));
    }

    // ── AJAX: Ambil Jadwal Kedatangan dari Tahapan (incoming_stages) ──────

    public function getScheduledIncoming(Request $request)
    {
        $this->authorize('create goods receipts');

        $date = $request->query('date', 'all');
        $results = [];

        // 1. Ambil dari incoming_stages Material yang berstatus 'planned'
        $materials = Material::with(['unit', 'category', 'supplier'])
            ->whereNotNull('incoming_stages')
            ->get();

        foreach ($materials as $m) {
            if (empty($m->incoming_stages)) continue;
            foreach ($m->incoming_stages as $idx => $stg) {
                if (($stg['status'] ?? '') === 'planned') {
                    if (empty($date) || $date === 'all' || ($stg['date'] ?? '') === $date) {
                        $results[] = [
                            'item_type'       => 'material',
                            'material_id'     => $m->id,
                            'tool_id'         => null,
                            'code'            => $m->code ?? $m->sku ?? '-',
                            'name'            => $m->name,
                            'category_name'   => $m->category?->name ?? 'Material',
                            'unit'            => $m->unit?->abbreviation ?? 'Unit',
                            'stage_reference' => $stg['stage'] ?? ("Tahap " . ($idx + 1)),
                            'scheduled_date'  => $stg['date'] ?? '-',
                            'quantity'        => (float) ($stg['qty'] ?? 1),
                            'notes'           => $stg['notes'] ?? null,
                            'supplier_id'     => $m->supplier_id ?? null,
                            'supplier_name'   => $m->supplier_name ?? $m->supplier?->name ?? null,
                        ];
                    }
                }
            }
        }

        // 2. Ambil dari incoming_stages Tool yang berstatus 'planned'
        $tools = \App\Models\Tool::with(['category'])
            ->whereNotNull('incoming_stages')
            ->get();

        foreach ($tools as $t) {
            if (empty($t->incoming_stages)) continue;
            foreach ($t->incoming_stages as $idx => $stg) {
                if (($stg['status'] ?? '') === 'planned') {
                    if (empty($date) || $date === 'all' || ($stg['date'] ?? '') === $date) {
                        $results[] = [
                            'item_type'       => 'tool',
                            'material_id'     => null,
                            'tool_id'         => $t->id,
                            'code'            => $t->code ?? '-',
                            'name'            => $t->name,
                            'category_name'   => $t->category?->name ?? 'Alat / Mesin',
                            'unit'            => 'Unit',
                            'stage_reference' => $stg['stage'] ?? ("Tahap " . ($idx + 1)),
                            'scheduled_date'  => $stg['date'] ?? '-',
                            'quantity'        => (float) ($stg['qty'] ?? 1),
                            'notes'           => $stg['notes'] ?? null,
                            'supplier_id'     => null,
                            'supplier_name'   => null,
                        ];
                    }
                }
            }
        }

        // Urutkan berdasarkan tanggal kedatangan terdekat
        usort($results, function ($a, $b) {
            return strcmp($a['scheduled_date'], $b['scheduled_date']);
        });

        return response()->json([
            'date'  => $date,
            'count' => count($results),
            'items' => $results,
        ]);
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

        // Tentukan apakah supplier dari list atau ketik bebas
        $supplierIdRaw = $request->input('supplier_id');
        $supplierNameRaw = trim($request->input('supplier_name', ''));
        $isSupplierFromList = !empty($supplierIdRaw) && is_numeric($supplierIdRaw);

        // Jika tidak pilih dari list tapi ada nama ketik
        if (!$isSupplierFromList && !empty($supplierNameRaw)) {
            // Cari atau buat supplier baru
            $supplier = \App\Models\Supplier::firstOrCreate(
                ['name' => $supplierNameRaw],
                ['name' => $supplierNameRaw, 'phone' => null, 'address' => null]
            );
            $request->merge(['supplier_id' => $supplier->id]);
        }

        $validated = $request->validate([
            'purchase_order_id'              => 'nullable|exists:purchase_orders,id',
            'supplier_id'                    => 'required|exists:suppliers,id',
            'supplier_name'                  => 'nullable|string|max:255',
            'warehouse_id'                   => 'required|exists:warehouses,id',
            'received_at'                    => 'required|date',
            'received_by_name'               => 'nullable|string|max:150',
            'invoice_number'                 => 'nullable|string|max:100',
            'notes'                          => 'nullable|string|max:2000',
            'items'                          => 'required|array|min:1',
            'items.*.item_type'              => 'nullable|in:material,tool',
            'items.*.material_id'            => 'nullable|required_without:items.*.tool_id|exists:materials,id',
            'items.*.tool_id'                => 'nullable|required_without:items.*.material_id|exists:tools,id',
            'items.*.stage_reference'        => 'nullable|string|max:100',
            'items.*.condition'              => 'nullable|in:good,damaged,reject',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.quantity'               => 'required|numeric|min:0.01',
            'items.*.unit_price'             => 'nullable|numeric|min:0',
            'items.*.notes'                  => 'nullable|string|max:500',
        ]);

        $accessibleWarehouseIds = $this->accessibleWarehouses()->pluck('id');
        if (!$accessibleWarehouseIds->contains($request->warehouse_id)) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses untuk mencatat penerimaan di gudang ini.');
        }

        // Simpan nama supplier dan penerima ke validated
        $validated['supplier_name'] = $supplierNameRaw ?: (\App\Models\Supplier::find($validated['supplier_id'])?->name);
        $validated['received_by_name'] = trim($request->input('received_by_name', '')) ?: null;

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
            'confirmedBy', 'purchaseOrder', 
            'items.material.unit', 'items.material.category',
            'items.tool.category',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    // ── Confirm ───────────────────────────────────────────────────────────

    public function confirm(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('confirm goods receipts');

        if (!$goodsReceipt->canUserConfirm(auth()->user())) {
            return back()->with('error', 'Hanya petugas di gudang tujuan penerimaan yang berhak mengonfirmasi penerimaan barang ini.');
        }

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
