<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\MaterialRequestItem;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || (!$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO']) && !$user->can('view purchase orders'))) {
                abort(403, 'Akses ditolak: Hanya Admin Pusat, Admin Gudang Pusat, dan Admin PO yang berhak mengakses Purchase Order.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with([
            'supplier',
            'creator',
            'materialRequest',
            'procurementRequest',
            'items.material.unit',
            'items.materialRequestItem.materialRequest',
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
                  ->orWhereHas('materialRequest', fn($mr) => $mr->where('request_number', 'like', "%{$request->search}%"))
                  ->orWhereHas('procurementRequest', fn($pr) => $pr->where('pr_number', 'like', "%{$request->search}%"))
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
        $this->authorize('create purchase orders');
        $suppliers    = Supplier::orderBy('name')->get();
        $materials    = Material::with('unit')->orderBy('name')->get();
        $approvedPRs  = ProcurementRequest::whereIn('status', ['approved', 'partially_ordered'])->with('items.material')->get();
        $selectedPR   = $request->pr_id ? ProcurementRequest::with('items.material.unit')->find($request->pr_id) : null;
        $warehouses   = \App\Models\Warehouse::where('is_active', true)->orderBy('name')->get();

        // Ambil seluruh PO Lapangan (MR) yang approved atau partially_fulfilled yang butuh pengadaan
        $approvedMRs = \App\Models\MaterialRequest::whereIn('status', ['approved', 'partially_fulfilled'])
            ->with(['items.material.unit', 'items.tool', 'fromWarehouse', 'toWarehouse.project'])
            ->whereHas('items', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('fulfillment', 'purchase')
                        ->orWhereNull('material_id');
                });
            })
            ->latest()
            ->get()
            ->filter(function ($mr) {
                return $mr->items->contains(function ($it) {
                    $sisa = (float) $it->qty_approved > 0
                        ? (float) $it->qty_approved - (float) $it->qty_ordered
                        : (float) $it->qty_requested - (float) $it->qty_ordered;
                    return ($it->fulfillment === 'purchase' || empty($it->material_id)) && $sisa > 0;
                });
            })
            ->values();

        // Peta item PO Lapangan untuk interaktivitas JavaScript di antarmuka
        $mrItemsMap = $approvedMRs->mapWithKeys(function ($mr) {
            $items = $mr->items->filter(function ($it) {
                $sisa = (float) $it->qty_approved > 0
                    ? (float) $it->qty_approved - (float) $it->qty_ordered
                    : (float) $it->qty_requested - (float) $it->qty_ordered;
                return ($it->fulfillment === 'purchase' || empty($it->material_id)) && $sisa > 0;
            })->map(function ($it) use ($mr) {
                $sisa = (float) $it->qty_approved > 0
                    ? (float) $it->qty_approved - (float) $it->qty_ordered
                    : (float) $it->qty_requested - (float) $it->qty_ordered;
                return [
                    'id'               => $it->id,
                    'material_id'      => $it->material_id,
                    'tool_id'          => $it->tool_id,
                    'name'             => $it->displayName(),
                    'is_custom'        => $it->isCustom(),
                    'custom_item_name' => $it->custom_item_name,
                    'custom_item_type' => $it->custom_item_type,
                    'unit'             => $it->displayUnit(),
                    'quota'            => round($sisa, 2),
                    'notes'            => $it->notes,
                    'price'            => (float) ($it->material?->unit_price ?? 0),
                    'mr_num'           => $mr->request_number,
                    'warehouse'        => $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name ?? 'Proyek',
                ];
            })->values();

            return [$mr->id => [
                'id'              => $mr->id,
                'request_number'  => $mr->request_number,
                'to_warehouse_id' => $mr->to_warehouse_id,
                'project_name'    => $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name,
                'items'           => $items,
            ]];
        });

        $selectedMR = null;
        $mrPurchaseItems = collect();
        $targetMrId = $request->from_mr_id ?? old('material_request_id');
        if ($targetMrId) {
            $mr = \App\Models\MaterialRequest::with(['items.material.unit', 'items.tool', 'fromWarehouse', 'toWarehouse.project'])->find($targetMrId);
            if ($mr) {
                // T7: Dukung seluruh item master & custom dengan fulfillment purchase dan sisa kuota > 0
                $purchaseCandidates = $mr->items->filter(function ($it) use ($mr) {
                    if (in_array($mr->status, ['approved', 'partially_fulfilled'])) {
                        $sisa = (float) $it->qty_approved > 0
                            ? (float) $it->qty_approved - (float) $it->qty_ordered
                            : (float) $it->qty_requested - (float) $it->qty_ordered;
                        return ($it->fulfillment === 'purchase' || empty($it->material_id)) && $sisa > 0;
                    }
                    // Jika submitted, dukung item custom atau purchase untuk preview/notifikasi
                    return empty($it->material_id) || $it->fulfillment === 'purchase';
                })->values();

                if ($purchaseCandidates->isEmpty() && $request->from_mr_id) {
                    return redirect()->route('material-requests.show', $mr)
                        ->with('error', "PO Lapangan #{$mr->request_number} tidak memiliki item dengan jalur pembelian vendor atau seluruh kuota pengadaan telah habis.");
                }

                $selectedMR = $mr;
                $mrPurchaseItems = $purchaseCandidates;
            }
        }

        // Ambil item custom dari Permintaan Material (MR) yang belum dibatalkan/ditolak
        $mrCustomItems = MaterialRequestItem::whereNull('material_id')
            ->whereHas('materialRequest', function ($q) {
                $q->whereIn('status', ['submitted', 'approved', 'partially_fulfilled']);
            })
            ->with(['materialRequest.fromWarehouse', 'materialRequest.toWarehouse.project'])
            ->latest()
            ->get();

        return view('purchase-orders.create', compact('suppliers', 'materials', 'approvedPRs', 'selectedPR', 'approvedMRs', 'mrItemsMap', 'selectedMR', 'mrPurchaseItems', 'mrCustomItems', 'warehouses'));
    }

    public function store(Request $request)
    {
        $this->authorize('create purchase orders');
        $validated = $request->validate([
            'supplier_id'                      => 'nullable|exists:suppliers,id',
            'supplier_name'                    => 'nullable|string|max:255',
            'material_request_id'              => 'nullable|exists:material_requests,id',
            'procurement_request_id'           => 'nullable|exists:procurement_requests,id',
            'delivery_warehouse_id'            => 'nullable|exists:warehouses,id',
            'risk_bearer'                      => 'nullable|in:vendor,buyer',
            'order_date'                       => 'required|date',
            'expected_delivery'                => 'nullable|date|after_or_equal:order_date',
            'terms'                            => 'nullable|string',
            'notes'                            => 'nullable|string',
            'items'                            => 'required|array|min:1',
            'items.*.material_id'              => 'nullable|exists:materials,id',
            'items.*.tool_id'                  => 'nullable|exists:tools,id',
            'items.*.custom_item_name'         => 'nullable|string|max:255',
            'items.*.custom_item_type'         => 'nullable|string|max:100',
            'items.*.custom_item_unit'         => 'nullable|string|max:50',
            'items.*.material_request_item_id' => 'nullable|exists:material_request_items,id',
            'items.*.procurement_request_item_id' => 'nullable|exists:procurement_request_items,id',
            'items.*.quantity'                 => 'required|numeric|min:0.01',
            'items.*.unit_price'               => 'required|numeric|min:0',
            'items.*.schedules'                => 'nullable|array',
            'items.*.schedules.*.stage_label'  => 'nullable|string|max:100',
            'items.*.schedules.*.planned_date' => 'nullable|date',
            'items.*.schedules.*.planned_qty'  => 'required|numeric|min:0.01',
            'items.*.schedules.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'items.*.schedules.*.notes'        => 'nullable|string',
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

        $deliveryWarehouseId = !empty($validated['delivery_warehouse_id']) ? $validated['delivery_warehouse_id'] : null;

        // Validasi tiap baris: sumber pengadaan wajib sah (item MR approved ATAU PR approved)
        foreach ($request->input('items', []) as $idx => $item) {
            $hasMaterial = !empty($item['material_id']);
            $hasTool = !empty($item['tool_id']);
            $hasCustomName = !empty(trim($item['custom_item_name'] ?? ''));
            if (!$hasMaterial && !$hasTool && !$hasCustomName) {
                return back()->withInput()->withErrors([
                    "items.{$idx}.material_id" => "Baris ke-" . ($idx + 1) . ": Silakan pilih material dari master atau tentukan barang baru/custom."
                ]);
            }

            // Validasi T14: qty alat selalu bilangan bulat di PO
            if ($hasTool) {
                $qty = $item['quantity'] ?? null;
                if (!is_null($qty) && fmod((float)$qty, 1) != 0) {
                    return back()->withInput()->withErrors([
                        "items.{$idx}.quantity" => "Kuantitas alat harus berupa bilangan bulat."
                    ]);
                }
                if (!empty($item['schedules']) && is_array($item['schedules'])) {
                    foreach ($item['schedules'] as $sIdx => $sch) {
                        $planQty = $sch['planned_qty'] ?? null;
                        if (!is_null($planQty) && fmod((float)$planQty, 1) != 0) {
                            return back()->withInput()->withErrors([
                                "items.{$idx}.schedules.{$sIdx}.planned_qty" => "Kuantitas jadwal alat harus berupa bilangan bulat."
                            ]);
                        }
                    }
                }
            }

            if (!empty($item['schedules']) && is_array($item['schedules'])) {
                $totalPlanned = collect($item['schedules'])->sum(fn($s) => (float)($s['planned_qty'] ?? 0));
                if ($totalPlanned > (float)($item['quantity'] ?? 0)) {
                    return back()->withInput()->withErrors([
                        "items.{$idx}.schedules" => "Baris ke-" . ($idx + 1) . ": Total kuantitas jadwal ({$totalPlanned}) melebihi kuantitas pemesanan ({$item['quantity']})."
                    ]);
                }
            }

            $mrItemId = $item['material_request_item_id'] ?? null;
            $prItemId = $item['procurement_request_item_id'] ?? null;
            $hasPrId  = !empty($validated['procurement_request_id']);

            // T7: PO wajib punya sumber: item MR approved ATAU PR approved. PO tanpa sumber ditolak.
            if (!$mrItemId && !$prItemId && !$hasPrId) {
                return back()->withInput()->withErrors([
                    "items.{$idx}" => "Baris ke-" . ($idx + 1) . ": Purchase Order wajib memiliki sumber pengadaan yang sah (item PO Lapangan yang telah disetujui atau Permintaan Pengadaan PR yang telah disetujui)."
                ]);
            }

            if ($mrItemId) {
                $mrItem = MaterialRequestItem::with('materialRequest')->find($mrItemId);
                if (!$mrItem) {
                    return back()->withInput()->withErrors([
                        "items.{$idx}.material_request_item_id" => "Item PO Lapangan tidak ditemukan."
                    ]);
                }

                if (!in_array($mrItem->materialRequest?->status, ['approved', 'partially_fulfilled'])) {
                    return back()->withInput()->withErrors([
                        "items.{$idx}.material_request_item_id" => "PO Lapangan #{$mrItem->materialRequest?->request_number} belum disetujui (status: {$mrItem->materialRequest?->status})."
                    ]);
                }

                if ($mrItem->fulfillment === 'stock') {
                    $matName = $mrItem->displayName();
                    return back()->withInput()->withErrors([
                        "items.{$idx}.material_request_item_id" => "Item {$matName} dialokasikan untuk pemenuhan stok gudang (Surat Jalan), tidak dapat dibuatkan PO."
                    ]);
                }

                $approvedQty = (float) $mrItem->qty_approved > 0 ? (float) $mrItem->qty_approved : (float) $mrItem->qty_requested;
                $sisaKuotaMR = max(0, $approvedQty - (float) $mrItem->qty_ordered);
                if ((float) $item['quantity'] > $sisaKuotaMR) {
                    $matName = $mrItem->displayName();
                    return back()->withInput()->withErrors([
                        "items.{$idx}.quantity" => "Jumlah pemesanan {$matName} ({$item['quantity']}) melebihi sisa kuota MR ({$sisaKuotaMR})."
                    ]);
                }

                if (!$deliveryWarehouseId && $mrItem->materialRequest?->to_warehouse_id) {
                    $deliveryWarehouseId = $mrItem->materialRequest->to_warehouse_id;
                }
            }

            if ($prItemId || $hasPrId) {
                if (!$prItemId && $hasPrId && $hasMaterial) {
                    $prItemId = ProcurementRequestItem::where('procurement_request_id', $validated['procurement_request_id'])
                        ->where('material_id', $item['material_id'])
                        ->value('id');
                }

                if ($prItemId) {
                    $prItem = ProcurementRequestItem::with('procurementRequest')->find($prItemId);
                    if (!$prItem) {
                        return back()->withInput()->withErrors([
                            "items.{$idx}.procurement_request_item_id" => "Item Permintaan Pengadaan (PR) tidak ditemukan."
                        ]);
                    }

                    if (!in_array($prItem->procurementRequest?->status, ['approved', 'partially_ordered'])) {
                        return back()->withInput()->withErrors([
                            "items.{$idx}.procurement_request_item_id" => "Permintaan Pengadaan (PR) belum disetujui (status: {$prItem->procurementRequest?->status})."
                        ]);
                    }

                    $sisaKuota = (float) $prItem->quantity - (float) $prItem->qty_ordered;
                    if ((float) $item['quantity'] > $sisaKuota) {
                        $matName = $prItem->material?->name ?? 'Material';
                        return back()->withInput()->withErrors([
                            "items.{$idx}.quantity" => "Jumlah pemesanan {$matName} ({$item['quantity']}) melebihi sisa kuota PR ({$sisaKuota})."
                        ]);
                    }
                }
            }
        }

        $materialRequestId = !empty($validated['material_request_id']) ? $validated['material_request_id'] : null;
        if (!$materialRequestId) {
            foreach ($request->input('items', []) as $item) {
                if (!empty($item['material_request_item_id'])) {
                    $materialRequestId = MaterialRequestItem::where('id', $item['material_request_item_id'])->value('material_request_id');
                    if ($materialRequestId) {
                        break;
                    }
                }
            }
        }

        if (!$deliveryWarehouseId && $materialRequestId) {
            $deliveryWarehouseId = MaterialRequest::find($materialRequestId)?->to_warehouse_id;
        }

        $po = DB::transaction(function () use ($validated, $supplierId, $supplierName, $deliveryWarehouseId, $materialRequestId) {
            $po = PurchaseOrder::create([
                'supplier_id'           => $supplierId,
                'supplier_name'         => $supplierName,
                'material_request_id'   => $materialRequestId,
                'procurement_request_id'=> $validated['procurement_request_id'] ?? null,
                'delivery_warehouse_id' => $deliveryWarehouseId,
                'risk_bearer'           => $validated['risk_bearer'] ?? 'vendor',
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
                $toolId     = !empty($item['tool_id']) ? $item['tool_id'] : null;
                $mrItemId   = !empty($item['material_request_item_id']) ? $item['material_request_item_id'] : null;
                $prItemId   = !empty($item['procurement_request_item_id']) ? $item['procurement_request_item_id'] : null;

                if (!$prItemId && !empty($validated['procurement_request_id']) && $materialId) {
                    $prItemId = ProcurementRequestItem::where('procurement_request_id', $validated['procurement_request_id'])
                        ->where('material_id', $materialId)
                        ->value('id');
                }

                if ($mrItemId) {
                    $mrItem = MaterialRequestItem::where('id', $mrItemId)->lockForUpdate()->firstOrFail();
                    $approvedQty = (float) $mrItem->qty_approved > 0 ? (float) $mrItem->qty_approved : (float) $mrItem->qty_requested;
                    $sisaKuota = max(0, $approvedQty - (float) $mrItem->qty_ordered);
                    if ((float) $item['quantity'] > $sisaKuota) {
                        throw new \Exception("Jumlah pemesanan ({$item['quantity']}) melebihi sisa kuota PO Lapangan ({$sisaKuota}).");
                    }
                    $mrItem->increment('qty_ordered', (float) $item['quantity']);
                    if (!$toolId && $mrItem->tool_id) {
                        $toolId = $mrItem->tool_id;
                    }
                }

                if ($prItemId) {
                    $prItem = ProcurementRequestItem::where('id', $prItemId)->lockForUpdate()->firstOrFail();
                    $sisaKuota = max(0, (float) $prItem->quantity - (float) $prItem->qty_ordered);
                    if ((float) $item['quantity'] > $sisaKuota) {
                        throw new \Exception("Jumlah pemesanan ({$item['quantity']}) melebihi sisa kuota PR ({$sisaKuota}).");
                    }
                    $prItem->increment('qty_ordered', (float) $item['quantity']);
                }

                $poItem = $po->items()->create([
                    'material_id'                 => $materialId,
                    'tool_id'                     => $toolId,
                    'custom_item_name'            => $customName,
                    'custom_item_type'            => $mrItem?->custom_item_type ?: ($item['custom_item_type'] ?? null),
                    'custom_item_unit'            => $customUnit,
                    'material_request_item_id'    => $mrItemId,
                    'procurement_request_item_id' => $prItemId,
                    'quantity'                    => $item['quantity'],
                    'unit_price'                  => $item['unit_price'],
                    'subtotal'                    => $subtotal,
                    'unit'                        => $customUnit,
                ]);

                $schedules = $item['schedules'] ?? [];
                if (!empty($schedules)) {
                    foreach ($schedules as $sIdx => $sch) {
                        $poItem->deliverySchedules()->create([
                            'stage_label'  => !empty($sch['stage_label']) ? trim($sch['stage_label']) : ('Tahap ' . ($sIdx + 1)),
                            'planned_date' => $sch['planned_date'] ?? $po->expected_delivery,
                            'planned_qty'  => $sch['planned_qty'],
                            'warehouse_id' => $sch['warehouse_id'] ?? $deliveryWarehouseId,
                            'status'       => 'planned',
                            'notes'        => $sch['notes'] ?? null,
                        ]);
                    }
                } else {
                    $poItem->deliverySchedules()->create([
                        'stage_label'  => 'Pengiriman Penuh',
                        'planned_date' => $po->expected_delivery ?? now()->toDateString(),
                        'planned_qty'  => $item['quantity'],
                        'warehouse_id' => $deliveryWarehouseId,
                        'status'       => 'planned',
                    ]);
                }
            }
            $po->update(['total_amount' => $total]);

            if ($po->procurement_request_id) {
                $pr = ProcurementRequest::where('id', $po->procurement_request_id)->lockForUpdate()->first();
                if ($pr) {
                    $totalRequested = (float) $pr->items()->sum('quantity');
                    $totalOrdered = (float) $pr->items()->sum('qty_ordered');
                    if ($totalOrdered >= $totalRequested && $totalRequested > 0) {
                        $pr->update(['status' => 'fully_ordered']);
                    } elseif ($totalOrdered > 0) {
                        $pr->update(['status' => 'partially_ordered']);
                    } else {
                        $pr->update(['status' => 'approved']);
                    }
                }
            }

            return $po;
        });

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
            'items.deliverySchedules',
            'items.materialRequestItem.materialRequest.fromWarehouse',
            'payments.creator',
            'procurementRequest',
            'receipts.creator'
        ]);
        $printTemplate = \App\Models\PrintTemplate::activeForPO();
        $allTemplates  = \App\Models\PrintTemplate::latest()->get();
        return view('purchase-orders.show', compact('purchaseOrder', 'printTemplate', 'allTemplates'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (! (auth()->user()->can('approve purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin']))) {
            abort(403);
        }

        return DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if ($po->created_by === auth()->id()) {
                return back()->with('error', 'Pembuat PO tidak dapat menyetujui PO buatannya sendiri.');
            }

            if ($po->status !== 'draft') {
                return back()->with('error', 'Hanya PO berstatus draft yang dapat disetujui.');
            }

            $po->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return back()->with('success', "PO #{$po->po_number} disetujui.");
        });
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        if (! (auth()->user()->can('send purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin', 'Admin Gudang Pusat', 'Admin PO', 'Direktur', 'Manajer Operasional']))) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengirim purchase order.');
        }

        return DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if ($po->status !== 'approved') {
                return back()->with('error', "PO hanya dapat dikirim ke supplier jika berstatus 'approved' (status saat ini: {$po->status}).");
            }

            $po->update(['status' => 'sent']);

            return back()->with('success', "PO #{$po->po_number} dikirim ke supplier.");
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! (auth()->user()->can('cancel purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin', 'Admin Gudang Pusat', 'Admin PO', 'Direktur', 'Manajer Operasional']))) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan purchase order.');
        }

        return DB::transaction(function () use ($purchaseOrder) {
            $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if (! in_array($po->status, ['draft', 'approved', 'sent'])) {
                return back()->with('error', "PO berstatus '{$po->status}' tidak dapat dibatalkan.");
            }

            $hasConfirmedGR = GoodsReceipt::where('purchase_order_id', $po->id)
                ->where('status', 'confirmed')
                ->exists();

            if ($hasConfirmedGR) {
                return back()->with('error', 'PO tidak dapat dibatalkan karena sudah memiliki Goods Receipt yang terkonfirmasi.');
            }

            // Kurangi qty_ordered pada MR / PR item terkait
            foreach ($po->items()->lockForUpdate()->get() as $poItem) {
                if ($poItem->material_request_item_id) {
                    $mrItem = MaterialRequestItem::where('id', $poItem->material_request_item_id)->lockForUpdate()->first();
                    if ($mrItem) {
                        $newOrdered = max(0, (float) $mrItem->qty_ordered - (float) $poItem->quantity);
                        $mrItem->update(['qty_ordered' => $newOrdered]);
                    }
                }

                if ($poItem->procurement_request_item_id) {
                    $prItem = ProcurementRequestItem::where('id', $poItem->procurement_request_item_id)->lockForUpdate()->first();
                    if ($prItem) {
                        $newOrdered = max(0, (float) $prItem->qty_ordered - (float) $poItem->quantity);
                        $prItem->update(['qty_ordered' => $newOrdered]);
                    }
                } elseif ($po->procurement_request_id && $poItem->material_id) {
                    $prItem = ProcurementRequestItem::where('procurement_request_id', $po->procurement_request_id)
                        ->where('material_id', $poItem->material_id)
                        ->lockForUpdate()
                        ->first();
                    if ($prItem) {
                        $newOrdered = max(0, (float) $prItem->qty_ordered - (float) $poItem->quantity);
                        $prItem->update(['qty_ordered' => $newOrdered]);
                    }
                }
            }

            // Hitung ulang status PR jika terkait
            if ($po->procurement_request_id) {
                $pr = ProcurementRequest::where('id', $po->procurement_request_id)->lockForUpdate()->first();
                if ($pr) {
                    $totalRequested = (float) $pr->items()->sum('quantity');
                    $totalOrdered = (float) $pr->items()->sum('qty_ordered');

                    if ($totalOrdered <= 0) {
                        $pr->update(['status' => 'approved']);
                    } elseif ($totalOrdered < $totalRequested) {
                        $pr->update(['status' => 'partially_ordered']);
                    } else {
                        $pr->update(['status' => 'fully_ordered']);
                    }
                }
            }

            $po->update(['status' => 'cancelled']);

            return back()->with('success', "PO #{$po->po_number} dibatalkan.");
        });
    }

    public function close(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! (auth()->user()->can('close purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin']))) {
            abort(403, 'Anda tidak memiliki hak akses untuk menutup purchase order.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($purchaseOrder->created_by === auth()->id()) {
            return back()->with('error', 'Pembuat PO tidak dapat melakukan short close pada PO buatannya sendiri.');
        }

        if (!in_array($purchaseOrder->status, ['sent', 'partial_received'])) {
            return back()->with('error', "Short close hanya dapat dilakukan pada PO berstatus sent atau partial_received (status saat ini: {$purchaseOrder->status}).");
        }

        return DB::transaction(function () use ($purchaseOrder, $validated) {
            $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            if ($po->created_by === auth()->id()) {
                return back()->with('error', 'Pembuat PO tidak dapat melakukan short close pada PO buatannya sendiri.');
            }

            if (!in_array($po->status, ['sent', 'partial_received'])) {
                return back()->with('error', "Short close hanya dapat dilakukan pada PO berstatus sent atau partial_received (status saat ini: {$po->status}).");
            }

            $po->load('items');
            $totalOutstanding = $po->items->sum(fn($item) => $item->outstanding);

            if ($totalOutstanding <= 0) {
                return back()->with('error', 'PO tidak memiliki sisa kuota (outstanding) untuk ditutup.');
            }

            foreach ($po->items as $item) {
                $poItem = PurchaseOrderItem::where('id', $item->id)->lockForUpdate()->firstOrFail();
                $outstanding = max(0, (float)$poItem->quantity - (float)$poItem->received_qty - (float)$poItem->lost_qty - (float)$poItem->short_closed_qty);

                if ($outstanding > 0) {
                    $poItem->increment('short_closed_qty', $outstanding);

                    // Kurangi qty_ordered pada MR item sebesar sisa yang ditutup
                    if ($poItem->material_request_item_id) {
                        $mrItem = MaterialRequestItem::where('id', $poItem->material_request_item_id)->lockForUpdate()->first();
                        if ($mrItem) {
                            $newOrdered = max(0, (float) $mrItem->qty_ordered - $outstanding);
                            $mrItem->update(['qty_ordered' => $newOrdered]);
                        }
                    }

                    // Kurangi qty_ordered pada PR item jika bersumber dari PR
                    if ($poItem->procurement_request_item_id) {
                        $prItem = ProcurementRequestItem::where('id', $poItem->procurement_request_item_id)->lockForUpdate()->first();
                        if ($prItem) {
                            $newOrdered = max(0, (float) $prItem->qty_ordered - $outstanding);
                            $prItem->update(['qty_ordered' => $newOrdered]);
                        }
                    }
                }
            }

            // Batalkan sisa jadwal kedatangan (bila ada tabel po_delivery_schedules)
            if (Schema::hasTable('po_delivery_schedules')) {
                DB::table('po_delivery_schedules')
                    ->whereIn('purchase_order_item_id', $po->items->pluck('id'))
                    ->where('status', 'planned')
                    ->update(['status' => 'cancelled', 'updated_at' => now()]);
            }

            // Tambahkan catatan short close
            $userName = auth()->user()->name ?? 'User';
            $reasonNote = "[SHORT CLOSE " . now()->format('d/m/Y H:i') . " oleh {$userName}]: " . $validated['reason'];
            $newNotes = trim(($po->notes ? $po->notes . "\n" : '') . $reasonNote);

            $po->update([
                'status' => 'closed',
                'notes'  => $newNotes,
            ]);

            return back()->with('success', "PO #{$po->po_number} berhasil di-short-close (ditutup).");
        });
    }

    /**
     * T13: Perbarui jadwal kedatangan item PO.
     * Jadwal hanya bisa diubah saat PO berstatus 'draft'.
     */
    public function updateSchedules(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('create purchase orders');

        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Jadwal kedatangan hanya dapat diubah saat status PO adalah Draft.');
        }

        $validated = $request->validate([
            'items'                               => 'required|array|min:1',
            'items.*.purchase_order_item_id'      => 'required|exists:purchase_order_items,id',
            'items.*.schedules'                   => 'nullable|array',
            'items.*.schedules.*.stage_label'     => 'nullable|string|max:100',
            'items.*.schedules.*.planned_date'    => 'nullable|date',
            'items.*.schedules.*.planned_qty'     => 'required|numeric|min:0.01',
            'items.*.schedules.*.warehouse_id'    => 'nullable|exists:warehouses,id',
            'items.*.schedules.*.notes'           => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $purchaseOrder) {
                foreach ($validated['items'] as $itemData) {
                    $poItem = $purchaseOrder->items()->where('id', $itemData['purchase_order_item_id'])->firstOrFail();
                    $schedules = $itemData['schedules'] ?? [];

                    $totalPlanned = collect($schedules)->sum(fn($s) => (float)($s['planned_qty'] ?? 0));
                    if ($totalPlanned > (float) $poItem->quantity) {
                        throw new \DomainException("Total kuantitas jadwal untuk {$poItem->displayName()} ({$totalPlanned}) melebihi kuantitas PO ({$poItem->quantity}).");
                    }

                    // Hapus jadwal eksisting dan buat ulang
                    $poItem->deliverySchedules()->delete();

                    if (!empty($schedules)) {
                        foreach ($schedules as $sIdx => $sch) {
                            $poItem->deliverySchedules()->create([
                                'stage_label'  => !empty($sch['stage_label']) ? trim($sch['stage_label']) : ('Tahap ' . ($sIdx + 1)),
                                'planned_date' => $sch['planned_date'] ?? $purchaseOrder->expected_delivery,
                                'planned_qty'  => $sch['planned_qty'],
                                'warehouse_id' => $sch['warehouse_id'] ?? $purchaseOrder->delivery_warehouse_id,
                                'status'       => 'planned',
                                'notes'        => $sch['notes'] ?? null,
                            ]);
                        }
                    } else {
                        $poItem->deliverySchedules()->create([
                            'stage_label'  => 'Pengiriman Penuh',
                            'planned_date' => $purchaseOrder->expected_delivery ?? now()->toDateString(),
                            'planned_qty'  => $poItem->quantity,
                            'warehouse_id' => $purchaseOrder->delivery_warehouse_id,
                            'status'       => 'planned',
                        ]);
                    }
                }
            });

            return back()->with('success', 'Jadwal kedatangan PO berhasil diperbarui.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
