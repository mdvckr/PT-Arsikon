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
                $q->whereIn('status', ['draft', 'approved', 'sent', 'partial_received', 'partially_received', 'received']);
                if (!empty($whIds)) {
                    $q->where(function ($sub) use ($whIds) {
                        $sub->whereIn('delivery_warehouse_id', $whIds)
                            ->orWhereNull('delivery_warehouse_id');
                    });
                }
            })
            ->with(['purchaseOrder.deliveryWarehouse', 'purchaseOrder.supplier', 'purchaseOrder.goodsReceipts.warehouse', 'purchaseOrder.materialRequest']);

        foreach ($poItemsQuery->get() as $poItem) {
            $po = $poItem->purchaseOrder;
            if (!$po) continue;

            $qtyPlanned = (int) $poItem->quantity;
            $qtyReceived = (int) $poItem->received_qty;
            $remaining = max(0, $qtyPlanned - $qtyReceived - (int)$poItem->lost_qty - (int)$poItem->short_closed_qty);

            $isFullyReceived = ($po->status === 'received') || ($remaining <= 0 && $qtyReceived > 0);
            $isPartial = in_array($po->status, ['partial_received', 'partially_received']) || ($qtyReceived > 0 && $remaining > 0);

            // Cari nomor penerimaan barang (Goods Receipt)
            $grs = $po->goodsReceipts->filter(function($gr) use ($whIds) {
                return empty($whIds) || in_array($gr->warehouse_id, $whIds);
            });
            $grNumber = $grs->pluck('receipt_number')->filter()->unique()->implode(', ');

            if ($isFullyReceived) {
                $status = 'received';
                $statusLabel = 'Lengkap Masuk';
            } elseif ($isPartial) {
                $status = 'partial';
                $statusLabel = 'Masuk Sebagian (' . number_format($qtyReceived, 0, ',', '.') . '/' . number_format($qtyPlanned, 0, ',', '.') . ')';
            } elseif ($po->status === 'draft') {
                $status = 'planned';
                $statusLabel = 'Draft PO';
            } elseif ($po->status === 'sent') {
                $status = 'planned';
                $statusLabel = 'Dalam Pengiriman';
            } else {
                $status = 'planned';
                $statusLabel = 'Disetujui (Menunggu Kirim)';
            }

            $refDisplay = $po->po_number;
            if ($grNumber) {
                $refDisplay .= ' · ' . $grNumber;
            } elseif ($po->materialRequest) {
                $refDisplay .= ' (MR: ' . $po->materialRequest->request_number . ')';
            }

            $stageDate = null;
            if ($isFullyReceived && $grs->isNotEmpty() && $grs->first()->receipt_date) {
                $stageDate = $grs->first()->receipt_date->format('Y-m-d');
            } elseif ($po->expected_delivery) {
                $stageDate = $po->expected_delivery->format('Y-m-d');
            } elseif ($po->order_date) {
                $stageDate = $po->order_date->format('Y-m-d');
            }

            $stages->push([
                'type'           => 'PO',
                'stage'          => $po->po_number,
                'reference'      => $refDisplay,
                'source'         => $po->supplier?->name ?: ($po->supplier_name ?: 'Vendor PO'),
                'warehouse_name' => $po->deliveryWarehouse?->name,
                'warehouse_id'   => $po->delivery_warehouse_id,
                'qty'            => $isFullyReceived ? $qtyReceived : ($remaining > 0 ? $remaining : $qtyPlanned),
                'planned_qty'    => $qtyPlanned,
                'received_qty'   => $qtyReceived,
                'remaining_qty'  => $remaining,
                'date'           => $stageDate,
                'status'         => $status,
                'status_label'   => $statusLabel,
                'notes'          => $po->notes ?: null,
            ]);
        }

        // 2. Dari Penerimaan Barang (GR) langsung tanpa PO
        $directGrItems = \App\Models\GoodsReceiptItem::where('tool_id', $this->id)
            ->whereHas('goodsReceipt', function ($q) use ($whIds) {
                $q->where('status', 'confirmed')
                  ->whereNull('purchase_order_id');
                if (!empty($whIds)) {
                    $q->whereIn('warehouse_id', $whIds);
                }
            })
            ->with(['goodsReceipt.warehouse', 'goodsReceipt.materialRequest'])
            ->get();

        foreach ($directGrItems as $grItem) {
            $gr = $grItem->goodsReceipt;
            if (!$gr) continue;
            $qtyRec = (int) $grItem->qty_received;
            if ($qtyRec <= 0) continue;

            $refDisplay = $gr->receipt_number;
            if ($gr->materialRequest) {
                $refDisplay .= ' (MR: ' . $gr->materialRequest->request_number . ')';
            }

            $stages->push([
                'type'           => 'Penerimaan (GR)',
                'stage'          => $gr->receipt_number,
                'reference'      => $refDisplay,
                'source'         => $gr->supplier_name ?: 'Penerimaan Langsung Proyek',
                'warehouse_name' => $gr->warehouse?->name,
                'warehouse_id'   => $gr->warehouse_id,
                'qty'            => $qtyRec,
                'planned_qty'    => $qtyRec,
                'received_qty'   => $qtyRec,
                'remaining_qty'  => 0,
                'date'           => $gr->receipt_date ? $gr->receipt_date->format('Y-m-d') : null,
                'status'         => 'received',
                'status_label'   => 'Lengkap Masuk',
                'notes'          => $gr->notes ?: null,
            ]);
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

    /**
     * Auto-register custom/manual tool to master tools table.
     */
    public static function autoRegisterCustom(
        string $name,
        ?int $categoryId = null,
        ?string $typeName = null,
        ?string $brand = null,
        ?string $size = null
    ): Tool {
        $cleanName = trim($name);
        $tool = static::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
        if ($tool) {
            return $tool;
        }

        // Resolve category: prioritaskan pilihan user ($categoryId), atau cari kategori Khusus Proyek, jika belum ada buatkan CAT-TOOL-CUST
        $category = null;
        if ($categoryId) {
            $category = Category::where('type', 'tool')->find($categoryId);
        }

        if (!$category) {
            $category = Category::where('type', 'tool')
                ->where(function ($q) {
                    $q->where('code', 'CAT-TOOL-CUST')
                      ->orWhere('name', 'like', '%Khusus Proyek%')
                      ->orWhere('name', 'like', '%Custom%');
                })->first();
        }

        if (!$category) {
            $category = Category::firstOrCreate(
                ['code' => 'CAT-TOOL-CUST'],
                [
                    'name' => 'Alat Khusus Proyek',
                    'type' => 'tool',
                    'description' => 'Kategori otomatis untuk alat custom/manual dari pengadaan proyek',
                ]
            );
        }

        // Resolve kelompok/type: prioritaskan $typeName, atau cari kelompok yang sudah ada di kategori ini, atau fallback kontekstual
        $resolvedType = trim($typeName ?? '');
        if (empty($resolvedType)) {
            $existingType = static::where('category_id', $category->id)
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->where('type', '!=', 'Alat Proyek')
                ->value('type');

            $resolvedType = $existingType ?: ($category->code !== 'CAT-TOOL-CUST' ? $category->name : 'Alat Proyek');
        }

        // Generate unique Code
        $maxId = (int) (static::max('id') ?? 0) + 1;
        $code = 'TL-AUTO-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);
        while (static::where('code', $code)->exists()) {
            $maxId++;
            $code = 'TL-AUTO-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);
        }

        $centralWarehouse = Warehouse::where('is_central', true)->first();

        return static::create([
            'category_id'          => $category->id,
            'owner_warehouse_id'   => $centralWarehouse?->id,
            'current_warehouse_id' => $centralWarehouse?->id,
            'code'                 => $code,
            'name'                 => $cleanName,
            'brand'                => $brand ?: 'Custom / Proyek',
            'type'                 => $resolvedType,
            'size'                 => $size ?: null,
            'is_active'            => true,
            'notes'                => 'Didaftarkan otomatis oleh sistem dari Permintaan / Penerimaan Barang Proyek',
        ]);
    }
}
