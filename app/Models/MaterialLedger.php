<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_name',
        'title',
        'supplier_id',
        'supplier_name',
        'account_behalf',
        'default_unit',
        'default_unit_price',
        'week_number',
        'year',
        'start_date',
        'end_date',
        'total_deposit_vol',
        'total_delivered_vol',
        'remaining_deposit_vol',
        'total_used_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'default_unit_price'    => 'decimal:2',
        'total_deposit_vol'     => 'decimal:2',
        'total_delivered_vol'   => 'decimal:2',
        'remaining_deposit_vol' => 'decimal:2',
        'total_used_amount'     => 'decimal:2',
        'start_date'            => 'date',
        'end_date'              => 'date',
        'week_number'           => 'integer',
        'year'                  => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MaterialLedgerEntry::class)->orderBy('delivery_date')->orderBy('sort_order')->orderBy('id');
    }
}
