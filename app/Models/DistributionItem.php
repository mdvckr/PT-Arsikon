<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'material_id',
        'tool_id',
        'tool_assignment_id',
        'custom_item_name',
        'custom_item_unit',
        'qty_shipped',
        'qty_received',
        'qty_damaged_or_lost',
        'notes',
    ];

    protected $casts = [
        'qty_shipped'         => 'decimal:2',
        'qty_received'        => 'decimal:2',
        'qty_damaged_or_lost' => 'decimal:2',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function toolAssignment(): BelongsTo
    {
        return $this->belongsTo(ToolAssignment::class);
    }

    public function isTool(): bool
    {
        return $this->tool_id !== null;
    }

    /**
     * Indicates whether this is a custom (non-inventory) item —
     * i.e. neither a system material nor a registered tool.
     */
    public function isCustom(): bool
    {
        return $this->material_id === null && $this->tool_id === null;
    }

    public function name(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_name ?? '(Item Custom)';
        }
        if ($this->isTool()) {
            return $this->tool?->name ?? 'Alat';
        }
        return $this->material?->name ?? 'Material';
    }

    public function detail(): string
    {
        if ($this->isCustom()) {
            return '-';
        }
        if ($this->isTool()) {
            return $this->tool?->code ?? '';
        }
        return $this->material?->code ?? '';
    }

    public function unitAbbr(): string
    {
        if ($this->isCustom()) {
            return $this->custom_item_unit ?? 'unit';
        }
        if ($this->isTool()) {
            return 'unit';
        }
        return $this->material?->unit?->abbreviation ?? '';
    }
}