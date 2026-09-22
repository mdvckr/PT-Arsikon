<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToolLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'from_warehouse_id',
        'assigned_by_user_id',
        'approved_by_user_id',
        'borrower_name',
        'borrower_phone',
        'location_name',
        'assigned_at',
        'expected_return_at',
        'returned_at',
        'status',
        'notes',
        'rejection_reason',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
    ];

    protected $casts = [
        'assigned_at'        => 'datetime',
        'expected_return_at' => 'date',
        'returned_at'        => 'datetime',
        'cancelled_at'       => 'datetime',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ToolAssignment::class, 'tool_loan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ToolAssignment::class, 'tool_loan_id');
    }

    public static function generateLoanNumber(): string
    {
        $prefix = 'TL-' . date('Ymd') . '-';
        $latest = self::where('loan_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->loan_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
