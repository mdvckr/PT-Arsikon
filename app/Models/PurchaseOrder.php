<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'procurement_request_id',
        'supplier_id',
        'supplier_name',
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
    public function procurementRequest() { return $this->belongsTo(ProcurementRequest::class); }
    public function items()              { return $this->hasMany(PurchaseOrderItem::class); }
    public function payments()           { return $this->hasMany(Payment::class); }
    public function receipts()           { return $this->hasMany(PurchaseReceipt::class); }
    public function goodsReceipts()      { return $this->hasMany(GoodsReceipt::class); }

    public function getRemainingAmountAttribute(): float
    {
        return (float)$this->total_amount - (float)$this->paid_amount;
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
}
