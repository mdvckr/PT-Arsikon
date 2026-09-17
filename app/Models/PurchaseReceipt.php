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
        'total_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid'   => 'Sudah Dibayar',
            'unpaid' => 'Belum Dibayar',
            default  => 'Belum Dibayar',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid'   => 'success',
            'unpaid' => 'warning',
            default  => 'warning',
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
