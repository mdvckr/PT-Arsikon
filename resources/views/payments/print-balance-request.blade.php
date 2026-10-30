<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PENGAJUAN KAS LAPANGAN - {{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; background: #fff; color: #000; padding: 20px 24px; }
        .print-controls { position: fixed; top: 14px; right: 18px; display: flex; gap: 8px; align-items: center; z-index: 9999; background: #ffffff; padding: 6px 12px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; }
        .btn-print { background: #0f172a; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 12px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print:hover { background: #1e293b; }
        .btn-close { background: #64748b; color: #fff; border: none; border-radius: 6px; padding: 8px 14px; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-close:hover { background: #475569; }

        .doc-wrapper { border: 1.5px solid #ea580c; max-width: 800px; margin: 0 auto; position: relative; background: #fff; }
        .doc-header { display: flex; align-items: stretch; border-bottom: 1.5px solid #ea580c; }
        .header-logo-col { width: 125px; min-width: 125px; border-right: 1.5px solid #ea580c; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 8px; }
        .header-logo-col img { max-width: 75px; max-height: 75px; object-fit: contain; }
        .company-name-under-logo { font-size: 8px; text-align: center; font-weight: bold; color: #222; margin-top: 4px; line-height: 1.2; text-transform: uppercase; }

        .header-info-col { flex: 1; border-right: 1.5px solid #ea580c; display: flex; flex-direction: column; justify-content: center; padding: 10px 14px; font-size: 10.5px; line-height: 1.5; }
        .header-info-col .company-main { font-weight: bold; font-size: 12px; color: #0f172a; margin-bottom: 3px; letter-spacing: 0.3px; }
        .header-info-col .company-sub { color: #475569; font-size: 10px; }

        .header-title-col { width: 240px; min-width: 240px; background: #ea580c; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 10px; text-align: center; color: #ffffff; }
        .doc-title { font-size: 17px; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.2; text-align: center; }
        .doc-subtitle { font-size: 9.5px; font-weight: 700; background: rgba(0,0,0,0.2); padding: 3px 8px; border-radius: 4px; margin-top: 5px; letter-spacing: 0.5px; text-transform: uppercase; }

        .doc-fields { border-bottom: 1.5px solid #ea580c; padding: 10px 14px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; background: #fffaf5; }
        .fields-left { flex: 1; display: flex; flex-direction: column; gap: 5px; }
        .fields-right { min-width: 260px; display: flex; flex-direction: column; gap: 5px; }
        .field-row { display: flex; align-items: baseline; font-size: 11px; }
        .field-label { font-weight: bold; min-width: 90px; text-transform: uppercase; font-size: 10px; color: #334155; }
        .field-colon { margin: 0 6px; font-weight: bold; }
        .field-val { flex: 1; color: #0f172a; font-weight: 600; }

        /* Tabel Rincian */
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table th { background: #fff; color: #c2410c; text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 6px 8px; border: 1px solid #ea580c; vertical-align: middle; }
        .item-table td { border: 1px solid #ea580c; padding: 6px 8px; vertical-align: top; font-size: 11px; }
        .col-no { text-align: center; width: 34px; font-weight: 600; }
        .col-name { min-width: 220px; }
        .col-qty { text-align: center; width: 75px; }
        .col-unit { text-align: center; width: 65px; }
        .col-price { text-align: right; width: 120px; white-space: nowrap; }
        .col-total { text-align: right; width: 140px; white-space: nowrap; font-weight: 700; }

        .total-row td { background: #fffaf5; font-size: 11.5px; padding: 8px; font-weight: 800; border: 1px solid #ea580c; }

        .purpose-section { border-top: 1.5px solid #ea580c; padding: 8px 14px; font-size: 11px; line-height: 1.5; background: #ffffff; }
        .purpose-label { font-weight: bold; text-transform: uppercase; font-size: 10px; color: #334155; margin-bottom: 2px; }

        /* Tanda Tangan */
        .sign-section { border-top: 1.5px solid #ea580c; display: flex; }
        .sign-box { flex: 1; padding: 10px 12px; text-align: center; border-right: 1px solid #ea580c; display: flex; flex-direction: column; justify-content: space-between; min-height: 130px; }
        .sign-box:last-child { border-right: none; }
        .sign-label-top { font-size: 10.5px; font-weight: bold; text-transform: uppercase; color: #0f172a; margin-bottom: 4px; }
        .sign-role { font-size: 9.5px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .sign-space { height: 60px; display: flex; align-items: flex-end; justify-content: center; }
        .sign-name { border-top: 1px solid #ea580c; padding-top: 4px; font-size: 10.5px; font-weight: 700; color: #000; width: 85%; margin: 0 auto; }

        @media print {
            body { padding: 0; }
            .print-controls { display: none !important; }
            .doc-wrapper { border: 1.5px solid #000; width: 100%; max-width: 100%; }
            .doc-header, .doc-fields, .item-table th, .item-table td, .total-row td, .purpose-section, .sign-section, .sign-box, .sign-name {
                border-color: #000 !important;
            }
            .header-title-col { background: #f1f5f9 !important; color: #000 !important; border-left: 1.5px solid #000; }
            .doc-title { color: #000 !important; }
            .doc-subtitle { background: #e2e8f0 !important; color: #000 !important; border: 1px solid #000; }
            .item-table th { color: #000 !important; background: #f8fafc !important; }
            .doc-fields, .total-row td { background: #fff !important; }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> Cetak Dokumen
        </button>
        <a href="{{ route('payments.index') }}" class="btn-close">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="doc-wrapper">
        {{-- Header Dokumen --}}
        <div class="doc-header">
            <div class="header-logo-col">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo PT Arsikon" onerror="this.style.display='none'">
                <div class="company-name-under-logo">PT ARSIKON CIPTA KARYA</div>
            </div>
            <div class="header-info-col">
                <div class="company-main">PT ARSIKON CIPTA KARYA</div>
                <div class="company-sub">General Contractor, Civil Works & Structural Engineering</div>
                <div class="company-sub">Formulir Resmi Pengajuan Operasional Lapangan (Petty Cash)</div>
            </div>
            <div class="header-title-col">
                <div class="doc-title">PENGAJUAN KAS LAPANGAN</div>
                <div class="doc-subtitle">PETTY CASH PROYEK</div>
            </div>
        </div>

        {{-- Metadata Dokumen --}}
        <div class="doc-fields">
            <div class="fields-left">
                <div class="field-row">
                    <span class="field-label">Proyek</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $balanceRequest->project?->name ?? '-' }} (Kode: {{ $balanceRequest->project?->code ?? '-' }})</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Pemohon</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin PO Proyek') }}</span>
                </div>
            </div>

            <div class="fields-right">
                <div class="field-row">
                    <span class="field-label">No. Pengajuan</span>
                    <span class="field-colon">:</span>
                    <span class="field-val" style="font-family:monospace;font-size:12px;font-weight:bold;">
                        {{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->created_at->format('Ymd') . '-' . str_pad($balanceRequest->id, 4, '0', STR_PAD_LEFT)) }}
                    </span>
                </div>
                <div class="field-row">
                    <span class="field-label">Tgl. Pengajuan</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">
                        {{ $balanceRequest->request_date ? $balanceRequest->request_date->translatedFormat('d F Y') : $balanceRequest->created_at->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div class="field-row">
                    <span class="field-label">Status</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">
                        {{ $balanceRequest->status_label }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Kebutuhan Kas Lapangan --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-name">Nama Barang</th>
                    <th class="col-qty">Kuantitas</th>
                    <th class="col-unit">Satuan</th>
                    <th class="col-price">Estimasi Harga (Rp)</th>
                    <th class="col-total">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @if($balanceRequest->items && $balanceRequest->items->count() > 0)
                    @foreach($balanceRequest->items as $idx => $item)
                        <tr>
                            <td class="col-no">{{ $idx + 1 }}</td>
                            <td class="col-name"><strong>{{ $item->item_name }}</strong></td>
                            <td class="col-qty">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td class="col-unit">{{ $item->unit ?: '-' }}</td>
                            <td class="col-price">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="col-total">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    {{-- Fallback jika data historis diajukan tanpa rincian item --}}
                    <tr>
                        <td class="col-no">1</td>
                        <td class="col-name"><strong>{{ $balanceRequest->purpose }}</strong></td>
                        <td class="col-qty">1,00</td>
                        <td class="col-unit">Paket</td>
                        <td class="col-price">Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}</td>
                        <td class="col-total">Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;text-transform:uppercase;">
                        TOTAL PENGAJUAN KAS LAPANGAN:
                    </td>
                    <td class="col-total" style="color:#c2410c;font-size:12px;">
                        Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        @if($balanceRequest->status === 'rejected' && $balanceRequest->rejection_reason)
            <div style="border-top:1.5px solid #ef4444;padding:8px 14px;background:#fef2f2;color:#dc2626;font-size:10.5px;">
                <strong>Alasan Penolakan:</strong> {{ $balanceRequest->rejection_reason }}
            </div>
        @endif

{{-- 3 Kolom Tanda Tangan Resmi --}}
        <div class="sign-section">
            {{-- 1. Pemohon --}}
            <div class="sign-box">
                <div>
                    <div class="sign-label-top">Yang Mengajukan</div>
                    <div class="sign-role">Admin PO Proyek / Lapangan</div>
                </div>
                <div class="sign-space">
                    {{-- Ruang tanda tangan fisik --}}
                </div>
                <div class="sign-name">
                    ( {{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin PO Proyek') }} )
                </div>
            </div>

            {{-- 2. Mengetahui --}}
            <div class="sign-box">
                <div>
                    <div class="sign-label-top">Mengetahui / Diperiksa</div>
                    <div class="sign-role">Site Manager / Proyek</div>
                </div>
                <div class="sign-space">
                    {{-- Ruang tanda tangan fisik --}}
                </div>
                <div class="sign-name">
                    ( {{ $balanceRequest->signer_site_manager ?: '..........................................' }} )
                </div>
            </div>

            {{-- 3. Menyetujui --}}
            <div class="sign-box">
                <div>
                    <div class="sign-label-top">Disetujui Oleh</div>
                    <div class="sign-role">Kantor Pusat / Owner / Keuangan</div>
                </div>
                <div class="sign-space">
                    @if($balanceRequest->status === 'approved')
                        <div style="font-size:9.5px;color:#047857;font-weight:bold;margin-bottom:4px;">
                            <i class="fa fa-check-circle"></i> DISETUJUI {{ $balanceRequest->approved_at ? $balanceRequest->approved_at->format('d/m/Y') : '' }}
                        </div>
                    @endif
                </div>
                <div class="sign-name">
                    ( {{ $balanceRequest->signer_approver ?: ($balanceRequest->approver?->name ?? '..........................................') }} )
                </div>
            </div>
        </div>
    </div>

</body>
</html>

