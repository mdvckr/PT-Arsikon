<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_number',
        'tool_id',
        'reported_by_user_id',
        'maintenance_type',
        'cost',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public static function generateMaintenanceNumber(): string
    {
        $prefix = 'MNT-' . date('Ymd') . '-';
        $latest = self::where('maintenance_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->maintenance_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
