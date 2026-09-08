<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number','purchase_order_id','created_by','verified_by','status',
        'amount','payment_method','payment_date','bank_account','reference_number',
        'proof_file','notes','rejection_reason','verified_at',
    ];

    protected $casts = ['payment_date' => 'date', 'verified_at' => 'datetime', 'amount' => 'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->payment_number = 'PAY-' . date('Y') . '-' . str_pad(
                (static::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT
            );
        });
        static::created(function ($m) {
            $po = $m->purchaseOrder;
            if ($po) {
                $totalPaid = $po->payments()->where('status', 'verified')->sum('amount');
                $po->update(['paid_amount' => $totalPaid]);
            }
        });
        static::updated(function ($m) {
            $po = $m->purchaseOrder;
            if ($po) {
                $totalPaid = $po->payments()->where('status', 'verified')->sum('amount');
                $po->update(['paid_amount' => $totalPaid]);
            }
        });
    }

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function creator()       { return $this->belongsTo(User::class, 'created_by'); }
    public function verifier()      { return $this->belongsTo(User::class, 'verified_by'); }
    public function items()         { return $this->hasMany(PaymentItem::class); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'pending'  => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'gray',
            'pending'  => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            default    => 'gray',
        };
    }
}
