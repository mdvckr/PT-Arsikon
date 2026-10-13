<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'item_type',
        'material_id',
        'category_id',
        'tool_id',
        'custom_item_name',
        'custom_item_unit',
        'fulfillment',
        'qty_requested',
        'qty_approved',
        'qty_fulfilled',
        'qty_ordered',
        'qty_in_transit',
        'qty_received',
        'qty_issued',
        'notes',
        'specification',
        'usage_location',
        'usage_date',
    ];

    protected $casts = [
        'qty_requested'  => 'decimal:2',
        'qty_approved'   => 'decimal:2',
        'qty_fulfilled'  => 'decimal:2',
        'qty_ordered'    => 'decimal:2',
        'qty_in_transit' => 'decimal:2',
        'qty_received'   => 'decimal:2',
        'qty_issued'     => 'decimal:2',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'material_request_item_id');
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

    public function displayCategoryName(): string
    {
        if ($this->category) {
            return $this->category->name;
        }
        if ($this->material && $this->material->category) {
            return $this->material->category->name;
        }
        if ($this->tool && $this->tool->category) {
            return $this->tool->category->name;
        }
        return '-';
    }
}
