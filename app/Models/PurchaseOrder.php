<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number','procurement_request_id','supplier_id','created_by','approved_by',
        'status','order_date','expected_delivery','total_amount','paid_amount',
        'terms','notes','approved_at',
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
            $m->po_number = 'PO-' . date('Y') . '-' . str_pad(
                (static::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT
            );
        });
    }

    public function supplier()           { return $this->belongsTo(Supplier::class); }
    public function creator()            { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()           { return $this->belongsTo(User::class, 'approved_by'); }
    public function procurementRequest() { return $this->belongsTo(ProcurementRequest::class); }
    public function items()              { return $this->hasMany(PurchaseOrderItem::class); }
    public function payments()           { return $this->hasMany(Payment::class); }

    public function getRemainingAmountAttribute(): float
    {
        return (float)$this->total_amount - (float)$this->paid_amount;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'Draft',
            'sent'             => 'Dikirim ke Supplier',
            'partial_received' => 'Sebagian Diterima',
            'received'         => 'Diterima',
            'cancelled'        => 'Dibatalkan',
            default            => $this->status,
        };
    }
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'gray',
            'sent'             => 'primary',
            'partial_received' => 'warning',
            'received'         => 'success',
            'cancelled'        => 'danger',
            default            => 'gray',
        };
    }
}
