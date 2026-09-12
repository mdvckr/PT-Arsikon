<?php

namespace App\Http\Controllers;

use App\Models\MaterialReturn;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Services\StockService;
use App\Services\NotificationHelper;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = MaterialReturn::with(['fromWarehouse','toWarehouse','requester']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('return_number', 'like', "%{$request->search}%");

        $returns = $query->latest()->paginate(15)->withQueryString();
        return view('returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $materials  = Material::with('unit')->orderBy('name')->get();
        $central    = Warehouse::where('is_central', true)->first();

        // Ambil stok material aktif per gudang proyek
        $inventories = Inventory::with('material.unit')
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('warehouse_id');

        return view('returns.create', compact('warehouses', 'materials', 'central', 'inventories'));
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

        // Notifikasi ke Admin & Central Warehouse
        $fromWhName = Warehouse::find($validated['from_warehouse_id'])?->name ?? 'Gudang Proyek';
        NotificationHelper::notifyAdmins(
            "Pengembalian Material Baru: #{$return->return_number}",
            "Pengembalian material diajukan dari {$fromWhName} menuju Gudang Pusat.",
            "return_created",
            route('returns.show', $return)
        );

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

        // Notifikasi ke pemohon / admin gudang proyek
        if ($return->requester) {
            NotificationHelper::notifyUser(
                $return->requester,
                "Pengembalian Material Disetujui: #{$return->return_number}",
                "Pengembalian material #{$return->return_number} telah disetujui. Silakan kirim fisik barang ke Gudang Pusat.",
                "return_approved",
                route('returns.show', $return)
            );
        }

        return back()->with('success', "Pengembalian #{$return->return_number} disetujui.");
    }

    public function receive(MaterialReturn $return)
    {
        if ($return->status !== 'approved') {
            return back()->with('error', 'Hanya pengembalian yang sudah disetujui yang dapat diterima.');
        }

        foreach ($return->items as $item) {
            if ($item->condition === 'good') {
                $material  = Material::findOrFail($item->material_id);
                $toWarehouse   = Warehouse::findOrFail($return->to_warehouse_id);
                $fromWarehouse = Warehouse::findOrFail($return->from_warehouse_id);

                // Tambah stok di Gudang Pusat
                $this->stockService->addStock(
                    warehouse: $toWarehouse,
                    material: $material,
                    quantity: (float) $item->quantity,
                    referenceType: 'MaterialReturn',
                    referenceId: $return->id,
                    userId: auth()->id(),
                    notes: "Pengembalian #{$return->return_number} dari " . $fromWarehouse->name
                );

                // Kurangi stok di Gudang Proyek (apabila ada stok tercatat)
                $fromInv = Inventory::where([
                    'material_id'  => $item->material_id,
                    'warehouse_id' => $return->from_warehouse_id,
                ])->first();

                if ($fromInv && $fromInv->quantity >= $item->quantity) {
                    $this->stockService->deductStock(
                        warehouse: $fromWarehouse,
                        material: $material,
                        quantity: (float) $item->quantity,
                        referenceType: 'MaterialReturn',
                        referenceId: $return->id,
                        userId: auth()->id(),
                        notes: "Pengembalian #{$return->return_number} ke " . $toWarehouse->name
                    );
                }
            }
            $item->update(['received_qty' => $item->quantity]);
        }

        $return->update([
            'status'      => 'received',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        // Notifikasi pengembalian diterima & stok ter-update
        if ($return->requester) {
            NotificationHelper::notifyUser(
                $return->requester,
                "Pengembalian Material Selesai: #{$return->return_number}",
                "Material pengembalian #{$return->return_number} telah diterima di Gudang Pusat & stok berhasil diperbarui.",
                "return_received",
                route('returns.show', $return)
            );
        }

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

        // Notifikasi penolakan ke pemohon
        if ($return->requester) {
            NotificationHelper::notifyUser(
                $return->requester,
                "Pengembalian Material Ditolak: #{$return->return_number}",
                "Pengembalian material #{$return->return_number} ditolak dengan alasan: {$request->rejection_reason}",
                "return_rejected",
                route('returns.show', $return)
            );
        }

        return back()->with('success', "Pengembalian #{$return->return_number} ditolak.");
    }
}


