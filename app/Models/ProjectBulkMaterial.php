<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProjectBulkMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_name',
        'title',
        'supplier_id',
        'supplier_name',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectBulkMaterialItem::class, 'project_bulk_material_id')
            ->orderBy('delivery_date')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Hitung total kuantitas per jenis material (misal: Pasir, Batu, dsb)
     */
    public function getSummaryByTypeAttribute(): Collection
    {
        return $this->items
            ->groupBy(function ($item) {
                return trim($item->material_type ?? 'Lainnya');
            })
            ->map(function ($group, $type) {
                return [
                    'material_type' => $type,
                    'total_qty'     => $group->sum('quantity'),
                    'unit'          => $group->first()->unit ?? 'Rit',
                    'count'         => $group->count(),
                ];
            });
    }

    /**
     * Total Rit / Kuantitas keseluruhan
     */
    public function getTotalRitAttribute(): float
    {
        return (float) $this->items->sum('quantity');
    }

    /**
     * Total Biaya Keseluruhan jika diinput harga
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }
}
