<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receipt_id',
        'material_id',
        'item_type',
        'material_name',
        'item_name',
        'unit_name',
        'quantity',
        'unit_price',
        'item_discount',
        'subtotal',
        'notes',
    ];

    public function getItemTypeLabelAttribute(): string
    {
        return $this->item_type ?: 'Material';
    }

    public function getItemTypeBadgeClassAttribute(): string
    {
        return match (strtolower($this->item_type ?? 'material')) {
            'alat'        => 'badge-warning',
            'konsumsi'    => 'badge-success',
            'operasional' => 'badge-info',
            default       => 'badge-primary',
        };
    }

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'item_discount' => 'decimal:2',
        'subtotal'      => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
