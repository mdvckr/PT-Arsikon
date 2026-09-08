<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_assignment_id',
        'tool_id',
        'inspected_by_user_id',
        'condition',
        'action_taken',
        'notes',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function toolAssignment(): BelongsTo
    {
        return $this->belongsTo(ToolAssignment::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by_user_id');
    }
}
