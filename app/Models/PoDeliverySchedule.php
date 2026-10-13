<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoDeliverySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_item_id',
        'stage_label',
        'planned_date',
        'planned_qty',
        'received_qty',
        'warehouse_id',
        'status',
        'notes',
        'late_notified_at',
    ];

    protected $casts = [
        'planned_date'     => 'date',
        'planned_qty'      => 'decimal:2',
        'received_qty'     => 'decimal:2',
        'late_notified_at' => 'datetime',
    ];

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Hitung sisa kuantitas yang belum diterima.
     */
    public function getRemainingQtyAttribute(): float
    {
        return max(0, (float) $this->planned_qty - (float) $this->received_qty);
    }

    /**
     * Jadwal dianggap terlambat jika tanggal lewat dan masih ada sisa barang belum diterima.
     */
    public function getIsLateAttribute(): bool
    {
        if (in_array($this->status, ['received', 'cancelled'])) {
            return false;
        }

        if (!$this->planned_date) {
            return false;
        }

        return $this->planned_date->isPast() && $this->remaining_qty > 0;
    }

    /**
     * Scope untuk jadwal yang masih terbuka (belum tuntas diterima atau dibatalkan).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['planned', 'partial'])
            ->whereRaw('(planned_qty - received_qty) > 0');
    }

    /**
     * Scope untuk jadwal yang terlambat (planned_date sebelum hari ini dan masih bersisa).
     */
    public function scopeLate($query)
    {
        return $query->scopeOpen()
            ->whereNotNull('planned_date')
            ->whereDate('planned_date', '<', now()->toDateString());
    }

    /**
     * Scope untuk jadwal hari ini.
     */
    public function scopeToday($query)
    {
        return $query->scopeOpen()
            ->whereDate('planned_date', now()->toDateString());
    }
}
