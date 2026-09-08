<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class DistributionService
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * Create Distribution & Ship items from Central Warehouse to Project Warehouse.
     * Supports Partial Shipment.
     */
    public function shipDistribution(
        MaterialRequest $request,
        User $shippedBy,
        array $itemsShippedData,
        ?string $notes = null
    ): Distribution {
        if ($request->status !== 'approved' && $request->status !== 'partially_fulfilled') {
            throw new Exception("Hanya pengajuan berstatus 'approved' atau 'partially_fulfilled' yang dapat dikirim.");
        }

        if (empty($itemsShippedData)) {
            throw new Exception("Daftar barang yang dikirim tidak boleh kosong.");
        }

        return DB::transaction(function () use ($request, $shippedBy, $itemsShippedData, $notes) {
            $distribution = Distribution::create([
                'distribution_number' => Distribution::generateDistributionNumber(),
                'material_request_id' => $request->id,
                'from_warehouse_id' => $request->to_warehouse_id, // Central
                'to_warehouse_id' => $request->from_warehouse_id, // Project
                'shipped_by_user_id' => $shippedBy->id,
                'shipped_at' => now(),
                'status' => 'in_transit',
                'notes' => $notes,
            ]);

            foreach ($itemsShippedData as $itemData) {
                $material = Material::findOrFail($itemData['material_id']);
                $qtyShipped = (float) $itemData['qty_shipped'];

                if ($qtyShipped <= 0) {
                    throw new Exception("Jumlah pengiriman untuk material {$material->name} harus > 0.");
                }

                // Match with Request Item
                $requestItem = MaterialRequestItem::where('material_request_id', $request->id)
                    ->where('material_id', $material->id)
                    ->firstOrFail();

                $remainingApproved = (float) $requestItem->qty_approved - (float) $requestItem->qty_fulfilled;

                if ($qtyShipped > $remainingApproved) {
                    throw new Exception(sprintf(
                        "Jumlah pengiriman (%s) melebihi sisa persetujuan (%s) untuk material %s.",
                        $qtyShipped,
                        $remainingApproved,
                        $material->name
                    ));
                }

                // Create Distribution Item
                DistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'material_id' => $material->id,
                    'qty_shipped' => $qtyShipped,
                    'qty_received' => 0,
                    'qty_damaged_or_lost' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Deduct from Central Warehouse Stock via StockService
                $this->stockService->deductStock(
                    $request->toWarehouse, // Central
                    $material,
                    $qtyShipped,
                    'distribution_ship',
                    $distribution->id,
                    $shippedBy->id,
                    "Pengiriman Surat Jalan No: {$distribution->distribution_number}"
                );

                // Increment in_transit quantity in Project Inventory
                $projectInventory = Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $request->from_warehouse_id,
                        'material_id' => $material->id,
                    ],
                    [
                        'quantity'       => 0,
                        'min_stock'      => 0,
                        'qty_allocated'  => 0,
                        'qty_in_transit' => 0,
                    ]
                );

                $projectInventory->increment('qty_in_transit', $qtyShipped);

                // Update Request Item Fulfilled Qty
                $requestItem->increment('qty_fulfilled', $qtyShipped);
            }

            // Update MaterialRequest Overall Status
            $request->refresh();
            $allFulfilled = true;
            foreach ($request->items as $reqItem) {
                if ((float) $reqItem->qty_fulfilled < (float) $reqItem->qty_approved) {
                    $allFulfilled = false;
                    break;
                }
            }

            $request->update([
                'status' => $allFulfilled ? 'fulfilled' : 'partially_fulfilled',
            ]);

            return $distribution->load('items.material', 'fromWarehouse', 'toWarehouse', 'shippedBy');
        });
    }

    /**
     * Receive Goods at Project Warehouse (Partial Receive & Discrepancy handling).
     */
    public function receiveDistribution(
        Distribution $distribution,
        User $receivedBy,
        array $itemsReceivedData,
        ?string $notes = null
    ): Distribution {
        if ($distribution->status !== 'in_transit') {
            throw new Exception("Hanya Surat Jalan berstatus 'in_transit' yang dapat diterima.");
        }

        if (!$receivedBy->hasAccessToWarehouse($distribution->toWarehouse)) {
            throw new Exception("User tidak memiliki akses ke Gudang Proyek penerima.");
        }

        return DB::transaction(function () use ($distribution, $receivedBy, $itemsReceivedData, $notes) {
            foreach ($itemsReceivedData as $itemData) {
                $distributionItem = DistributionItem::where('distribution_id', $distribution->id)
                    ->where('material_id', $itemData['material_id'])
                    ->firstOrFail();

                $qtyReceived = (float) $itemData['qty_received'];
                $qtyDamaged = isset($itemData['qty_damaged_or_lost']) ? (float) $itemData['qty_damaged_or_lost'] : 0.0;
                $qtyShipped = (float) $distributionItem->qty_shipped;

                if (($qtyReceived + $qtyDamaged) > $qtyShipped) {
                    throw new Exception("Total barang diterima + rusak ({$qtyReceived} + {$qtyDamaged}) melebihi yang dikirim ({$qtyShipped}).");
                }

                $distributionItem->update([
                    'qty_received' => $qtyReceived,
                    'qty_damaged_or_lost' => $qtyDamaged,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Reduce in_transit stock from Project Inventory
                $projectInventory = Inventory::where('warehouse_id', $distribution->to_warehouse_id)
                    ->where('material_id', $distributionItem->material_id)
                    ->firstOrFail();

                $projectInventory->decrement('qty_in_transit', $qtyShipped);

                // Add received stock to Project Warehouse via StockService
                if ($qtyReceived > 0) {
                    $this->stockService->addStock(
                        $distribution->toWarehouse,
                        $distributionItem->material,
                        $qtyReceived,
                        'distribution_receive',
                        $distribution->id,
                        $receivedBy->id,
                        "Penerimaan Surat Jalan No: {$distribution->distribution_number}"
                    );
                }
            }

            $distribution->update([
                'status' => 'completed',
                'received_by_user_id' => $receivedBy->id,
                'received_at' => now(),
                'notes' => $notes ?? $distribution->notes,
            ]);

            return $distribution->fresh(['items.material', 'fromWarehouse', 'toWarehouse', 'receivedBy']);
        });
    }
}
