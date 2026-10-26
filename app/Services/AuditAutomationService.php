<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\User;
use App\Mail\AuditFindingAssignedMail;
use App\Mail\AuditReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuditAutomationService
{
    /**
     * Panggil method ini saat Stock Opname disubmit / disahkan dan memiliki selisih (diskrepansi)
     */
    public function createFromOpnameDiscrepancy($opname, float $discrepancyValue, int $picUserId): AuditFinding
    {
        $absVal = abs($discrepancyValue);
        $risk = $absVal >= 10000000 ? 'Critical' : ($absVal >= 2500000 ? 'High' : 'Medium');

        $finding = AuditFinding::create([
            'finding_code'     => AuditFinding::generateCode(),
            'source_type'      => 'opname_discrepancy',
            'auditable_type'   => get_class($opname),
            'auditable_id'     => $opname->id,
            'stock_opname_id'  => $opname->id,
            'warehouse_id'     => $opname->warehouse_id,
            'title'            => "Diskrepansi Stock Opname #" . ($opname->opname_number ?? $opname->id),
            'description'      => "Ditemukan selisih perhitungan fisik vs sistem pada proses Stock Opname dengan estimasi selisih nilai Rp " . number_format($absVal, 0, ',', '.'),
            'financial_impact' => $absVal,
            'risk_level'       => $risk,
            'pic_id'           => $picUserId,
            'created_by'       => auth()->id() ?: User::value('id'),
            'due_date'         => Carbon::now()->addDays(7),
            'status'           => 'Open',
        ]);

        if ($finding->pic && $finding->pic->email) {
            try {
                Mail::to($finding->pic->email)->send(new AuditFindingAssignedMail($finding));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $finding;
    }

    /**
     * Panggil method ini untuk deviasi PO (Keterlambatan pengiriman / selisih nilai penerimaan PO)
     */
    public function createFromPurchaseOrderIssue($purchaseOrder, string $issueReason, float $impactAmount, int $picUserId): AuditFinding
    {
        $absImpact = abs($impactAmount);
        $risk = $absImpact >= 15000000 ? 'High' : ($absImpact >= 50000000 ? 'Critical' : 'Medium');

        $finding = AuditFinding::create([
            'finding_code'      => AuditFinding::generateCode(),
            'source_type'       => 'po_finance',
            'auditable_type'    => get_class($purchaseOrder),
            'auditable_id'      => $purchaseOrder->id,
            'purchase_order_id' => $purchaseOrder->id,
            'warehouse_id'      => $purchaseOrder->delivery_warehouse_id ?? $purchaseOrder->warehouse_id ?? null,
            'title'             => "Audit Pengadaan PO #" . ($purchaseOrder->po_number ?? $purchaseOrder->id),
            'description'       => $issueReason,
            'financial_impact'  => $absImpact,
            'risk_level'        => $risk,
            'pic_id'            => $picUserId,
            'created_by'        => auth()->id() ?: User::value('id'),
            'due_date'          => Carbon::now()->addDays(5),
            'status'            => 'Open',
        ]);

        if ($finding->pic && $finding->pic->email) {
            try {
                Mail::to($finding->pic->email)->send(new AuditFindingAssignedMail($finding));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $finding;
    }

    /**
     * Signature alternatif sesuai spesifikasi rencana pengembangan (1).md
     */
    public function recordOpnameDiscrepancy(
        int $opnameId,
        int $warehouseId,
        string $materialName,
        float $systemQty,
        float $physicalQty,
        float $unitPrice,
        int $picUserId
    ): ?AuditFinding {
        $diff = $physicalQty - $systemQty;
        if ($diff == 0) {
            return null;
        }

        $financialImpact = abs($diff) * $unitPrice;
        $riskLevel = $financialImpact >= 10000000 ? 'High' : ($financialImpact >= 2500000 ? 'Medium' : 'Low');

        $finding = AuditFinding::create([
            'finding_code'     => 'AUD-OPN-' . now()->format('Ym') . '-' . strtoupper(Str::random(4)),
            'source_type'      => 'inventory_discrepancy',
            'warehouse_id'     => $warehouseId,
            'stock_opname_id'  => $opnameId,
            'title'            => "Diskrepansi Stok: {$materialName}",
            'description'      => "Terdapat selisih stok pada {$materialName}. Sistem: {$systemQty}, Fisik: {$physicalQty} (Selisih: {$diff}).",
            'financial_impact' => $financialImpact,
            'risk_level'       => $riskLevel,
            'pic_id'           => $picUserId,
            'created_by'       => auth()->id() ?: User::value('id'),
            'due_date'         => Carbon::now()->addDays(7)->toDateString(),
            'status'           => 'Open',
        ]);

        if ($finding->pic && $finding->pic->email) {
            try {
                Mail::to($finding->pic->email)->send(new AuditReminderMail($finding, 'NEW_FINDING'));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $finding;
    }

    /**
     * Record PO Delay per spesifikasi rencana pengembangan (1).md
     */
    public function recordPoDelay(
        int $poId,
        string $poNumber,
        string $vendorName,
        float $poTotalAmount,
        int $picUserId,
        int $daysDelayed
    ): AuditFinding {
        $existing = AuditFinding::where('purchase_order_id', $poId)
            ->where('source_type', 'po_delay')
            ->whereIn('status', ['Open', 'In Progress', 'Overdue'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $finding = AuditFinding::create([
            'finding_code'      => 'AUD-PO-' . now()->format('Ym') . '-' . strtoupper(Str::random(4)),
            'source_type'       => 'po_delay',
            'purchase_order_id' => $poId,
            'title'             => "Keterlambatan Pengiriman PO #{$poNumber}",
            'description'       => "PO #{$poNumber} dari vendor {$vendorName} telah melewati batas waktu pengiriman selama {$daysDelayed} hari.",
            'financial_impact'  => $poTotalAmount,
            'risk_level'        => $daysDelayed > 14 ? 'High' : 'Medium',
            'pic_id'            => $picUserId,
            'created_by'        => auth()->id() ?: User::value('id'),
            'due_date'          => Carbon::now()->addDays(5)->toDateString(),
            'status'            => 'Open',
        ]);

        if ($finding->pic && $finding->pic->email) {
            try {
                Mail::to($finding->pic->email)->send(new AuditReminderMail($finding, 'NEW_FINDING'));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $finding;
    }
}
