<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBalanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'request_date',
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
        'signer_requester',
        'signer_site_manager',
        'signer_approver',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'request_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = DocumentNumberService::generate('PKL');
            }
            if (empty($model->request_date)) {
                $model->request_date = now()->toDateString();
            }
        });
    }

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

    public function items(): HasMany
    {
        return $this->hasMany(ProjectBalanceRequestItem::class, 'project_balance_request_id');
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
