<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(protected StockService $stockService) {}

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
                'warehouse_id'        => $validated['warehouse_id'],
                'received_by_user_id' => $userId,
                'created_by'          => $userId,
                'status'              => 'draft',
                'invoice_number'      => $validated['invoice_number'] ?? null,
                'receipt_date'        => $validated['received_at'],
                'notes'               => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id'       => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'material_id'            => $item['material_id'],
                    'qty_received'           => $item['quantity'],
                    'unit_price'             => $item['unit_price'] ?? 0,
                ]);
            }

            return $receipt->load(['supplier', 'warehouse', 'items.material']);
        });
    }

    /**
     * Konfirmasi GR: validasi items, update stok gudang, update status PO jika ada.
     * Bisa dipanggil untuk gudang pusat maupun gudang proyek.
     */
    public function confirm(GoodsReceipt $receipt, int $userId): GoodsReceipt
    {
        if ($receipt->status === 'confirmed') {
            throw new Exception('Penerimaan barang ini sudah dikonfirmasi sebelumnya.');
        }

        return DB::transaction(function () use ($receipt, $userId) {
            $receipt->load(['items.material', 'warehouse', 'supplier']);

            $warehouse = $receipt->warehouse;

            if (!$warehouse) {
                throw new Exception('Gudang tujuan tidak ditemukan.');
            }

            // Tambah stok di gudang tujuan untuk setiap item
            foreach ($receipt->items as $item) {
                if ((float) $item->qty_received <= 0) {
                    continue;
                }

                $this->stockService->addStock(
                    $warehouse,
                    $item->material,
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
