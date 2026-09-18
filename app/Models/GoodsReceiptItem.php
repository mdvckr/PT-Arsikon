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
        'purchase_order_item_id',
        'material_id',
        'qty_received',
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

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
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
