<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses')
                    ->withPivot('role_in_warehouse')
                    ->withTimestamps();
    }

    public function activeWarehouse(): ?Warehouse
    {
        $activeId = session('active_warehouse_id');
        if ($activeId) {
            $warehouse = Warehouse::find($activeId);
            if ($warehouse && $this->hasAccessToWarehouse($warehouse)) {
                return $warehouse;
            }
        }

        // Default fallback for Owner/Admin vs User
        if ($this->hasRole(['Owner', 'Admin'])) {
            return Warehouse::where('is_central', true)->first() ?? $this->warehouses()->first();
        }

        return $this->warehouses()->first();
    }

    public function hasAccessToWarehouse(Warehouse $warehouse): bool
    {
        if ($this->hasRole('Owner')) {
            return true;
        }

        if ($this->hasRole('Admin') && $warehouse->is_central) {
            return true;
        }

        return $this->warehouses()->where('warehouses.id', $warehouse->id)->exists();
    }
}
