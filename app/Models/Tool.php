<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'current_warehouse_id',
        'owner_warehouse_id',
        'code',
        'name',
        'type',
        'size',
        'brand',
        'stock_total',
        'stock_available',
        'stock_borrowed',
        'stock_maintenance',
        'stock_damaged',
        'is_active',
        'notes',
        'incoming_stages',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'owner_warehouse_id' => 'integer',
        'stock_total'        => 'integer',
        'stock_available'    => 'integer',
        'stock_borrowed'     => 'integer',
        'stock_maintenance'  => 'integer',
        'stock_damaged'      => 'integer',
        'incoming_stages'    => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function ownerWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'owner_warehouse_id');
    }

    public function isOwnedByCentral(): bool
    {
        if ($this->owner_warehouse_id) {
            return (bool) ($this->ownerWarehouse?->is_central ?? false);
        }
        return true;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ToolAssignment::class);
    }

    /**
     * Per-warehouse inventory records for this tool (multi-warehouse support).
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(ToolInventory::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = !empty($value) ? strtoupper(trim($value)) : $value;
    }

    /**
     * Mendapatkan rincian tahapan masuk aktif (PO Lapangan & PO Pengadaan yang sudah disetujui) untuk Alat.
     *
     * @param int|array|null $warehouseIds
     * @return \Illuminate\Support\Collection
     */
    public function getDynamicIncomingStages($warehouseIds = null)
    {
        $whIds = is_array($warehouseIds) ? $warehouseIds : ($warehouseIds ? [$warehouseIds] : null);

        $stages = collect();

        // 1. Dari Purchase Order untuk Alat
        $poItemsQuery = PurchaseOrderItem::where(function ($q) {
                $q->where('tool_id', $this->id);
                if (!empty($this->name)) {
                    $q->orWhereRaw('LOWER(TRIM(custom_item_name)) = ?', [strtolower(trim($this->name))]);
                }
            })
            ->whereHas('purchaseOrder', function ($q) use ($whIds) {
                $q->whereIn('status', ['approved', 'sent', 'partial_received', 'partially_received']);
                if (!empty($whIds)) {
                    $q->where(function ($sub) use ($whIds) {
                        $sub->whereIn('delivery_warehouse_id', $whIds)
                            ->orWhereNull('delivery_warehouse_id');
                    });
                }
            })
            ->with(['purchaseOrder.deliveryWarehouse', 'purchaseOrder.supplier']);

        foreach ($poItemsQuery->get() as $poItem) {
            $po = $poItem->purchaseOrder;
            if (!$po) continue;

            $qtyPlanned = (int) $poItem->quantity;
            $qtyReceived = (int) $poItem->received_qty;
            $remaining = max(0, $qtyPlanned - $qtyReceived - (int)$poItem->lost_qty - (int)$poItem->short_closed_qty);

            if ($remaining > 0 || ($qtyReceived > 0 && in_array($po->status, ['partial_received', 'partially_received']))) {
                $stages->push([
                    'type'           => 'PO',
                    'stage'          => $po->po_number,
                    'reference'      => $po->po_number,
                    'source'         => $po->supplier?->name ?: ($po->supplier_name ?: 'Vendor PO'),
                    'warehouse_name' => $po->deliveryWarehouse?->name,
                    'warehouse_id'   => $po->delivery_warehouse_id,
                    'qty'            => $remaining > 0 ? $remaining : $qtyReceived,
                    'planned_qty'    => $qtyPlanned,
                    'received_qty'   => $qtyReceived,
                    'remaining_qty'  => $remaining,
                    'date'           => $po->expected_delivery ? $po->expected_delivery->format('Y-m-d') : ($po->order_date ? $po->order_date->format('Y-m-d') : null),
                    'status'         => $remaining <= 0 ? 'received' : ($qtyReceived > 0 ? 'partial' : 'planned'),
                    'status_label'   => $remaining <= 0 ? 'Lengkap Masuk' : ($qtyReceived > 0 ? 'Masuk Sebagian (' . number_format($qtyReceived, 0, ',', '.') . '/' . number_format($qtyPlanned, 0, ',', '.') . ')' : 'Rencana'),
                    'notes'          => $po->notes ?: null,
                ]);
            }
        }

        // 2. Dari PO Lapangan untuk Alat
        $mrItemsQuery = MaterialRequestItem::where(function ($q) {
                $q->where('tool_id', $this->id);
                if (!empty($this->name)) {
                    $q->orWhereRaw('LOWER(TRIM(custom_item_name)) = ?', [strtolower(trim($this->name))]);
                }
            })
            ->whereHas('materialRequest', function ($q) use ($whIds) {
                $q->whereIn('status', ['approved', 'partially_fulfilled']);
                if (!empty($whIds)) {
                    $q->where(function ($sub) use ($whIds) {
                        $sub->whereIn('to_warehouse_id', $whIds)
                            ->orWhereNull('to_warehouse_id');
                    });
                }
            })
            ->with(['materialRequest.toWarehouse', 'materialRequest.requestedBy']);

        foreach ($mrItemsQuery->get() as $mrItem) {
            $mr = $mrItem->materialRequest;
            if (!$mr) continue;

            $qtyApproved = (int) ($mrItem->qty_approved > 0 ? $mrItem->qty_approved : $mrItem->qty_requested);
            $qtyReceived = (int) $mrItem->qty_received;
            $qtyOrdered = (int) $mrItem->qty_ordered;

            $unpoQty = max(0, $qtyApproved - max($qtyOrdered, $qtyReceived));

            if ($unpoQty > 0) {
                $stages->push([
                    'type'           => 'PO Lapangan',
                    'stage'          => $mr->request_number,
                    'reference'      => $mr->request_number,
                    'source'         => 'PO Lapangan (' . ($mr->requestedBy?->name ?: 'Proyek') . ')',
                    'warehouse_name' => $mr->toWarehouse?->name,
                    'warehouse_id'   => $mr->to_warehouse_id,
                    'qty'            => $unpoQty,
                    'planned_qty'    => $unpoQty,
                    'received_qty'   => 0,
                    'remaining_qty'  => $unpoQty,
                    'date'           => $mr->needed_at ? $mr->needed_at->format('Y-m-d') : null,
                    'status'         => 'planned',
                    'status_label'   => 'Menunggu Pengadaan',
                    'notes'          => $mr->notes ?: null,
                ]);
            }
        }

        return $stages;
    }

    /**
     * Accessor: aggregate total stock across all warehouses.
     */
    public function getStockTotalAttribute(): int
    {
        $cached = $this->getRelationValue('inventories') ?? null;
        if ($cached !== null) {
            return (int) $cached->sum('stock_total');
        }
        return (int) ($this->getAttributeFromArray('stock_total') ?? 0);
    }

    public function getStockAvailableAttribute(): int
    {
        $cached = $this->getRelationValue('inventories') ?? null;
        if ($cached !== null) {
            return (int) $cached->sum('stock_available');
        }
        return (int) ($this->getAttributeFromArray('stock_available') ?? 0);
    }

    public function getStockBorrowedAttribute(): int
    {
        $cached = $this->getRelationValue('inventories') ?? null;
        if ($cached !== null) {
            return (int) $cached->sum('stock_borrowed');
        }
        return (int) ($this->getAttributeFromArray('stock_borrowed') ?? 0);
    }

    public function getStockMaintenanceAttribute(): int
    {
        $cached = $this->getRelationValue('inventories') ?? null;
        if ($cached !== null) {
            return (int) $cached->sum('stock_maintenance');
        }
        return (int) ($this->getAttributeFromArray('stock_maintenance') ?? 0);
    }

    public function getStockDamagedAttribute(): int
    {
        $cached = $this->getRelationValue('inventories') ?? null;
        if ($cached !== null) {
            return (int) $cached->sum('stock_damaged');
        }
        return (int) ($this->getAttributeFromArray('stock_damaged') ?? 0);
    }

    /**
     * Get (or create) the inventory record for a specific warehouse.
     */
    public function inventoryFor(?Warehouse $warehouse): ?ToolInventory
    {
        if (!$warehouse) {
            return null;
        }

        return $this->inventories()
            ->where('warehouse_id', $warehouse->id)
            ->get()
            ->first(fn($i) => true)
            ?? ToolInventory::where('tool_id', $this->id)
                ->where('warehouse_id', $warehouse->id)
                ->first();
    }

    /**
     * Apakah alat ini memiliki assignment aktif yang melewati batas waktu.
     */
    public function hasOverdue(): bool
    {
        return $this->assignments()
            ->where('status', 'active')
            ->whereNotNull('expected_return_at')
            ->where('expected_return_at', '<', now())
            ->exists();
    }

