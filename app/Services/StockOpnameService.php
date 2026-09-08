<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * Create Stock Opname & snapshot current system inventory.
     */
    public function createOpname(
        Warehouse $warehouse,
        User $conductedBy,
        ?string $conductedAt = null,
        ?string $notes = null
    ): StockOpname {
        if (!$conductedBy->hasAccessToWarehouse($warehouse)) {
            throw new Exception("User tidak memiliki akses ke gudang ini untuk melakukan Stock Opname.");
        }

        return DB::transaction(function () use ($warehouse, $conductedBy, $conductedAt, $notes) {
            $opname = StockOpname::create([
                'opname_number' => StockOpname::generateOpnameNumber(),
                'warehouse_id' => $warehouse->id,
                'conducted_by_user_id' => $conductedBy->id,
                'status' => 'draft',
                'conducted_at' => $conductedAt ?? now()->toDateString(),
                'notes' => $notes,
            ]);

            // Snapshot all existing inventory items for this warehouse
            $inventories = Inventory::where('warehouse_id', $warehouse->id)->get();

            foreach ($inventories as $inventory) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'material_id' => $inventory->material_id,
                    'qty_system' => $inventory->quantity,
                    'qty_physical' => $inventory->quantity, // Initial default equal to system
                    'qty_difference' => 0,
                ]);
            }

            return $opname->load('items.material', 'warehouse', 'conductedBy');
        });
    }

    /**
     * Submit physical count results for Stock Opname.
     */
    public function submitPhysicalCounts(
        StockOpname $opname,
        array $physicalCountsData
    ): StockOpname {
        if ($opname->status !== 'draft') {
            throw new Exception("Hanya Stock Opname berstatus 'draft' yang dapat diubah dan diajukan.");
        }

        return DB::transaction(function () use ($opname, $physicalCountsData) {
            foreach ($physicalCountsData as $itemData) {
                $opnameItem = StockOpnameItem::where('stock_opname_id', $opname->id)
                    ->where('material_id', $itemData['material_id'])
                    ->first();

                if ($opnameItem) {
                    $qtyPhysical = (float) $itemData['qty_physical'];
                    $qtySystem = (float) $opnameItem->qty_system;
                    $difference = $qtyPhysical - $qtySystem;

                    $opnameItem->update([
                        'qty_physical' => $qtyPhysical,
                        'qty_difference' => $difference,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            $opname->update([
                'status' => 'submitted',
            ]);

            return $opname->fresh('items.material');
        });
    }

    /**
     * Approve Stock Opname & automatically adjust inventory stock levels.
     */
    public function approveOpname(
        StockOpname $opname,
        User $approvedBy
    ): StockOpname {
        if ($opname->status !== 'submitted') {
            throw new Exception("Hanya Stock Opname berstatus 'submitted' yang dapat disetujui.");
        }

        return DB::transaction(function () use ($opname, $approvedBy) {
            $warehouse = $opname->warehouse;

            foreach ($opname->items as $item) {
                $difference = (float) $item->qty_difference;
                $material = $item->material;

                if ($difference > 0) {
                    // Physical > System: Add stock adjustment
                    $this->stockService->addStock(
                        $warehouse,
                        $material,
                        $difference,
                        'opname_adjust',
                        $opname->id,
                        $approvedBy->id,
                        "Penyesuaian Opname (+{$difference}) No: {$opname->opname_number}"
                    );
                } elseif ($difference < 0) {
                    // Physical < System: Deduct stock adjustment
                    $deductQty = abs($difference);
                    $this->stockService->deductStock(
                        $warehouse,
                        $material,
                        $deductQty,
                        'opname_adjust',
                        $opname->id,
                        $approvedBy->id,
                        "Penyesuaian Opname (-{$deductQty}) No: {$opname->opname_number}"
                    );
                }
            }

            $opname->update([
                'status' => 'approved',
                'approved_by_user_id' => $approvedBy->id,
            ]);

            return $opname->fresh(['items.material', 'approvedBy']);
        });
    }
}
