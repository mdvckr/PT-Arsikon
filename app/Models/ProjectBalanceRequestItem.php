<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBalanceRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_balance_request_id',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'estimated_unit_price',
        'estimated_subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function setEstimatedUnitPriceAttribute($value): void
    {
        $this->attributes['unit_price'] = $value;
    }

    public function setEstimatedSubtotalAttribute($value): void
    {
        $this->attributes['total_price'] = $value;
    }

    public function getEstimatedUnitPriceAttribute(): float
    {
        return (float) ($this->attributes['unit_price'] ?? 0);
    }

    public function getEstimatedSubtotalAttribute(): float
    {
        return (float) ($this->attributes['total_price'] ?? 0);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProjectBalanceRequest::class, 'project_balance_request_id');
    }
}

