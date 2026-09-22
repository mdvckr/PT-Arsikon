<?php

namespace App\Services;

use App\Models\ToolInventory;
use App\Models\Tool;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class ToolInventoryService
{
    /**
     * Add stock for a tool in a specific warehouse.
     */
    public function addStock(Warehouse $warehouse, Tool $tool, int $quantity, ?string $notes = null): ToolInventory
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive.');
        }

        return DB::transaction(function () use ($warehouse, $tool, $quantity, $notes) {
            $inventory = ToolInventory::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'tool_id'      => $tool->id,
                ],
                [
                    'stock_total'      => 0,
                    'stock_available'  => 0,
                    'stock_borrowed'   => 0,
                    'stock_maintenance'=> 0,
                    'stock_damaged'    => 0,
                ]
            );

            $inventory->increment('stock_total', $quantity);
            $inventory->increment('stock_available', $quantity);
            $inventory->validateInvariants();

            $tool->increment('stock_total', $quantity);
            $tool->increment('stock_available', $quantity);

            return $inventory->fresh();
        });
    }

    /**
     * Borrow (decrease available, increase borrowed) stock.
     */
    public function borrow(Warehouse $warehouse, Tool $tool, int $quantity): ToolInventory
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive.');
        }

        return DB::transaction(function () use ($warehouse, $tool, $quantity) {
            $inventory = ToolInventory::where('warehouse_id', $warehouse->id)
                ->where('tool_id', $tool->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                // If this tool already has inventory records in other warehouses, this warehouse has 0 stock
                $hasExistingInventories = ToolInventory::where('tool_id', $tool->id)->exists();
                if ($hasExistingInventories) {
                    throw new Exception("Stok alat {$tool->name} tidak tersedia di gudang {$warehouse->name} (tersedia: 0, dibutuhkan: {$quantity}).");
                }

                $avail = (int) $tool->stock_available > 0 ? (int) $tool->stock_available : $quantity;
                $tot   = (int) $tool->stock_total > 0 ? (int) $tool->stock_total : $avail;
                $inventory = ToolInventory::create([
                    'warehouse_id'      => $warehouse->id,
                    'tool_id'           => $tool->id,
                    'stock_total'       => max($tot, $quantity),
                    'stock_available'   => max($avail, $quantity),
                    'stock_borrowed'    => (int) $tool->stock_borrowed,
                    'stock_maintenance' => (int) $tool->stock_maintenance,
                    'stock_damaged'     => (int) $tool->stock_damaged,
                ]);
            }

            if ($inventory->stock_available < $quantity) {
                throw new Exception("Stok alat {$tool->name} tidak mencukupi di gudang {$warehouse->name} (tersedia: {$inventory->stock_available}, dibutuhkan: {$quantity}).");
            }

            $inventory->decrement('stock_available', $quantity);
            $inventory->increment('stock_borrowed', $quantity);
            $inventory->validateInvariants();

            $tool->decrement('stock_available', $quantity);
            $tool->increment('stock_borrowed', $quantity);

            return $inventory->fresh();
        });
    }

    /**
     * Return stock with condition handling.
     */
    public function returnStock(Warehouse $warehouse, Tool $tool, int $quantity, string $condition = 'good'): ToolInventory
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive.');
        }

        return DB::transaction(function () use ($warehouse, $tool, $quantity, $condition) {
            $inventory = ToolInventory::where('warehouse_id', $warehouse->id)
                ->where('tool_id', $tool->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = ToolInventory::create([
                    'warehouse_id'      => $warehouse->id,
                    'tool_id'           => $tool->id,
                    'stock_total'       => $quantity,
                    'stock_available'   => 0,
                    'stock_borrowed'    => $quantity,
                    'stock_maintenance' => 0,
                    'stock_damaged'     => 0,
                ]);
            }

            $inventory->decrement('stock_borrowed', min($quantity, (int) $inventory->stock_borrowed));
            $field = match ($condition) {
                'damaged'           => 'stock_damaged',
                'under_maintenance' => 'stock_maintenance',
                default             => 'stock_available',
            };
            $inventory->increment($field, $quantity);
            $inventory->validateInvariants();

            $tool->decrement('stock_borrowed', min($quantity, (int) $tool->stock_borrowed));
            $tool->increment($field, $quantity);

            return $inventory->fresh();
        });
    }

    /**
     * Sync an entire stock snapshot for a tool in a specific warehouse.
     * Accepts optional deltas for total, available, borrowed, maintenance, damaged.
     */
    public function syncStock(
        Warehouse $warehouse,
        Tool $tool,
        int $total = null,
        int $available = null,
        int $borrowed = null,
        int $maintenance = null,
        int $damaged = null
    ): ToolInventory {
        return DB::transaction(function () use ($warehouse, $tool, $total, $available, $borrowed, $maintenance, $damaged) {
            $inventory = ToolInventory::where('warehouse_id', $warehouse->id)
                ->where('tool_id', $tool->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'warehouse_id' => $warehouse->id,
                    'tool_id'      => $tool->id,
                ], [
                    'stock_total'      => 0,
                    'stock_available'  => 0,
                    'stock_borrowed'   => 0,
                    'stock_maintenance'=> 0,
                    'stock_damaged'    => 0,
                ]);

            $lockInv = ToolInventory::where('id', $inventory->id)->lockForUpdate()->first();

            $lockInv->update([
                'stock_total'      => $total ?? $lockInv->stock_total,
                'stock_available'  => $available ?? $lockInv->stock_available,
                'stock_borrowed'   => $borrowed ?? $lockInv->stock_borrowed,
                'stock_maintenance'=> $maintenance ?? $lockInv->stock_maintenance,
                'stock_damaged'    => $damaged ?? $lockInv->stock_damaged,
            ]);

            $lockInv->validateInvariants();

            return $lockInv->fresh();
        });
    }

    /**
     * Move stock from available to maintenance (e.g., scheduled maintenance).
     */
    public function moveToMaintenance(Warehouse $warehouse, Tool $tool, int $quantity): ToolInventory
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive.');
        }

        return DB::transaction(function () use ($warehouse, $tool, $quantity) {
            $inventory = ToolInventory::where('warehouse_id', $warehouse->id)
                ->where('tool_id', $tool->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = ToolInventory::create([
                    'warehouse_id'      => $warehouse->id,
                    'tool_id'           => $tool->id,
                    'stock_total'       => max((int) $tool->stock_total, $quantity),
                    'stock_available'   => max((int) $tool->stock_available, $quantity),
                    'stock_borrowed'    => (int) $tool->stock_borrowed,
                    'stock_maintenance' => (int) $tool->stock_maintenance,
                    'stock_damaged'     => (int) $tool->stock_damaged,
                ]);
            }

            if ($inventory->stock_available < $quantity) {
                throw new Exception("Stok alat {$tool->name} tidak mencukupi untuk pemeliharaan.");
            }

            $inventory->decrement('stock_available', $quantity);
            $inventory->increment('stock_maintenance', $quantity);
            $inventory->validateInvariants();

            return $inventory->fresh();
        });
    }

    /**
     * Restore stock from maintenance back to available (e.g., maintenance completed).
     */
    public function restoreFromMaintenance(Warehouse $warehouse, Tool $tool, int $quantity): ToolInventory
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive.');
        }

        return DB::transaction(function () use ($warehouse, $tool, $quantity) {
            $inventory = ToolInventory::where('warehouse_id', $warehouse->id)
                ->where('tool_id', $tool->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = ToolInventory::create([
                    'warehouse_id'      => $warehouse->id,
                    'tool_id'           => $tool->id,
                    'stock_total'       => max((int) $tool->stock_total, $quantity),
                    'stock_available'   => 0,
                    'stock_borrowed'    => (int) $tool->stock_borrowed,
                    'stock_maintenance' => max((int) $tool->stock_maintenance, $quantity),
                    'stock_damaged'     => (int) $tool->stock_damaged,
                ]);
            }

            if ($inventory->stock_maintenance < $quantity) {
                throw new Exception("Stok pemeliharaan alat {$tool->name} tidak mencukupi.");
            }

            $inventory->decrement('stock_maintenance', $quantity);
            $inventory->increment('stock_available', $quantity);
            $inventory->validateInvariants();

            return $inventory->fresh();
        });
    }
}
