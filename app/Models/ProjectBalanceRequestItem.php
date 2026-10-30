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
        'notes',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProjectBalanceRequest::class, 'project_balance_request_id');
    }
}

