<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectToolRentalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_tool_rental_id',
        'usage_date',
        'usage_duration',
        'duration_unit',
        'activity_type',
        'equipment_spec',
        'notes',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'usage_date'     => 'date',
        'usage_duration' => 'decimal:2',
        'sort_order'     => 'integer',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(ProjectToolRental::class, 'project_tool_rental_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
