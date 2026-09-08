<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number','from_warehouse_id','to_warehouse_id','requested_by','approved_by',
        'status','reason','return_date','notes','rejection_reason',
        'approved_at','received_at','received_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->return_number = 'RTN-' . date('Y') . '-' . str_pad(
                (static::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT
            );
        });
    }

    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse()   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function requester()     { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()      { return $this->belongsTo(User::class, 'approved_by'); }
    public function receiver()      { return $this->belongsTo(User::class, 'received_by'); }
    public function items()         { return $this->hasMany(MaterialReturnItem::class, 'return_id'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'pending'  => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'received' => 'Diterima',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'gray',
            'pending'  => 'warning',
            'approved' => 'primary',
            'received' => 'success',
            'rejected' => 'danger',
            default    => 'gray',
        };
    }
    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'excess'           => 'Kelebihan Stok',
            'damaged'          => 'Barang Rusak',
            'wrong_item'       => 'Barang Salah',
            'project_complete' => 'Proyek Selesai',
            'other'            => 'Lainnya',
            default            => $this->reason,
        };
    }
}
