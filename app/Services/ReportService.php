<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Inventory;
use App\Models\Maintenance;
use App\Models\MaterialRequest;
use App\Models\StockMutation;
use App\Models\Tool;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get Scoped Stock Summary Report.
     */
    public function getStockReport(User $user, ?Warehouse $selectedWarehouse = null): Collection
    {
        $query = Inventory::with(['material.category', 'material.unit', 'warehouse']);

        if ($selectedWarehouse) {
            if (!$user->hasAccessToWarehouse($selectedWarehouse)) {
                return collect([]);
            }
            $query->where('warehouse_id', $selectedWarehouse->id);
        } else {
            // Apply user role scoping
            if ($user->hasRole('Admin')) {
                $central = Warehouse::where('is_central', true)->first();
                if ($central) {
                    $query->where('warehouse_id', $central->id);
                }
            } elseif (!$user->hasRole('Owner')) {
                $assignedIds = $user->warehouses()->pluck('warehouses.id');
                $query->whereIn('warehouse_id', $assignedIds);
            }
        }

        return $query->get()->map(function ($inventory) {
            $minStock = (float) ($inventory->min_stock ?? 0);
            $isLowStock = $inventory->quantity < $minStock;

            return [
                'warehouse_name'      => $inventory->warehouse->name,
                'material_name'       => $inventory->material->name,
                'material_code'       => $inventory->material->code ?? '',
                'category_name'       => $inventory->material->category?->name ?? '-',
                'unit_abbr'           => $inventory->material->unit?->abbreviation ?? '',
                'unit_price'          => (float) ($inventory->material->unit_price ?? 0),
                'quantity'            => (float) $inventory->quantity,
                'min_stock'           => $minStock,
                'qty_allocated'       => (float) $inventory->qty_allocated,
                'qty_in_transit'      => (float) $inventory->qty_in_transit,
                'is_low_stock'        => $isLowStock,
            ];
        });
    }

    /**
     * Get Scoped Stock Movement (Kartu Stok) Report.
     */
    public function getStockMovementReport(
        User $user,
        ?Warehouse $warehouse = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = StockMutation::with(['material.unit', 'warehouse', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if ($warehouse) {
            if (!$user->hasAccessToWarehouse($warehouse)) {
                return collect([]);
            }
            $query->where('warehouse_id', $warehouse->id);
        } else {
            if ($user->hasRole('Admin')) {
                $central = Warehouse::where('is_central', true)->first();
                if ($central) {
                    $query->where('warehouse_id', $central->id);
                }
            } elseif (!$user->hasRole('Owner')) {
                $assignedIds = $user->warehouses()->pluck('warehouses.id');
                $query->whereIn('warehouse_id', $assignedIds);
            }
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get Scoped Distribution & Discrepancy (Damaged Goods) Report.
     */
    public function getDiscrepancyReport(User $user): Collection
    {
        $query = DistributionItem::with(['distribution.fromWarehouse', 'distribution.toWarehouse', 'material.unit'])
            ->where('qty_damaged_or_lost', '>', 0);

        if (!$user->hasRole('Owner')) {
            $assignedIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereHas('distribution', function (Builder $q) use ($assignedIds) {
                $q->whereIn('from_warehouse_id', $assignedIds)
                  ->orWhereIn('to_warehouse_id', $assignedIds);
            });
        }

        return $query->get();
    }

    /**
     * Get Tool Usage & Maintenance Report.
     */
    public function getToolReport(): Collection
    {
        return Tool::with(['category', 'currentWarehouse'])
            ->get()
            ->map(function ($tool) {
                return [
                    'code' => $tool->code,
                    'name' => $tool->name,
                    'brand' => $tool->brand,
                    'serial_number' => $tool->serial_number,
                    'category' => $tool->category->name,
                    'current_warehouse' => $tool->currentWarehouse?->name ?? 'N/A',
                    'status' => $tool->status,
                ];
            });
    }
}
