<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = ['payment_id','description','amount','termin','notes'];
    protected $casts = ['amount' => 'decimal:2'];

    public function payment() { return $this->belongsTo(Payment::class); }
}
