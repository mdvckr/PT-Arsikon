<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'warehouse_id',
        'conducted_by_user_id',
        'approved_by_user_id',
        'status',
        'conducted_at',
        'notes',
    ];

    protected $casts = [
        'conducted_at' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public static function generateOpnameNumber(): string
    {
        $prefix = 'OPN-' . date('Ymd') . '-';
        $latest = self::where('opname_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->opname_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
