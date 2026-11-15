<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_ledger_id',
        'delivery_date',
        'supplier_name',
        'account_behalf',
        'delivery_note_number',
        'material_specification',
        'deposit_volume',
        'delivery_volume',
        'unit',
        'unit_price',
        'amount_used',
        'batch_color',
        'notes',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'delivery_date'   => 'date',
        'deposit_volume'  => 'decimal:2',
        'delivery_volume' => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'amount_used'     => 'decimal:2',
        'sort_order'      => 'integer',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(MaterialLedger::class, 'material_ledger_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
