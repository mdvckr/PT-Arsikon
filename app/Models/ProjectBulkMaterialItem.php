<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBulkMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_bulk_material_id',
        'delivery_date',
        'ticket_number',
        'quantity',
        'unit',
        'material_type',
        'unit_price',
        'subtotal',
        'notes',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'sort_order'    => 'integer',
    ];

    public function bulkMaterial(): BelongsTo
    {
        return $this->belongsTo(ProjectBulkMaterial::class, 'project_bulk_material_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
