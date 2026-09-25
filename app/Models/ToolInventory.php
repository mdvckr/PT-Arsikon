<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'tool_id',
        'stock_total',
        'stock_available',
        'stock_borrowed',
        'stock_maintenance',
        'stock_damaged',
    ];

    protected $casts = [
        'stock_total' => 'integer',
        'stock_available' => 'integer',
        'stock_borrowed' => 'integer',
        'stock_maintenance' => 'integer',
        'stock_damaged' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function validateInvariants(): void
    {
        $stocks = [
            (int) $this->stock_total,
            (int) $this->stock_available,
            (int) $this->stock_borrowed,
            (int) $this->stock_maintenance,
            (int) $this->stock_damaged,
        ];
        foreach ($stocks as $s) {
            if ($s < 0) {
                throw new \Exception('Stock values cannot be negative.');
            }
        }
        $calc = (int) $this->stock_available + (int) $this->stock_borrowed + (int) $this->stock_maintenance + (int) $this->stock_damaged;
        if ($calc !== (int) $this->stock_total) {
            throw new \Exception("Invariant violation: stock_total ({$this->stock_total}) does not equal sum of detail stocks ({$calc}).");
        }
    }
}