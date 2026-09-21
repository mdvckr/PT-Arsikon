<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Warehouse;

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
        'is_active',
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
        'is_active' => 'boolean',
    ];

    /**
     * Get warehouses accessible by this user, filtered by role.
     * Returns a Collection of Warehouse models.
     */
    public function accessibleWarehouses(): Collection
    {
        if ($this->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::where('is_active', true)->orderBy('name')->get();
        }

        return $this->warehouses()->where('is_active', true)->orderBy('name')->get();
    }

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
        if ($this->hasRole(['Owner', 'Admin', 'Admin Gudang Pusat'])) {
            return Warehouse::where('is_central', true)->first() ?? $this->warehouses()->first();
        }

        return $this->warehouses()->first();
    }

    public function hasAccessToWarehouse(Warehouse $warehouse): bool
    {
        if ($this->hasRole(['Owner', 'Admin', 'Admin Gudang Pusat'])) {
            return true;
        }

        return $this->warehouses()->where('warehouses.id', $warehouse->id)->exists();
    }

    /**
     * Mengembalikan array ID warehouse yang boleh diakses user.
     * Owner/Admin/Admin Gudang Pusat/Admin PO mendapat akses ke semua warehouse.
     * Role lain (Admin Gudang Proyek, Karyawan, dll) hanya ke warehouse yang ditugaskan.
     */
    public function accessibleWarehouseIds(): array
    {
        if ($this->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return Warehouse::pluck('id')->toArray();
        }

        return $this->warehouses()->pluck('warehouses.id')->toArray();
    }
}
