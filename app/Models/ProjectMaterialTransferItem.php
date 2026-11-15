<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMaterialTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_material_transfer_id',
        'transfer_date',
        'item_name',
        'unit',
        'quantity',
        'notes',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'quantity'      => 'decimal:2',
        'sort_order'    => 'integer',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(ProjectMaterialTransfer::class, 'project_material_transfer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
