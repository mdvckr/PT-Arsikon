<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'received_by_user_id',
        'created_by',
        'status',
        'invoice_number',
        'receipt_date',
        'notes',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'receipt_date'  => 'date',
        'confirmed_at'  => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    /** User yang membuat dokumen GR (via form). */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** User yang mengkonfirmasi GR (stok diupdate). */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'GR-' . date('Ymd') . '-';
        $latest = self::where('receipt_number', 'like', $prefix . '%')
                      ->orderBy('id', 'desc')
                      ->first();

        $lastNumber = $latest ? (int) substr($latest->receipt_number, -4) : 0;

        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
