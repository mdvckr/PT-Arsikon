<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'item_type',
        'purchase_order_item_id',
        'stage_reference',
        'material_id',
        'tool_id',
        'qty_received',
        'condition',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'qty_received' => 'decimal:2',
        'unit_price'   => 'decimal:2',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function isTool(): bool
    {
        return $this->item_type === 'tool' || !empty($this->tool_id);
    }

    public function getItemNameAttribute(): string
    {
        if ($this->isTool()) {
            return $this->tool?->name ?? 'Alat #' . $this->tool_id;
        }
        return $this->material?->name ?? 'Material #' . $this->material_id;
    }

    public function getItemCodeAttribute(): string
    {
        if ($this->isTool()) {
            return $this->tool?->code ?? '-';
        }
        return $this->material?->sku ?? $this->material?->code ?? '-';
    }

    public function getItemUnitAttribute(): string
    {
        if ($this->isTool()) {
            return 'Unit';
        }
        return $this->material?->unit?->abbreviation ?? 'Unit';
    }

    // Alias attribute agar DailyLogController yang pakai 'quantity_received' tetap bisa berjalan
    public function getQuantityReceivedAttribute(): float
    {
        return (float) $this->qty_received;
    }

    // Alias untuk 'quantity' dipakai di show.blade.php
    public function getQuantityAttribute(): float
    {
        return (float) $this->qty_received;
    }
}
