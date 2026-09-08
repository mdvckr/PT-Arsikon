<?php

namespace App\Http\Controllers;

use App\Models\MaterialReturn;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialReturn::with(['fromWarehouse','toWarehouse','requester']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('return_number', 'like', "%{$request->search}%");

        $returns = $query->latest()->paginate(15)->withQueryString();
        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $materials  = Material::with('unit')->orderBy('name')->get();
        $central    = Warehouse::where('is_central', true)->first();
        return view('returns.create', compact('warehouses','materials','central'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'reason'            => 'required|in:excess,damaged,wrong_item,project_complete,other',
            'return_date'       => 'required|date',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.material_id'=> 'required|exists:materials,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.condition'  => 'required|in:good,damaged,unusable',
            'items.*.notes'      => 'nullable|string',
        ]);

        $return = MaterialReturn::create([
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id'   => $validated['to_warehouse_id'],
            'requested_by'      => auth()->id(),
            'status'            => 'pending',
            'reason'            => $validated['reason'],
            'return_date'       => $validated['return_date'],
            'notes'             => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $return->items()->create([
                'material_id' => $item['material_id'],
                'quantity'    => $item['quantity'],
                'condition'   => $item['condition'],
                'notes'       => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('returns.show', $return)
            ->with('success', "Pengembalian #{$return->return_number} berhasil diajukan.");
    }

    public function show(MaterialReturn $return)
    {
        $return->load(['fromWarehouse','toWarehouse','requester','approver','receiver','items.material.unit']);
        return view('returns.show', compact('return'));
    }

    public function approve(MaterialReturn $return)
    {
        if ($return->status !== 'pending') {
            return back()->with('error', 'Status tidak valid.');
        }
        $return->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', "Pengembalian #{$return->return_number} disetujui.");
    }

    public function receive(MaterialReturn $return)
    {
        if ($return->status !== 'approved') {
            return back()->with('error', 'Hanya pengembalian yang sudah disetujui yang dapat diterima.');
        }

        foreach ($return->items as $item) {
            if ($item->condition === 'good') {
                $inv = Inventory::firstOrCreate(
                    ['material_id' => $item->material_id, 'warehouse_id' => $return->to_warehouse_id],
                    ['quantity' => 0]
                );
                $inv->increment('quantity', $item->quantity);

                StockMutation::create([
                    'material_id'  => $item->material_id,
                    'warehouse_id' => $return->to_warehouse_id,
                    'type'         => 'in',
                    'quantity'     => $item->quantity,
                    'reference'    => $return->return_number,
                    'notes'        => 'Pengembalian dari ' . $return->fromWarehouse->name,
                    'created_by'   => auth()->id(),
                ]);

                $fromInv = Inventory::where([
                    'material_id'  => $item->material_id,
                    'warehouse_id' => $return->from_warehouse_id,
                ])->first();
                if ($fromInv) {
                    $fromInv->decrement('quantity', $item->quantity);
                    StockMutation::create([
                        'material_id'  => $item->material_id,
                        'warehouse_id' => $return->from_warehouse_id,
                        'type'         => 'out',
                        'quantity'     => $item->quantity,
                        'reference'    => $return->return_number,
                        'notes'        => 'Pengembalian ke ' . $return->toWarehouse->name,
                        'created_by'   => auth()->id(),
                    ]);
                }
            }
            $item->update(['received_qty' => $item->quantity]);
        }

        $return->update([
            'status'      => 'received',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        return back()->with('success', "Pengembalian #{$return->return_number} berhasil diterima. Stok diperbarui.");
    }

    public function reject(Request $request, MaterialReturn $return)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $return->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);
        return back()->with('success', "Pengembalian #{$return->return_number} ditolak.");
    }
}
