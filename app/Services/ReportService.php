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
            if ($user->hasRole(['Admin', 'Admin Gudang Pusat'])) {
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
            $minStock = (float) ($inventory->min_stock > 0
                ? $inventory->min_stock
                : ($inventory->warehouse?->is_central ? ($inventory->material?->min_stock_central ?? 0) : 0));
            $isLowStock = $inventory->quantity < $minStock;

            return [
                'warehouse_name'      => $inventory->warehouse->name,
                'material_name'       => $inventory->material->name,
                'sku'                 => $inventory->material->sku ?? '',
                'material_code'       => $inventory->material->sku ?? '',
                'category_name'       => $inventory->material->category?->name ?? '-',
                'unit_abbr'           => $inventory->material->unit?->abbreviation ?? '',
                'unit_price'          => (float) ($inventory->material->unit_price ?? 0),
                'quantity'            => (float) $inventory->quantity,
                'qty_on_hand'         => (float) $inventory->quantity,
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
                    'category' => $tool->category?->name ?? '-',
                    'category_name' => $tool->category?->name ?? '-',
                    'current_warehouse' => $tool->currentWarehouse?->name ?? 'N/A',
                    'condition' => $tool->condition ?? 'good',
                    'status' => $tool->status,
                ];
            });
    }

    /**
     * Controller-facing stock report method.
     */
    public function stockReport(?int $warehouseId = null, ?int $categoryId = null): Collection
    {
        $query = Inventory::with(['material.category', 'material.unit', 'warehouse']);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($categoryId) {
            $query->whereHas('material', fn($q) => $q->where('category_id', $categoryId));
        }

        return $query->get()->map(function ($inventory) {
            $minStock = (float) ($inventory->min_stock ?? 0);
            return (object) [
                'warehouse_name' => $inventory->warehouse?->name ?? '-',
                'material_name'  => $inventory->material?->name ?? '-',
                'material_code'  => $inventory->material?->sku ?? $inventory->material?->code ?? '',
                'category_name'  => $inventory->material?->category?->name ?? '-',
                'unit_abbr'      => $inventory->material?->unit?->abbreviation ?? $inventory->material?->unit?->name ?? '',
                'unit_price'     => (float) ($inventory->material?->unit_price ?? 0),
                'quantity'       => (float) $inventory->quantity,
                'min_stock'      => $minStock,
                'qty_allocated'  => (float) $inventory->qty_allocated,
                'qty_in_transit' => (float) $inventory->qty_in_transit,
                'is_low_stock'   => $inventory->quantity < $minStock,
            ];
        });
    }

    /**
     * Controller-facing stock mutation report method.
     */
    public function mutationReport(?int $warehouseId = null, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = StockMutation::with(['material.unit', 'warehouse', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->get()->map(function ($m) {
            return (object) [
                'created_at'     => $m->created_at,
                'material_name'  => $m->material?->name ?? '-',
                'warehouse_name' => $m->warehouse?->name ?? '-',
                'type'           => $m->qty_change >= 0 ? 'in' : 'out',
                'reference_type' => $m->reference_type,
                'quantity'       => $m->qty_change,
                'notes'          => $m->notes ?? '-',
            ];
        });
    }

    /**
     * Controller-facing discrepancy report method.
     */
    public function discrepancyReport(?int $warehouseId = null): Collection
    {
        $query = \App\Models\StockOpnameItem::with(['stockOpname.warehouse', 'material'])
            ->whereHas('stockOpname', function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            })
            ->latest();

        return $query->get()->map(function ($item) {
            return (object) [
                'opname_date'       => $item->stockOpname?->conducted_at ?? $item->created_at,
                'warehouse_name'    => $item->stockOpname?->warehouse?->name ?? '-',
                'material_name'     => $item->material?->name ?? '-',
                'system_quantity'   => (float) $item->qty_system,
                'physical_quantity' => (float) $item->qty_physical,
                'difference'        => (float) $item->qty_difference,
                'notes'             => $item->notes ?? '-',
            ];
        });
    }

    /**
     * Controller-facing tool report method.
     */
    public function toolReport(): Collection
    {
        return Tool::with(['category', 'currentWarehouse'])
            ->orderBy('name')
            ->get()
            ->map(function ($tool) {
                return (object) [
                    'code'              => $tool->code,
                    'name'              => $tool->name,
                    'brand'             => $tool->brand,
                    'serial_number'     => $tool->serial_number,
                    'category_name'     => $tool->category?->name ?? '-',
                    'current_warehouse' => $tool->currentWarehouse?->name ?? '-',
                    'condition'         => $tool->condition ?? 'good',
                    'status'            => $tool->status,
                ];
            });
    }
}
