<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'material_id',
        'custom_item_name',
        'custom_item_unit',
        'qty_requested',
        'qty_approved',
        'qty_fulfilled',
        'notes',
    ];

    protected $casts = [
        'qty_requested' => 'decimal:2',
        'qty_approved' => 'decimal:2',
        'qty_fulfilled' => 'decimal:2',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function isCustom(): bool
    {
        return $this->material_id === null;
    }

    public function displayName(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_name ?? '(Item Custom)';
        }
        return $this->material?->name ?? '-';
    }

    public function displayUnit(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_unit ?? 'unit';
        }
        return $this->material?->unit?->abbreviation ?? 'unit';
    }
}
