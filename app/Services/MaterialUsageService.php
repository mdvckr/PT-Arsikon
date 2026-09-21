<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialUsage;
use App\Models\MaterialUsageItem;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class MaterialUsageService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Record material usage / release from warehouse to field worker/mandor.
     */
    public function createUsage(Warehouse $warehouse, User $issuedBy, array $data): MaterialUsage
    {
        $items = (array) ($data['items'] ?? []);
        if (empty($items)) {
            throw new Exception("Daftar material yang dikeluarkan tidak boleh kosong.");
        }

        if (empty($data['recipient_name'])) {
            throw new Exception("Nama penerima / mandor / tukang wajib diisi.");
        }

        return DB::transaction(function () use ($warehouse, $issuedBy, $data, $items) {
            // If linked to a Material Request, perform strict validation against approved quantities
            $materialRequest = null;
            $mrItemsMap = null;
            if (!empty($data['material_request_id'])) {
                $materialRequest = MaterialRequest::with('items.material')->find($data['material_request_id']);
                if (!$materialRequest) {
                    throw new Exception("Nomor Permintaan Material (MR) tidak ditemukan.");
                }
                if (!in_array($materialRequest->status, ['approved', 'partially_fulfilled'])) {
                    throw new Exception("Hanya Permintaan Material (MR) dengan status 'Disetujui' atau 'Terkirim Sebagian' yang dapat ditarik.");
                }
                $mrItemsMap = $materialRequest->items->keyBy('material_id');
            }

            // First pass: validate all items and check stock availability & MR quota
            $validatedItems = [];
            foreach ($items as $item) {
                $materialId = isset($item['material_id']) && $item['material_id'] !== '' ? (int) $item['material_id'] : null;
                $qty = (float) ($item['quantity'] ?? 0);
                $isCustom = $materialId === null;

                if ($qty <= 0) {
                    continue;
                }

                if ($isCustom) {
                    // Custom item: validate name, skip inventory/MR checks
                    $customName = trim($item['custom_item_name'] ?? '');
                    if (empty($customName)) {
                        throw new Exception("Item custom harus memiliki nama.");
                    }
                    $validatedItems[] = [
                        'material'         => null,
                        'custom_item_name' => $customName,
                        'custom_item_unit' => trim($item['custom_item_unit'] ?? 'unit') ?: 'unit',
                        'quantity'         => $qty,
                        'notes'            => $item['notes'] ?? null,
                        'is_custom'        => true,
                    ];
                    continue;
                }

                $material = Material::findOrFail($materialId);

                // If MR is linked, ensure item belongs to MR and does not exceed remaining approved qty
                if ($materialRequest) {
                    $mrItem = $mrItemsMap->get($materialId);
                    if (!$mrItem) {
                        throw new Exception(sprintf(
                            "Material '%s' tidak terdaftar dalam rincian Permintaan Material #%s.",
                            $material->name,
                            $materialRequest->request_number
                        ));
                    }

                    $remainingQuota = max(0, (float)$mrItem->qty_approved - (float)$mrItem->qty_fulfilled);
                    if ($qty > $remainingQuota) {
                        throw new Exception(sprintf(
                            "Kuantitas material '%s' (%s %s) melebihi sisa kuota persetujuan MR #%s (sisa disetujui: %s %s). Pengeluaran harus sesuai persetujuan.",
                            $material->name,
                            $qty,
                            $material->unit?->abbreviation ?? 'unit',
                            $materialRequest->request_number,
                            $remainingQuota,
                            $material->unit?->abbreviation ?? 'unit'
                        ));
                    }
                }

                // Check inventory
                $inventory = Inventory::where('warehouse_id', $warehouse->id)
                    ->where('material_id', $material->id)
                    ->lockForUpdate()
                    ->first();

                $available = $inventory ? (float) $inventory->quantity : 0;
                if ($available < $qty) {
                    throw new Exception(sprintf(
                        "Stok material '%s' di %s tidak mencukupi. Tersedia: %s %s, diminta: %s %s.",
                        $material->name,
                        $warehouse->name,
                        $available,
                        $material->unit?->abbreviation ?? 'unit',
                        $qty,
                        $material->unit?->abbreviation ?? 'unit'
                    ));
                }

                $validatedItems[] = [
                    'material'         => $material,
                    'custom_item_name' => null,
                    'custom_item_unit' => null,
                    'quantity'         => $qty,
                    'notes'            => $item['notes'] ?? null,
                    'is_custom'        => false,
                ];
            }

            if (empty($validatedItems)) {
                throw new Exception("Silakan masukkan jumlah minimal 1 material dengan kuantitas lebih dari 0.");
            }

            // Create MaterialUsage header
            $usage = MaterialUsage::create([
                'usage_number'        => MaterialUsage::generateUsageNumber(),
                'warehouse_id'        => $warehouse->id,
                'project_id'          => $warehouse->project_id,
                'material_request_id' => $data['material_request_id'] ?? null,
                'issued_by_user_id'   => $issuedBy->id,
                'recipient_name'      => $data['recipient_name'],
                'job_section'         => $data['job_section'] ?? null,
                'usage_date'          => $data['usage_date'] ?? now()->toDateString(),
                'status'              => 'completed',
                'notes'               => $data['notes'] ?? null,
            ]);

            // Save items, deduct stock, and update MR fulfilled qty
            foreach ($validatedItems as $vItem) {
                MaterialUsageItem::create([
                    'material_usage_id' => $usage->id,
                    'material_id'       => $vItem['is_custom'] ? null : $vItem['material']->id,
                    'custom_item_name'  => $vItem['custom_item_name'],
                    'custom_item_unit'  => $vItem['custom_item_unit'],
                    'quantity'          => $vItem['quantity'],
                    'notes'             => $vItem['notes'],
                ]);

                // Custom items: skip stock deduction and MR quota update
                if ($vItem['is_custom']) {
                    continue;
                }

                // Deduct stock via StockService (updates quantity & creates StockMutation)
                $mutationNotes = sprintf(
                    "Pemakaian lapangan: %s - %s (%s)",
                    $usage->recipient_name,
                    $usage->job_section ?? 'Pekerjaan Umum',
                    $usage->usage_number
                );

                $this->stockService->deductStock(
                    $warehouse,
                    $vItem['material'],
                    $vItem['quantity'],
                    'material_usage',
                    $usage->id,
                    $issuedBy->id,
                    $mutationNotes
                );

                // If linked to MR, increment fulfilled qty
                if ($materialRequest && $mrItemsMap) {
                    $mrItem = $mrItemsMap->get($vItem['material']->id);
                    if ($mrItem) {
                        $mrItem->increment('qty_fulfilled', $vItem['quantity']);
                    }
                }
            }

            // Update MR status based on fulfillment
            if ($materialRequest) {
                $freshMrItems = $materialRequest->items()->get();
                $allFulfilled = $freshMrItems->every(fn($i) => (float)$i->qty_fulfilled >= (float)$i->qty_approved);
                $anyFulfilled = $freshMrItems->contains(fn($i) => (float)$i->qty_fulfilled > 0);

                if ($allFulfilled) {
                    $materialRequest->update(['status' => 'fulfilled']);
                } elseif ($anyFulfilled) {
                    $materialRequest->update(['status' => 'partially_fulfilled']);
                }
            }

            // Notification
            $notifMsg = $materialRequest 
                ? "Pengeluaran material dari MR #{$materialRequest->request_number} di {$warehouse->name} kepada {$usage->recipient_name} untuk {$usage->job_section}."
                : "Pengeluaran material di {$warehouse->name} kepada {$usage->recipient_name} untuk {$usage->job_section}.";

            NotificationHelper::notifyAdmins(
                "Pengeluaran Material: {$usage->usage_number}",
                $notifMsg,
                "info",
                route('material-usages.show', $usage)
            );

            return $usage->fresh(['items.material.unit', 'warehouse', 'project', 'issuedBy', 'materialRequest']);
        });
    }

    /**
     * Cancel / void a completed material usage and reverse stock deductions.
     */
    public function cancelUsage(MaterialUsage $usage, User $cancelledBy, string $reason): MaterialUsage
    {
        if ($usage->status !== 'completed') {
            throw new Exception("Hanya transaksi berstatus 'Selesai' yang dapat dibatalkan.");
        }

        return DB::transaction(function () use ($usage, $cancelledBy, $reason) {
            $usage->load(['items.material', 'warehouse']);
            $warehouse = $usage->warehouse;

            // Reverse each item: add stock back
            foreach ($usage->items as $item) {
                $material = $item->material;
                if (!$material || !$warehouse) continue;

                $mutationNotes = sprintf(
                    "Pembatalan pemakaian: %s (%s) — Alasan: %s",
                    $usage->usage_number,
                    $usage->recipient_name,
                    $reason
                );

                $this->stockService->addStock(
                    $warehouse,
                    $material,
                    (float) $item->quantity,
                    'material_usage_cancellation',
                    $usage->id,
                    $cancelledBy->id,
                    $mutationNotes
                );
            }

            // If this usage was linked to a Material Request, rollback fulfilled quantities and status
            if ($usage->material_request_id) {
                $mr = MaterialRequest::with('items')->find($usage->material_request_id);
                if ($mr) {
                    foreach ($usage->items as $item) {
                        $mrItem = $mr->items->where('material_id', $item->material_id)->first();
                        if ($mrItem) {
                            $newFulfilled = max(0, (float)$mrItem->qty_fulfilled - (float)$item->quantity);
                            $mrItem->update(['qty_fulfilled' => $newFulfilled]);
                        }
                    }

                    $freshItems = $mr->items()->get();
                    $allFulfilled = $freshItems->every(fn($it) => (float)$it->qty_fulfilled >= (float)$it->qty_approved);
                    $anyFulfilled = $freshItems->contains(fn($it) => (float)$it->qty_fulfilled > 0);

                    if ($allFulfilled) {
                        $mr->update(['status' => 'fulfilled']);
                    } elseif ($anyFulfilled) {
                        $mr->update(['status' => 'partially_fulfilled']);
                    } else {
                        $mr->update(['status' => 'approved']);
                    }
                }
            }

            // Mark as cancelled
            $usage->update([
                'status'               => 'cancelled',
                'cancelled_at'         => now(),
                'cancelled_by_user_id' => $cancelledBy->id,
                'cancellation_reason'  => $reason,
            ]);

            // Notify admins
            NotificationHelper::notifyAdmins(
                "Pembatalan Pemakaian Material: {$usage->usage_number}",
                "Pemakaian material #{$usage->usage_number} di {$warehouse->name} dibatalkan oleh {$cancelledBy->name}. Alasan: {$reason}. Stok telah dikembalikan.",
                "warning",
                route('material-usages.show', $usage)
            );

            return $usage->fresh();
        });
    }
}
