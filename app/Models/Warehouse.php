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
     * Owner/Admin/Admin Gudang Pusat/Admin PO: all active warehouses.
     * Others: only warehouses assigned to the user.
     */
    public function scopeForUser(Builder $query, ?object $user = null): Builder
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->where('is_active', true);
        }

        if ($user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO'])) {
            return $query->where('is_active', true);
        }

        // Admin Gudang Proyek, Karyawan, etc.: only assigned warehouses
        return $query->whereIn('id', $user->warehouses->pluck('id'));
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
