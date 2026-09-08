<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Material;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $service) {}

    public function index(Request $request)
    {
        $this->authorize('view goods receipts');

        $warehouseId = session('active_warehouse_id');

        $query = GoodsReceipt::with(['supplier', 'warehouse', 'creator'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $receipts = $query->latest()->paginate(15)->withQueryString();

        return view('goods-receipts.index', compact('receipts'));
    }

    public function create()
    {
        $this->authorize('create goods receipts');

        $suppliers  = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::where('type', 'pusat')->get();
        $materials  = Material::with('unit')->orderBy('name')->get();

        return view('goods-receipts.create', compact('suppliers', 'warehouses', 'materials'));
    }

    public function store(Request $request)
    {
        $this->authorize('create goods receipts');

        $validated = $request->validate([
            'supplier_id'     => 'required|exists:suppliers,id',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'received_at'     => 'required|date',
            'invoice_number'  => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $receipt = $this->service->create($validated, auth()->id());

        return redirect()->route('goods-receipts.show', $receipt)
            ->with('success', "Penerimaan barang #{$receipt->receipt_number} berhasil disimpan.");
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('view goods receipts');
        $goodsReceipt->load(['supplier', 'warehouse', 'creator', 'items.material.unit']);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    public function confirm(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('confirm goods receipts');

        $this->service->confirm($goodsReceipt, auth()->id());

        return back()->with('success', 'Penerimaan barang berhasil dikonfirmasi dan stok diperbarui.');
    }
}
