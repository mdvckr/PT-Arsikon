<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'material_id',
        'qty_shipped',
        'qty_received',
        'qty_damaged_or_lost',
        'notes',
    ];

    protected $casts = [
        'qty_shipped' => 'decimal:2',
        'qty_received' => 'decimal:2',
        'qty_damaged_or_lost' => 'decimal:2',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
