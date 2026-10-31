<?php

namespace App\Models;

use App\Traits\SecureFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory, SecureFilterable;

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
        'supplier',
        'new_category',
        'new_unit',
    ];

    protected $casts = [
        'min_stock_central' => 'decimal:2',
        'is_active' => 'boolean',
        'incoming_stages' => 'array',
    ];

    /**
     * Virtual mutator: form helper for new category on-the-fly.
     * Prevents persisting nonexistent column to materials table.
     */
    public function setNewCategoryAttribute($value): void
    {
        // Handled via controller relationship resolution
    }

    /**
     * Virtual mutator: form helper for new unit on-the-fly.
     * Prevents persisting nonexistent column to materials table.
     */
    public function setNewUnitAttribute($value): void
    {
        // Handled via controller relationship resolution
    }

    /**
     * Virtual mutator: form helper for supplier text name.
     */
    public function setSupplierAttribute($value): void
    {
        if (!empty($value) && empty($this->attributes['supplier_name'])) {
            $this->attributes['supplier_name'] = trim($value);
        }
    }

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

    public function getDisplaySupplierNameAttribute(): string
    {
        if (!empty($this->supplier?->name)) {
            return $this->supplier->name;
        }

        if (!empty($this->supplier_name)) {
            return $this->supplier_name;
        }

        $grItem = GoodsReceiptItem::where('material_id', $this->id)
            ->whereHas('goodsReceipt')
            ->latest()
            ->first();

        if ($grItem && $grItem->goodsReceipt) {
            $grSup = $grItem->goodsReceipt->supplier?->name ?: $grItem->goodsReceipt->supplier_name;
            if (!empty($grSup)) {
                return $grSup;
            }
        }

        $poItem = PurchaseOrderItem::where('material_id', $this->id)
            ->whereHas('purchaseOrder')
            ->latest()
            ->first();

        if ($poItem && $poItem->purchaseOrder) {
            $poSup = $poItem->purchaseOrder->supplier?->name ?: $poItem->purchaseOrder->supplier_name;
            if (!empty($poSup)) {
                return $poSup;
            }
        }

        return '-';
    }

    public function setSkuAttribute($value): void
    {
        $this->attributes['sku'] = !empty($value) ? strtoupper(trim($value)) : $value;
    }

    /**
     * Mendapatkan rincian tahapan masuk aktif (PO Lapangan & PO Pengadaan yang sudah disetujui)
     * untuk gudang yang ditentukan.
     *
     * @param int|array|null $warehouseIds
     * @return \Illuminate\Support\Collection
     */
    public function getDynamicIncomingStages($warehouseIds = null)
    {
        $whIds = is_array($warehouseIds) ? $warehouseIds : ($warehouseIds ? [$warehouseIds] : null);

        $stages = collect();

        // 1. Dari Purchase Order (PO Vendor / Pengadaan)
        $poItemsQuery = PurchaseOrderItem::where(function ($q) {
                $q->where('material_id', $this->id);
                if (!empty($this->name)) {
                    $q->orWhereRaw('LOWER(TRIM(custom_item_name)) = ?', [strtolower(trim($this->name))]);
                }
            })
            ->whereHas('purchaseOrder', function ($q) use ($whIds) {
                $q->whereIn('status', ['draft', 'approved', 'sent', 'partial_received', 'partially_received', 'received']);
                if (!empty($whIds)) {
                    $q->where(function ($sub) use ($whIds) {
                        $sub->whereIn('delivery_warehouse_id', $whIds)
                            ->orWhereNull('delivery_warehouse_id');
                    });
                }
            })
            ->with(['purchaseOrder.deliveryWarehouse', 'purchaseOrder.supplier', 'purchaseOrder.goodsReceipts.warehouse', 'purchaseOrder.materialRequest']);

        foreach ($poItemsQuery->get() as $poItem) {
            $po = $poItem->purchaseOrder;
            if (!$po) continue;

            $qtyPlanned = (float) $poItem->quantity;
            $qtyReceived = (float) $poItem->received_qty;
            $remaining = max(0, $qtyPlanned - $qtyReceived - (float)$poItem->lost_qty - (float)$poItem->short_closed_qty);

            $isFullyReceived = ($po->status === 'received') || ($remaining <= 0 && $qtyReceived > 0);
            $isPartial = in_array($po->status, ['partial_received', 'partially_received']) || ($qtyReceived > 0 && $remaining > 0);

            // Cari nomor penerimaan barang (Goods Receipt)
            $grs = $po->goodsReceipts->filter(function($gr) use ($whIds) {
                return empty($whIds) || in_array($gr->warehouse_id, $whIds);
            });
            $grNumber = $grs->pluck('receipt_number')->filter()->unique()->implode(', ');

            // Status label & badge
            if ($isFullyReceived) {
                $status = 'received';
                $statusLabel = 'Lengkap Masuk';
            } elseif ($isPartial) {
                $status = 'partial';
                $statusLabel = 'Masuk Sebagian (' . number_format($qtyReceived, 0, ',', '.') . '/' . number_format($qtyPlanned, 0, ',', '.') . ')';
            } elseif ($po->status === 'draft') {
                $status = 'planned';
                $statusLabel = 'Draft PO';
            } elseif ($po->status === 'sent') {
                $status = 'planned';
                $statusLabel = 'Dalam Pengiriman';
            } else {
                $status = 'planned';
                $statusLabel = 'Disetujui (Menunggu Kirim)';
            }

            // Reference display: cantumkan PO dan GR jika ada
            $refDisplay = $po->po_number;
            if ($grNumber) {
                $refDisplay .= ' · ' . $grNumber;
            } elseif ($po->materialRequest) {
                $refDisplay .= ' (MR: ' . $po->materialRequest->request_number . ')';
            }

            $stageDate = null;
            if ($isFullyReceived && $grs->isNotEmpty() && $grs->first()->receipt_date) {
                $stageDate = $grs->first()->receipt_date->format('Y-m-d');
            } elseif ($po->expected_delivery) {
                $stageDate = $po->expected_delivery->format('Y-m-d');
            } elseif ($po->order_date) {
                $stageDate = $po->order_date->format('Y-m-d');
            }

            $stages->push([
                'type'           => 'PO',
                'stage'          => $po->po_number,
                'reference'      => $refDisplay,
                'source'         => $po->supplier?->name ?: ($po->supplier_name ?: 'Vendor PO'),
                'warehouse_name' => $po->deliveryWarehouse?->name,
                'warehouse_id'   => $po->delivery_warehouse_id,
                'qty'            => $isFullyReceived ? $qtyReceived : ($remaining > 0 ? $remaining : $qtyPlanned),
                'planned_qty'    => $qtyPlanned,
                'received_qty'   => $qtyReceived,
                'remaining_qty'  => $remaining,
                'date'           => $stageDate,
                'status'         => $status,
                'status_label'   => $statusLabel,
                'notes'          => $po->notes ?: null,
            ]);
        }

        // 2. Dari Penerimaan Barang (GR) langsung tanpa PO
        $directGrItems = \App\Models\GoodsReceiptItem::where('material_id', $this->id)
            ->whereHas('goodsReceipt', function ($q) use ($whIds) {
                $q->where('status', 'confirmed')
                  ->whereNull('purchase_order_id');
                if (!empty($whIds)) {
                    $q->whereIn('warehouse_id', $whIds);
                }
            })
            ->with(['goodsReceipt.warehouse', 'goodsReceipt.materialRequest'])
            ->get();

        foreach ($directGrItems as $grItem) {
            $gr = $grItem->goodsReceipt;
            if (!$gr) continue;
            $qtyRec = (float) $grItem->qty_received;
            if ($qtyRec <= 0) continue;

            $refDisplay = $gr->receipt_number;
            if ($gr->materialRequest) {
                $refDisplay .= ' (MR: ' . $gr->materialRequest->request_number . ')';
            }

            $stages->push([
                'type'           => 'Penerimaan (GR)',
                'stage'          => $gr->receipt_number,
                'reference'      => $refDisplay,
                'source'         => $gr->supplier_name ?: 'Penerimaan Langsung Proyek',
                'warehouse_name' => $gr->warehouse?->name,
                'warehouse_id'   => $gr->warehouse_id,
                'qty'            => $qtyRec,
                'planned_qty'    => $qtyRec,
                'received_qty'   => $qtyRec,
                'remaining_qty'  => 0,
                'date'           => $gr->receipt_date ? $gr->receipt_date->format('Y-m-d') : null,
                'status'         => 'received',
                'status_label'   => 'Lengkap Masuk',
                'notes'          => $gr->notes ?: null,
            ]);
        }

        // 2. Dari PO Lapangan (Material Request yang sudah disetujui tapi belum/hanya sebagian dibuat PO)
        $mrItemsQuery = MaterialRequestItem::where(function ($q) {
                $q->where('material_id', $this->id);
                if (!empty($this->name)) {
                    $q->orWhereRaw('LOWER(TRIM(custom_item_name)) = ?', [strtolower(trim($this->name))]);
                }
            })
            ->whereHas('materialRequest', function ($q) use ($whIds) {
                $q->whereIn('status', ['approved', 'partially_fulfilled']);
                if (!empty($whIds)) {
                    $q->where(function ($sub) use ($whIds) {
                        $sub->whereIn('to_warehouse_id', $whIds)
                            ->orWhereNull('to_warehouse_id');
                    });
                }
            })
            ->with(['materialRequest.toWarehouse', 'materialRequest.requestedBy']);

        foreach ($mrItemsQuery->get() as $mrItem) {
            $mr = $mrItem->materialRequest;
            if (!$mr) continue;

            $qtyApproved = (float) ($mrItem->qty_approved > 0 ? $mrItem->qty_approved : $mrItem->qty_requested);
            $qtyReceived = (float) $mrItem->qty_received;
            $qtyOrdered = (float) $mrItem->qty_ordered;

            $unpoQty = max(0, $qtyApproved - max($qtyOrdered, $qtyReceived));

            if ($unpoQty > 0) {
                $stages->push([
                    'type'           => 'PO Lapangan',
                    'stage'          => $mr->request_number,
                    'reference'      => $mr->request_number,
                    'source'         => 'PO Lapangan (' . ($mr->requestedBy?->name ?: 'Proyek') . ')',
                    'warehouse_name' => $mr->toWarehouse?->name,
                    'warehouse_id'   => $mr->to_warehouse_id,
                    'qty'            => $unpoQty,
                    'planned_qty'    => $unpoQty,
                    'received_qty'   => 0,
                    'remaining_qty'  => $unpoQty,
                    'date'           => $mr->needed_at ? $mr->needed_at->format('Y-m-d') : null,
                    'status'         => 'planned',
                    'status_label'   => 'Menunggu Pengadaan',
                    'notes'          => $mr->notes ?: null,
                ]);
            }
        }

        return $stages;
    }

    /**
     * Auto-register custom/manual item to master materials table.
     */
    public static function autoRegisterCustom(
        string $name,
        ?string $unitName = 'unit',
        ?int $categoryId = null,
        ?string $typeName = null,
        ?string $brand = null,
        ?string $size = null
    ): Material {
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

        // Resolve category: prioritaskan pilihan user ($categoryId), atau cari kategori Khusus Proyek, jika belum ada buatkan CAT-CUST
        $category = null;
        if ($categoryId) {
            $category = Category::where('type', 'material')->find($categoryId);
        }

        if (!$category) {
            $category = Category::where('type', 'material')
                ->where(function ($q) {
                    $q->where('code', 'CAT-CUST')
                      ->orWhere('name', 'like', '%Khusus Proyek%')
                      ->orWhere('name', 'like', '%Custom%');
                })->first();
        }

        if (!$category) {
            $category = Category::firstOrCreate(
                ['code' => 'CAT-CUST'],
                [
                    'name' => 'Material Khusus Proyek',
                    'type' => 'material',
                    'description' => 'Kategori otomatis untuk barang custom/manual dari pengadaan proyek',
                ]
            );
        }

        // Resolve kelompok/type: prioritaskan $typeName, atau cari kelompok yang sudah ada di kategori ini, atau fallback kontekstual
        $resolvedType = trim($typeName ?? '');
        if (empty($resolvedType)) {
            // Cek apakah kategori ini sudah memiliki kelompok (type) yang ada di master
            $existingType = static::where('category_id', $category->id)
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->where('type', '!=', 'Material Proyek')
                ->value('type');

            $resolvedType = $existingType ?: ($category->code !== 'CAT-CUST' ? $category->name : 'Material Proyek');
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
            'brand'       => $brand ?: 'Custom / Proyek',
            'type'        => $resolvedType,
            'size'        => $size ?: null,
            'is_active'   => true,
            'description' => 'Didaftarkan otomatis oleh sistem dari Permintaan / Penerimaan Barang Proyek',
        ]);
    }
}
