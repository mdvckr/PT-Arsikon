<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * Create Goods Receipt document and atomically add stock to Central Warehouse.
     */
    public function processGoodsReceipt(
        Supplier $supplier,
        Warehouse $centralWarehouse,
        User $receivedBy,
        array $itemsData,
        ?string $receiptDate = null,
        ?string $notes = null
    ): GoodsReceipt {
        if (!$centralWarehouse->is_central) {
            throw new Exception("Penerimaan barang dari supplier hanya boleh dilakukan di Gudang Pusat.");
        }

        if (empty($itemsData)) {
            throw new Exception("Daftar barang yang diterima tidak boleh kosong.");
        }

        return DB::transaction(function () use ($supplier, $centralWarehouse, $receivedBy, $itemsData, $receiptDate, $notes) {
            $receipt = GoodsReceipt::create([
                'receipt_number' => GoodsReceipt::generateReceiptNumber(),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $centralWarehouse->id,
                'received_by_user_id' => $receivedBy->id,
                'receipt_date' => $receiptDate ?? now()->toDateString(),
                'notes' => $notes ?? 'Penerimaan barang dari supplier',
            ]);

            foreach ($itemsData as $item) {
                $material = Material::findOrFail($item['material_id']);
                $qtyReceived = (float) $item['qty_received'];

                if ($qtyReceived <= 0) {
                    throw new Exception("Jumlah barang diterima untuk material {$material->name} harus > 0.");
                }

                // Create Item Detail
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'material_id' => $material->id,
                    'qty_received' => $qtyReceived,
                    'unit_price' => $item['unit_price'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Add stock via StockService
                $this->stockService->addStock(
                    $centralWarehouse,
                    $material,
                    $qtyReceived,
                    'goods_receipt',
                    $receipt->id,
                    $receivedBy->id,
                    "Penerimaan Supplier (Surat Jalan/No: {$receipt->receipt_number})"
                );
            }

            $loaded = $receipt->load('items.material', 'supplier', 'warehouse', 'receivedBy');

            NotificationHelper::notifyAdmins(
                "Penerimaan Barang: #{$receipt->receipt_number}",
                "Barang dari {$supplier->name} telah diterima di {$centralWarehouse->name} oleh {$receivedBy->name}.",
                "system_info",
                route('goods-receipts.show', $receipt)
            );

            return $loaded;
        });
    }
}
