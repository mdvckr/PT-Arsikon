<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUsageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_usage_id',
        'material_id',
        'custom_item_name',
        'custom_item_unit',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function materialUsage(): BelongsTo
    {
        return $this->belongsTo(MaterialUsage::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Indicates whether this is a custom (non-inventory) item.
     */
    public function isCustom(): bool
    {
        return $this->material_id === null;
    }

    /**
     * Get the display name: custom name or material name.
     */
    public function displayName(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_name ?? '(Item Custom)';
        }
        return $this->material?->name ?? '-';
    }

    /**
     * Get the display unit: custom unit or material unit abbreviation.
     */
    public function displayUnit(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_unit ?? 'unit';
        }
        return $this->material?->unit?->abbreviation ?? 'unit';
    }
}
