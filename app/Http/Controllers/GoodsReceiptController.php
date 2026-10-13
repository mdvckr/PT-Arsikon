<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\PoDeliverySchedule;
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

        $query = GoodsReceipt::with(['supplier', 'warehouse', 'creator', 'purchaseOrder', 'materialRequest'])->withCount('items')
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

        // Batasi ke gudang yang boleh diakses user (kecuali Owner/Admin)
        if (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO'])) {
            $allowedIds = $user->warehouses->pluck('id');
            $query->whereIn('warehouse_id', $allowedIds);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhere('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('purchaseOrder', fn($pq) => $pq->where('po_number', 'like', "%{$request->search}%"))
                  ->orWhereHas('materialRequest', fn($mq) => $mq->where('request_number', 'like', "%{$request->search}%"))
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

    public function create(Request $request)
    {
        $this->authorize('create goods receipts');

        $user       = auth()->user();
        $suppliers  = Supplier::orderBy('name')->get();
        $warehouses = $this->accessibleWarehouses();
        $materials  = Material::with(['unit', 'category', 'supplier', 'inventories.warehouse'])->orderBy('name')->get();

        $materialsJson = $materials->map(function ($m) {
            $stages = collect($m->incoming_stages ?? []);
            $planned = $stages->where('status', 'planned')->values();
            // supplier_name: dari field langsung, atau dari relasi jika ada
            $supplierName = $m->supplier_name ?: ($m->supplier?->name);
            $firstInv = $m->inventories->first();
            $warehouseIds = $m->inventories->pluck('warehouse_id')->unique()->values()->all();
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
                'warehouse_id'    => $firstInv?->warehouse_id,
                'warehouse_name'  => $firstInv?->warehouse?->name,
                'warehouse_ids'   => $warehouseIds,
                'incoming_stages' => $stages->toArray(),
                'planned_stages'  => $planned->toArray(),
                'has_stages'      => $stages->isNotEmpty(),
            ];
        });

        $tools = \App\Models\Tool::with(['category', 'currentWarehouse', 'inventories.warehouse'])->where('is_active', true)->orderBy('name')->get();

        $toolsJson = $tools->map(function ($t) {
            $stages = collect($t->incoming_stages ?? []);
            $planned = $stages->where('status', 'planned')->values();
            $whId = $t->current_warehouse_id ?? $t->inventories->first()?->warehouse_id;
            $whName = $t->currentWarehouse?->name ?? $t->inventories->first()?->warehouse?->name;
            $whIds = array_values(array_filter(array_unique(array_merge([$t->current_warehouse_id], $t->inventories->pluck('warehouse_id')->all()))));
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
                'warehouse_id'    => $whId,
                'warehouse_name'  => $whName,
                'warehouse_ids'   => $whIds,
                'incoming_stages' => $stages->toArray(),
                'planned_stages'  => $planned->toArray(),
                'has_stages'      => $stages->isNotEmpty(),
            ];
        });

        $accessibleWarehouseIds = $warehouses->pluck('id');

        // PO yang statusnya bisa diterima barangnya (approved, sent, atau partial_received)
        $poQuery = PurchaseOrder::with([
            'supplier',
            'deliveryWarehouse.project',
            'items.material',
            'items.tool',
            'items.materialRequestItem.materialRequest.toWarehouse.project'
        ])
        ->whereIn('status', ['approved', 'sent', 'partial_received']);

        if (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO'])) {
            $poQuery->where(function ($sub) use ($accessibleWarehouseIds) {
                $sub->whereIn('delivery_warehouse_id', $accessibleWarehouseIds)
                    ->orWhereHas('items.materialRequestItem.materialRequest', fn($mrQ) => $mrQ->whereIn('to_warehouse_id', $accessibleWarehouseIds))
                    ->orWhereHas('materialRequest', fn($mrQ) => $mrQ->whereIn('to_warehouse_id', $accessibleWarehouseIds));
            });
        }

        $purchaseOrders = $poQuery->orderBy('po_number')->get();

        // PO Lapangan (Material Request) yang aktif dan bisa diproses penerimaannya
        $mrQuery = \App\Models\MaterialRequest::with([
            'toWarehouse.project',
            'requestedBy',
            'purchaseOrders',
            'items.purchaseOrderItems.purchaseOrder',
            'distributions' => fn($dq) => $dq->where('status', 'in_transit')
        ])
        ->whereIn('status', ['approved', 'partially_fulfilled']);

        if (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO'])) {
            $mrQuery->whereIn('to_warehouse_id', $accessibleWarehouseIds);
        }

        $materialRequests = $mrQuery->latest()->get();

        // Surat Jalan yang sedang dikirim (in_transit) menuju gudang user
        $distQuery = \App\Models\Distribution::with(['fromWarehouse', 'toWarehouse.project', 'materialRequest'])
            ->where('status', 'in_transit');

        if (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO'])) {
            $distQuery->whereIn('to_warehouse_id', $accessibleWarehouseIds);
        }

        $inTransitDistributions = $distQuery->latest()->get();

        $selectedMrId = $request->query('from_mr_id', $request->query('material_request_id', $request->query('mr_id')));
        $selectedPoId = $request->query('purchase_order_id', $request->query('po_id'));

        return view('goods-receipts.create', compact(
            'suppliers', 'warehouses', 'materials', 'materialsJson', 'tools', 'toolsJson',
            'purchaseOrders', 'materialRequests', 'inTransitDistributions', 'selectedMrId', 'selectedPoId'
        ));
    }

    // ── AJAX: Ambil Jadwal Kedatangan dari po_delivery_schedules ──────────

    public function getScheduledIncoming(Request $request)
    {
        $this->authorize('create goods receipts');

        $date = $request->query('date', 'all');
        $warehouseId = $request->query('warehouse_id');

        $query = PoDeliverySchedule::with([
            'purchaseOrderItem.purchaseOrder.supplier',
            'purchaseOrderItem.purchaseOrder.deliveryWarehouse',
            'purchaseOrderItem.material.category',
            'purchaseOrderItem.material.unit',
            'purchaseOrderItem.tool.category',
            'warehouse',
        ])
        ->whereIn('status', ['planned', 'partial'])
        ->whereRaw('(planned_qty - received_qty) > 0')
        ->whereHas('purchaseOrderItem.purchaseOrder', function ($q) {
            $q->whereIn('status', ['approved', 'sent', 'partial_received']);
        });

        if (!empty($date) && $date !== 'all') {
            $query->whereDate('planned_date', $date);
        }

        if (!empty($warehouseId)) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->orWhereHas('purchaseOrderItem.purchaseOrder', fn($poQ) => $poQ->where('delivery_warehouse_id', $warehouseId));
            });
        }

        $schedules = $query->orderBy('planned_date', 'asc')->get();
        $results = [];

        foreach ($schedules as $sch) {
            $poItem = $sch->purchaseOrderItem;
            if (!$poItem) continue;

            $po = $poItem->purchaseOrder;
            $isTool = $poItem->isTool();

            $whId = $sch->warehouse_id ?? $po?->delivery_warehouse_id;
            $whName = $sch->warehouse?->name ?? $po?->deliveryWarehouse?->name;

            $code = $isTool
                ? ($poItem->tool?->code ?? '-')
                : ($poItem->material?->sku ?? $poItem->material?->code ?? 'NON-MASTER');

            $name = $poItem->displayName();
            $categoryName = $isTool
                ? ($poItem->tool?->category?->name ?? 'Alat / Mesin')
                : ($poItem->material?->category?->name ?? 'Material');

            $unit = $poItem->displayUnit();
            $remainingQty = (float) $sch->remaining_qty;

            $results[] = [
                'schedule_id'             => $sch->id,
                'id'                      => $sch->id,
                'purchase_order_id'       => $po?->id,
                'purchase_order_number'   => $po?->po_number,
                'purchase_order_item_id'  => $poItem->id,
                'po_delivery_schedule_id' => $sch->id,
                'item_type'               => $isTool ? 'tool' : 'material',
                'material_id'             => $poItem->material_id,
                'tool_id'                 => $poItem->tool_id,
                'code'                    => $code,
                'name'                    => $name,
                'category_name'           => $categoryName,
                'unit'                    => $unit,
                'stage_reference'         => $sch->stage_label ?? ('Tahap #' . $sch->id),
                'scheduled_date'          => $sch->planned_date ? $sch->planned_date->format('Y-m-d') : '-',
                'quantity'                => $remainingQty,
                'planned_qty'             => (float) $sch->planned_qty,
                'received_qty'            => (float) $sch->received_qty,
                'notes'                   => $sch->notes,
                'warehouse_id'            => $whId,
                'warehouse_name'          => $whName,
                'supplier_id'             => $po?->supplier_id,
                'supplier_name'           => $po?->supplier_name ?? $po?->supplier?->name,
                'is_late'                 => $sch->is_late,
            ];
        }

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

        $purchaseOrder->load([
            'materialRequest.toWarehouse.project',
            'items.material.unit',
            'items.material.category',
            'items.tool.category',
            'items.materialRequestItem.materialRequest.toWarehouse.project',
            'supplier',
            'deliveryWarehouse.project'
        ]);

        $items = $purchaseOrder->items->map(function ($item) {
            $isTool = $item->isTool();
            $mr = $item->materialRequestItem?->materialRequest ?? $item->purchaseOrder?->materialRequest;
            $qtyRemaining = max(0, (float) $item->quantity - (float) $item->received_qty);

            return [
                'purchase_order_item_id'   => $item->id,
                'material_request_item_id' => $item->material_request_item_id,
                'item_type'                => $isTool ? 'tool' : 'material',
                'material_id'              => $item->material_id,
                'tool_id'                  => $item->tool_id,
                'material_name'            => $item->displayName(),
                'material_code'            => $isTool ? ($item->tool?->code ?? '-') : ($item->material?->code ?? 'NON-MASTER'),
                'category_name'            => $isTool ? ($item->tool?->category?->name ?? 'Alat / Mesin') : ($item->material?->category?->name ?? 'Material Khusus Proyek'),
                'unit_abbr'                => $item->displayUnit(),
                'qty_ordered'              => (float) $item->quantity,
                'qty_received'             => (float) $item->received_qty,
                'qty_remaining'            => $qtyRemaining > 0 ? $qtyRemaining : (float) $item->quantity,
                'unit_price'               => (float) $item->unit_price,
                'mr_id'                    => $mr?->id,
                'mr_number'                => $mr?->request_number,
                'project_name'             => $mr?->toWarehouse?->project?->name ?? $mr?->toWarehouse?->name,
            ];
        })->values();

        $linkedMr = $purchaseOrder->materialRequest ?? $purchaseOrder->items->pluck('materialRequestItem.materialRequest')->filter()->first();

        return response()->json([
            'po_id'               => $purchaseOrder->id,
            'po_number'           => $purchaseOrder->po_number,
            'supplier_id'         => $purchaseOrder->supplier_id,
            'supplier_name'       => $purchaseOrder->supplier?->name ?? $purchaseOrder->supplier_name,
            'warehouse_id'        => $purchaseOrder->delivery_warehouse_id,
            'material_request_id' => $linkedMr?->id,
            'mr_number'           => $linkedMr?->request_number,
            'project_name'        => $linkedMr?->toWarehouse?->project?->name ?? $linkedMr?->toWarehouse?->name,
            'items'               => $items,
        ]);
    }

    // ── AJAX: Load MR Items (PO Lapangan) untuk di-prefill ke form ──────────

    public function getMrItems(\App\Models\MaterialRequest $materialRequest)
    {
        $this->authorize('create goods receipts');

        $materialRequest->load([
            'items.material.unit',
            'items.material.category',
            'items.tool.category',
            'items.purchaseOrderItems.purchaseOrder.supplier',
            'purchaseOrders.supplier',
            'toWarehouse.project'
        ]);

        $items = $materialRequest->items->map(function ($item) use ($materialRequest) {
            $isTool = $item->tool_id !== null || ($item->item_type ?? '') === 'tool';
            $approved = (float) ($item->qty_approved > 0 ? $item->qty_approved : $item->qty_requested);
            $received = (float) $item->qty_received;
            $remaining = max(0, $approved - $received);
            $linkedPoi = $item->purchaseOrderItems->first();

            return [
                'item_type'                => $isTool ? 'tool' : 'material',
                'material_request_item_id' => $item->id,
                'purchase_order_item_id'   => $linkedPoi?->id,
                'material_id'              => $item->material_id,
                'tool_id'                  => $item->tool_id,
                'material_name'            => $item->displayName(),
                'material_code'            => $isTool ? ($item->tool?->code ?? '-') : ($item->material?->code ?? 'NON-MASTER'),
                'category_name'            => $item->displayCategoryName(),
                'unit_abbr'                => $item->displayUnit(),
                'qty_ordered'              => $approved,
                'qty_received'             => $received,
                'qty_remaining'            => $remaining > 0 ? $remaining : $approved,
                'unit_price'               => (float) ($linkedPoi?->unit_price ?? 0),
                'mr_id'                    => $materialRequest->id,
                'mr_number'                => $materialRequest->request_number,
            ];
        })->values();

        $linkedPo = $materialRequest->purchaseOrders()->latest()->first() 
            ?? $materialRequest->items->flatMap->purchaseOrderItems->pluck('purchaseOrder')->filter()->first();

        return response()->json([
            'material_request_id' => $materialRequest->id,
            'mr_number'           => $materialRequest->request_number,
            'warehouse_id'        => $materialRequest->to_warehouse_id,
            'warehouse_name'      => $materialRequest->toWarehouse?->project?->name ?? $materialRequest->toWarehouse?->name,
            'purchase_order_id'   => $linkedPo?->id,
            'supplier_id'         => $linkedPo?->supplier_id,
            'supplier_name'       => $linkedPo?->supplier?->name ?? $linkedPo?->supplier_name,
            'items'               => $items,
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorize('create goods receipts');

        // Auto-register custom items from PO or Material Request if material_id is not yet created
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $v) {
                if (empty($v['material_id']) && empty($v['tool_id'])) {
                    if (!empty($v['purchase_order_item_id'])) {
                        $poItem = \App\Models\PurchaseOrderItem::find($v['purchase_order_item_id']);
                        if ($poItem && !empty($poItem->custom_item_name)) {
                            $mrItem = $poItem->materialRequestItem;
                            $catId  = $mrItem?->category_id ?: ($v['category_id'] ?? null);
                            $spec   = $mrItem?->specification ?: ($v['specification'] ?? null);
                            $typeName = $mrItem?->custom_item_type ?: ($poItem->custom_item_type ?? ($v['type'] ?? null));
                            $mat    = Material::autoRegisterCustom(
                                $poItem->custom_item_name,
                                $poItem->custom_item_unit ?? $poItem->unit,
                                $catId,
                                $typeName,
                                null,
                                $spec
                            );
                            $items[$k]['material_id'] = $mat->id;
                            $poItem->update(['material_id' => $mat->id]);
                            if ($poItem->material_request_item_id) {
                                $poItem->materialRequestItem?->update(['material_id' => $mat->id]);
                            }
                        }
                    } elseif (!empty($v['material_request_item_id'])) {
                        $mrItem = \App\Models\MaterialRequestItem::find($v['material_request_item_id']);
                        if ($mrItem && !empty($mrItem->custom_item_name)) {
                            $catId = $mrItem->category_id ?: ($v['category_id'] ?? null);
                            $spec  = $mrItem->specification ?: ($v['specification'] ?? null);
                            $typeName = $mrItem->custom_item_type ?: ($v['type'] ?? null);
                            $mat   = Material::autoRegisterCustom(
                                $mrItem->custom_item_name,
                                $mrItem->custom_item_unit ?? 'unit',
                                $catId,
                                $typeName,
                                null,
                                $spec
                            );
                            $items[$k]['material_id'] = $mat->id;
                            $mrItem->update(['material_id' => $mat->id]);
                        }
                    } elseif (!empty($v['custom_item_name'])) {
                        $mat = Material::autoRegisterCustom(
                            $v['custom_item_name'],
                            $v['custom_item_unit'] ?? 'unit',
                            !empty($v['category_id']) ? (int) $v['category_id'] : null,
                            $v['type'] ?? null,
                            $v['brand'] ?? null,
                            $v['specification'] ?? ($v['size'] ?? null)
                        );
                        $items[$k]['material_id'] = $mat->id;
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        // Fallback warehouse_id jika dari PO atau PO Lapangan
        if (!$request->filled('warehouse_id')) {
            if ($request->filled('purchase_order_id')) {
                $po = PurchaseOrder::find($request->purchase_order_id);
                if ($po?->delivery_warehouse_id) {
                    $request->merge(['warehouse_id' => $po->delivery_warehouse_id]);
                }
            } elseif ($request->filled('material_request_id')) {
                $mr = \App\Models\MaterialRequest::find($request->material_request_id);
                if ($mr?->to_warehouse_id) {
                    $request->merge(['warehouse_id' => $mr->to_warehouse_id]);
                }
            }
        }

        // Tentukan apakah supplier dari list atau ketik bebas
        $supplierIdRaw = $request->input('supplier_id');
        $cleanedSupplierName = preg_replace('/\s+/', ' ', trim($request->input('supplier_name', '')));
        $isSupplierFromList = !empty($supplierIdRaw) && is_numeric($supplierIdRaw);

        // Jika tidak pilih dari list tapi ada nama ketik
        if (!$isSupplierFromList && !empty($cleanedSupplierName)) {
            // Cari atau buat supplier baru secara case-insensitive
            $supplier = \App\Models\Supplier::whereRaw('LOWER(name) = ?', [mb_strtolower($cleanedSupplierName)])->first();
            if (!$supplier) {
                $supplier = \App\Models\Supplier::create([
                    'name'    => $cleanedSupplierName,
                    'phone'   => null,
                    'address' => null,
                ]);
            }
            $request->merge(['supplier_id' => $supplier->id]);
        }

        $validated = $request->validate([
            'purchase_order_id'              => 'nullable|exists:purchase_orders,id',
            'material_request_id'            => 'nullable|exists:material_requests,id',
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
            'items.*.material_request_item_id'=> 'nullable|exists:material_request_items,id',
            'items.*.po_delivery_schedule_id'=> 'nullable|exists:po_delivery_schedules,id',
            'items.*.quantity'               => 'required|numeric|min:0.01',
            'items.*.qty_shipped'            => 'nullable|numeric|min:0',
            'items.*.qty_damaged'            => 'nullable|numeric|min:0',
            'items.*.unit_price'             => 'nullable|numeric|min:0',
            'items.*.notes'                  => 'nullable|string|max:500',
        ]);

        $accessibleWarehouseIds = $this->accessibleWarehouses()->pluck('id');
        if (!$accessibleWarehouseIds->contains($request->warehouse_id)) {
            return back()->withInput()->with('error', 'Anda tidak memiliki hak akses untuk mencatat penerimaan di gudang ini.');
        }

        // Validasi T14: qty alat selalu bilangan bulat di GR
        foreach ($request->input('items', []) as $idx => $item) {
            $isTool = ($item['item_type'] ?? '') === 'tool' || !empty($item['tool_id']);
            if (!$isTool && !empty($item['purchase_order_item_id'])) {
                $poi = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);
                if ($poi && $poi->isTool()) {
                    $isTool = true;
                }
            }

            if ($isTool) {
                $qty = $item['quantity'] ?? null;
                $shipped = $item['qty_shipped'] ?? null;
                $damaged = $item['qty_damaged'] ?? null;
                if (!is_null($qty) && fmod((float)$qty, 1) != 0) {
                    return back()->withInput()->withErrors(["items.{$idx}.quantity" => "Kuantitas alat harus berupa bilangan bulat."]);
                }
                if (!is_null($shipped) && fmod((float)$shipped, 1) != 0) {
                    return back()->withInput()->withErrors(["items.{$idx}.qty_shipped" => "Kuantitas alat yang dikirim harus berupa bilangan bulat."]);
                }
                if (!is_null($damaged) && fmod((float)$damaged, 1) != 0) {
                    return back()->withInput()->withErrors(["items.{$idx}.qty_damaged" => "Kuantitas alat rusak harus berupa bilangan bulat."]);
                }
            }
        }

        // Simpan nama supplier dan penerima ke validated
        $validated['supplier_name'] = $cleanedSupplierName ?: (\App\Models\Supplier::find($validated['supplier_id'])?->name);
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
            'confirmedBy', 'purchaseOrder', 'materialRequest.toWarehouse.project',
            'items.material.unit', 'items.material.category',
            'items.tool.category', 'items.materialRequestItem',
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

        if ($user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::where('is_active', true)
                ->orWhere('is_central', true)
                ->orderBy('name')
                ->get();
        }

        return $user->warehouses()->orderBy('name')->get();
    }
}
