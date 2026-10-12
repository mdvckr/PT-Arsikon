<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\Tool;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\NotificationHelper;
use Exception;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    protected ToolInventoryService $toolInventoryService;

    public function __construct(
        protected StockService $stockService,
        ?ToolInventoryService $toolInventoryService = null
    ) {
        $this->toolInventoryService = $toolInventoryService ?? app(ToolInventoryService::class);
    }

    /**
     * Helper untuk memproses Goods Receipt secara langsung (create + confirm).
     * Memvalidasi bahwa penerimaan barang dari supplier hanya boleh di Gudang Pusat.
     */
    public function processGoodsReceipt(
        Supplier $supplier,
        Warehouse $warehouse,
        User $user,
        array $items,
        ?string $receiptDate = null,
        ?string $notes = null
    ): GoodsReceipt {
        if (!$warehouse->is_central) {
            throw new Exception("Penerimaan barang dari supplier hanya boleh dilakukan di Gudang Pusat.");
        }

        $formattedItems = [];
        foreach ($items as $item) {
            $isTool = ($item['item_type'] ?? '') === 'tool' || !empty($item['tool_id']);
            $formattedItems[] = [
                'item_type'              => $isTool ? 'tool' : 'material',
                'material_id'            => $isTool ? null : ($item['material_id'] ?? null),
                'tool_id'                => $isTool ? ($item['tool_id'] ?? null) : null,
                'quantity'               => $item['qty_received'] ?? $item['quantity'] ?? 0,
                'condition'              => $item['condition'] ?? 'good',
                'unit_price'             => $item['unit_price'] ?? 0,
                'stage_reference'        => $item['stage_reference'] ?? null,
                'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
            ];
        }

        $receipt = $this->create([
            'supplier_id'         => $supplier->id,
            'warehouse_id'        => $warehouse->id,
            'received_at'         => $receiptDate ?? now()->toDateString(),
            'notes'               => $notes,
            'purchase_order_id'   => null,
            'invoice_number'      => null,
            'items'               => $formattedItems,
        ], $user->id);

        return $this->confirm($receipt, $user->id);
    }

    /**
     * Buat dokumen Goods Receipt baru (status = draft).
     * Stok BELUM diubah; hanya dicatat. Konfirmasi dilakukan terpisah.
     *
     * Berlaku untuk semua tipe gudang: pusat maupun proyek.
     */
    public function create(array $validated, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($validated, $userId) {
            $supplierName = !empty($validated['supplier_name'])
                ? preg_replace('/\s+/', ' ', trim($validated['supplier_name']))
                : null;

            $receipt = GoodsReceipt::create([
                'receipt_number'      => GoodsReceipt::generateReceiptNumber(),
                'purchase_order_id'   => $validated['purchase_order_id'] ?? null,
                'supplier_id'         => $validated['supplier_id'],
                'supplier_name'       => $supplierName,
                'warehouse_id'        => $validated['warehouse_id'],
                'received_by_user_id' => $userId,
                'received_by_name'    => $validated['received_by_name'] ?? null,
                'created_by'          => $userId,
                'status'              => 'draft',
                'invoice_number'      => $validated['invoice_number'] ?? null,
                'receipt_date'        => $validated['received_at'],
                'notes'               => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $isTool = ($item['item_type'] ?? '') === 'tool' || !empty($item['tool_id']);
                $poItemId = $item['purchase_order_item_id'] ?? null;
                $poItem = $poItemId ? PurchaseOrderItem::where('id', $poItemId)->lockForUpdate()->first() : null;

                $qtyReceived = (float) ($item['qty_received'] ?? $item['quantity'] ?? 0);
                $qtyDamaged  = (float) ($item['qty_damaged'] ?? 0);
                $qtyShipped  = (float) ($item['qty_shipped'] ?? ($qtyReceived + $qtyDamaged));

                if ($qtyReceived + $qtyDamaged > $qtyShipped) {
                    throw new Exception("Jumlah diterima ({$qtyReceived}) + rusak ({$qtyDamaged}) tidak boleh melebihi jumlah yang dikirim vendor ({$qtyShipped}).");
                }

                // Validasi batas outstanding PO
                if ($poItem) {
                    $outstanding = max(0, (float)$poItem->quantity - (float)$poItem->received_qty - (float)$poItem->lost_qty - (float)$poItem->short_closed_qty);
                    if (($qtyReceived + $qtyDamaged) > $outstanding) {
                        $itemName = $poItem->displayName();
                        throw new Exception("Penerimaan barang {$itemName} (" . ($qtyReceived + $qtyDamaged) . ") melebihi sisa outstanding PO ({$outstanding}).");
                    }
                }

                $qtyMissing = max(0, $qtyShipped - $qtyReceived - $qtyDamaged);
                $missingStatus = $qtyMissing > 0 ? 'unresolved' : 'none';

                // T9: GR tidak menerima unit_price dari request untuk item ber-PO. Selalu salin dari PO item.
                $unitPrice = $poItem ? (float) $poItem->unit_price : (float) ($item['unit_price'] ?? 0);

                GoodsReceiptItem::create([
                    'goods_receipt_id'        => $receipt->id,
                    'item_type'               => $isTool ? 'tool' : 'material',
                    'purchase_order_item_id'  => $poItemId,
                    'po_delivery_schedule_id' => $item['po_delivery_schedule_id'] ?? null,
                    'stage_reference'         => $item['stage_reference'] ?? null,
                    'material_id'             => $isTool ? null : ($item['material_id'] ?? null),
                    'tool_id'                 => $isTool ? ($item['tool_id'] ?? null) : null,
                    'qty_shipped'             => $qtyShipped,
                    'qty_received'            => $qtyReceived,
                    'qty_damaged'             => $qtyDamaged,
                    'qty_missing'             => $qtyMissing,
                    'missing_status'          => $missingStatus,
                    'condition'               => $item['condition'] ?? 'good',
                    'unit_price'              => $unitPrice,
                    'notes'                   => $item['notes'] ?? null,
                ]);
            }

            return $receipt->load(['supplier', 'warehouse', 'items.material', 'items.tool']);
        });
    }

    /**
     * Konfirmasi GR: validasi items, update stok gudang, update status PO jika ada,
     * serta perbarui status tahapan kedatangan (incoming_stages) menjadi 'received'.
     */
    public function confirm(GoodsReceipt $receipt, int $userId): GoodsReceipt
    {
        if ($receipt->status === 'confirmed') {
            throw new Exception('Penerimaan barang ini sudah dikonfirmasi sebelumnya.');
        }

        return DB::transaction(function () use ($receipt, $userId) {
            $receipt->load(['items.material', 'items.tool', 'warehouse', 'supplier']);

            $warehouse = $receipt->warehouse;

            if (!$warehouse) {
                throw new Exception('Gudang tujuan tidak ditemukan.');
            }

            $receiptDateStr = $receipt->receipt_date ? $receipt->receipt_date->format('Y-m-d') : now()->toDateString();
            $mrToRecalculate = collect();

            // Tambah stok di gudang tujuan untuk setiap item
            foreach ($receipt->items as $item) {
                $poItem = $item->purchase_order_item_id
                    ? PurchaseOrderItem::where('id', $item->purchase_order_item_id)->lockForUpdate()->first()
                    : null;

                // Validasi ulang kuota outstanding saat confirm
                if ($poItem) {
                    $outstanding = max(0, (float)$poItem->quantity - (float)$poItem->received_qty - (float)$poItem->lost_qty - (float)$poItem->short_closed_qty);
                    if (((float)$item->qty_received + (float)$item->qty_damaged) > $outstanding) {
                        $itemName = $poItem->displayName();
                        throw new Exception("Penerimaan barang {$itemName} (" . ((float)$item->qty_received + (float)$item->qty_damaged) . ") melebihi sisa outstanding PO ({$outstanding}).");
                    }
                }

                if ($item->isTool()) {
                    // Penanganan Alat / Mesin: stok bertambah HANYA dari qty_received baik
                    $tool = $item->tool;
                    if ($tool && (float) $item->qty_received > 0) {
                        $this->toolInventoryService->addStock(
                            $warehouse,
                            $tool,
                            (int) round($item->qty_received),
                            "Penerimaan Supplier #{$receipt->receipt_number} — {$receipt->supplier?->name}"
                        );
                    }

                    // T11 BUGFIX: cabang ALAT menaikkan received_qty PO item persis seperti cabang material
                    if ($poItem && (float) $item->qty_received > 0) {
                        $poItem->increment('received_qty', (float) $item->qty_received);
                    }

                    // Sinkronisasi status incoming_stages di Tool
                    if ($tool && !empty($tool->incoming_stages)) {
                        $stages = $tool->incoming_stages;
                        $stageUpdated = false;

                        if (!empty($item->stage_reference)) {
                            foreach ($stages as $idx => $stg) {
                                if (strcasecmp($stg['stage'] ?? '', $item->stage_reference) === 0 || (string)$idx === (string)$item->stage_reference) {
                                    if (($stg['status'] ?? '') === 'planned') {
                                        $stages[$idx]['status'] = 'received';
                                        $stages[$idx]['received_date'] = $receiptDateStr;
                                        $stages[$idx]['received_gr'] = $receipt->receipt_number;
                                        $stageUpdated = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if (!$stageUpdated) {
                            foreach ($stages as $idx => $stg) {
                                if (($stg['status'] ?? '') === 'planned') {
                                    $stages[$idx]['status'] = 'received';
                                    $stages[$idx]['received_date'] = $receiptDateStr;
                                    $stages[$idx]['received_gr'] = $receipt->receipt_number;
                                    $stageUpdated = true;
                                    break;
                                }
                            }
                        }

                        if ($stageUpdated) {
                            $tool->update(['incoming_stages' => $stages]);
                        }
                    }
                } else {
                    // Penanganan Material
                    $material = $item->material;
                    if (!$material && $item->purchase_order_item_id) {
                        if ($poItem && !empty($poItem->custom_item_name)) {
                            $material = Material::autoRegisterCustom($poItem->custom_item_name, $poItem->custom_item_unit ?? $poItem->unit);
                            $item->update(['material_id' => $material->id]);
                            $poItem->update(['material_id' => $material->id]);
                            if ($poItem->material_request_item_id) {
                                $poItem->materialRequestItem?->update(['material_id' => $material->id]);
                            }
                        }
                    }

                    if ($material) {
                        if (empty($material->supplier_id) && empty($material->supplier_name) && ($receipt->supplier_id || $receipt->supplier_name)) {
                            $material->update([
                                'supplier_id'   => $receipt->supplier_id,
                                'supplier_name' => $receipt->supplier_name ?: $receipt->supplier?->name,
                            ]);
                        }

                        // Stok bertambah HANYA dari qty_received (diterima baik)
                        if ((float) $item->qty_received > 0) {
                            $this->stockService->addStock(
                                $warehouse,
                                $material,
                                (float) $item->qty_received,
                                'goods_receipt',
                                $receipt->id,
                                $userId,
                                "Penerimaan Supplier #{$receipt->receipt_number} — {$receipt->supplier?->name}"
                            );
                        }

                        // Update received_qty di PO item (HANYA dari qty_received baik)
                        if ($poItem && (float) $item->qty_received > 0) {
                            $poItem->increment('received_qty', (float) $item->qty_received);
                        }

                        // Sinkronisasi status incoming_stages di Material
                        if (!empty($material->incoming_stages)) {
                            $stages = $material->incoming_stages;
                            $stageUpdated = false;

                            if (!empty($item->stage_reference)) {
                                foreach ($stages as $idx => $stg) {
                                    if (strcasecmp($stg['stage'] ?? '', $item->stage_reference) === 0 || (string)$idx === (string)$item->stage_reference) {
                                        if (($stg['status'] ?? '') === 'planned') {
                                            $stages[$idx]['status'] = 'received';
                                            $stages[$idx]['received_date'] = $receiptDateStr;
                                            $stages[$idx]['received_gr'] = $receipt->receipt_number;
                                            $stageUpdated = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!$stageUpdated) {
                                foreach ($stages as $idx => $stg) {
                                    if (($stg['status'] ?? '') === 'planned') {
                                        $stages[$idx]['status'] = 'received';
                                        $stages[$idx]['received_date'] = $receiptDateStr;
                                        $stages[$idx]['received_gr'] = $receipt->receipt_number;
                                        $stageUpdated = true;
                                        break;
                                    }
                                }
                            }

                            if ($stageUpdated) {
                                $material->update(['incoming_stages' => $stages]);
                            }
                        }
                    }
                }

                // Resolusi missing_status proporsional dari GR sebelumnya jika PO item mengirimkan barang susulan
                if ($poItem && (float) $item->qty_received > 0) {
                    $unresolvedItems = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)
                        ->where('id', '!=', $item->id)
                        ->where('missing_status', 'unresolved')
                        ->where('qty_missing', '>', 0)
                        ->orderBy('id', 'asc')
                        ->get();

                    $remainingIncoming = (float) $item->qty_received;
                    foreach ($unresolvedItems as $unresolved) {
                        if ($remainingIncoming <= 0) break;
                        if ($remainingIncoming >= (float) $unresolved->qty_missing) {
                            $remainingIncoming -= (float) $unresolved->qty_missing;
                            $unresolved->update(['missing_status' => 'resolved']);
                        } else {
                            $unresolved->decrement('qty_missing', $remainingIncoming);
                            $remainingIncoming = 0;
                        }
                    }
                }

                // T11: Hubungkan ke MR item (qty_received MR item += qty_received GR item)
                if ($poItem && $poItem->material_request_item_id && (float) $item->qty_received > 0) {
                    $mrItem = MaterialRequestItem::where('id', $poItem->material_request_item_id)->lockForUpdate()->first();
                    if ($mrItem) {
                        $mrItem->increment('qty_received', (float) $item->qty_received);
                        if ($mrItem->material_request_id) {
                            $mrToRecalculate->push($mrItem->material_request_id);
                        }
                    }
                }
            }

            // Hitung ulang status MR dan kirim notifikasi
            foreach ($mrToRecalculate->unique() as $mrId) {
                $mr = MaterialRequest::with('items')->find($mrId);
                if ($mr) {
                    $allReceived = true;
                    $anyReceived = false;
                    foreach ($mr->items as $mri) {
                        $targetQty = (float)$mri->qty_approved > 0 ? (float)$mri->qty_approved : (float)$mri->qty_requested;
                        if ((float)$mri->qty_received >= $targetQty) {
                            $anyReceived = true;
                        } else {
                            $allReceived = false;
                            if ((float)$mri->qty_received > 0) {
                                $anyReceived = true;
                            }
                        }
                    }

                    if ($allReceived && $mr->items->isNotEmpty()) {
                        $mr->update(['status' => 'fulfilled']);
                    } elseif ($anyReceived) {
                        $mr->update(['status' => 'partially_fulfilled']);
                    }

                    if ($mr->requestedBy) {
                        NotificationHelper::notifyUser(
                            $mr->requestedBy,
                            "Barang PO Lapangan Diterima: #{$mr->request_number}",
                            "Barang untuk PO Lapangan #{$mr->request_number} sudah diterima di gudang {$warehouse->name}.",
                            'info',
                            route('material-requests.show', $mr)
                        );
                    }
                }
            }

            // Update status PO jika ada
            if ($receipt->purchase_order_id) {
                $this->updatePurchaseOrderStatus($receipt->purchase_order_id);
            }

            // Tandai GR sebagai confirmed
            $receipt->update([
                'status'       => 'confirmed',
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
            ]);

            NotificationHelper::notifyAdmins(
                "GR Dikonfirmasi: #{$receipt->receipt_number}",
                "Penerimaan dari {$receipt->supplier?->name} ke {$warehouse->name} telah dikonfirmasi. Stok diperbarui.",
                'system_info',
                route('goods-receipts.show', $receipt)
            );

            // Notifikasi spesifik ke Purchasing / Admin PO jika berasal dari PO
            if ($receipt->purchase_order_id) {
                $po = PurchaseOrder::find($receipt->purchase_order_id);
                if ($po) {
                    NotificationHelper::notifyPurchasingAdmins(
                        "Barang PO Diterima: #{$po->po_number}",
                        "Barang untuk PO #{$po->po_number} telah diterima di {$warehouse->name}. Status PO: {$po->status_label}.",
                        'success',
                        route('purchase-orders.show', $po)
                    );
                }
            }

            return $receipt->fresh(['supplier', 'warehouse', 'items.material']);
        });
    }

    /**
     * Perbarui status Purchase Order berdasarkan qty yang sudah diterima vs dipesan.
     */
    public function updatePurchaseOrderStatus(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::with('items')->find($purchaseOrderId);
        if (!$po) {
            return;
        }

        $totalOutstanding = (float) $po->items->sum(fn($i) => $i->outstanding);
        $totalReceived    = (float) $po->items->sum('received_qty');

        if ($totalReceived <= 0) {
            return;
        }

        // T11: 'received' HANYA jika SEMUA item outstanding = 0; selain itu 'partial_received'
        $newStatus = $totalOutstanding <= 0 ? 'received' : 'partial_received';

        if (!in_array($po->status, ['received', 'cancelled', 'closed'])) {
            $po->update(['status' => $newStatus]);
        }
    }
}
