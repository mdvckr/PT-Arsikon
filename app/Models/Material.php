<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'supplier_name',
        'unit_id',
        'sku',
        'name',
        'brand',
        'size',
        'type',
        'min_stock_central',
        'is_active',
        'description',
        'incoming_stages',
    ];

    protected $casts = [
        'min_stock_central' => 'decimal:2',
        'is_active' => 'boolean',
        'incoming_stages' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function getCodeAttribute(): ?string
    {
        return $this->sku;
    }
}
