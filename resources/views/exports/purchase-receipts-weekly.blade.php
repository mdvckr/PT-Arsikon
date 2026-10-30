<table>
    <thead>
        <tr>
            <th colspan="13" style="font-size: 14pt; font-weight: bold; text-align: center;">
                PT. ARSIKON CIPTA KARYA
            </th>
        </tr>
        <tr>
            <th colspan="13" style="font-size: 11pt; font-weight: bold; text-align: center;">
                REKAPITULASI LAPORAN NOTA BELANJA LAPANGAN (KAS OPERASIONAL)
            </th>
        </tr>
        <tr>
            <th colspan="13" style="font-size: 10pt; text-align: center; color: #555555;">
                Proyek: {{ $project ? $project->name : 'Semua Proyek' }} | Periode: {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="13"></th>
        </tr>
        <tr style="background-color: #1E293B; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000000; width: 5; text-align: center;">No</th>
            <th style="border: 1px solid #000000; width: 14; text-align: center;">Tanggal</th>
            <th style="border: 1px solid #000000; width: 18; text-align: center;">No. Nota / Ref</th>
            <th style="border: 1px solid #000000; width: 22; text-align: center;">Proyek</th>
            <th style="border: 1px solid #000000; width: 22; text-align: center;">Toko / Supplier</th>
            <th style="border: 1px solid #000000; width: 40; text-align: center;">Rincian Material / Belanja</th>
            <th style="border: 1px solid #000000; width: 16; text-align: right;">Subtotal Kotor (Rp)</th>
            <th style="border: 1px solid #000000; width: 14; text-align: right;">Diskon (Rp)</th>
            <th style="border: 1px solid #000000; width: 14; text-align: right;">Ongkir (Rp)</th>
            <th style="border: 1px solid #000000; width: 14; text-align: right;">Iuran / Kuli (Rp)</th>
            <th style="border: 1px solid #000000; width: 22; text-align: left;">Keterangan Iuran</th>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">Total Bersih (Rp)</th>
            <th style="border: 1px solid #000000; width: 14; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($receipts as $index => $receipt)
            @php
                $itemLines = [];
                $itemDiscountSum = 0;
                $rawSubtotalSum = 0;
                foreach($receipt->items as $it) {
                    $qtyStr = number_format($it->quantity, 2, ',', '.') + 0;
                    $priceStr = number_format($it->unit_price, 0, ',', '.');
                    $itSubtotal = (float) $it->subtotal;
                    $itemDiscount = (float) ($it->item_discount ?? 0);
                    $itemDiscountSum += $itemDiscount;
                    $rawSubtotalSum += ((float)$it->quantity * (float)$it->unit_price);
                    
                    $line = $it->item_name . ' (' . $qtyStr . ' ' . ($it->unit_name ?? 'Bh') . ' @ Rp ' . $priceStr;
                    if ($itemDiscount > 0) {
                        $line .= ' - disc Rp ' . number_format($itemDiscount, 0, ',', '.');
                    }
                    $line .= ')';
                    $itemLines[] = $line;
                }
                $rincianText = implode("; \n", $itemLines);
                if (empty($rincianText)) {
                    $rincianText = $receipt->notes ?? '-';
                }

                $kotor = (float) ($receipt->items_subtotal > 0 ? $receipt->items_subtotal : ($rawSubtotalSum > 0 ? $rawSubtotalSum : $receipt->total_amount));
                $diskon = (float) (($receipt->global_discount ?? 0) + $itemDiscountSum);
                $ongkir = (float) ($receipt->shipping_fee ?? 0);
                $iuran  = (float) ($receipt->additional_fee ?? 0);
                $bersih = (float) $receipt->total_amount;
            @endphp
            <tr>
                <td style="border: 1px solid #D1D5DB; text-align: center; vertical-align: top;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: center; vertical-align: top;">{{ $receipt->receipt_date ? $receipt->receipt_date->format('d/m/Y') : '-' }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: center; vertical-align: top;">{{ $receipt->receipt_number ?? ('NOTA-' . $receipt->id) }}</td>
                <td style="border: 1px solid #D1D5DB; vertical-align: top;">{{ $receipt->project?->name ?? $receipt->project_name ?? '-' }}</td>
                <td style="border: 1px solid #D1D5DB; vertical-align: top;">{{ $receipt->supplier?->name ?? $receipt->supplier_name ?? 'Toko / Pembelian Langsung' }}</td>
                <td style="border: 1px solid #D1D5DB; vertical-align: top; white-space: pre-line;">{{ $rincianText }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: right; vertical-align: top;">{{ $kotor }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: right; vertical-align: top;">{{ $diskon }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: right; vertical-align: top;">{{ $ongkir }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: right; vertical-align: top;">{{ $iuran }}</td>
                <td style="border: 1px solid #D1D5DB; vertical-align: top;">{{ $receipt->additional_fee_notes ?? '-' }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: right; vertical-align: top; font-weight: bold;">{{ $bersih }}</td>
                <td style="border: 1px solid #D1D5DB; text-align: center; vertical-align: top;">{{ strtoupper($receipt->status_label) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" style="border: 1px solid #D1D5DB; text-align: center; padding: 12px; color: #6B7280;">
                    Tidak ada data nota harian pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #F1F5F9; font-weight: bold;">
            <td colspan="6" style="border: 1px solid #000000; text-align: right; font-weight: bold;">TOTAL REKAPITULASI:</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $subtotalKotor }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $totalDiskon }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $totalOngkir }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $totalIuran }}</td>
            <td style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold; background-color: #FEF3C7;">{{ $grandTotal }}</td>
            <td style="border: 1px solid #000000;"></td>
        </tr>
        <tr><td colspan="13"></td></tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">Dibuat Oleh,</td>
            <td colspan="4" style="text-align: center; font-weight: bold;">Diperiksa Oleh,</td>
            <td colspan="6" style="text-align: center; font-weight: bold;">Disetujui Oleh,</td>
        </tr>
        <tr><td colspan="13" style="height: 40px;"></td></tr>
        <tr>
            <td colspan="3" style="text-align: center;">( Admin Lapangan / PO )</td>
            <td colspan="4" style="text-align: center;">( Project Manager / Site Manager )</td>
            <td colspan="6" style="text-align: center;">( Keuangan / Direktur )</td>
        </tr>
    </tfoot>
</table>
