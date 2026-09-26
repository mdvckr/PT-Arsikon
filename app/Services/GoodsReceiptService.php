<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\Tool;
use App\Models\PurchaseOrder;
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
            $receipt = GoodsReceipt::create([
                'receipt_number'      => GoodsReceipt::generateReceiptNumber(),
                'purchase_order_id'   => $validated['purchase_order_id'] ?? null,
                'supplier_id'         => $validated['supplier_id'],
                'supplier_name'       => $validated['supplier_name'] ?? null,
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
                GoodsReceiptItem::create([
                    'goods_receipt_id'       => $receipt->id,
                    'item_type'              => $isTool ? 'tool' : 'material',
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'stage_reference'        => $item['stage_reference'] ?? null,
                    'material_id'            => $isTool ? null : ($item['material_id'] ?? null),
                    'tool_id'                => $isTool ? ($item['tool_id'] ?? null) : null,
                    'qty_received'           => $item['quantity'] ?? $item['qty_received'] ?? 0,
                    'condition'              => $item['condition'] ?? 'good',
                    'unit_price'             => $item['unit_price'] ?? 0,
                    'notes'                  => $item['notes'] ?? null,
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

            // Tambah stok di gudang tujuan untuk setiap item
            foreach ($receipt->items as $item) {
                if ((float) $item->qty_received <= 0) {
                    continue;
                }

                if ($item->isTool()) {
                    // Penanganan Alat / Mesin
                    $tool = $item->tool;
                    if ($tool) {
                        $this->toolInventoryService->addStock(
                            $warehouse,
                            $tool,
                            (int) round($item->qty_received),
                            "Penerimaan Supplier #{$receipt->receipt_number} — {$receipt->supplier?->name}"
                        );

                        // Sinkronisasi status incoming_stages di Tool
                        if (!empty($tool->incoming_stages)) {
                            $stages = $tool->incoming_stages;
                            $stageUpdated = false;

                            // 1. Cocokkan dengan stage_reference jika diberikan
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

                            // 2. Jika belum terupdate (misal stage_reference tidak dipilih manual), otomatis ubah tahap 'planned' pertama
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
                    }
                } else {
                    // Penanganan Material
                    $material = $item->material;
                    if (!$material && $item->purchase_order_item_id) {
                        $poItem = $item->purchaseOrderItem;
                        if ($poItem && !empty($poItem->custom_item_name)) {
                            $material = Material::autoRegisterCustom($poItem->custom_item_name, $poItem->custom_item_unit ?? $poItem->unit);
                            $item->update(['material_id' => $material->id]);
                            $poItem->update(['material_id' => $material->id]);
                            if ($poItem->material_request_item_id) {
                                $poItem->materialRequestItem?->update(['material_id' => $material->id]);
                            }
                        }
                    }

                    if (!$material) {
                        continue;
                    }

                    $this->stockService->addStock(
                        $warehouse,
                        $material,
                        (float) $item->qty_received,
                        'goods_receipt',
                        $receipt->id,
                        $userId,
                        "Penerimaan Supplier #{$receipt->receipt_number} — {$receipt->supplier?->name}"
                    );

                    // Update qty_received di PO item jika terhubung
                    if ($item->purchase_order_item_id) {
                        $poItem = $item->purchaseOrderItem;
                        if ($poItem) {
                            $poItem->increment('received_qty', (float) $item->qty_received);
                        }
                    }

                    // Sinkronisasi status incoming_stages di Material
                    if (!empty($material->incoming_stages)) {
                        $stages = $material->incoming_stages;
                        $stageUpdated = false;

                        // 1. Cocokkan dengan stage_reference jika diberikan
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

                        // 2. Jika belum terupdate (misal stage_reference tidak dipilih manual), otomatis ubah tahap 'planned' pertama
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
    protected function updatePurchaseOrderStatus(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::with('items')->find($purchaseOrderId);
        if (!$po) {
            return;
        }

        $totalOrdered  = (float) $po->items->sum('quantity');
        $totalReceived = (float) $po->items->sum('received_qty');

        if ($totalReceived <= 0) {
            return;
        }

        $newStatus = $totalReceived >= $totalOrdered ? 'received' : 'partial_received';

        if (!in_array($po->status, ['received', 'cancelled'])) {
            $po->update(['status' => $newStatus]);
        }
    }
}
