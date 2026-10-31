<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\TransitLoss;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransitLossService
{
    /**
     * Catat laporan kehilangan barang (Aksi "Nyatakan Hilang").
     */
    public function recordLoss(array $data, int $userId): TransitLoss
    {
        $user = User::findOrFail($userId);
        $grItem = GoodsReceiptItem::with(['goodsReceipt', 'purchaseOrderItem', 'material', 'tool'])
            ->findOrFail($data['goods_receipt_item_id']);

        $gr = $grItem->goodsReceipt;
        if (!$gr) {
            throw new DomainException('Data penerimaan barang tidak ditemukan.');
        }

        if ((float) $grItem->qty_missing <= 0) {
            throw new DomainException('Item ini tidak memiliki kuantitas barang yang hilang/selisih.');
        }

        if ($grItem->missing_status === 'confirmed_lost') {
            throw new DomainException('Kehilangan untuk item ini sudah dinyatakan dan disetujui sebelumnya.');
        }

        // Cek grace period (batas hari pencatatan)
        $receiptDate = $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date) : $gr->created_at;
        $graceDays   = config('procurement.loss_grace_days', 3);
        $daysPassed  = now()->startOfDay()->diffInDays($receiptDate->startOfDay());

        if ($daysPassed > $graceDays && !$user->hasAnyRole(['Admin Pusat', 'Admin'])) {
            throw new DomainException("Batas waktu pencatatan kehilangan ({$graceDays} hari) telah terlewati. Diperlukan otorisasi Admin Pusat.");
        }

        // Simpan file bukti secara aman (UUID + strip EXIF metadata)
        $evidencePath = null;
        if (isset($data['evidence']) && $data['evidence'] instanceof UploadedFile) {
            $evidencePath = app(SecureFileUploadService::class)->uploadDocument($data['evidence'], 'transit-losses', 'public');
        } elseif (isset($data['evidence_path'])) {
            $evidencePath = $data['evidence_path'];
        }

        if (empty($evidencePath)) {
            throw new DomainException('Bukti foto/dokumen kehilangan wajib dilampirkan.');
        }

        // Tentukan unit price terkunci dari PO
        $unitPrice = $grItem->purchaseOrderItem?->unit_price ?? $grItem->unit_price ?? 0;
        $lossValue = round((float) $grItem->qty_missing * (float) $unitPrice, 2);

        return DB::transaction(function () use ($gr, $grItem, $data, $userId, $user, $evidencePath, $unitPrice, $lossValue) {
            $transitLoss = TransitLoss::create([
                'goods_receipt_id'       => $gr->id,
                'goods_receipt_item_id'  => $grItem->id,
                'purchase_order_id'      => $gr->purchase_order_id,
                'purchase_order_item_id' => $grItem->purchase_order_item_id,
                'material_id'            => $grItem->material_id,
                'tool_id'                => $grItem->tool_id,
                'qty'                    => $grItem->qty_missing,
                'lost_qty'               => $grItem->qty_missing,
                'unit_price'             => $unitPrice,
                'loss_value'             => $lossValue,
                'status'                 => 'recorded',
                'evidence_path'          => $evidencePath,
                'notes'                  => $data['notes'],
                'recorded_by'            => $userId,
            ]);

            AuditLogService::log(
                'transit_loss_recorded',
                $transitLoss,
                null,
                $transitLoss->toArray(),
                $user
            );

            NotificationHelper::notifyAdminPusat(
                "Laporan Kehilangan Barang Masuk: #{$gr->receipt_number}",
                "Laporan kehilangan barang sebanyak {$grItem->qty_missing} {$grItem->item_unit} diajukan oleh {$user->name} dan memerlukan persetujuan.",
                'approval_needed',
                route('transit-losses.index')
            );

            return $transitLoss;
        });
    }

    /**
     * Setujui laporan kehilangan barang (Aksi "Persetujuan Kehilangan").
     */
    public function approveLoss(TransitLoss $transitLoss, int $approverId): TransitLoss
    {
        $approver = User::findOrFail($approverId);
        $gr = $transitLoss->goodsReceipt;

        if ($transitLoss->status !== 'recorded') {
            throw new DomainException('Hanya laporan kehilangan dengan status recorded yang dapat disetujui.');
        }

        // Pemisahan Peran: approver tidak boleh sama dengan pelapor loss
        if ($approverId === (int) $transitLoss->recorded_by) {
            throw new DomainException('Penyetuju kehilangan harus pengguna yang berbeda dari pembuat laporan kehilangan.');
        }

        // Pemisahan Peran: approver tidak boleh sama dengan penerima atau pembuat GR
        if ($gr) {
            $prohibitedUserIds = array_filter([
                (int) $gr->created_by,
                (int) $gr->confirmed_by,
                (int) $gr->received_by_user_id,
            ]);

            if (in_array($approverId, $prohibitedUserIds, true)) {
                throw new DomainException('Penyetuju kehilangan harus pengguna yang berbeda dari petugas penerima barang / pembuat tanda terima.');
            }
        }

        return DB::transaction(function () use ($transitLoss, $approverId, $approver) {
            $lockedLoss = TransitLoss::lockForUpdate()->findOrFail($transitLoss->id);

            $oldStatus = $lockedLoss->status;
            $lockedLoss->update([
                'status'      => 'claim_filed',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Update status missing pada GR item
            $grItem = $lockedLoss->goodsReceiptItem;
            if ($grItem) {
                $grItem->update(['missing_status' => 'confirmed_lost']);
            }

            // Tambah lost_qty pada purchase_order_items dan perbarui status PO
            if ($lockedLoss->purchase_order_item_id) {
                $poItem = PurchaseOrderItem::lockForUpdate()->find($lockedLoss->purchase_order_item_id);
                if ($poItem) {
                    $poItem->increment('lost_qty', $lockedLoss->qty);
                }

                if ($lockedLoss->purchase_order_id) {
                    app(GoodsReceiptService::class)->updatePurchaseOrderStatus($lockedLoss->purchase_order_id);
                }
            }

            AuditLogService::log(
                'transit_loss_approved',
                $lockedLoss,
                ['status' => $oldStatus],
                ['status' => 'claim_filed', 'approved_by' => $approverId],
                $approver
            );

            // Cek ambang batas kehilangan per vendor
            if ($lockedLoss->purchase_order_id && $lockedLoss->purchaseOrder?->supplier_id) {
                $this->checkVendorLossAlert($lockedLoss->purchaseOrder->supplier_id);
            }

            return $lockedLoss;
        });
    }

    /**
     * Perbarui status klaim kehilangan (claim_filed -> recovered | written_off).
     */
    public function updateClaimStatus(TransitLoss $transitLoss, string $status, ?string $notes = null, ?int $userId = null): TransitLoss
    {
        if (!in_array($status, ['claim_filed', 'recovered', 'written_off'], true)) {
            throw new DomainException('Status klaim tidak valid.');
        }

        $user = $userId ? User::find($userId) : auth()->user();

        return DB::transaction(function () use ($transitLoss, $status, $notes, $user) {
            $locked = TransitLoss::lockForUpdate()->findOrFail($transitLoss->id);
            $oldStatus = $locked->status;

            $updateData = ['status' => $status];
            if ($notes) {
                $updateData['notes'] = ($locked->notes ? $locked->notes . "\n" : '') . "[Update Klaim " . now()->toDateTimeString() . "]: " . $notes;
            }

            $locked->update($updateData);

            AuditLogService::log(
                'transit_loss_claim_status_updated',
                $locked,
                ['status' => $oldStatus],
                ['status' => $status],
                $user
            );

            return $locked;
        });
    }

    /**
     * Hitung tingkat kehilangan vendor selama 30 hari terakhir dan kirim peringatan jika melampaui ambang batas.
     */
    protected function checkVendorLossAlert(int $supplierId): void
    {
        try {
            $supplier = Supplier::find($supplierId);
            if (!$supplier) return;

            $startDate = now()->subDays(30);

            // Total kuantitas dipesan dalam 30 hari
            $totalOrdered = (float) PurchaseOrderItem::whereHas('purchaseOrder', function ($q) use ($supplierId, $startDate) {
                $q->where('supplier_id', $supplierId)
                  ->where('order_date', '>=', $startDate->toDateString());
            })->sum('quantity');

            if ($totalOrdered <= 0) return;

            // Total kuantitas dinyatakan hilang dalam 30 hari
            $totalLost = (float) TransitLoss::whereHas('purchaseOrder', function ($q) use ($supplierId, $startDate) {
                $q->where('supplier_id', $supplierId)
                  ->where('order_date', '>=', $startDate->toDateString());
            })->whereIn('status', ['claim_filed', 'recovered', 'written_off'])
              ->sum('qty');

            $lossPercent = ($totalLost / $totalOrdered) * 100;
            $alertThreshold = (float) config('procurement.loss_alert_percent', 3);

            if ($lossPercent > $alertThreshold) {
                NotificationHelper::notifyPurchasingAdmins(
                    "Peringatan Vendor: Tingkat Kehilangan Tinggi",
                    "Vendor {$supplier->name} memiliki tingkat kehilangan barang {$lossPercent}% dalam 30 hari terakhir (Ambang batas: {$alertThreshold}%). Periksa histori ekspedisi dan kualitas pengiriman.",
                    'warning'
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal menghitung vendor loss alert: " . $e->getMessage());
        }
    }
}
