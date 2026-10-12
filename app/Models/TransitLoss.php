<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransitLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'distribution_item_id',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'purchase_order_id',
        'purchase_order_item_id',
        'material_id',
        'tool_id',
        'qty',
        'lost_qty',
        'unit_price',
        'loss_value',
        'status',
        'evidence_path',
        'notes',
        'recorded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'qty'         => 'decimal:2',
        'lost_qty'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'loss_value'  => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function distributionItem(): BelongsTo
    {
        return $this->belongsTo(DistributionItem::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
