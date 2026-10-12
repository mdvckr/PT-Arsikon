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
        $this->authorize('create purchase orders');
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
        $this->authorize('create purchase orders');
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
            'items.*.procurement_request_item_id' => 'nullable|exists:procurement_request_items,id',
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
        // serta validasi kuota tersisa jika item berasal dari Procurement Request
        foreach ($request->input('items', []) as $idx => $item) {
            $hasMaterial = !empty($item['material_id']);
            $hasCustomName = !empty(trim($item['custom_item_name'] ?? ''));
            if (!$hasMaterial && !$hasCustomName) {
                return back()->withInput()->withErrors([
                    "items.{$idx}.material_id" => "Baris ke-" . ($idx + 1) . ": Silakan pilih material dari master atau tentukan barang baru/custom."
                ]);
            }

            $prItemId = $item['procurement_request_item_id'] ?? null;
            if (!$prItemId && !empty($validated['procurement_request_id']) && $hasMaterial) {
                $prItemId = ProcurementRequestItem::where('procurement_request_id', $validated['procurement_request_id'])
                    ->where('material_id', $item['material_id'])
                    ->value('id');
            }

            if ($prItemId) {
                $prItem = ProcurementRequestItem::find($prItemId);
                if ($prItem) {
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

        $po = DB::transaction(function () use ($validated, $supplierId, $supplierName) {
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
                $prItemId   = !empty($item['procurement_request_item_id']) ? $item['procurement_request_item_id'] : null;

                if (!$prItemId && !empty($validated['procurement_request_id']) && $materialId) {
                    $prItemId = ProcurementRequestItem::where('procurement_request_id', $validated['procurement_request_id'])
                        ->where('material_id', $materialId)
                        ->value('id');
                }

                $po->items()->create([
                    'material_id'                 => $materialId,
                    'custom_item_name'            => $customName,
                    'custom_item_unit'            => $customUnit,
                    'material_request_item_id'    => $mrItemId,
                    'procurement_request_item_id' => $prItemId,
                    'quantity'                    => $item['quantity'],
                    'unit_price'                  => $item['unit_price'],
                    'subtotal'                    => $subtotal,
                    'unit'                        => $customUnit,
                ]);

                if ($mrItemId) {
                    $mrItem = MaterialRequestItem::where('id', $mrItemId)->lockForUpdate()->first();
                    if ($mrItem) {
                        $mrItem->increment('qty_ordered', (float) $item['quantity']);
                    }
                }

                if ($prItemId) {
                    $prItem = ProcurementRequestItem::where('id', $prItemId)->lockForUpdate()->first();
                    if ($prItem) {
                        $prItem->increment('qty_ordered', (float) $item['quantity']);
                    }
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

        if ($purchaseOrder->created_by === auth()->id()) {
            return back()->with('error', 'Pembuat PO tidak dapat menyetujui PO buatannya sendiri.');
        }

        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Hanya PO berstatus draft yang dapat disetujui.');
        }

        $purchaseOrder->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "PO #{$purchaseOrder->po_number} disetujui.");
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
}
