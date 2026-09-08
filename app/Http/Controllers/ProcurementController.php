<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\Material;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcurementRequest::with(['requester','items.material']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('pr_number', 'like', "%{$request->search}%");

        $procurements = $query->latest()->paginate(15)->withQueryString();
        return view('procurement.index', compact('procurements'));
    }

    public function create()
    {
        $materials = Material::with('unit')->orderBy('name')->get();
        return view('procurement.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'needed_by'    => 'nullable|date',
            'justification'=> 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.material_id'     => 'required|exists:materials,id',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes'           => 'nullable|string',
        ]);

        $pr = ProcurementRequest::create([
            'requested_by'  => auth()->id(),
            'status'        => 'submitted',
            'needed_by'     => $validated['needed_by'] ?? null,
            'justification' => $validated['justification'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $pr->items()->create([
                'material_id'     => $item['material_id'],
                'quantity'        => $item['quantity'],
                'estimated_price' => $item['estimated_price'] ?? 0,
                'notes'           => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('procurement.show', $pr)
            ->with('success', "Pengadaan #{$pr->pr_number} berhasil diajukan.");
    }

    public function show(ProcurementRequest $procurement)
    {
        $procurement->load(['requester','approver','items.material.unit','purchaseOrders.supplier']);
        return view('procurement.show', compact('procurement'));
    }

    public function approve(ProcurementRequest $procurement)
    {
        if ($procurement->status !== 'submitted') {
            return back()->with('error', 'Status tidak valid untuk disetujui.');
        }
        $procurement->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', "PR #{$procurement->pr_number} telah disetujui.");
    }

    public function reject(Request $request, ProcurementRequest $procurement)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $procurement->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);
        return back()->with('success', "PR #{$procurement->pr_number} telah ditolak.");
    }
}
