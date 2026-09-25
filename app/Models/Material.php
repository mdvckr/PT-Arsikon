<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'supplier_name',
        'unit_id',
        'sku',
        'name',
        'brand',
        'size',
        'type',
        'min_stock_central',
        'is_active',
        'description',
        'incoming_stages',
    ];

    protected $casts = [
        'min_stock_central' => 'decimal:2',
        'is_active' => 'boolean',
        'incoming_stages' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function getCodeAttribute(): ?string
    {
        return $this->sku;
    }

    /**
     * Auto-register custom/manual item to master materials table.
     */
    public static function autoRegisterCustom(string $name, ?string $unitName = 'unit'): Material
    {
        $cleanName = trim($name);
        $material = static::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
        if ($material) {
            return $material;
        }

        // Resolve unit
        $cleanUnit = trim($unitName ?: 'unit');
        $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($cleanUnit)])
            ->orWhereRaw('LOWER(code) = ?', [strtolower($cleanUnit)])
            ->first();

        if (!$unit) {
            $unitCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $cleanUnit), 0, 4)) ?: 'UNT';
            $unit = Unit::firstOrCreate(
                ['code' => $unitCode],
                ['name' => ucfirst($cleanUnit), 'is_decimal' => true]
            );
        }

        // Resolve default category
        $category = Category::where('type', 'material')
            ->where(function ($q) {
                $q->where('name', 'like', '%Umum%')
                  ->orWhere('name', 'like', '%Khusus%')
                  ->orWhere('name', 'like', '%Proyek%');
            })->first();

        if (!$category) {
            $category = Category::where('type', 'material')->first();
        }

        if (!$category) {
            $category = Category::create([
                'code' => 'CAT-CUST',
                'name' => 'Material Khusus Proyek',
                'type' => 'material',
                'description' => 'Kategori otomatis untuk barang custom/manual dari pengadaan proyek',
            ]);
        }

        // Generate unique SKU
        $maxId = (int) (static::max('id') ?? 0) + 1;
        $sku = 'MAT-AUTO-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);
        while (static::where('sku', $sku)->exists()) {
            $maxId++;
            $sku = 'MAT-AUTO-' . str_pad((string)$maxId, 4, '0', STR_PAD_LEFT);
        }

        return static::create([
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'sku'         => $sku,
            'name'        => $cleanName,
            'brand'       => 'Custom / Proyek',
            'type'        => 'Material Proyek',
            'is_active'   => true,
            'description' => 'Didaftarkan otomatis oleh sistem dari Permintaan / Penerimaan Barang Proyek',
        ]);
    }
}
