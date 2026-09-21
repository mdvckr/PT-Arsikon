<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInspection;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class ToolManagementService
{
    protected ToolInventoryService $toolInvService;

    public function __construct(ToolInventoryService $toolInvService)
    {
        $this->toolInvService = $toolInvService;
    }
    public function assignTool(
        Tool $tool,
        Warehouse $fromWarehouse,
        User $assignedBy,
        int|Warehouse $quantityOrToWarehouse = 1,
        mixed $arg5 = null,
        mixed $arg6 = null,
        mixed $arg7 = null,
        mixed $arg8 = null
    ): ToolAssignment {
        if ($quantityOrToWarehouse instanceof Warehouse) {
            $quantity = 1;
            $toWarehouse = $quantityOrToWarehouse;
            $assignedToUser = $arg5 instanceof User ? $arg5 : null;
            $expectedReturnAt = is_string($arg6) ? $arg6 : null;
            $notes = is_string($arg7) ? $arg7 : null;
        } else {
            $quantity = (int) $quantityOrToWarehouse;
            $toWarehouse = $arg5 instanceof Warehouse ? $arg5 : null;
            $assignedToUser = $arg6 instanceof User ? $arg6 : null;
            $expectedReturnAt = is_string($arg7) ? $arg7 : null;
            $notes = is_string($arg8) ? $arg8 : null;
        }

        return DB::transaction(function () use ($tool, $fromWarehouse, $assignedBy, $toWarehouse, $assignedToUser, $expectedReturnAt, $notes, $quantity) {
            // Lock tool record for update
            $tool = Tool::where('id', $tool->id)->lockForUpdate()->first();

            // Ensure enough available stock (re-check inside transaction with lock)
            if ($tool->stock_available < $quantity) {
                throw new Exception("Alat '{$tool->name}' sedang tidak dapat dipinjamkan / stok tidak mencukupi (tersedia: {$tool->stock_available}, diminta: {$quantity}).");
            }

            $assignment = ToolAssignment::create([
                'assignment_number' => ToolAssignment::generateAssignmentNumber(),
                'tool_id' => $tool->id,
                'quantity' => $quantity,
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse?->id,
                'assigned_to_user_id' => $assignedToUser?->id,
                'assigned_by_user_id' => $assignedBy->id,
                'assigned_at' => now(),
                'expected_return_at' => $expectedReturnAt,
                'status' => 'active',
                'notes' => $notes,
            ]);

            // Update current warehouse reference on tool record
            if ($toWarehouse) {
                $tool->update([
                    'current_warehouse_id' => $toWarehouse->id,
                ]);
            }

            // Use ToolInventoryService to borrow stock
            $this->toolInvService->borrow($fromWarehouse, $tool, $quantity);

            return $assignment->load('tool', 'fromWarehouse', 'toWarehouse', 'assignedTo', 'assignedBy');
        });
    }

    /**
     * Return tool & record physical inspection.
     */
    public function returnTool(
        ToolAssignment $assignment,
        User $inspectedBy,
        string $condition = 'good', // good, damaged, lost
        ?string $notes = null
    ): ToolInspection {
        if ($assignment->status !== 'active' && $assignment->status !== 'overdue') {
            throw new Exception("Peminjaman alat ini sudah tidak aktif.");
        }

        return DB::transaction(function () use ($assignment, $inspectedBy, $condition, $notes) {
            // Lock tool record
            $tool = Tool::where('id', $assignment->tool_id)->lockForUpdate()->first();
            $qty = (int) ($assignment->quantity ?? 1);

            $actionTaken = match ($condition) {
                'good' => 'returned_to_stock',
                'damaged' => 'sent_to_maintenance',
                'lost' => 'scrapped',
                default => 'returned_to_stock',
            };

            $inspection = ToolInspection::create([
                'tool_assignment_id' => $assignment->id,
                'tool_id' => $tool->id,
                'quantity' => $qty,
                'inspected_by_user_id' => $inspectedBy->id,
                'condition' => $condition,
                'action_taken' => $actionTaken,
                'notes' => $notes,
                'inspected_at' => now(),
            ]);

            $assignment->update([
                'returned_at' => now(),
                'status' => $condition === 'lost' ? 'lost' : 'returned',
            ]);

            $mapCondition = match ($condition) {
                'damaged' => 'damaged',
                'lost' => 'damaged',
                default => 'good',
            };
            if ($condition === 'damaged') {
                $mapCondition = 'under_maintenance';
            }

            // Adjust status and location
            $tool->update([
                'current_warehouse_id' => $assignment->from_warehouse_id,
            ]);

            // Use ToolInventoryService to return stock with condition mapping
            $this->toolInvService->returnStock($assignment->fromWarehouse, $tool, $qty, $mapCondition);

            if ($condition === 'damaged') {
                $this->createMaintenance(
                    $tool,
                    $inspectedBy,
                    'Perbaikan setelah pengembalian alat (kondisi rusak)',
                    0.0,
                    $notes,
                    $qty,
                    false // Stock already moved to maintenance via returnStock
                );
            }

            return $inspection->load('tool', 'toolAssignment', 'inspectedBy');
        });
    }

    /**
     * Create Maintenance entry for a tool.
     */
    public function createMaintenance(
        Tool $tool,
        User $reportedBy,
        string $maintenanceType = 'repair',
        float $cost = 0.0,
        ?string $notes = null,
        int $quantity = 1,
        bool $moveStock = true
    ): Maintenance {
        return DB::transaction(function () use ($tool, $reportedBy, $maintenanceType, $cost, $notes, $quantity, $moveStock) {
            $maintenance = Maintenance::create([
                'maintenance_number' => Maintenance::generateMaintenanceNumber(),
                'tool_id' => $tool->id,
                'quantity' => $quantity,
                'reported_by_user_id' => $reportedBy->id,
                'maintenance_type' => $maintenanceType,
                'cost' => $cost,
                'status' => 'in_progress',
                'started_at' => now()->toDateString(),
                'notes' => $notes,
            ]);

            // Move stock from available to maintenance via ToolInventoryService if required
            if ($moveStock) {
                $warehouse = $tool->currentWarehouse;
                if ($warehouse) {
                    $this->toolInvService->moveToMaintenance($warehouse, $tool, $quantity);
                }
            }

            return $maintenance->load('tool', 'reportedBy');
        });
    }

    /**
     * Complete tool maintenance & restore to available status.
     */
    public function completeMaintenance(
        Maintenance $maintenance,
        float $finalCost = 0.0,
        ?string $notes = null
    ): Maintenance {
        if ($maintenance->status === 'completed') {
            throw new Exception("Pemeliharaan ini sudah selesai.");
        }

        return DB::transaction(function () use ($maintenance, $finalCost, $notes) {
            $maintenance->update([
                'status' => 'completed',
                'cost' => $finalCost > 0 ? $finalCost : $maintenance->cost,
                'completed_at' => now()->toDateString(),
                'notes' => $notes ?? $maintenance->notes,
            ]);

            $tool = $maintenance->tool;
            $qty = (int) ($maintenance->quantity ?? 1);
            $warehouse = $tool->currentWarehouse;

            // Restore stock from maintenance back to available via ToolInventoryService
            $this->toolInvService->restoreFromMaintenance($warehouse, $tool, $qty);

            return $maintenance->fresh('tool');
        });
    }
}
