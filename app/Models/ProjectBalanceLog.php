<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBalanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'payment_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'topup'             => 'Top-up Saldo',
            'payment_deduction' => 'Pembayaran Instan (Nota Harian)',
            'adjustment'        => 'Penyesuaian Saldo',
            default             => ucfirst($this->type),
        };
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'topup'             => 'success',
            'payment_deduction' => 'primary',
            'adjustment'        => 'warning',
            default             => 'secondary',
        };
    }
}
