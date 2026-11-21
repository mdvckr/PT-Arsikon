<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'description',
        'status',
        'operational_balance',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'operational_balance' => 'decimal:2',
    ];

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class)->where('is_central', false);
    }

    public function balanceLogs(): HasMany
    {
        return $this->hasMany(ProjectBalanceLog::class)->latest('id');
    }

    public function purchaseReceipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function materialLedgers(): HasMany
    {
        return $this->hasMany(MaterialLedger::class);
    }

    public function toolRentals(): HasMany
    {
        return $this->hasMany(ProjectToolRental::class);
    }
}
