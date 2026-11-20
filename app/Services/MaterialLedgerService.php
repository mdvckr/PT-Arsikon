<?php

namespace App\Services;

use App\Models\MaterialLedger;
use App\Models\MaterialLedgerEntry;
use Illuminate\Support\Collection;

class MaterialLedgerService {
    /**
     * Hitung baris tabel lengkap dengan Running Total (Kumulatif Rp & Kumulatif Volume)
     * persis seperti rumus lembar kerja Excel PT Arsikon.
     */
    public function getCalculatedEntries(MaterialLedger $ledger): Collection
    {
        $entries = $ledger->entries;

        $runningAmount = 0.0;
        $runningVolume = 0.0;
        $runningDepositVol = 0.0;

        return $entries->map(function (MaterialLedgerEntry $entry) use (&$runningAmount, &$runningVolume, &$runningDepositVol) {
            $deliveryVol = (float) $entry->delivery_volume;
            $unitPrice   = (float) $entry->unit_price;
            $depositVol  = (float) ($entry->deposit_volume ?? 0);

            $amountUsed = $deliveryVol * $unitPrice;
            $runningAmount += $amountUsed;
            $runningVolume += $deliveryVol;
            $runningDepositVol += $depositVol;

            // Properti kalkulasi dinamis untuk Blade & Excel
            $entry->calculated_amount_used = $amountUsed;
            $entry->running_total_amount   = $runningAmount; // Kolom SISA DEPOSIT / Kumulatif Rp
            $entry->running_total_volume   = $runningVolume; // Kolom TOTAL VOLUME (5 -> 11 -> 16 -> 21... m3)
            $entry->remaining_deposit_vol  = $runningDepositVol - $runningVolume;

            return $entry;
        });
    }

    /**
     * Sinkronisasi ringkasan total di Header Kartu Rekap Catat Material
     */
    public function syncLedgerSummary(MaterialLedger $ledger): void
    {
        $totalDepositVol   = (float) $ledger->entries()->sum('deposit_volume');
        $totalDeliveredVol = (float) $ledger->entries()->sum('delivery_volume');
        $totalUsedAmount   = (float) $ledger->entries()->sum('amount_used');

        $ledger->update([
            'total_deposit_vol'     => $totalDepositVol,
            'total_delivered_vol'   => $totalDeliveredVol,
            'remaining_deposit_vol' => $totalDepositVol - $totalDeliveredVol,
            'total_used_amount'     => $totalUsedAmount,
        ]);
    }
}
