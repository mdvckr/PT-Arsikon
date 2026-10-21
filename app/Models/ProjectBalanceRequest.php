<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBalanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requested_by',
        'amount',
        'purpose',
        'urgency',
        'status',
        'approved_by',
        'approved_at',
        'reference_number',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'approved' => 'Disetujui & Masuk Saldo',
            'rejected' => 'Ditolak Pusat/Owner',
            default    => 'Menunggu Persetujuan Pusat/Owner',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'warning',
        };
    }

    public function getUrgencyLabelAttribute(): string
    {
        return match($this->urgency) {
            'urgent' => 'Mendesak (Segera)',
            default  => 'Normal',
        };
    }

    public function getUrgencyBadgeColorAttribute(): string
    {
        return match($this->urgency) {
            'urgent' => 'danger',
            default  => 'info',
        };
    }
}
