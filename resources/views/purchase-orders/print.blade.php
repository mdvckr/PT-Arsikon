<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; background: #fff; color: #000; padding: 18px 24px; }
        .print-controls { position: fixed; top: 14px; right: 18px; display: flex; gap: 8px; z-index: 9999; }
        .btn-print { background: #000000ff; color: #fff; border: none; border-radius: 5px; padding: 8px 18px; font-size: 12px; cursor: pointer; font-weight: bold; }
        .btn-close { background: #64748b; color: #fff; border: none; border-radius: 5px; padding: 8px 14px; font-size: 12px; cursor: pointer; }
        .po-wrapper { border: 1.5px solid #f5a623; max-width: 760px; margin: 0 auto; }
        .po-header { display: flex; align-items: stretch; border-bottom: 1.5px solid #f5a623; }
        .header-logo-col { width: 120px; min-width: 120px; border-right: 1.5px solid #f5a623; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 8px; }
        .header-logo-col img { max-width: 70px; max-height: 70px; object-fit: contain; }
        .company-name-under-logo { font-size: 7.5px; text-align: center; font-weight: bold; color: #222; margin-top: 4px; line-height: 1.3; }
        .header-info-col { flex: 1; border-right: 1.5px solid #f5a623; display: flex; flex-direction: column; justify-content: center; padding: 8px 12px; font-size: 10px; line-height: 1.6; }
        .header-info-col .company-main { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .header-title-col { width: 200px; min-width: 200px; background: #f5a623; display: flex; align-items: center; justify-content: center; padding: 12px 10px; }
        .doc-title { font-size: 24px; font-weight: 900; color: #ffffffff; text-align: center; letter-spacing: 1px; text-transform: uppercase; line-height: 1.15; }
        .po-fields { border-bottom: 1.5px solid #f5a623; padding: 7px 12px; display: flex; align-items: flex-start; }
        .po-fields-left { flex: 1; padding-right: 12px; }
        .po-fields-right { min-width: 210px; display: flex; flex-direction: column; gap: 5px; }
        .field-row { display: flex; align-items: baseline; margin-bottom: 5px; font-size: 11px; }
        .field-row:last-child { margin-bottom: 0; }
        .field-label { font-weight: bold; min-width: 56px; text-transform: uppercase; font-size: 10.5px; }
        .field-colon { margin: 0 5px; font-weight: bold; }
        .field-val { border-bottom: 1px solid #f5a623; flex: 1; padding-bottom: 1px; min-width: 120px; }
        .tgl-row { display: flex; align-items: center; gap: 5px; font-size: 11px; }
        .tgl-label { font-weight: bold; font-size: 10.5px; text-transform: uppercase; min-width: 62px; text-align: right; }
        .tgl-val { font-size: 11px; font-weight: 600; }
        .no-po-row { display: flex; align-items: center; gap: 5px; font-size: 11px; }
        .no-po-label { font-weight: bold; font-size: 10.5px; text-transform: uppercase; min-width: 62px; text-align: right; }
        .no-po-box-val { border: 1px solid #888; padding: 2px 10px; font-size: 13px; font-weight: 900; color: #000; min-width: 90px; text-align: center; letter-spacing: 1px; }
        /* === TABEL ITEM === */
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table th { background: #fff; color: #f5a623; text-align: center; font-size: 10.5px; font-weight: bold; text-transform: uppercase; padding: 5px 6px; border: 1px solid #f5a623; vertical-align: middle; }
        .item-table td { border: 1px solid #f5a623; padding: 5px 7px; vertical-align: top; font-size: 11px; }
        .col-no { text-align: center; width: 32px; }
        .col-mat { min-width: 200px; }
        .col-qty { text-align: center; width: 90px; }
        .col-hsat { width: 140px; }
        .col-ket { min-width: 110px; font-size: 10.5px; }
        .empty-row td { height: 26px; }
        .po-footer-notes { border-top: 1.5px solid #f5a623; padding: 7px 12px; font-size: 11px; line-height: 1.8; }
        .dest-section { border-top: 1.5px solid #f5a623; padding: 6px 12px; font-size: 11px; line-height: 1.9; }
        .dest-row { display: flex; align-items: baseline; gap: 4px; }
        .dest-label { font-weight: bold; min-width: 90px; }
        .sign-section { border-top: 1.5px solid #f5a623; display: flex; }
        .sign-box { flex: 1; padding: 8px 12px 8px; text-align: center; border-right: 1px solid #f5a623; }
        .sign-box:last-child { border-right: none; }
        .sign-label-top { font-size: 11px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .sign-space { height: 64px; }
        .sign-name { border-top: 1px solid #f5a623; padding-top: 3px; font-size: 10.5px; color: #000000ff; }
        @media print {
            .print-controls { display: none; }
            body { padding: 6px 10px; }
            @page { size: A4 portrait; margin: 8mm 10mm; }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
        <button class="btn-close" onclick="window.history.back()">&#10005; Tutup</button>
    </div>

    @php
        $supplier    = $po->display_supplier_name;
        $tglOrder    = $po->order_date        ? $po->order_date->format('d/m/Y')          : '-';
        $tglKirim    = $po->expected_delivery ? $po->expected_delivery->format('d/m/Y')   : null;
        $proyek      = $po->deliveryWarehouse?->project?->name ?? $po->deliveryWarehouse?->name ?? '-';
        $items       = $po->items;
        $minRows     = 10;
        $emptyRows   = max(0, $minRows - $items->count());
        $grandTotal  = (float) $po->total_amount;
    @endphp

    <div class="po-wrapper">

        {{-- ===== HEADER ===== --}}
        <div class="po-header">
            <div class="header-logo-col">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo PT Arsikon">
                <div class="company-name-under-logo">PT. ARSIKON<br>CIPTA KARYA</div>
            </div>
            <div class="header-info-col">
                <div class="company-main">PT. ARSIKON CIPTA KARYA</div>
                <div>Alamat : Jl. Pasir Luhur, No.1,</div>
                <div>Taraman Sinduharjo, Ngaglik, Sleman</div>
            </div>
            <div class="header-title-col">
                <div class="doc-title">PURCHASE<br>ORDER</div>
            </div>
        </div>

        {{-- ===== FIELDS ===== --}}
        <div class="po-fields">
            <div class="po-fields-left">
                <div class="field-row">
                    <span class="field-label">Kepada</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $supplier }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Proyek</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $proyek }}</span>
                </div>
            </div>
            <div class="po-fields-right">
                <div class="tgl-row">
                    <span class="tgl-label">Tanggal</span>
                    <span style="font-weight:bold;">:</span>
                    <span class="tgl-val">{{ $tglOrder }}</span>
                </div>
                <div class="no-po-row">
                    <span class="no-po-label">No. PO</span>
                    <span style="font-weight:bold;">:</span>
                    <span class="no-po-box-val">{{ $po->po_number }}</span>
                </div>
            </div>
        </div>

        {{-- ===== TABEL ITEM ===== --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-mat">JENIS MATRIAL</th>
                    <th class="col-qty">JUMLAH</th>
                    <th class="col-hsat">HARGA<br>SATUAN</th>
                    <th class="col-ket">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                <tr>
                    <td class="col-no">{{ $idx + 1 }}.</td>
                    <td class="col-mat">
                        {{ $item->displayName() }}
                        @if($item->isCustom())
                            <br><small style="color:#92400e;">(Custom)</small>
                        @endif
                    </td>
                    <td class="col-qty">
                        {{ format_quantity($item->quantity) }}
                        {{ $item->displayUnit() }}
                    </td>
                    <td class="col-hsat">
                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="col-ket">{{ $item->notes ?? '' }}</td>
                </tr>
                @endforeach
                @for($e = 0; $e < $emptyRows; $e++)
                <tr class="empty-row">
                    <td class="col-no"></td>
                    <td class="col-mat"></td>
                    <td class="col-qty"></td>
                    <td class="col-hsat"></td>
                    <td class="col-ket"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        {{-- ===== CATATAN / FOOTER NOTES ===== --}}
        @php
            $cpText = !empty($po->contact_person) ? $po->contact_person : ($po->creator?->name ?? null);
        @endphp
        @if($po->notes || $tglKirim || $cpText)
        <div class="po-footer-notes">
            @if($tglKirim)
                <div>Dikirim {{ $tglKirim }}{{ $po->terms ? ', '.$po->terms : '' }}</div>
            @endif
            @if($po->notes)
                <div style="margin-top:2px; white-space: pre-line;">{{ $po->notes }}</div>
            @endif
            @if($cpText)
                <div style="margin-top:4px;">CP : {{ $cpText }}</div>
            @endif
        </div>
        @endif

        {{-- ===== DIKIRIM KE + ALAMAT ===== --}}
        @if($po->deliveryWarehouse)
        <div class="dest-section">
            <div class="dest-row">
                <span class="dest-label">Dikirim Ke</span>
                <span>:</span>
                <span>{{ $po->deliveryWarehouse->project?->name ?? $po->deliveryWarehouse->name }}</span>
            </div>
            <div class="dest-row">
                <span class="dest-label">Alamat</span>
                <span>:</span>
                <span>{{ $po->deliveryWarehouse->project?->name ? $po->deliveryWarehouse->name.', '.$po->deliveryWarehouse->project->name : $po->deliveryWarehouse->name }}</span>
            </div>
        </div>
        @endif

        {{-- ===== TANDA TANGAN ===== --}}
        <div class="sign-section">
            <div class="sign-box">
                <div class="sign-label-top">Mengetahui,</div>
                <div class="sign-space">
                    @if($po->approver)
                        <div style="font-size:10px;margin-top:8px;color:#333;">{{ $po->approver->name }}</div>
                    @endif
                </div>
                <div class="sign-name">Direksi</div>
            </div>
            <div class="sign-box">
                <div class="sign-label-top">Pemesan,</div>
                <div class="sign-space">
                    @if($po->creator)
                        <div style="font-size:10px;margin-top:8px;color:#333;">{{ $po->creator->name }}</div>
                    @endif
                </div>
                <div class="sign-name">Purchasing</div>
            </div>
        </div>

    </div>

</body>
</html>