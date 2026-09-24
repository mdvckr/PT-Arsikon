<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'material_id',
        'custom_item_name',
        'custom_item_unit',
        'material_request_item_id',
        'quantity',
        'received_qty',
        'unit_price',
        'subtotal',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'received_qty' => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function materialRequestItem()
    {
        return $this->belongsTo(MaterialRequestItem::class);
    }

    public function isCustom(): bool
    {
        return empty($this->material_id) || !empty($this->custom_item_name);
    }

    public function displayName(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_name ?? '(Barang Manual/Custom)';
        }
        return $this->material?->name ?? '-';
    }

    public function displayUnit(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_unit ?? ($this->unit ?? 'unit');
        }
        return $this->material?->unit?->symbol ?? ($this->unit ?? 'unit');
    }
}
