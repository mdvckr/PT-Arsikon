<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToolAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_loan_id',
        'assignment_number',
        'tool_id',
        'quantity',
        'from_warehouse_id',
        'to_warehouse_id',
        'assigned_to_user_id',
        'borrower_name',
        'borrower_phone',
        'location_name',
        'assigned_by_user_id',
        'assigned_at',
        'expected_return_at',
        'returned_at',
        'status',
        'notes',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
    ];

    protected $casts = [
        'assigned_at'        => 'datetime',
        'expected_return_at' => 'date',
        'returned_at'        => 'datetime',
        'approved_at'        => 'datetime',
        'cancelled_at'       => 'datetime',
    ];

    public function toolLoan(): BelongsTo
    {
        return $this->belongsTo(ToolLoan::class, 'tool_loan_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getBorrowerDisplayAttribute(): string
    {
        if (!empty($this->borrower_name)) {
            return $this->borrower_name;
        }

        if ($this->assignedTo) {
            return $this->assignedTo->name;
        }

        if (!empty($this->notes)) {
            $parts = explode('|', $this->notes);
            $first = trim($parts[0]);
            if (str_starts_with($first, 'Peminjam:')) {
                return trim(substr($first, 9));
            }
        }

        return '-';
    }

    public function getLocationDisplayAttribute(): string
    {
        if (!empty($this->location_name)) {
            return $this->location_name;
        }

        if ($this->toWarehouse) {
            return $this->toWarehouse->name;
        }

        if ($this->fromWarehouse) {
            return $this->fromWarehouse->name;
        }

        return '-';
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(ToolInspection::class);
    }

    public static function generateAssignmentNumber(): string
    {
        $prefix = 'ASN-' . date('Ymd') . '-';
        $latest = self::where('assignment_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->assignment_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
