<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoDeliverySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_item_id',
        'stage_label',
        'planned_date',
        'planned_qty',
        'received_qty',
        'warehouse_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'planned_qty'  => 'decimal:2',
        'received_qty' => 'decimal:2',
    ];

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
