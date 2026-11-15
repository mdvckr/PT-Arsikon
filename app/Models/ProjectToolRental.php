<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectToolRental extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_name',
        'tool_name',
        'vendor_name',
        'docket_number',
        'week_number',
        'year',
        'start_date',
        'end_date',
        'rental_duration',
        'duration_unit',
        'rate_per_unit',
        'deposit_amount',
        'total_rental_cost',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'rental_duration'   => 'decimal:2',
        'rate_per_unit'     => 'decimal:2',
        'deposit_amount'    => 'decimal:2',
        'total_rental_cost' => 'decimal:2',
        'week_number'       => 'integer',
        'year'              => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProjectToolRentalLog::class, 'project_tool_rental_id')->orderBy('usage_date')->orderBy('id');
    }

    /**
     * Hitung ulang durasi dan total biaya dari rincian log pemakaian harian
     */
    public function recalculateFromLogs(): void
    {
        if ($this->logs()->exists()) {
            $totalDuration = (float) $this->logs()->sum('usage_duration');
            $rate = (float) $this->rate_per_unit;
            $deposit = (float) $this->deposit_amount;
            $totalCost = ($totalDuration * $rate) + $deposit;

            $firstLog = $this->logs()->orderBy('usage_date')->first();
            $lastLog = $this->logs()->orderByDesc('usage_date')->first();

            $this->update([
                'rental_duration'   => $totalDuration,
                'total_rental_cost' => $totalCost,
                'start_date'        => $firstLog ? $firstLog->usage_date : $this->start_date,
                'end_date'          => $lastLog ? $lastLog->usage_date : $this->end_date,
            ]);
        } else {
            $deposit = (float) $this->deposit_amount;
            $this->update([
                'rental_duration'   => 0,
                'total_rental_cost' => $deposit,
            ]);
        }
    }
}
