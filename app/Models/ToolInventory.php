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
            $this->stock_total,
            $this->stock_available,
            $this->stock_borrowed,
            $this->stock_maintenance,
            $this->stock_damaged,
        ];
        foreach ($stocks as $s) {
            if ((int) $s < 0) {
                throw new \Exception('Stock values cannot be negative.');
            }
        }
        $calc = $this->stock_available + $this->stock_borrowed + $this->stock_maintenance + $this->stock_damaged;
        if ($calc !== $this->stock_total) {
            throw new \Exception('Invariant violation: stock_total does not equal sum of detail stocks.');
        }
    }
}