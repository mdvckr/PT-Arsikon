<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'current_warehouse_id',
        'code',
        'name',
        'type',
        'brand',
        'stock_total',
        'stock_available',
        'stock_borrowed',
        'stock_maintenance',
        'stock_damaged',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'stock_total'      => 'integer',
        'stock_available'  => 'integer',
        'stock_borrowed'   => 'integer',
        'stock_maintenance'=> 'integer',
        'stock_damaged'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ToolAssignment::class);
    }

    /**
     * Tambah stok tersedia ke alat ini.
     */
    public function addStock(int $qty): void
    {
        $this->increment('stock_total', $qty);
        $this->increment('stock_available', $qty);
    }

    /**
     * Pinjamkan stok (kurangi available, tambah borrowed).
     */
    public function borrow(int $qty): void
    {
        $this->decrement('stock_available', $qty);
        $this->increment('stock_borrowed', $qty);
    }

    /**
     * Kembalikan stok (kurangi borrowed, tambah available atau damaged/maintenance).
     */
    public function returnStock(int $qty, string $condition = 'good'): void
    {
        $this->decrement('stock_borrowed', $qty);
        match($condition) {
            'damaged'           => $this->increment('stock_damaged', $qty),
            'under_maintenance' => $this->increment('stock_maintenance', $qty),
            default             => $this->increment('stock_available', $qty),
        };
    }
}
