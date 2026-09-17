<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReceipt::with(['purchaseOrder', 'project', 'supplier', 'creator', 'items']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhere('day_label', 'like', "%{$request->search}%");
            });
        }

        if ($request->po_id) {
            $query->where('purchase_order_id', $request->po_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->date) {
            $query->whereDate('receipt_date', $request->date);
        }

        $receipts = $query->latest('receipt_date')->latest('id')->paginate(15)->withQueryString();
        $pos = PurchaseOrder::orderBy('po_number', 'desc')->get();

        return view('purchase-receipts.index', compact('receipts', 'pos'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::with('supplier')->latest()->get();
        $projects = Project::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::with('unit')->orderBy('name')->get();
        $selectedPO = $request->po_id ? PurchaseOrder::find($request->po_id) : null;

        return view('purchase-receipts.create', compact('pos', 'projects', 'suppliers', 'materials', 'selectedPO'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'project_name'      => 'nullable|string|max:255',
            'supplier_name'     => 'nullable|string|max:255',
            'receipt_number'    => 'nullable|string|max:100',
            'receipt_date'      => 'required|date',
            'payment_status'    => 'required|in:unpaid,paid',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.material_id'=> 'nullable|exists:materials,id',
            'items.*.item_name'  => 'nullable|string|max:255',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('purchase_receipts', 'public');
            }

            $supplierName = $validated['supplier_name'] ?? null;
            $supplierId = null;
            if (!empty($validated['purchase_order_id'])) {
                $po = PurchaseOrder::with('supplier')->find($validated['purchase_order_id']);
                if ($po) {
                    $supplierId = $po->supplier_id;
                    if (!$supplierName && $po->supplier) {
                        $supplierName = $po->supplier->name;
                    }
                }
            }

            $receipt = PurchaseReceipt::create([
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'project_name'      => $validated['project_name'] ?? null,
                'supplier_id'       => $supplierId,
                'supplier_name'     => $supplierName,
                'receipt_number'    => $validated['receipt_number'] ?? null,
                'receipt_date'      => $validated['receipt_date'],
                'payment_status'    => $validated['payment_status'],
                'image_path'        => $imagePath,
                'notes'             => $validated['notes'] ?? null,
                'created_by'        => auth()->id(),
                'total_amount'      => 0,
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $material = !empty($item['material_id']) ? Material::find($item['material_id']) : null;
                $itemName = $item['item_name'] ?? ($material ? $material->name : 'Item Nota');
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                $receipt->items()->create([
                    'material_id' => $item['material_id'] ?? null,
                    'item_name'   => $itemName,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $subtotal,
                    'notes'       => $item['notes'] ?? null,
                ]);
            }

            $receipt->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-receipts.show', $receipt)
                ->with('success', 'Nota Pembelian Harian berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan nota: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReceipt $purchaseReceipt)
    {
        $purchaseReceipt->load(['purchaseOrder', 'project', 'supplier', 'creator', 'items.material.unit']);
        return view('purchase-receipts.show', compact('purchaseReceipt'));
    }

    public function destroy(PurchaseReceipt $purchaseReceipt)
    {
        if ($purchaseReceipt->image_path) {
            Storage::disk('public')->delete($purchaseReceipt->image_path);
        }
        $purchaseReceipt->delete();

        return redirect()->route('purchase-receipts.index')
            ->with('success', 'Nota Pembelian berhasil dihapus.');
    }
}
