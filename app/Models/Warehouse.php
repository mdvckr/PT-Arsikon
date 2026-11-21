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
        // Catatan: is_active TIDAK di-cast di sini karena ada accessor getIsActiveAttribute()
        // yang menghitung nilai dinamis berdasarkan status proyek & is_central.
        // Cast di $casts + accessor sekaligus menyebabkan perilaku tak terduga.
    ];

    /**
     * Scope to filter warehouses based on user role.
     * Owner/Admin: all active warehouses.
     * Admin Logistik Pusat: only central warehouses.
     * Admin Logistik Proyek: only assigned project warehouses.
     * Admin Pengadaan: only central warehouses.
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

        if ($user->hasRole('Admin Pusat')) {
            return $query->where('is_active', true);
        }

        if ($user->hasRole('Admin Logistik Pusat')) {
            return $query->where('is_active', true)->where('is_central', true);
        }

        if ($user->hasRole('Admin Logistik Proyek')) {
            return $query->whereIn('id', $user->warehouses->pluck('id'))->where('is_active', true);
        }

        if ($user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan'])) {
            return $query->where('is_active', true)->where('is_central', true);
        }

        // Site Engineer, User, dll: only assigned warehouses
        return $query->whereIn('id', $user->warehouses->pluck('id'))->where('is_active', true);
    }

    /**
     * Get warehouse type label for display.
     */
    public function getTypeLabel(): string
    {
        return $this->isCentral() ? 'Pusat' : 'Proyek';
    }

    /**
     * Get warehouse badge class.
     */
    public function getBadgeClass(): string
    {
        return $this->isCentral() ? 'badge-primary' : 'badge-info';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get display name formatted as 'Nama Gudang - Nama Proyek' if connected to a project.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->project && !empty($this->project->name)) {
            return "{$this->name} - {$this->project->name}";
        }
        return $this->name;
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

        if ($this->isCentral()) {
            return true;
        }

        if ($this->project) {
            return $this->project->status === 'active';
        }

        return true;
    }

    /**
     * Status administratif murni dari kolom database.
     * Berbeda dari accessor is_active yang juga mengikuti status proyek.
     */
    public function isAdministrativelyActive(): bool
    {
        return (bool) ($this->getRawOriginal('is_active') ?? true);
    }

    public function isCentral(): bool
    {
        return (bool) $this->is_central
            || in_array($this->type, ['central', 'main', 'pusat'], true);
    }

    public function isProject(): bool
    {
        return !$this->isCentral();
    }

    public function scopeCentral(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_central', true)
                ->orWhereIn('type', ['central', 'main', 'pusat']);
        });
    }

    public function scopeProject(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_central', false)->orWhereNull('is_central');
        })->where(function (Builder $q) {
            $q->whereNotIn('type', ['central', 'main', 'pusat'])
                ->orWhereNull('type');
        });
    }

    /**
     * Dynamic label for warehouse operational status.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isCentral()) {
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

    /**
     * Label fase proyek untuk gudang proyek.
     */
    public function getProjectPhaseLabelAttribute(): ?string
    {
        if ($this->isCentral() || ! $this->project) {
            return null;
        }

        return match ($this->project->status) {
            'active'    => 'Proyek berjalan',
            'completed' => 'Proyek selesai',
            'on_hold'   => 'Proyek ditangguhkan',
            'planning'  => 'Proyek perencanaan',
            default     => 'Proyek '.str_replace('_', ' ', (string) $this->project->status),
        };
    }
}
