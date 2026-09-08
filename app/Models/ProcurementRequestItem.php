<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id','material_id','quantity','estimated_price','unit','notes',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'estimated_price' => 'decimal:2'];

    public function procurementRequest() { return $this->belongsTo(ProcurementRequest::class); }
    public function material() { return $this->belongsTo(Material::class); }
}
