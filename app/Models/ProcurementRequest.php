<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number','requested_by','material_request_id','status',
        'needed_by','justification','rejection_reason','approved_by','approved_at',
    ];

    protected $casts = ['needed_by' => 'date', 'approved_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->pr_number = 'PR-' . date('Y') . '-' . str_pad(
                (static::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT
            );
        });
    }

    public function requester()  { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
    public function materialRequest() { return $this->belongsTo(MaterialRequest::class); }
    public function items()      { return $this->hasMany(ProcurementRequestItem::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'      => 'Draft',
            'submitted'  => 'Diajukan',
            'approved'   => 'Disetujui',
            'rejected'   => 'Ditolak',
            'po_created' => 'PO Dibuat',
            default      => $this->status,
        };
    }
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'      => 'gray',
            'submitted'  => 'warning',
            'approved'   => 'success',
            'rejected'   => 'danger',
            'po_created' => 'primary',
            default      => 'gray',
        };
    }
}
