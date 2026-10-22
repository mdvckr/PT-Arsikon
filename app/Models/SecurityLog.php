<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'severity',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User yang terkait dengan insiden/aktivitas keamanan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk kejadian dengan tingkat keparahan tinggi/kritis.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope untuk event tertentu.
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