/**
     * Get computed status label based on stock levels and overdue assignments.
     */
    public function statusLabel(): string
    {
        if ($this->hasOverdue()) {
            return 'OVERDUE';
        }
        if ($this->stock_damaged > 0 && $this->stock_total === $this->stock_damaged) {
            return 'DAMAGED';
        }
        if ($this->stock_maintenance > 0 && $this->stock_total === $this->stock_maintenance) {
            return 'MAINTENANCE';
        }
        if ($this->stock_borrowed > 0 && $this->stock_total === $this->stock_borrowed) {
            return 'IN USE';
        }
        return 'AVAILABLE';
    }

    /**
     * Magic getter for status - returns lowercase canonical status for backward compatibility.
     */
    public function getStatusAttribute(): string
    {
        if ($this->hasOverdue()) {
            return 'overdue';
        }
        if ($this->stock_damaged > 0 && ($this->stock_total === $this->stock_damaged || $this->stock_available === 0)) {
            return 'damaged';
        }
        if ($this->stock_maintenance > 0 && ($this->stock_total === $this->stock_maintenance || $this->stock_available === 0)) {
            return 'maintenance';
        }
        if ($this->stock_borrowed > 0 && ($this->stock_total === $this->stock_borrowed || $this->stock_available === 0)) {
            return 'assigned';
        }
        if ($this->stock_available > 0) {
            return 'available';
        }
        return strtolower($this->statusLabel());
    }

    /**
     * Validate that total stock equals the sum of all derived stock columns.
     * Throws an Exception if invariant is violated.
     */
    protected function ensureInvariant(): void
    {
        $calc = $this->stock_available + $this->stock_borrowed + $this->stock_maintenance + $this->stock_damaged;
        if ($calc !== $this->stock_total) {
            throw new Exception("Invariant violation: stock_total ({$this->stock_total}) tidak sama dengan jumlah detail stok ({$calc}).");
        }
    }

    /**
     * Lokasi / proyek dari assignment aktif terakhir.
     */
    public function currentAssignmentContext(): array
    {
        $active = $this->assignments()
            ->where('status', 'active')
            ->latest('assigned_at')
            ->first();

        if (! $active) {
            return ['warehouse' => $this->currentWarehouse?->name ?? 'Gudang Utama', 'project' => null];
        }

        return [
            'warehouse' => $active->fromWarehouse?->name ?? 'Gudang Utama',
            'project'   => $active->notes,
        ];
    }

    /**
     * Tambah stok tersedia ke alat ini.
     */
    public function addStock(int $qty): void
    {
        $this->increment('stock_total', $qty);
        $this->increment('stock_available', $qty);
    }

    /**
     * Pinjamkan stok (kurangi available, tambah borrowed).
     */
    public function borrow(int $qty): void
    {
        $this->decrement('stock_available', $qty);
        $this->increment('stock_borrowed', $qty);
    }

    /**
     * Kembalikan stok (kurangi borrowed, tambah available atau damaged/maintenance).
     */
    public function returnStock(int $qty, string $condition = 'good'): void
    {
        $this->decrement('stock_borrowed', $qty);
        match($condition) {
            'damaged'           => $this->increment('stock_damaged', $qty),
            'under_maintenance' => $this->increment('stock_maintenance', $qty),
            default             => $this->increment('stock_available', $qty),
        };
    }
}
