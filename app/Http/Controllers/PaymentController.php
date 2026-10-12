<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['purchaseOrder.supplier', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('bank_account', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%")
                          ->orWhereHas('supplier', function ($supQ) use ($search) {
                              $supQ->where('name', 'like', "%{$search}%");
                          });
                  });
            });
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->start_date) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();

        $metrics = [
            'total_verified_amount' => (float) Payment::where('status', 'verified')->sum('amount'),
            'verified_count'        => (int) Payment::where('status', 'verified')->count(),
            'pending_count'         => (int) Payment::where('status', 'pending')->count(),
            'pending_amount'        => (float) Payment::where('status', 'pending')->sum('amount'),
            'total_count'           => (int) Payment::count(),
            'rejected_count'        => (int) Payment::where('status', 'rejected')->count(),
        ];

        return view('payments.index', compact('payments', 'metrics'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::with(['supplier', 'goodsReceipts', 'payments', 'items'])
            ->whereIn('status', ['sent', 'partial_received', 'received', 'closed'])
            ->whereHas('goodsReceipts', function ($q) {
                $q->where('status', 'confirmed');
            })
            ->get()
            ->filter(fn($po) => $po->payable_remaining_amount > 0);

        $selectedPO = null;
        if ($request->po_id) {
            $selectedPO = PurchaseOrder::with(['supplier', 'items.material', 'items.tool', 'goodsReceipts.items', 'payments'])->find($request->po_id);
            if ($selectedPO && ! $selectedPO->hasConfirmedGoodsReceipt()) {
                return redirect()->route('payments.create')
                    ->with('error', "Purchase Order {$selectedPO->po_number} belum memiliki Penerimaan Barang (GR) terkonfirmasi sehingga belum dapat dibayar.");
            }
        }

        return view('payments.create', compact('pos', 'selectedPO'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'amount'            => 'required|numeric|min:1',
            'payment_method'    => 'required|string',
            'payment_date'      => 'required|date',
            'bank_account'      => 'nullable|string',
            'reference_number'  => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        try {
            $payment = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
                $po = PurchaseOrder::where('id', $validated['purchase_order_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $po->hasConfirmedGoodsReceipt()) {
                    throw new \DomainException('Pembayaran hanya untuk Purchase Order yang memiliki minimal satu Penerimaan Barang (GR) terkonfirmasi.');
                }

                $maxPayable = $po->payable_remaining_amount;
                if ((float) $validated['amount'] > ($maxPayable + 0.001)) {
                    $formattedMax = number_format($maxPayable, 0, ',', '.');
                    throw new \DomainException("Jumlah pembayaran melebihi batas nilai sah yang dapat dibayar (Maksimal: Rp {$formattedMax}).");
                }

                return Payment::create([
                    ...$validated,
                    'created_by' => auth()->id(),
                    'status'     => 'pending',
                ]);
            });

            return redirect()->route('payments.show', $payment)
                ->with('success', "Pembayaran #{$payment->payment_number} berhasil diajukan.");
        } catch (\DomainException $e) {
            $msg = $e->getMessage();
            $field = str_contains($msg, 'GR') ? 'purchase_order_id' : 'amount';
            return back()->withErrors([$field => $msg])->withInput();
        }
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'purchaseOrder.supplier',
            'purchaseOrder.items.material',
            'purchaseOrder.items.tool',
            'purchaseOrder.goodsReceipts.items',
            'purchaseOrder.payments',
            'creator',
            'verifier',
            'items'
        ]);
        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        $this->authorize('verify payments');

        if ($payment->created_by === auth()->id()) {
            return back()->with('error', 'Pembuat pembayaran tidak dapat memverifikasi pembayarannya sendiri.');
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Status tidak valid untuk diverifikasi.');
        }
        $payment->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
        // Update paid_amount on PO
        $po = $payment->purchaseOrder;
        $totalPaid = $po->payments()->where('status','verified')->sum('amount');
        $po->update(['paid_amount' => $totalPaid]);

        return back()->with('success', "Pembayaran #{$payment->payment_number} terverifikasi.");
    }

    public function reject(Request $request, Payment $payment)
    {
        $this->authorize('verify payments');

        if ($payment->created_by === auth()->id()) {
            return back()->with('error', 'Pembuat pembayaran tidak dapat menolak pembayarannya sendiri.');
        }

        $request->validate(['rejection_reason' => 'required|string']);
        $payment->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
        ]);
        return back()->with('success', "Pembayaran #{$payment->payment_number} ditolak.");
    }
}
