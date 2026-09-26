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
        'supplier_name',
        'warehouse_id',
        'received_by_user_id',
        'received_by_name',
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

    /**
     * Memeriksa apakah user berhak mengonfirmasi penerimaan barang (Goods Receipt) ini.
     * Hanya petugas di gudang tujuan (Pusat atau Proyek), Admin Pusat (bila tujuan pusat), atau Owner/Super Admin.
     */
    public function canUserConfirm(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($this->status !== 'draft') {
            return false;
        }

        if (!$user->can('confirm goods receipts')) {
            return false;
        }

        // Owner & Super Admin ('Admin') always have full authority/override
        if ($user->hasAnyRole(['Owner', 'Admin'])) {
            return true;
        }

        // Jika gudang tujuan adalah Gudang Pusat, Admin Gudang Pusat berhak
        if ($this->warehouse?->is_central && $user->hasRole('Admin Gudang Pusat')) {
            return true;
        }

        // Untuk Gudang Proyek, user WAJIB ditugaskan di gudang tujuan ini
        return $user->warehouses()->where('warehouses.id', $this->warehouse_id)->exists();
    }
}
