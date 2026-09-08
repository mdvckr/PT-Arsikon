<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['purchaseOrder.supplier','creator']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('payment_number', 'like', "%{$request->search}%");

        $payments = $query->latest()->paginate(15)->withQueryString();
        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::with('supplier')
            ->whereIn('status', ['sent','partial_received','received'])
            ->where('total_amount', '>', 0)
            ->get()
            ->filter(fn($po) => $po->remaining_amount > 0);

        $selectedPO = $request->po_id ? PurchaseOrder::with(['supplier','items.material'])->find($request->po_id) : null;
        return view('payments.create', compact('pos','selectedPO'));
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

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        if ($validated['amount'] > $po->remaining_amount) {
            return back()->withErrors(['amount' => 'Jumlah pembayaran melebihi sisa tagihan.'])->withInput();
        }

        $payment = Payment::create([
            ...$validated,
            'created_by' => auth()->id(),
            'status'     => 'pending',
        ]);

        return redirect()->route('payments.show', $payment)
            ->with('success', "Pembayaran #{$payment->payment_number} berhasil diajukan.");
    }

    public function show(Payment $payment)
    {
        $payment->load(['purchaseOrder.supplier','creator','verifier','items']);
        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
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
