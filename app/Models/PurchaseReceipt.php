<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'project_id',
        'project_name',
        'supplier_id',
        'supplier_name',
        'receipt_number',
        'receipt_date',
        'day_label',
        'image_path',
        'items_subtotal',
        'global_discount',
        'shipping_fee',
        'additional_fee',
        'additional_fee_notes',
        'total_amount',
        'payment_status',
        'payment_source',
        'paid_by_name',
        'notes',
        'created_by',
    ];

    public function getIsTalanganAttribute(): bool
    {
        return $this->payment_source === 'personal_advance' || !empty($this->paid_by_name);
    }

    public function getPaymentSourceLabelAttribute(): string
    {
        if ($this->is_talangan) {
            return 'Talangan Pribadi' . ($this->paid_by_name ? " ({$this->paid_by_name})" : '');
        }
        return 'Kas Operasional Proyek';
    }

    protected $casts = [
        'receipt_date'         => 'date',
        'items_subtotal'       => 'decimal:2',
        'global_discount'      => 'decimal:2',
        'shipping_fee'         => 'decimal:2',
        'additional_fee'       => 'decimal:2',
        'total_amount'         => 'decimal:2',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid'      => 'Sudah Dibayar',
            'unpaid'    => 'Belum Dibayar',
            'cancelled' => 'Dibatalkan',
            default     => 'Belum Dibayar',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid'      => 'success',
            'unpaid'    => 'warning',
            'cancelled' => 'danger',
            default     => 'warning',
        };
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

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
        return $this->hasMany(PurchaseReceiptItem::class);
    }
}
