<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'code',
        'name',
        'type',
        'is_central',
        'is_active',
        'address',
    ];

    protected $casts = [
        'is_central' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * Scope to filter warehouses based on user role.
     * Owner/Admin: all active warehouses.
     * Admin Gudang Pusat: only central warehouses.
     * Admin Gudang Proyek: only assigned project warehouses.
     * Admin PO: only central warehouses.
     * Others: only warehouses assigned to the user.
     */
    public function scopeForUser(Builder $query, ?object $user = null): Builder
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->where('is_active', true);
        }

        if ($user->hasRole('Owner')) {
            return $query->where('is_active', true);
        }

        if ($user->hasRole('Admin')) {
            return $query->where('is_active', true); // backward compat
        }

        if ($user->hasRole('Admin Gudang Pusat')) {
            return $query->where('is_active', true)->where('is_central', true);
        }

        if ($user->hasRole('Admin Gudang Proyek')) {
            return $query->whereIn('id', $user->warehouses->pluck('id'))->where('is_active', true);
        }

        if ($user->hasRole('Admin PO')) {
            return $query->where('is_active', true)->where('is_central', true);
        }

        // Karyawan, User, dll: only assigned warehouses
        return $query->whereIn('id', $user->warehouses->pluck('id'))->where('is_active', true);
    }

    /**
     * Get warehouse type label for display.
     */
    public function getTypeLabel(): string
    {
        return $this->is_central ? 'Pusat' : 'Proyek';
    }

    /**
     * Get warehouse badge class.
     */
    public function getBadgeClass(): string
    {
        return $this->is_central ? 'badge-primary' : 'badge-info';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_warehouses')
                    ->withPivot('role_in_warehouse')
                    ->withTimestamps();
    }

    public function inventories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function userWarehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserWarehouse::class);
    }

    /**
     * Determine if warehouse is active automatically based on central status or project status.
     */
    public function getIsActiveAttribute(): bool
    {
        // If explicitly set to inactive in database
        if (isset($this->attributes['is_active']) && !$this->attributes['is_active']) {
            return false;
        }

        if ($this->is_central || $this->type === 'central') {
            return true;
        }

        if ($this->project) {
            return $this->project->status === 'active';
        }

        return true;
    }

    public function isCentral(): bool
    {
        return (bool) $this->is_central || $this->type === 'central';
    }

    public function isProject(): bool
    {
        return !$this->isCentral();
    }

    public function scopeCentral(Builder $query): Builder
    {
        return $query->where('is_central', true);
    }

    public function scopeProject(Builder $query): Builder
    {
        return $query->where('is_central', false);
    }

    /**
     * Dynamic label for warehouse operational status.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_central || $this->type === 'central') {
            return 'Aktif (Pusat)';
        }

        if ($this->project) {
            return match ($this->project->status) {
                'active'    => 'Aktif (Proyek)',
                'completed' => 'Selesai (Proyek)',
                'on_hold'   => 'Ditunda (On Hold)',
                default     => ucfirst($this->project->status ?? 'Aktif'),
            };
        }

        return 'Aktif';
    }
}
