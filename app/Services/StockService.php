<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMutation;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Increase inventory stock (e.g. from Goods Receipt or Receive Goods at Project).
     * Uses DB Transaction and lockForUpdate.
     */
    public function addStock(
        Warehouse $warehouse,
        Material $material,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?int $userId = null,
        ?string $notes = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new Exception("Jumlah stok yang ditambahkan harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($warehouse, $material, $quantity, $referenceType, $referenceId, $userId, $notes) {
            // Retrieve inventory record with row locking
            $inventory = Inventory::where('warehouse_id', $warehouse->id)
                ->where('material_id', $material->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $material->id,
                    'quantity' => 0,
                    'min_stock' => 0,
                    'qty_allocated' => 0,
                    'qty_in_transit' => 0,
                ]);

                // Lock the newly created record
                $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();
            }

            $oldQty = (float) $inventory->quantity;
            $newQty = $oldQty + $quantity;

            $inventory->update([
                'quantity' => $newQty,
            ]);

            // Log mutation
            StockMutation::create([
                'warehouse_id' => $warehouse->id,
                'material_id' => $material->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'qty_change' => $quantity,
                'qty_balance_after' => $newQty,
                'created_by_user_id' => $userId ?? auth()->id(),
                'notes' => $notes ?? 'Penambahan stok ke gudang',
            ]);

            return $inventory->fresh();
        });
    }

    /**
     * Deduct inventory stock (e.g. for Shipment to Project or Stock Adjustment).
     * Uses DB Transaction, lockForUpdate, and validates available stock.
     */
    public function deductStock(
        Warehouse $warehouse,
        Material $material,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        ?int $userId = null,
        ?string $notes = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new Exception("Jumlah stok yang dikurangi harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($warehouse, $material, $quantity, $referenceType, $referenceId, $userId, $notes) {
            $inventory = Inventory::where('warehouse_id', $warehouse->id)
                ->where('material_id', $material->id)
                ->lockForUpdate()
                ->first();

            $availableQty = $inventory ? (float) $inventory->quantity : 0;

            if ($availableQty < $quantity) {
                throw new Exception(sprintf(
                    "Stok tidak mencukupi untuk material %s di gudang %s. Stok tersedia: %s, diminta: %s",
                    $material->name,
                    $warehouse->name,
                    $availableQty,
                    $quantity
                ));
            }

            $newQty = $availableQty - $quantity;

            $inventory->update([
                'quantity' => $newQty,
            ]);

            StockMutation::create([
                'warehouse_id' => $warehouse->id,
                'material_id' => $material->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'qty_change' => -$quantity,
                'qty_balance_after' => $newQty,
                'created_by_user_id' => $userId ?? auth()->id(),
                'notes' => $notes ?? 'Pengurangan stok dari gudang',
            ]);

            if ((float) $inventory->min_stock > 0 && $newQty <= (float) $inventory->min_stock) {
                NotificationHelper::notifyAdmins(
                    "Stok Rendah: {$material->name}",
                    "Stok material {$material->name} di {$warehouse->name} tersisa {$newQty} {$material->unit?->abbreviation}, sudah mencapai batas minimum ({$inventory->min_stock}).",
                    "stock_alert",
                    route('inventory.index')
                );
            }

            return $inventory->fresh();
        });
    }
}
