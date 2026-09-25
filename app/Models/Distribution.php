<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_number',
        'material_request_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'delivery_date',
        'driver_name',
        'vehicle_number',
        'created_by_user_id',
        'shipped_by_user_id',
        'received_by_user_id',
        'shipped_at',
        'received_at',
        'status',
        'notes',
        'surat_jalan',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }

    public static function generateDistributionNumber(): string
    {
        $prefix = 'DST-' . date('Ymd') . '-';
        $latest = self::where('distribution_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->distribution_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Memeriksa apakah user berhak memproses pengiriman (Ship) Surat Jalan ini.
     * Hanya petugas gudang asal, Admin Pusat (bila asal pusat), atau Owner/Super Admin.
     */
    public function canUserShip(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($this->status !== 'draft') {
            return false;
        }

        if (!$user->can('ship distributions')) {
            return false;
        }

        // Owner & Super Admin ('Admin') always have full authority/override
        if ($user->hasAnyRole(['Owner', 'Admin'])) {
            return true;
        }

        // Jika gudang asal adalah Gudang Pusat, Admin Gudang Pusat berhak
        if ($this->fromWarehouse?->is_central && $user->hasRole('Admin Gudang Pusat')) {
            return true;
        }

        // Untuk Gudang Proyek, user WAJIB ditugaskan di gudang asal ini
        return $user->warehouses()->where('warehouses.id', $this->from_warehouse_id)->exists();
    }

    /**
     * Memeriksa apakah user berhak menyetujui / mengonfirmasi penerimaan (Receive) Surat Jalan ini.
     * Hanya petugas di GUDANG TUJUAN, Admin Pusat (bila tujuan pusat), atau Owner/Super Admin.
     */
    public function canUserReceive(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($this->status !== 'in_transit') {
            return false;
        }

        if (!$user->can('receive distributions')) {
            return false;
        }

        // Owner & Super Admin ('Admin') always have full authority/override
        if ($user->hasAnyRole(['Owner', 'Admin'])) {
            return true;
        }

        // Jika gudang tujuan adalah Gudang Pusat, Admin Gudang Pusat berhak
        if ($this->toWarehouse?->is_central && $user->hasRole('Admin Gudang Pusat')) {
            return true;
        }

        // Untuk Gudang Proyek, user WAJIB ditugaskan di gudang tujuan ini
        return $user->warehouses()->where('warehouses.id', $this->to_warehouse_id)->exists();
    }
}
