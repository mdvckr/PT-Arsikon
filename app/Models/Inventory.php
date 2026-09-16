<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'material_id',
        'quantity',
        'min_stock',
        'qty_allocated',
        'qty_in_transit',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'min_stock'     => 'decimal:2',
        'qty_allocated' => 'decimal:2',
        'qty_in_transit'=> 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Mutasi stok untuk material ini di gudang ini.
     * StockMutation tidak punya FK langsung ke inventories,
     * sehingga dijembatani lewat material_id + warehouse_id.
     */
    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class, 'material_id', 'material_id')
            ->where('stock_mutations.warehouse_id', $this->warehouse_id);
    }
}
