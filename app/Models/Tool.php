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
        'is_active'        => 'boolean',
        'stock_total'      => 'integer',
        'stock_available'  => 'integer',
        'stock_borrowed'   => 'integer',
        'stock_maintenance'=> 'integer',
        'stock_damaged'    => 'integer',
        'incoming_stages'  => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
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
