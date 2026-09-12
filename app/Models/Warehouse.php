<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
