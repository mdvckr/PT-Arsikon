<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturnItem extends Model
{
    use HasFactory;

    protected $table = 'return_items';

    protected $fillable = ['return_id','material_id','quantity','received_qty','condition','notes'];
    protected $casts = ['quantity' => 'decimal:2', 'received_qty' => 'decimal:2'];

    public function materialReturn() { return $this->belongsTo(MaterialReturn::class, 'return_id'); }
    public function material()       { return $this->belongsTo(Material::class); }
}
