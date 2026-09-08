<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id','material_id','quantity','received_qty','unit_price','subtotal','unit','notes',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'received_qty' => 'decimal:2', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function material()      { return $this->belongsTo(Material::class); }
}
