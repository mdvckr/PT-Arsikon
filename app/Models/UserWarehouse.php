<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWarehouse extends Model
{
    use HasFactory;

    protected $table = 'user_warehouses';

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'role_in_warehouse',
    ];

    /**
     * Get the user that owns this warehouse assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for this assignment.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
