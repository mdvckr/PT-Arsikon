<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Material;
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
            // First pass: validate all items and check stock availability
            $validatedItems = [];
            foreach ($items as $item) {
                $materialId = (int) ($item['material_id'] ?? 0);
                $qty = (float) ($item['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $material = Material::findOrFail($materialId);

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
                    'material' => $material,
                    'quantity' => $qty,
                    'notes'    => $item['notes'] ?? null,
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

            // Save items and deduct stock
            foreach ($validatedItems as $vItem) {
                MaterialUsageItem::create([
                    'material_usage_id' => $usage->id,
                    'material_id'       => $vItem['material']->id,
                    'quantity'          => $vItem['quantity'],
                    'notes'             => $vItem['notes'],
                ]);

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
            }

            // Notification
            NotificationHelper::notifyAdmins(
                "Pengeluaran Material: {$usage->usage_number}",
                "Pengeluaran material di {$warehouse->name} kepada {$usage->recipient_name} untuk {$usage->job_section}.",
                "info",
                route('material-usages.show', $usage)
            );

            return $usage->fresh(['items.material.unit', 'warehouse', 'project', 'issuedBy']);
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
