<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\ToolAssignment;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class DistributionService
{
    public function __construct(
        protected StockService $stockService,
        protected ToolInventoryService $toolInvService
    ) {
    }

    /**
     * Buat Surat Jalan (status draft) dari sumber permintaan:
     *  - Satu Material Request (opsional) -> item material otomatis dari MR
     *  - Beberapa Tool Assignment (opsional) -> item alat otomatis dari TA
     * Item boleh dikurangi qty-nya (partial), tapi tidak boleh melebihi sisa sumber.
     */
    public function create(array $data, int $userId): Distribution
    {
        $mr = null;
        if (!empty($data['material_request_id'])) {
            $mr = MaterialRequest::with(['items.material.unit'])->findOrFail($data['material_request_id']);
            if (!in_array($mr->status, ['approved', 'partially_fulfilled'])) {
                throw new Exception("Permintaan material {$mr->request_number} berstatus '{$mr->status}', tidak dapat dibuatkan Surat Jalan.");
            }
        }

        $taIds = collect($data['tool_assignment_ids'] ?? [])
            ->merge(collect($data['items'] ?? [])->pluck('tool_assignment_id'))
            ->filter()->unique()->values();

        $tas = $taIds->isNotEmpty()
            ? ToolAssignment::with(['tool'])->whereIn('id', $taIds)->get()->keyBy('id')
            : collect();

        foreach ($taIds as $taId) {
            $ta = $tas->get($taId);
            if (!$ta) {
                throw new Exception("Pengajuan peminjaman alat tidak ditemukan.");
            }
            if (!in_array($ta->status, ['pending', 'active'])) {
                throw new Exception("Pengajuan peminjaman alat '{$ta->assignment_number}' berstatus '{$ta->status}', tidak dapat dibuatkan Surat Jalan.");
            }
        }

        $items = (array) ($data['items'] ?? []);
        if (empty($items)) {
            throw new Exception("Daftar barang/alat yang dikirim tidak boleh kosong. Pilih permintaan material, peminjaman alat, atau tambahkan item.");
        }

        $fromWarehouse = Warehouse::findOrFail($data['from_warehouse_id']);
        $toWarehouse   = Warehouse::findOrFail($data['to_warehouse_id']);

        return DB::transaction(function () use ($data, $userId, $mr, $tas, $items, $fromWarehouse, $toWarehouse) {
            $distribution = Distribution::create([
                'distribution_number'   => Distribution::generateDistributionNumber(),
                'material_request_id'   => $mr?->id,
                'from_warehouse_id'     => $fromWarehouse->id,
                'to_warehouse_id'       => $toWarehouse->id,
                'delivery_date'         => $data['delivery_date'] ?? null,
                'driver_name'           => $data['driver_name'] ?? null,
                'vehicle_number'        => $data['vehicle_number'] ?? null,
                'created_by_user_id'    => $userId,
                'status'                => 'draft',
                'notes'                 => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                if (($item['type'] ?? 'material') === 'tool' && !empty($item['tool_id'])) {
                    $ta = !empty($item['tool_assignment_id']) ? $tas->get($item['tool_assignment_id']) : null;
                    if ($ta) {
                        if ((int) $ta->tool_id !== (int) $item['tool_id']) {
                            throw new Exception("Sumber pengajuan alat tidak sesuai.");
                        }
                        // Untuk gudang pusat (central), jangan batasi qty alat berdasarkan TA.
                        // Pembatasan sesungguhnya adalah stok fisik di gudang, yang dicek saat pengiriman (ship()).
                        if (!$fromWarehouse->is_central && $qty > $ta->quantity) {
                            throw new Exception(sprintf(
                                "Jumlah alat %s (%s) melebihi jumlah pengajuan (%s).",
                                $ta->tool?->name,
                                $qty,
                                $ta->quantity
                            ));
                        }
                    }

                    DistributionItem::create([
                        'distribution_id'     => $distribution->id,
                        'tool_id'             => (int) $item['tool_id'],
                        'tool_assignment_id'  => $ta?->id,
                        'qty_shipped'         => $qty,
                        'qty_received'        => 0,
                        'qty_damaged_or_lost' => 0,
                        'notes'               => $item['notes'] ?? null,
                    ]);
                    continue;
                }

                // Item custom (tidak ada di sistem — bebas tulis nama)
                $itemType = $item['type'] ?? 'material';
                if ($itemType === 'custom') {
                    $customName = trim($item['custom_item_name'] ?? '');
                    if (empty($customName)) {
                        continue; // skip baris custom tanpa nama
                    }
                    DistributionItem::create([
                        'distribution_id'     => $distribution->id,
                        'custom_item_name'    => $customName,
                        'custom_item_unit'    => trim($item['custom_item_unit'] ?? 'unit') ?: 'unit',
                        'qty_shipped'         => $qty,
                        'qty_received'        => 0,
                        'qty_damaged_or_lost' => 0,
                        'notes'               => $item['notes'] ?? null,
                    ]);
                    continue;
                }

                // Item material
                if (!empty($item['material_id'])) {
                    if ($mr) {
                        $requestItem = MaterialRequestItem::where('material_request_id', $mr->id)
                            ->where('material_id', $item['material_id'])
                            ->first();

                        if ($requestItem) {
                            $remaining = (float) $requestItem->qty_approved - (float) $requestItem->qty_fulfilled;
                            if ($qty > $remaining) {
                                $material = $requestItem->material;
                                throw new Exception(sprintf(
                                    "Jumlah pengiriman %s (%s) melebihi sisa persetujuan (%s).",
                                    $material?->name,
                                    $qty,
                                    $remaining
                                ));
                            }
                        }
                    }

                    DistributionItem::create([
                        'distribution_id'     => $distribution->id,
                        'material_id'         => (int) $item['material_id'],
                        'qty_shipped'         => $qty,
                        'qty_received'        => 0,
                        'qty_damaged_or_lost' => 0,
                        'notes'               => $item['notes'] ?? null,
                    ]);
                }
            }


            $freshItems = $distribution->items()->count();
            if ($freshItems === 0) {
                // rollback manual: hapus distribution jika tidak ada item valid
                $distribution->delete();
                throw new Exception("Daftar barang/alat yang dikirim tidak boleh kosong.");
            }

            $loaded = $distribution->load(['items.material', 'items.tool', 'fromWarehouse', 'toWarehouse', 'creator', 'materialRequest']);

            NotificationHelper::notifyAdmins(
                "Surat Jalan Baru Dibuat: #{$distribution->distribution_number}",
                "Surat Jalan #{$distribution->distribution_number} dibuat dari {$fromWarehouse->name} menuju {$toWarehouse->name}.",
                "info",
                route('distributions.show', $distribution)
            );

            return $loaded;
        });
    }

    /**
     * Kirim Surat Jalan: kurangi stok material di gudang asal, naikkan in-transit di tujuan.
     * Untuk item alat yang pengajuannya masih pending -> otomatis disetujui & alat dipinjamkan.
     */
    public function ship(Distribution $distribution, int $userId): Distribution
    {
        if ($distribution->status !== 'draft') {
            throw new Exception("Hanya Surat Jalan berstatus 'draft' yang dapat dikirim.");
        }

        return DB::transaction(function () use ($distribution, $userId) {
            $distribution->load(['items.material', 'items.tool', 'items.toolAssignment', 'materialRequest', 'fromWarehouse', 'toWarehouse']);

            foreach ($distribution->items as $item) {
                if ($item->isTool()) {
                    $ta = $item->toolAssignment;
                    $tool = $item->tool;

                    $sourceInv = \App\Models\ToolInventory::where('warehouse_id', $distribution->from_warehouse_id)
                        ->where('tool_id', $tool->id)
                        ->first();
                    $availQty = $sourceInv ? (int) $sourceInv->stock_available : (int) $tool->stock_available;

                    if ($availQty < (int) $item->qty_shipped) {
                        throw new Exception("Stok alat {$tool->name} tidak mencukupi di gudang {$distribution->fromWarehouse?->name} saat pengiriman (tersedia: {$availQty}, dibutuhkan: {$item->qty_shipped}).");
                    }

                    // Update tool assignment status to active if it exists
                    if ($ta) {
                        $ta->update([
                            'status'              => 'active',
                            'approved_by_user_id' => $userId,
                            'approved_at'         => now(),
                        ]);
                    }

                    // Borrow via service from source warehouse
                    $warehouse = $distribution->fromWarehouse;
                    $this->toolInvService->borrow($warehouse, $tool, (int) $item->qty_shipped);

                    continue;
                }

                if ($item->isCustom()) {
                    // Custom items don't have stock in inventory, skip stock deduction
                    continue;
                }

                // Material
                $this->stockService->deductStock(
                    $distribution->fromWarehouse,
                    $item->material,
                    (float) $item->qty_shipped,
                    'distribution_ship',
                    $distribution->id,
                    $userId,
                    "Pengiriman Surat Jalan No: {$distribution->distribution_number}"
                );

                $projectInventory = Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $distribution->to_warehouse_id,
                        'material_id'  => $item->material_id,
                    ],
                    [
                        'quantity'       => 0,
                        'min_stock'      => 0,
                        'qty_allocated'  => 0,
                        'qty_in_transit' => 0,
                    ]
                );
                $projectInventory->increment('qty_in_transit', (float) $item->qty_shipped);

                // Update qty_fulfilled pada item permintaan jika terhubung dengan MR
                if ($distribution->material_request_id) {
                    MaterialRequestItem::where('material_request_id', $distribution->material_request_id)
                        ->where('material_id', $item->material_id)
                        ->increment('qty_fulfilled', (float) $item->qty_shipped);
                }
            }

            // Perbarui status MR keseluruhan
            if ($distribution->materialRequest) {
                $mr        = $distribution->materialRequest->fresh(['items']);
                $fulfilled = $mr->items->every(fn($i) => (float) $i->qty_fulfilled >= (float) $i->qty_approved);
                $mr->update([
                    'status' => $fulfilled ? 'fulfilled'
                                : (in_array($mr->status, ['approved', 'partially_fulfilled']) ? 'partially_fulfilled' : $mr->status),
                ]);
            }

            $distribution->update([
                'status'            => 'in_transit',
                'shipped_by_user_id' => $userId,
                'shipped_at'        => now(),
            ]);

            // Notify destination warehouse users / admins
            $destUsers = User::whereHas('warehouses', fn($q) => $q->where('warehouses.id', $distribution->to_warehouse_id))->get();
            if ($destUsers->isEmpty()) {
                $destUsers = User::role(['Owner', 'Admin', 'Admin Gudang Pusat'])->get();
            }
            foreach ($destUsers as $destUser) {
                NotificationHelper::notifyUser(
                    $destUser,
                    "Surat Jalan Dalam Pengiriman: #{$distribution->distribution_number}",
                    "Surat Jalan #{$distribution->distribution_number} sedang dikirim menuju {$distribution->toWarehouse?->name}.",
                    "info",
                    route('distributions.show', $distribution)
                );
            }

            // Notify central admins (Owner, Admin, Admin Gudang Pusat) - always notify for visibility
            $centralAdmins = User::role(['Owner', 'Admin', 'Admin Gudang Pusat'])->get();
            foreach ($centralAdmins as $admin) {
                // Avoid duplicate notification if already notified as destUser
                if (!$destUsers->contains('id', $admin->id)) {
                    NotificationHelper::notifyUser(
                        $admin,
                        "Surat Jalan Dikirim: #{$distribution->distribution_number}",
                        "Surat Jalan #{$distribution->distribution_number} dari {$distribution->fromWarehouse?->name} menuju {$distribution->toWarehouse?->name} telah dikirim.",
                        "info",
                        route('distributions.show', $distribution)
                    );
                }
            }

            // Notify the creator (applicant) that the surat jalan has been shipped
            if ($distribution->creator) {
                NotificationHelper::notifyUser(
                    $distribution->creator,
                    "Surat Jalan Dikirim: #{$distribution->distribution_number}",
                    "Surat Jalan #{$distribution->distribution_number} yang Anda ajukan telah dikirim ke {$distribution->toWarehouse?->name}.",
                    "success",
                    route('distributions.show', $distribution)
                );
            }

            // Notify the user who shipped the distribution (if not already notified)
            $shipper = User::find($userId);
            if ($shipper && !$destUsers->contains('id', $shipper->id) && !$centralAdmins->contains('id', $shipper->id) && (!$distribution->creator || $distribution->creator->id !== $shipper->id)) {
                NotificationHelper::notifyUser(
                    $shipper,
                    "Surat Jalan Dikirim: #{$distribution->distribution_number}",
                    "Anda telah mengirim Surat Jalan #{$distribution->distribution_number} dari {$distribution->fromWarehouse?->name} ke {$distribution->toWarehouse?->name}.",
                    "success",
                    route('distributions.show', $distribution)
                );
            }


            return $distribution->fresh(['items.material', 'items.tool', 'fromWarehouse', 'toWarehouse']);
        });
    }

    /**
     * Terima barang/alat di gudang tujuan. Mendukung partial receive & discrepancy (rusak/hilang).
     */
    public function receive(Distribution $distribution, array $itemsReceivedData, int $userId, ?string $surat_jalan = null): Distribution
    {
        if ($distribution->status !== 'in_transit') {
            throw new Exception("Hanya Surat Jalan berstatus 'in_transit' yang dapat diterima.");
        }

        if (!$distribution->toWarehouse) {
            throw new Exception("Gudang tujuan tidak ditemukan.");
        }

        return DB::transaction(function () use ($distribution, $itemsReceivedData, $userId, $surat_jalan) {
            $distribution->load(['items.material', 'items.tool', 'fromWarehouse', 'toWarehouse']);

            // Simpan No. Surat Jalan ke distribusi jika diberikan
            if ($surat_jalan) {
                $distribution->surat_jalan = $surat_jalan;
            }

            foreach ($itemsReceivedData as $itemData) {
                $distributionItem = DistributionItem::where('distribution_id', $distribution->id)
                    ->find($itemData['distribution_item_id'] ?? null);
                if (!$distributionItem) {
                    throw new Exception("Item Surat Jalan tidak ditemukan.");
                }

                $qtyReceived = (float) ($itemData['received_quantity'] ?? 0);
                $qtyDamaged  = isset($itemData['qty_damaged_or_lost']) ? (float) $itemData['qty_damaged_or_lost'] : 0;
                $qtyShipped  = (float) $distributionItem->qty_shipped;

                if (($qtyReceived + $qtyDamaged) > $qtyShipped) {
                    throw new Exception("Total diterima + rusak melebihi jumlah kirim ({$qtyShipped}).");
                }

                if ($qtyReceived < 0 || $qtyDamaged < 0) {
                    throw new Exception("Jumlah diterima/rusak tidak boleh negatif.");
                }

                $distributionItem->update([
                    'qty_received'        => $qtyReceived,
                    'qty_damaged_or_lost' => $qtyDamaged,
                ]);

                if ($distributionItem->isTool() || $distributionItem->isCustom()) {
                    // Stok alat atau item custom tidak dikelola via inventory material
                    continue;
                }

                $projectInventory = Inventory::where('warehouse_id', $distribution->to_warehouse_id)
                    ->where('material_id', $distributionItem->material_id)
                    ->first();

                if ($projectInventory) {
                    $projectInventory->decrement('qty_in_transit', min($qtyShipped, (float) $projectInventory->qty_in_transit));
                }

                if ($qtyReceived > 0) {
                    $this->stockService->addStock(
                        $distribution->toWarehouse,
                        $distributionItem->material,
                        $qtyReceived,
                        'distribution_receive',
                        $distribution->id,
                        $userId,
                        "Penerimaan Surat Jalan No: {$distribution->distribution_number}"
                    );
                }
            }

            $distribution->update([
                'status'              => 'completed',
                'received_by_user_id' => $userId,
                'received_at'         => now(),
                'surat_jalan'         => $surat_jalan,
            ]);

            // Notify creator & central admins
            if ($distribution->creator) {
                NotificationHelper::notifyUser(
                    $distribution->creator,
                    "Surat Jalan Selesai: #{$distribution->distribution_number}",
                    "Barang/alat pada Surat Jalan #{$distribution->distribution_number} telah diterima di {$distribution->toWarehouse?->name}.",
                    "success",
                    route('distributions.show', $distribution)
                );
            }

            return $distribution->fresh(['items.material', 'items.tool', 'fromWarehouse', 'toWarehouse', 'receivedBy']);
        });
    }
}