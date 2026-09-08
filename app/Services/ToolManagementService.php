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
    /**
     * Assign tool to Project Warehouse or Worker.
     */
    public function assignTool(
        Tool $tool,
        Warehouse $fromWarehouse,
        User $assignedBy,
        ?Warehouse $toWarehouse = null,
        ?User $assignedToUser = null,
        ?string $expectedReturnAt = null,
        ?string $notes = null
    ): ToolAssignment {
        if ($tool->status !== 'available') {
            throw new Exception("Alat dengan status '{$tool->status}' tidak dapat dipinjamkan / didistribusikan.");
        }

        return DB::transaction(function () use ($tool, $fromWarehouse, $assignedBy, $toWarehouse, $assignedToUser, $expectedReturnAt, $notes) {
            $assignment = ToolAssignment::create([
                'assignment_number' => ToolAssignment::generateAssignmentNumber(),
                'tool_id' => $tool->id,
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse?->id,
                'assigned_to_user_id' => $assignedToUser?->id,
                'assigned_by_user_id' => $assignedBy->id,
                'assigned_at' => now(),
                'expected_return_at' => $expectedReturnAt,
                'status' => 'active',
                'notes' => $notes,
            ]);

            $tool->update([
                'status' => 'assigned',
                'current_warehouse_id' => $toWarehouse?->id ?? $tool->current_warehouse_id,
            ]);

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
            $tool = $assignment->tool;

            $actionTaken = match ($condition) {
                'good' => 'returned_to_stock',
                'damaged' => 'sent_to_maintenance',
                'lost' => 'scrapped',
                default => 'returned_to_stock',
            };

            $inspection = ToolInspection::create([
                'tool_assignment_id' => $assignment->id,
                'tool_id' => $tool->id,
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

            $newToolStatus = match ($condition) {
                'good' => 'available',
                'damaged' => 'maintenance',
                'lost' => 'lost',
                default => 'available',
            };

            $tool->update([
                'status' => $newToolStatus,
                'current_warehouse_id' => $assignment->from_warehouse_id, // Returns to original warehouse
            ]);

            if ($condition === 'damaged') {
                $this->createMaintenance(
                    $tool,
                    $inspectedBy,
                    'Perbaikan setelah pengembalian alat (kondisi rusak)',
                    0.0,
                    $notes
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
        ?string $notes = null
    ): Maintenance {
        return DB::transaction(function () use ($tool, $reportedBy, $maintenanceType, $cost, $notes) {
            $maintenance = Maintenance::create([
                'maintenance_number' => Maintenance::generateMaintenanceNumber(),
                'tool_id' => $tool->id,
                'reported_by_user_id' => $reportedBy->id,
                'maintenance_type' => $maintenanceType,
                'cost' => $cost,
                'status' => 'in_progress',
                'started_at' => now()->toDateString(),
                'notes' => $notes,
            ]);

            $tool->update([
                'status' => 'maintenance',
            ]);

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

            $maintenance->tool->update([
                'status' => 'available',
            ]);

            return $maintenance->fresh('tool');
        });
    }
}
