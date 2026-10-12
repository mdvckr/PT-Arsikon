<?php

namespace App\Console\Commands;

use App\Models\PoDeliverySchedule;
use App\Services\NotificationHelper;
use Illuminate\Console\Command;

class CheckLateDeliverySchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procurement:check-late-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa jadwal kedatangan PO yang melewati batas waktu (terlambat) dan kirim notifikasi ke Admin PO.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lateSchedules = PoDeliverySchedule::with([
            'purchaseOrderItem.purchaseOrder.supplier',
            'warehouse'
        ])
        ->whereIn('status', ['planned', 'partial'])
        ->whereNotNull('planned_date')
        ->whereDate('planned_date', '<', now()->toDateString())
        ->whereRaw('(planned_qty - received_qty) > 0')
        ->whereNull('late_notified_at')
        ->whereHas('purchaseOrderItem.purchaseOrder', function ($q) {
            $q->whereIn('status', ['approved', 'sent', 'partial_received']);
        })
        ->get();

        if ($lateSchedules->isEmpty()) {
            $this->info('Tidak ada jadwal kedatangan terlambat yang perlu dinotifikasikan.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($lateSchedules as $sch) {
            $poItem = $sch->purchaseOrderItem;
            $po = $poItem?->purchaseOrder;

            if (!$poItem || !$po) {
                continue;
            }

            $itemName = $poItem->displayName();
            $itemUnit = $poItem->displayUnit();
            $poNumber = $po->po_number;
            $supplierName = $po->supplier_name ?: ($po->supplier?->name ?? 'Supplier');
            $dateStr = $sch->planned_date ? $sch->planned_date->format('d/m/Y') : '-';
            $remaining = (float) $sch->remaining_qty;
            $stage = $sch->stage_label ?: ('Tahap #' . $sch->id);

            $title = "Jadwal Kedatangan Terlambat: {$itemName}";
            $message = "PO #{$poNumber} ({$supplierName}) item {$itemName} untuk jadwal '{$stage}' (rencana: {$dateStr}) belum diterima. Sisa belum datang: {$remaining} {$itemUnit}.";
            $url = route('purchase-orders.show', $po->id);

            NotificationHelper::notifyPurchasingAdmins($title, $message, 'warning', $url);

            $sch->update(['late_notified_at' => now()]);
            $count++;
        }

        $this->info("Berhasil mengirim notifikasi untuk {$count} jadwal pengiriman terlambat.");
        return Command::SUCCESS;

        $this->info("Berhasil mengirim notifikasi untuk {$count} jadwal pengiriman terlambat.");
        return Command::SUCCESS;
    }
}
