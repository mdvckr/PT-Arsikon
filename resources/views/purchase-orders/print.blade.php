<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hidePrice ? 'PO Lapangan (Tanpa Harga)' : 'Purchase Order' }} - {{ $po->po_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; background: #fff; color: #000; padding: 18px 24px; }
        .print-controls { position: fixed; top: 14px; right: 18px; display: flex; gap: 8px; align-items: center; z-index: 9999; background: #ffffff; padding: 6px 10px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; }
        .btn-print { background: #0f172a; color: #fff; border: none; border-radius: 5px; padding: 8px 18px; font-size: 12px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print:hover { background: #1e293b; }
        .btn-close { background: #64748b; color: #fff; border: none; border-radius: 5px; padding: 8px 14px; font-size: 12px; cursor: pointer; }
        .btn-close:hover { background: #475569; }
        .po-wrapper { border: 1.5px solid #f5a623; max-width: 760px; margin: 0 auto; position: relative; }
        .po-header { display: flex; align-items: stretch; border-bottom: 1.5px solid #f5a623; }
        .header-logo-col { width: 120px; min-width: 120px; border-right: 1.5px solid #f5a623; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 8px; }
        .header-logo-col img { max-width: 70px; max-height: 70px; object-fit: contain; }
        .company-name-under-logo { font-size: 7.5px; text-align: center; font-weight: bold; color: #222; margin-top: 4px; line-height: 1.3; }
        .header-info-col { flex: 1; border-right: 1.5px solid #f5a623; display: flex; flex-direction: column; justify-content: center; padding: 8px 12px; font-size: 10px; line-height: 1.6; }
        .header-info-col .company-main { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .header-title-col { width: 200px; min-width: 200px; background: #f5a623; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 10px; text-align: center; }
        .header-title-col.header-title-blind { background: #334155; }
        .doc-title { font-size: 22px; font-weight: 900; color: #ffffff; text-align: center; letter-spacing: 1px; text-transform: uppercase; line-height: 1.15; }
        .doc-subtitle-blind { font-size: 8.5px; font-weight: 800; color: #f1f5f9; background: rgba(0,0,0,0.25); padding: 3px 6px; border-radius: 4px; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px dashed rgba(255,255,255,0.4); }
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
        .col-hsat { width: 130px; }
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
        .sign-name { border-top: 1px solid #f5a623; padding-top: 3px; font-size: 10.5px; color: #000; }

        .blind-watermark {
            display: none;
        }

        @media print {
            .print-controls { display: none !important; }
            body { padding: 4px 8px; }
            @page { size: A4 portrait; margin: 8mm 10mm; }
        }
    </style>
</head>
<body>

    @php
        $hidePrice = $hidePrice ?? false;
        $userCanViewPrice = $userCanViewPrice ?? (auth()->user() ? auth()->user()->canViewPoPrice() : false);
    @endphp

    {{-- Toolbar Kontrol Cetak --}}
    <div class="print-controls">
        @if($userCanViewPrice)
            <div style="display:inline-flex;background:#f1f5f9;border-radius:6px;border:1px solid #cbd5e1;padding:2px;align-items:center;margin-right:4px;">
                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po->id, 'mode' => 'official']) }}" 
                   style="text-decoration:none;padding:5px 12px;font-size:11.5px;font-weight:700;border-radius:4px;display:inline-flex;align-items:center;gap:5px;transition:all 0.15s;{{ !$hidePrice ? 'background:#0f766e;color:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);' : 'color:#475569;' }}">
                    <i class="fas fa-file-invoice-dollar"></i> Cetak Resmi (Dengan Harga)
                </a>
                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po->id, 'mode' => 'blind']) }}" 
                   style="text-decoration:none;padding:5px 12px;font-size:11.5px;font-weight:700;border-radius:4px;display:inline-flex;align-items:center;gap:5px;transition:all 0.15s;{{ $hidePrice ? 'background:#334155;color:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);' : 'color:#475569;' }}">
                    <i class="fas fa-file-shield"></i> Cetak Lapangan (Tanpa Harga / Rp 0)
                </a>
            </div>
        @else
            <span style="background:#fee2e2;color:#991b1b;padding:5px 10px;border-radius:5px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #fca5a5;">
                <i class="fas fa-shield-halved"></i> Mode Lapangan (Harga Dirahasiakan)
            </span>
        @endif

        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Dokumen
        </button>
        <button class="btn-close" onclick="window.history.back()">
            <i class="fas fa-xmark"></i> Tutup
        </button>
    </div>

    @php
        $supplier     = $po->display_supplier_name;
        $tglOrder     = $po->order_date        ? $po->order_date->format('d/m/Y')          : '-';
        $tglKirim     = $po->expected_delivery ? $po->expected_delivery->format('d/m/Y')   : null;
        $projectModel = $po->deliveryWarehouse?->project 
            ?? $po->items->first(fn($i) => $i->materialRequestItem?->materialRequest?->toWarehouse?->project)?->materialRequestItem?->materialRequest?->toWarehouse?->project
            ?? $po->procurementRequest?->project;
        $proyek       = $projectModel?->name ?? $po->deliveryWarehouse?->name ?? '-';
        $alamatProyek = $po->deliveryWarehouse?->address 
            ?: ($projectModel?->location ?: ($po->deliveryWarehouse?->name ?? '-'));
        $items        = $po->items;
        $minRows      = 6;
        $emptyRows    = max(0, $minRows - $items->count());
        $grandTotal   = $hidePrice ? 0.0 : (float) $po->total_amount;
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
            <div class="header-title-col {{ $hidePrice ? 'header-title-blind' : '' }}">
                <div class="doc-title">PURCHASE<br>ORDER</div>
                @if($hidePrice)
                    <div class="doc-subtitle-blind">
                        <i class="fas fa-shield-alt"></i> PO LAPANGAN / GUDANG<br>(TANPA HARGA / Rp 0)
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== FIELDS ===== --}}
        <div class="po-fields">
            <div class="po-fields-left">
                <div class="field-row">
                    <span class="field-label">Kepada</span>
                    <span class="field-colon">:</span>
                    <span class="field-val"><strong>{{ $supplier }}</strong></span>
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
                    <th class="col-mat">JENIS MATERIAL & SPESIFIKASI</th>
                    <th class="col-qty">JUMLAH</th>
                    <th class="col-hsat">HARGA<br>SATUAN</th>
                    <th style="width: 110px;">SUBTOTAL</th>
                    <th class="col-ket">CATATAN / KET</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                @php
                    $rawSub = (float) $item->subtotal > 0 ? (float) $item->subtotal : ((float) $item->quantity * (float) $item->unit_price);
                    $sub = $hidePrice ? 0.0 : $rawSub;
                    $unitPrice = $hidePrice ? 0.0 : (float) $item->unit_price;
                @endphp
                <tr>
                    <td class="col-no">{{ $idx + 1 }}.</td>
                    <td class="col-mat">
                        <strong>{{ $item->displayName() }}</strong>
                        @if($item->isCustom())
                            <br><small style="color:#b45309;font-weight:bold;">(Barang Khusus / Non-Katalog)</small>
                        @endif
                    </td>
                    <td class="col-qty">
                        <strong>{{ format_quantity($item->quantity) }}</strong>
                        {{ $item->displayUnit() }}
                    </td>
                    <td class="col-hsat" style="text-align: right; color: {{ $hidePrice ? '#64748b' : '#000000' }};">
                        Rp {{ number_format($unitPrice, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: 600; color: {{ $hidePrice ? '#64748b' : '#000000' }};">
                        Rp {{ number_format($sub, 0, ',', '.') }}
                    </td>
                    <td class="col-ket">{{ $item->notes ?? '-' }}</td>
                </tr>
                @endforeach
                @for($e = 0; $e < $emptyRows; $e++)
                <tr class="empty-row">
                    <td class="col-no"></td>
                    <td class="col-mat"></td>
                    <td class="col-qty"></td>
                    <td class="col-hsat"></td>
                    <td></td>
                    <td class="col-ket"></td>
                </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr style="background:#fffaf0;">
                    <td colspan="4" style="text-align: right; font-weight: bold; padding: 6px 8px; border: 1px solid #f5a623;">TOTAL :</td>
                    <td style="text-align: right; font-weight: 900; border: 1px solid #f5a623; color: {{ $hidePrice ? '#64748b' : '#000000' }};">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </td>
                    <td style="border: 1px solid #f5a623;"></td>
                </tr>
            </tfoot>
        </table>

        {{-- ===== CATATAN / FOOTER NOTES ===== --}}
        @php
            $cpText = !empty($po->contact_person) ? $po->contact_person : ($po->creator?->name ?? null);
        @endphp
        @if($po->notes || $tglKirim || $cpText || $po->terms || $hidePrice)
        <div class="po-footer-notes">
            @if($tglKirim)
                <div><strong>Estimasi Tiba :</strong> {{ $tglKirim }}{{ $po->terms ? ' | Ketentuan: '.$po->terms : '' }}</div>
            @elseif($po->terms)
                <div><strong>Ketentuan :</strong> {{ $po->terms }}</div>
            @endif
            @if($po->notes)
                <div style="margin-top:2px; white-space: pre-line;"><strong>Catatan :</strong> {{ $po->notes }}</div>
            @endif
            @if($cpText)
                <div style="margin-top:3px;"><strong>Contact Person (CP) :</strong> {{ $cpText }}</div>
            @endif
            @if($hidePrice)
                <div style="margin-top:4px;padding:3px 6px;background:#f8fafc;border:1px dashed #94a3b8;font-size:10px;color:#334155;">
                    <i class="fas fa-shield-alt text-slate-500"></i> <strong>Catatan Penerimaan:</strong> Dokumen PO ini dicetak khusus untuk bagian penerimaan barang di proyek/gudang. Seluruh nominal harga beli dirahasiakan (Rp 0).
                </div>
            @endif
        </div>
        @endif

        {{-- ===== DIKIRIM KE + ALAMAT PENGIRIMAN ===== --}}
        <div class="dest-section">
            <div class="dest-row">
                <span class="dest-label">Alamat Pengiriman</span>
                <span class="field-colon">:</span>
                <span>
                    <strong>{{ $projectModel?->name ?? $po->deliveryWarehouse?->name ?? '-' }}</strong>
                    @if($po->deliveryWarehouse && $projectModel && $po->deliveryWarehouse->name !== $projectModel->name)
                        ({{ $po->deliveryWarehouse->name }})
                    @endif
                </span>
            </div>
            <div class="dest-row">
                <span class="dest-label">Lokasi / Detail</span>
                <span class="field-colon">:</span>
                <span>{{ $alamatProyek }}</span>
            </div>
        </div>

        {{-- ===== TANDA TANGAN ===== --}}
        <div class="sign-section">
            <div class="sign-box">
                <div class="sign-label-top">Mengetahui,</div>
                <div class="sign-space">
                    @if($po->approver)
                        <div style="font-size:10px;margin-top:40px;color:#333;"><strong>{{ $po->approver->name }}</strong></div>
                    @endif
                </div>
                <div class="sign-name">Direksi</div>
            </div>
            <div class="sign-box">
                <div class="sign-label-top">Pemesan,</div>
                <div class="sign-space">
                    @if($po->creator)
                        <div style="font-size:10px;margin-top:40px;color:#333;"><strong>{{ $po->creator->name }}</strong></div>
                    @endif
                </div>
                <div class="sign-name">Purchasing</div>
            </div>
        </div>

    </div>

</body>
</html>