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
        'tool_id',
        'custom_item_name',
        'custom_item_unit',
        'material_request_item_id',
        'procurement_request_item_id',
        'quantity',
        'received_qty',
        'lost_qty',
        'short_closed_qty',
        'unit_price',
        'subtotal',
        'total_price',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity'         => 'decimal:2',
        'received_qty'     => 'decimal:2',
        'lost_qty'         => 'decimal:2',
        'short_closed_qty' => 'decimal:2',
        'unit_price'       => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(function ($item) {
            if ($item->isDirty('unit_price')) {
                $po = $item->purchaseOrder ?? PurchaseOrder::find($item->purchase_order_id);
                if ($po && $po->status !== 'draft') {
                    throw new \DomainException("Harga satuan item PO tidak dapat diubah setelah PO melewati status draft (status PO saat ini: {$po->status}).");
                }
            }
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function materialRequestItem()
    {
        return $this->belongsTo(MaterialRequestItem::class);
    }

    public function procurementRequestItem()
    {
        return $this->belongsTo(ProcurementRequestItem::class);
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

    public function getOutstandingAttribute(): float
    {
        return max(0, (float)$this->quantity - (float)$this->received_qty - (float)$this->lost_qty - (float)$this->short_closed_qty);
    }
}
