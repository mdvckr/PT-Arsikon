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
use App\Models\PurchaseReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || (!$user->hasAnyRole(['Owner', 'Super Admin', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat', 'Admin PO', 'admin_pusat', 'admin_po', 'po_lapangan', 'po_nota_harian', 'admin-po-nota']) && !$user->can('view purchase orders'))) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengakses Purchase Order.');
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
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Role po_nota_harian hanya memiliki akses read-only pada modul Purchase Order.');
        }
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
                        ->orWhereNull('material_id')
                        ->orWhere(function ($nested) {
                            $nested->where(function ($n2) {
                                $n2->whereNull('fulfillment')->orWhere('fulfillment', '!=', 'stock');
                            })->whereHas('materialRequest', fn($mrQ) => $mrQ->whereNull('from_warehouse_id'));
                        });
                });
            })
            ->latest()
            ->get()
            ->filter(function ($mr) {
                return $mr->items->contains(function ($it) use ($mr) {
                    $sisa = (float) $it->qty_approved > 0
                        ? (float) $it->qty_approved - (float) $it->qty_ordered
                        : (float) $it->qty_requested - (float) $it->qty_ordered;
                    $isPurchase = ($it->fulfillment === 'purchase')
                        || empty($it->material_id)
                        || ($it->fulfillment !== 'stock' && empty($mr->from_warehouse_id));
                    return $isPurchase && $sisa > 0;
                });
            })
            ->values();

        // Peta item PO Lapangan untuk interaktivitas JavaScript di antarmuka
        $mrItemsMap = $approvedMRs->mapWithKeys(function ($mr) {
            $items = $mr->items->filter(function ($it) use ($mr) {
                $sisa = (float) $it->qty_approved > 0
                    ? (float) $it->qty_approved - (float) $it->qty_ordered
                    : (float) $it->qty_requested - (float) $it->qty_ordered;
                $isPurchase = ($it->fulfillment === 'purchase')
                    || empty($it->material_id)
                    || ($it->fulfillment !== 'stock' && empty($mr->from_warehouse_id));
                return $isPurchase && $sisa > 0;
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
                    $isPurchase = ($it->fulfillment === 'purchase')
                        || empty($it->material_id)
                        || ($it->fulfillment !== 'stock' && empty($mr->from_warehouse_id));
                    if (in_array($mr->status, ['approved', 'partially_fulfilled'])) {
                        $sisa = (float) $it->qty_approved > 0
                            ? (float) $it->qty_approved - (float) $it->qty_ordered
                            : (float) $it->qty_requested - (float) $it->qty_ordered;
                        return $isPurchase && $sisa > 0;
                    }
                    // Jika submitted, dukung item custom atau purchase untuk preview/notifikasi
                    return $isPurchase;
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
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Role po_nota_harian hanya memiliki akses read-only pada modul Purchase Order.');
        }
        $this->authorize('create purchase orders');
        $validated = $request->validate([
            'supplier_id'                      => 'nullable|exists:suppliers,id',
            'supplier_name'                    => 'nullable|string|max:255',
            'contact_person'                   => 'nullable|string|max:255',
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
            'items.*.notes'                    => 'nullable|string|max:500',
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
            $isToolItem = $hasTool || (!empty($item['material_request_item_id']) && MaterialRequestItem::where('id', $item['material_request_item_id'])->value('item_type') === 'tool');
            if ($isToolItem) {
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

                if ($mrItem->fulfillment === 'stock' && !empty($mrItem->materialRequest?->from_warehouse_id)) {
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
                'contact_person'        => !empty(trim($validated['contact_person'] ?? '')) ? trim($validated['contact_person']) : null,
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
                $mrItem     = null;
                $prItem     = null;

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
                    'notes'                       => !empty($item['notes']) ? trim($item['notes']) : null,
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
            ->with('success', "PO #{$po->po_number} berhasil dibuat.")
            ->with('show_print_prompt', true);
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
        $userCanViewPrice = auth()->user() ? auth()->user()->canViewPoPrice() : false;

        // Keamanan Blind PO / Hidden Harga di Backend:
        // Jika user tidak berhak melihat harga, hilangkan seluruh nominal harga dari model & relasi sebelum dikirim ke View/JSON
        if (!$userCanViewPrice) {
            $purchaseOrder->total_amount = 0;
            $purchaseOrder->subtotal = 0;
            $purchaseOrder->tax_amount = 0;
            $purchaseOrder->discount_amount = 0;
            if ($purchaseOrder->items) {
                foreach ($purchaseOrder->items as $item) {
                    $item->unit_price = 0;
                    $item->subtotal = 0;
                    $item->total_price = 0;
                }
            }
            if ($purchaseOrder->payments) {
                foreach ($purchaseOrder->payments as $payment) {
                    $payment->amount = 0;
                }
            }
        }

        return view('purchase-orders.show', compact('purchaseOrder', 'printTemplate', 'allTemplates', 'userCanViewPrice'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Role po_nota_harian hanya memiliki akses read-only pada modul Purchase Order.');
        }
        $this->authorize('update', $purchaseOrder);

        if (in_array($purchaseOrder->status, ['cancelled', 'closed', 'received'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', "Purchase Order berstatus '{$purchaseOrder->status}' tidak dapat diedit.");
        }

        if ($purchaseOrder->hasConfirmedGoodsReceipt()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order tidak dapat diedit karena sudah memiliki penerimaan barang (Goods Receipt) yang terkonfirmasi.');
        }

        if ($purchaseOrder->payments()->where('status', 'verified')->exists()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order tidak dapat diedit karena sudah memiliki pembayaran yang terverifikasi.');
        }

        $suppliers  = Supplier::orderBy('name')->get();
        $materials  = Material::with('unit')->orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->orderBy('name')->get();

        // Approved PRs (sertakan PR saat ini jika ada)
        $approvedPRs = ProcurementRequest::whereIn('status', ['approved', 'partially_ordered'])
            ->when($purchaseOrder->procurement_request_id, function($q) use ($purchaseOrder) {
                $q->orWhere('id', $purchaseOrder->procurement_request_id);
            })
            ->with('items.material.unit')
            ->get();

        $selectedPR = $purchaseOrder->procurement_request_id
            ? ProcurementRequest::with('items.material.unit')->find($purchaseOrder->procurement_request_id)
            : null;

        // Load relasi PO
        $purchaseOrder->load([
            'items.material.unit',
            'items.tool',
            'items.deliverySchedules',
            'items.materialRequestItem.materialRequest.toWarehouse.project',
            'supplier',
            'deliveryWarehouse.project',
            'materialRequest.toWarehouse.project',
            'procurementRequest.items.material.unit',
        ]);

        // PO Lapangan (MR) yang approved atau partially_fulfilled
        $approvedMRs = \App\Models\MaterialRequest::whereIn('status', ['approved', 'partially_fulfilled'])
            ->when($purchaseOrder->material_request_id, function($q) use ($purchaseOrder) {
                $q->orWhere('id', $purchaseOrder->material_request_id);
            })
            ->with(['items.material.unit', 'items.tool', 'fromWarehouse', 'toWarehouse.project'])
            ->latest()
            ->get();

        // Map item PO Lapangan untuk interaktivitas JS
        $poItemQtyByMrItem = $purchaseOrder->items->pluck('quantity', 'material_request_item_id')->toArray();

        $mrItemsMap = $approvedMRs->mapWithKeys(function ($mr) use ($poItemQtyByMrItem) {
            $items = $mr->items->map(function ($it) use ($mr, $poItemQtyByMrItem) {
                $baseApproved = (float) $it->qty_approved > 0 ? (float) $it->qty_approved : (float) $it->qty_requested;
                $currentPoQty = (float) ($poItemQtyByMrItem[$it->id] ?? 0);
                $sisa = ($baseApproved - (float) $it->qty_ordered) + $currentPoQty;

                return [
                    'id'               => $it->id,
                    'material_id'      => $it->material_id,
                    'tool_id'          => $it->tool_id,
                    'name'             => $it->displayName(),
                    'is_custom'        => $it->isCustom(),
                    'custom_item_name' => $it->custom_item_name,
                    'custom_item_type' => $it->custom_item_type,
                    'unit'             => $it->displayUnit(),
                    'quota'            => round(max(0, $sisa), 2),
                    'notes'            => $it->notes,
                    'price'            => (float) ($it->material?->unit_price ?? 0),
                    'mr_num'           => $mr->request_number,
                    'warehouse'        => $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name ?? 'Proyek',
                ];
            })->filter(fn($it) => $it['quota'] > 0)->values();

            return [$mr->id => [
                'id'              => $mr->id,
                'request_number'  => $mr->request_number,
                'to_warehouse_id' => $mr->to_warehouse_id,
                'project_name'    => $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name,
                'items'           => $items,
            ]];
        });

        $mrCustomItems = MaterialRequestItem::whereNull('material_id')
            ->whereHas('materialRequest', function ($q) {
                $q->whereIn('status', ['submitted', 'approved', 'partially_fulfilled']);
            })
            ->with(['materialRequest.fromWarehouse', 'materialRequest.toWarehouse.project'])
            ->latest()
            ->get();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'materials',
            'approvedPRs',
            'selectedPR',
            'approvedMRs',
            'mrItemsMap',
            'mrCustomItems',
            'warehouses'
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Role po_nota_harian hanya memiliki akses read-only pada modul Purchase Order.');
        }
        $this->authorize('update', $purchaseOrder);

        if (in_array($purchaseOrder->status, ['cancelled', 'closed', 'received'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', "Purchase Order berstatus '{$purchaseOrder->status}' tidak dapat diedit.");
        }

        if ($purchaseOrder->hasConfirmedGoodsReceipt()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order tidak dapat diedit karena sudah memiliki penerimaan barang (Goods Receipt) yang terkonfirmasi.');
        }

        if ($purchaseOrder->payments()->where('status', 'verified')->exists()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Purchase Order tidak dapat diedit karena sudah memiliki pembayaran yang terverifikasi.');
        }

        $validated = $request->validate([
            'supplier_id'                      => 'nullable|exists:suppliers,id',
            'supplier_name'                    => 'nullable|string|max:255',
            'contact_person'                   => 'nullable|string|max:255',
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
            'items.*.notes'                    => 'nullable|string|max:500',
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

        $po = DB::transaction(function () use ($validated, $supplierId, $supplierName, $deliveryWarehouseId, $purchaseOrder) {
            $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            // 1. Kembalikan kuota sebelumnya pada item MR & PR
            foreach ($po->items()->lockForUpdate()->get() as $oldItem) {
                if ($oldItem->material_request_item_id) {
                    $mrItem = MaterialRequestItem::where('id', $oldItem->material_request_item_id)->lockForUpdate()->first();
                    if ($mrItem) {
                        $newOrdered = max(0, (float)$mrItem->qty_ordered - (float)$oldItem->quantity);
                        $mrItem->update(['qty_ordered' => $newOrdered]);
                    }
                }

                if ($oldItem->procurement_request_item_id) {
                    $prItem = ProcurementRequestItem::where('id', $oldItem->procurement_request_item_id)->lockForUpdate()->first();
                    if ($prItem) {
                        $newOrdered = max(0, (float)$prItem->qty_ordered - (float)$oldItem->quantity);
                        $prItem->update(['qty_ordered' => $newOrdered]);
                    }
                } elseif ($po->procurement_request_id && $oldItem->material_id) {
                    $prItem = ProcurementRequestItem::where('procurement_request_id', $po->procurement_request_id)
                        ->where('material_id', $oldItem->material_id)
                        ->lockForUpdate()
                        ->first();
                    if ($prItem) {
                        $newOrdered = max(0, (float)$prItem->qty_ordered - (float)$oldItem->quantity);
                        $prItem->update(['qty_ordered' => $newOrdered]);
                    }
                }
            }

            // 2. Validasi sumber pengadaan & ketersediaan kuota baru
            foreach ($validated['items'] as $idx => $item) {
                $hasMaterial = !empty($item['material_id']);
                $hasTool = !empty($item['tool_id']);
                $hasCustomName = !empty(trim($item['custom_item_name'] ?? ''));
                if (!$hasMaterial && !$hasTool && !$hasCustomName) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$idx}.material_id" => "Baris ke-" . ($idx + 1) . ": Silakan pilih material dari master atau tentukan barang baru/custom."
                    ]);
                }

                if ($hasTool) {
                    $qty = $item['quantity'] ?? null;
                    if (!is_null($qty) && fmod((float)$qty, 1) != 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.{$idx}.quantity" => "Kuantitas alat harus berupa bilangan bulat."
                        ]);
                    }
                }

                if (!empty($item['schedules']) && is_array($item['schedules'])) {
                    $totalPlanned = collect($item['schedules'])->sum(fn($s) => (float)($s['planned_qty'] ?? 0));
                    if ($totalPlanned > (float)($item['quantity'] ?? 0)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.{$idx}.schedules" => "Baris ke-" . ($idx + 1) . ": Total kuantitas jadwal ({$totalPlanned}) melebihi kuantitas pemesanan ({$item['quantity']})."
                        ]);
                    }
                }

                $mrItemId = $item['material_request_item_id'] ?? null;
                $prItemId = $item['procurement_request_item_id'] ?? null;
                $hasPrId  = !empty($validated['procurement_request_id']);

                if (!$mrItemId && !$prItemId && !$hasPrId) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$idx}" => "Baris ke-" . ($idx + 1) . ": Purchase Order wajib memiliki sumber pengadaan yang sah (item PO Lapangan yang telah disetujui atau Permintaan Pengadaan PR yang telah disetujui)."
                    ]);
                }

                if ($mrItemId) {
                    $mrItem = MaterialRequestItem::where('id', $mrItemId)->lockForUpdate()->first();
                    if (!$mrItem) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.{$idx}.material_request_item_id" => "Item PO Lapangan tidak ditemukan."
                        ]);
                    }
                    $approvedQty = (float) $mrItem->qty_approved > 0 ? (float) $mrItem->qty_approved : (float) $mrItem->qty_requested;
                    $sisaKuotaMR = max(0, $approvedQty - (float) $mrItem->qty_ordered);
                    if ((float) $item['quantity'] > $sisaKuotaMR) {
                        $matName = $mrItem->displayName();
                        throw \Illuminate\Validation\ValidationException::withMessages([
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
                        $prItem = ProcurementRequestItem::where('id', $prItemId)->lockForUpdate()->first();
                        if (!$prItem) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                "items.{$idx}.procurement_request_item_id" => "Item Permintaan Pengadaan (PR) tidak ditemukan."
                            ]);
                        }

                        $sisaKuota = (float) $prItem->quantity - (float) $prItem->qty_ordered;
                        if ((float) $item['quantity'] > $sisaKuota) {
                            $matName = $prItem->material?->name ?? 'Material';
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                "items.{$idx}.quantity" => "Jumlah pemesanan {$matName} ({$item['quantity']}) melebihi sisa kuota PR ({$sisaKuota})."
                            ]);
                        }
                    }
                }
            }

            $materialRequestId = !empty($validated['material_request_id']) ? $validated['material_request_id'] : null;
            if (!$materialRequestId) {
                foreach ($validated['items'] as $item) {
                    if (!empty($item['material_request_item_id'])) {
                        $materialRequestId = MaterialRequestItem::where('id', $item['material_request_item_id'])->value('material_request_id');
                        if ($materialRequestId) {
                            break;
                        }
                    }
                }
            }

            if (!$deliveryWarehouseId && $materialRequestId) {
                $deliveryWarehouseId = \App\Models\MaterialRequest::find($materialRequestId)?->to_warehouse_id;
            }

            $oldPrId = $po->procurement_request_id;

            $newStatus = $po->status;
            if (in_array($po->status, ['approved', 'sent'])) {
                $newStatus = 'draft';
            }

            $po->update([
                'supplier_id'            => $supplierId,
                'supplier_name'          => $supplierName,
                'contact_person'         => !empty(trim($validated['contact_person'] ?? '')) ? trim($validated['contact_person']) : null,
                'material_request_id'    => $materialRequestId,
                'procurement_request_id' => $validated['procurement_request_id'] ?? null,
                'delivery_warehouse_id'  => $deliveryWarehouseId,
                'risk_bearer'            => $validated['risk_bearer'] ?? ($po->risk_bearer ?? 'vendor'),
                'order_date'             => $validated['order_date'],
                'expected_delivery'      => $validated['expected_delivery'] ?? null,
                'terms'                  => $validated['terms'] ?? null,
                'notes'                  => $validated['notes'] ?? null,
                'status'                 => $newStatus,
                'approved_by'            => ($newStatus === 'draft') ? null : $po->approved_by,
                'approved_at'            => ($newStatus === 'draft') ? null : $po->approved_at,
            ]);

            // Hapus item dan jadwal lama
            foreach ($po->items()->get() as $oldPoItem) {
                $oldPoItem->deliverySchedules()->delete();
                $oldPoItem->delete();
            }

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
                $mrItem     = null;
                $prItem     = null;

                if (!$prItemId && !empty($validated['procurement_request_id']) && $materialId) {
                    $prItemId = ProcurementRequestItem::where('procurement_request_id', $validated['procurement_request_id'])
                        ->where('material_id', $materialId)
                        ->value('id');
                }

                if ($mrItemId) {
                    $mrItem = MaterialRequestItem::where('id', $mrItemId)->lockForUpdate()->firstOrFail();
                    $mrItem->increment('qty_ordered', (float) $item['quantity']);
                    if (!$toolId && $mrItem->tool_id) {
                        $toolId = $mrItem->tool_id;
                    }
                }

                if ($prItemId) {
                    $prItem = ProcurementRequestItem::where('id', $prItemId)->lockForUpdate()->firstOrFail();
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
                    'notes'                       => !empty($item['notes']) ? trim($item['notes']) : null,
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

            // Sinkronkan status PR induk
            $prsToUpdate = array_filter(array_unique([$oldPrId, $po->procurement_request_id]));
            foreach ($prsToUpdate as $prId) {
                $pr = ProcurementRequest::where('id', $prId)->lockForUpdate()->first();
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

        $msg = "PO #{$po->po_number} berhasil diperbarui.";
        if ($po->status === 'draft' && $purchaseOrder->status !== 'draft') {
            $msg .= " Status PO dikembalikan ke Draft untuk persetujuan ulang.";
        }

        return redirect()->route('purchase-orders.show', $po)->with('success', $msg);
    }

    public function print(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'creator',
            'approver',
            'deliveryWarehouse.project',
            'items.material.unit',
            'items.tool',
            'items.materialRequestItem.materialRequest.toWarehouse.project',
            'procurementRequest.project',
        ]);

        $user = auth()->user();
        $userCanViewPrice = $user ? $user->canViewPoPrice() : false;

        // Opsi mode cetak: 'official' (dengan harga) vs 'blind' (tanpa harga / Rp 0 untuk lapangan)
        $requestedMode = $request->query('mode', 'official');
        if ($request->has('blind') || $request->has('hide_price')) {
            $requestedMode = 'blind';
        }

        // Aturan: Jika user BUKAN Admin Pusat atau Admin PO (serta Owner), WAJIB dicetak tanpa harga (blind / Rp 0)
        $isBlind = (!$userCanViewPrice) || ($requestedMode === 'blind');
        $hidePrice = $isBlind;

        // Keamanan Blind PO di Backend: Sembunyikan harga langsung di level controller
        if ($hidePrice) {
            $purchaseOrder->total_amount = 0;
            $purchaseOrder->subtotal = 0;
            $purchaseOrder->tax_amount = 0;
            $purchaseOrder->discount_amount = 0;
            if ($purchaseOrder->items) {
                foreach ($purchaseOrder->items as $item) {
                    $item->unit_price = 0;
                    $item->subtotal = 0;
                    $item->total_price = 0;
                }
            }
        }

        $po = $purchaseOrder;
        return view('purchase-orders.print', compact('po', 'hidePrice', 'isBlind', 'userCanViewPrice'));
    }

    /**
     * Rekapitulasi Purchase Order Mingguan (Weekly Grouping)
     * Mendukung Native Web Print View & Direct DomPDF export.
     */
    public function weeklyReport(Request $request)
    {
        $user = auth()->user();
        $userCanViewPrice = $user ? $user->canViewPoPrice() : false;

        // 1. Tentukan tanggal acuan
        $now = Carbon::now('Asia/Jakarta');
        $targetDate = $request->filled('date')
            ? Carbon::parse($request->query('date'), 'Asia/Jakarta')
            : $now;

        // 2. Tentukan batasan tanggal Senin s.d Minggu
        $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek   = $targetDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $weekNumber  = $targetDate->weekOfMonth;
        $isoWeek     = $targetDate->isoWeek();
        $monthNumber = $targetDate->month;
        $yearNumber  = $targetDate->year;

        // Navigasi minggu
        $prevWeekDate    = $startOfWeek->copy()->subDays(7)->format('Y-m-d');
        $nextWeekDate    = $startOfWeek->copy()->addDays(7)->format('Y-m-d');
        $currentWeekDate = $now->format('Y-m-d');

        // 3. Query Purchase Orders dengan relasi optimal
        $query = PurchaseOrder::with([
            'supplier',
            'creator',
            'approver',
            'deliveryWarehouse.project',
            'materialRequest.toWarehouse.project',
            'procurementRequest.project',
            'items.material.unit',
            'items.tool',
            'receipts',
        ])
        ->whereBetween('order_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);

        // Filter status jika dipilih
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter proyek jika dipilih
        if ($request->filled('project_id')) {
            $projectId = $request->query('project_id');
            $query->where(function ($q) use ($projectId) {
                $q->whereHas('deliveryWarehouse', fn($w) => $w->where('project_id', $projectId))
                  ->orWhereHas('materialRequest.toWarehouse', fn($tw) => $tw->where('project_id', $projectId))
                  ->orWhereHas('procurementRequest', fn($pr) => $pr->where('project_id', $projectId));
            });
        }

        $orders = $query->orderBy('order_date', 'asc')->orderBy('id', 'asc')->get();

        // 4. Penegakan Kerahasiaan Harga (Blind PO Security)
        $requestedMode = $request->query('mode', 'official');
        if ($request->has('blind') || $request->has('hide_price')) {
            $requestedMode = 'blind';
        }
        $isBlind = (!$userCanViewPrice) || ($requestedMode === 'blind');
        $hidePrice = $isBlind;

        if ($hidePrice) {
            foreach ($orders as $order) {
                $order->total_amount = 0;
                $order->paid_amount = 0;
                if ($order->items) {
                    foreach ($order->items as $item) {
                        $item->unit_price = 0;
                        $item->subtotal = 0;
                        $item->total_price = 0;
                    }
                }
            }
        }

        // 5. Metadata Periode & Proyek
        $projects = \App\Models\Project::orderBy('name')->get();

        $bulanId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $bulanStart = $bulanId[$startOfWeek->month] ?? '';
        $bulanEnd   = $bulanId[$endOfWeek->month] ?? '';
        $periodLabel = "Minggu ke-{$weekNumber} ({$startOfWeek->format('d')} {$bulanStart} — {$endOfWeek->format('d')} {$bulanEnd} {$endOfWeek->format('Y')})";
        $reportTitle = "REKAPITULASI PURCHASE ORDER MINGGUAN — " . $startOfWeek->format('Y');
        $grandTotal  = $hidePrice ? 0 : $orders->sum('total_amount');
        $printedAt   = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';

        $viewData = compact(
            'orders', 'targetDate', 'startOfWeek', 'endOfWeek',
            'weekNumber', 'isoWeek', 'monthNumber', 'yearNumber',
            'periodLabel', 'reportTitle', 'isBlind', 'hidePrice',
            'userCanViewPrice', 'grandTotal', 'printedAt', 'bulanId',
            'prevWeekDate', 'nextWeekDate', 'currentWeekDate', 'projects'
        );

        // 6. Jika parameter format=pdf, render melalui DomPDF
        if ($request->query('format') === 'pdf') {
            @ini_set('memory_limit', '512M');
            $pdf = Pdf::loadView('purchase-orders.print-weekly-pdf', $viewData)
                ->setPaper('a4', 'landscape')
                ->setOption([
                    'dpi' => 120,
                    'defaultFont' => 'Helvetica',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                ]);

            $fileName = "Rekap-PO-Minggu-{$weekNumber}-{$yearNumber}.pdf";
            return $request->query('action') === 'download'
                ? $pdf->download($fileName)
                : $pdf->stream($fileName);
        }

        // Default: Tampilkan Layar Cetak Interaktif (Native Print Engine)
        return view('purchase-orders.print-weekly', $viewData);
    }


    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Role po_nota_harian tidak berhak melakukan approval Purchase Order.');
        }

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
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Operasi tidak diizinkan untuk role po_nota_harian.');
        }

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
        if (auth()->user()?->hasAnyRole(['po_nota_harian', 'admin-po-nota'])) {
            abort(403, 'Akses ditolak: Operasi tidak diizinkan untuk role po_nota_harian.');
        }

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

            // Batalkan juga nota pembelian yang belum dibayar terkait PO ini
            $po->receipts()->where('payment_status', 'unpaid')->update(['payment_status' => 'cancelled']);

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
