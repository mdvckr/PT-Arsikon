<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier) {
            if (empty($supplier->code)) {
                $supplier->code = static::generateUniqueCode($supplier->name);
            }
        });
    }

    public static function generateUniqueCode(?string $name = null): string
    {
        $cleanName = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $name ?? ''));
        $prefix = 'SUP-' . (!empty($cleanName) ? substr($cleanName, 0, 6) : 'GEN');
        $code = $prefix;
        $i = 1;
        while (static::where('code', $code)->exists()) {
            $code = $prefix . '-' . $i++;
        }
        return $code;
    }

    public function goodsReceipts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
