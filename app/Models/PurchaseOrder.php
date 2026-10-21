<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'material_request_id',
        'procurement_request_id',
        'supplier_id',
        'supplier_name',
        'contact_person',
        'delivery_warehouse_id',
        'risk_bearer',
        'created_by',
        'approved_by',
        'status',
        'order_date',
        'expected_delivery',
        'total_amount',
        'paid_amount',
        'terms',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date', 'expected_delivery' => 'date',
        'total_amount' => 'decimal:2', 'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->po_number)) {
                $year = $m->order_date ? (int) date('Y', strtotime($m->order_date)) : (int) date('Y');
                $m->po_number = \App\Services\DocumentNumberService::generate('PO', $year);
            }
        });
    }

    public function supplier()           { return $this->belongsTo(Supplier::class); }
    public function creator()            { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()           { return $this->belongsTo(User::class, 'approved_by'); }
    public function deliveryWarehouse()  { return $this->belongsTo(Warehouse::class, 'delivery_warehouse_id'); }
    public function materialRequest()    { return $this->belongsTo(MaterialRequest::class); }
    public function procurementRequest() { return $this->belongsTo(ProcurementRequest::class); }
    public function items()              { return $this->hasMany(PurchaseOrderItem::class); }
    public function payments()           { return $this->hasMany(Payment::class); }
    public function receipts()           { return $this->hasMany(PurchaseReceipt::class); }
    public function goodsReceipts()      { return $this->hasMany(GoodsReceipt::class); }

    public function getRemainingAmountAttribute(): float
    {
        return (float)$this->total_amount - (float)$this->paid_amount;
    }

    public function getProjectAttribute(): ?Project
    {
        if ($this->relationLoaded('receipts')) {
            $receiptWithProject = $this->receipts->first(fn($r) => !empty($r->project_id));
            if ($receiptWithProject && $receiptWithProject->project) {
                return $receiptWithProject->project;
            }
        } else {
            $firstReceipt = $this->receipts()->whereNotNull('project_id')->with('project')->first();
            if ($firstReceipt && $firstReceipt->project) {
                return $firstReceipt->project;
            }
        }

        if ($this->deliveryWarehouse && $this->deliveryWarehouse->project) {
            return $this->deliveryWarehouse->project;
        }

        if ($this->materialRequest && $this->materialRequest->toWarehouse && $this->materialRequest->toWarehouse->project) {
            return $this->materialRequest->toWarehouse->project;
        }

        return null;
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->project?->id;
    }

    public function hasConfirmedGoodsReceipt(): bool
    {
        return $this->goodsReceipts()->where('status', 'confirmed')->exists();
    }

    public function hasPayableProof(): bool
    {
        return $this->hasConfirmedGoodsReceipt() || $this->receipts()->exists();
    }

    public function getNilaiSahAttribute(): float
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        $riskBearer = $this->risk_bearer ?? 'vendor';

        $total = 0.0;
        foreach ($items as $item) {
            $receivedQty = (float) ($item->received_qty ?? 0);
            $lostQty = ($riskBearer === 'buyer') ? (float) ($item->lost_qty ?? 0) : 0.0;
            $unitPrice = (float) ($item->unit_price ?? 0);
            $total += ($receivedQty + $lostQty) * $unitPrice;
        }

        return round($total, 2);
    }

    public function getCommittedPaymentAmountAttribute(): float
    {
        return (float) $this->payments()
            ->whereIn('status', ['pending', 'verified'])
            ->sum('amount');
    }

    public function getPendingPaymentAmountAttribute(): float
    {
        return (float) $this->payments()
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function getVerifiedPaymentAmountAttribute(): float
    {
        return (float) $this->payments()
            ->where('status', 'verified')
            ->sum('amount');
    }

    public function getPayableRemainingAmountAttribute(): float
    {
        $validAmount = $this->nilai_sah;

        // Jika nilai_sah dari GR formal 0, namun memiliki bukti nota pembelian fisik (bon belanja lapangan)
        if ($validAmount <= 0 && $this->receipts()->exists()) {
            $validAmount = (float) $this->receipts()->sum('total_amount');
            if ((float) $this->total_amount > 0) {
                $validAmount = min($validAmount, (float) $this->total_amount);
            }
        }

        if ($validAmount <= 0) {
            return 0.0;
        }

        return max(0.0, round($validAmount - $this->committed_payment_amount, 2));
    }

    public function getWithheldSummaryAttribute(): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with(['goodsReceiptItems.goodsReceipt', 'material', 'tool'])->get();

        $details = [];
        $totalWithheld = 0.0;
        $riskBearer = $this->risk_bearer ?? 'vendor';

        foreach ($items as $item) {
            $unitPrice = (float) $item->unit_price;

            $damagedQty = 0.0;
            $unresolvedMissingQty = 0.0;

            if ($item->relationLoaded('goodsReceiptItems')) {
                $grItems = $item->goodsReceiptItems;
            } else {
                $grItems = $item->goodsReceiptItems()->with('goodsReceipt')->get();
            }

            foreach ($grItems as $grItem) {
                if ($grItem->goodsReceipt && $grItem->goodsReceipt->status === 'confirmed') {
                    $damagedQty += (float) ($grItem->qty_damaged ?? 0);
                    if ($grItem->missing_status === 'unresolved') {
                        $unresolvedMissingQty += (float) ($grItem->qty_missing ?? 0);
                    }
                }
            }

            if ($damagedQty > 0) {
                $val = round($damagedQty * $unitPrice, 2);
                $totalWithheld += $val;
                $details[] = [
                    'item_name' => $item->displayName(),
                    'qty'       => $damagedQty,
                    'unit'      => $item->displayUnit(),
                    'type'      => 'damaged',
                    'value'     => $val,
                    'reason'    => 'Barang rusak saat diterima, menunggu penggantian/resolusi vendor',
                ];
            }

            if ($unresolvedMissingQty > 0) {
                $val = round($unresolvedMissingQty * $unitPrice, 2);
                $totalWithheld += $val;
                $details[] = [
                    'item_name' => $item->displayName(),
                    'qty'       => $unresolvedMissingQty,
                    'unit'      => $item->displayUnit(),
                    'type'      => 'unresolved_missing',
                    'value'     => $val,
                    'reason'    => 'Barang selisih/hilang dalam perjalanan (status belum diselesaikan)',
                ];
            }

            if ($riskBearer === 'vendor' && (float) $item->lost_qty > 0) {
                $val = round((float) $item->lost_qty * $unitPrice, 2);
                $totalWithheld += $val;
                $details[] = [
                    'item_name' => $item->displayName(),
                    'qty'       => (float) $item->lost_qty,
                    'unit'      => $item->displayUnit(),
                    'type'      => 'vendor_lost',
                    'value'     => $val,
                    'reason'    => 'Barang hilang tanggungan vendor, tidak ditagihkan ke pembeli',
                ];
            }

            $unreceivedQty = max(0.0, (float) $item->quantity - (float) $item->received_qty - (float) $item->lost_qty - (float) $item->short_closed_qty);
            if ($unreceivedQty > 0) {
                $val = round($unreceivedQty * $unitPrice, 2);
                $totalWithheld += $val;
                $details[] = [
                    'item_name' => $item->displayName(),
                    'qty'       => $unreceivedQty,
                    'unit'      => $item->displayUnit(),
                    'type'      => 'unreceived',
                    'value'     => $val,
                    'reason'    => 'Barang belum tiba / belum ada konfirmasi penerimaan (GR)',
                ];
            }
        }

        return [
            'total_withheld' => round($totalWithheld, 2),
            'items'          => $details,
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'Draft',
            'approved'         => 'Disetujui',
            'sent'             => 'Dikirim ke Supplier',
            'partial_received' => 'Sebagian Diterima',
            'received'         => 'Diterima',
            'cancelled'        => 'Dibatalkan',
            'closed'           => 'Ditutup (Short Close)',
            default            => $this->status,
        };
    }
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'gray',
            'approved'         => 'info',
            'sent'             => 'primary',
            'partial_received' => 'warning',
            'received'         => 'success',
            'cancelled'        => 'danger',
            'closed'           => 'secondary',
            default            => 'gray',
        };
    }

    public function getDisplaySupplierNameAttribute(): string
    {
        return $this->supplier?->name ?? $this->supplier_name ?? '-';
    }

    public function isManualSupplier(): bool
    {
        return empty($this->supplier_id) && !empty($this->supplier_name);
    }

    public function getRiskBearerLabelAttribute(): string
    {
        return match($this->risk_bearer) {
            'buyer'  => 'Pembeli (PT Arsikon)',
            default  => 'Vendor / Supplier',
        };
    }

    public function getOutstandingAttribute(): float
    {
        return (float) $this->items->sum(fn($item) => $item->outstanding);
    }
}
