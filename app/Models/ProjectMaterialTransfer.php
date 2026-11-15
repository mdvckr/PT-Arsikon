<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMaterialTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_name',
        'title',
        'origin_location',
        'destination_location',
        'transfer_date',
        'docket_number',
        'driver_or_transporter',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectMaterialTransferItem::class, 'project_material_transfer_id')->orderBy('transfer_date')->orderBy('id');
    }
}
