<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Penerimaan Barang - {{ $goodsReceipt->receipt_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',Arial,sans-serif; color:#0f172a; background:#fff; font-size:12px; line-height:1.5; }
        .print-wrapper { max-width:800px; margin:0 auto; padding:30px 32px; }
        .header-logo-row { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:18px; padding-bottom:14px; border-bottom:2.5px solid #0f172a; }
        .company-name { font-size:17px; font-weight:800; color:#0f172a; letter-spacing:-0.3px; }
        .company-sub { font-size:10.5px; color:#64748b; margin-top:2px; }
        .doc-title-box { text-align:right; }
        .doc-title { font-size:16px; font-weight:800; color:#1e293b; text-transform:uppercase; letter-spacing:0.5px; }
        .doc-number { font-size:13px; font-weight:700; color:#2563eb; margin-top:3px; }
        .doc-status { display:inline-block; margin-top:6px; padding:3px 10px; border-radius:5px; font-size:10.5px; font-weight:700; }
        .doc-status.confirmed { background:#ecfdf5; color:#047857; border:1.5px solid #a7f3d0; }
        .doc-status.draft { background:#fffbeb; color:#b45309; border:1.5px solid #fde68a; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px 32px; margin-bottom:20px; }
        .info-section { }
        .info-section-title { font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; margin-bottom:7px; padding-bottom:4px; border-bottom:1px solid #f1f5f9; }
        .info-row { display:flex; gap:8px; margin-bottom:5px; }
        .info-label { color:#64748b; font-size:11px; font-weight:500; min-width:110px; flex-shrink:0; }
        .info-value { color:#0f172a; font-weight:600; font-size:11px; }
        .items-table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        .items-table th { background:#f8fafc; border:1px solid #e2e8f0; padding:7px 10px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; color:#64748b; text-align:left; white-space:nowrap; }
        .items-table td { border:1px solid #e2e8f0; padding:8px 10px; font-size:11.5px; vertical-align:middle; }
        .items-table tbody tr:nth-child(even) { background:#f8fafc; }
        .type-badge { display:inline-block; padding:2px 7px; border-radius:4px; font-size:10px; font-weight:700; }
        .type-badge.material { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
        .type-badge.tool { background:#fef3c7; color:#b45309; border:1px solid #fde68a; }
        .qty-good { color:#15803d; font-weight:700; }
        .qty-damaged { color:#b91c1c; font-weight:700; }
        .qty-missing { color:#92400e; font-weight:700; }
        .table-section-title { font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.6px; color:#475569; margin-bottom:8px; display:flex; align-items:center; gap:6px; }
        .table-section-title::after { content:''; flex:1; height:1.5px; background:#e2e8f0; }
        .summary-box { background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:8px; padding:10px 14px; margin-bottom:18px; display:flex; gap:24px; flex-wrap:wrap; }
        .summary-item { }
        .summary-label { font-size:9.5px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; }
        .summary-value { font-size:14px; font-weight:800; color:#0f172a; margin-top:1px; }
        .notes-box { background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; margin-bottom:16px; }
        .notes-label { font-size:10px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px; }
        .notes-text { font-size:11.5px; color:#0f172a; line-height:1.5; }
        .signature-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-top:28px; padding-top:16px; border-top:1.5px solid #e2e8f0; }
        .signature-box { text-align:center; }
        .signature-label { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:48px; }
        .signature-name { font-size:11px; font-weight:700; color:#0f172a; border-top:1.5px solid #0f172a; padding-top:5px; margin-top:0; }
        .signature-role { font-size:10px; color:#64748b; margin-top:2px; }
        .print-footer { margin-top:24px; padding-top:12px; border-top:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; }
        .footer-text { font-size:9.5px; color:#94a3b8; }
        .footer-watermark { font-size:9px; color:#cbd5e1; font-style:italic; }
        .no-print { background:#1e293b; color:#f1f5f9; padding:10px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .no-print .print-btn { background:#3b82f6; color:#fff; border:none; border-radius:6px; padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; }
        .no-print .back-link { color:#94a3b8; font-size:12px; text-decoration:none; display:flex; align-items:center; gap:5px; }
        .no-print .back-link:hover { color:#f1f5f9; }
        @media print {
            .no-print { display:none !important; }
            body { font-size:11px; }
            .print-wrapper { padding:15px 18px; max-width:100%; }
            @page { margin:1cm; size:A4; }
        }
    </style>
</head>
<body>

{{-- Print Bar (not printed) --}}
<div class="no-print">
    <a href="{{ route('goods-receipts.show', $goodsReceipt) }}" class="back-link">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Detail GR
    </a>
    <div style="font-size:12px;color:#94a3b8;">Dokumen: {{ $goodsReceipt->receipt_number }}</div>
    <button class="print-btn" onclick="window.print()">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Cetak / Simpan PDF
    </button>
</div>

<div class="print-wrapper">

    {{-- Header --}}
    <div class="header-logo-row">
        <div>
            <div class="company-name">PT ARSIKON BANGUN PRIMA</div>
            <div class="company-sub">Divisi Pengadaan & Logistik</div>
        </div>
        <div class="doc-title-box">
            <div class="doc-title">Berita Acara Penerimaan Barang</div>
            <div class="doc-number">{{ $goodsReceipt->receipt_number }}</div>
            @if($goodsReceipt->isConfirmed())
                <span class="doc-status confirmed">&#10003; Dikonfirmasi</span>
            @else
                <span class="doc-status draft">&#9679; Draft (Belum Dikonfirmasi)</span>
            @endif
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-section">
            <div class="info-section-title">Informasi Pengiriman</div>
            <div class="info-row">
                <span class="info-label">Tanggal Diterima</span>
                <span class="info-value">{{ $goodsReceipt->receipt_date?->format('d F Y') ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Supplier / Pemasok</span>
                <span class="info-value">{{ $goodsReceipt->supplier?->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Invoice / SJ</span>
                <span class="info-value">{{ $goodsReceipt->invoice_number ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Penerima Barang</span>
                <span class="info-value">{{ $goodsReceipt->received_by_name ?? $goodsReceipt->receivedBy?->name ?? '-' }}</span>
            </div>
        </div>
        <div class="info-section">
            <div class="info-section-title">Referensi Dokumen</div>
            <div class="info-row">
                <span class="info-label">Gudang Tujuan</span>
                <span class="info-value">{{ $goodsReceipt->warehouse?->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Purchase Order</span>
                <span class="info-value">{{ $goodsReceipt->purchaseOrder?->po_number ?? '-' }}</span>
            </div>
            @php $linkedMr = $goodsReceipt->materialRequest; @endphp
            @if($linkedMr)
            <div class="info-row">
                <span class="info-label">PO Lapangan</span>
                <span class="info-value">#{{ $linkedMr->request_number }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Dicatat Oleh</span>
                <span class="info-value">{{ $goodsReceipt->creator?->name ?? '-' }}</span>
            </div>
            @if($goodsReceipt->isConfirmed())
            <div class="info-row">
                <span class="info-label">Dikonfirmasi Oleh</span>
                <span class="info-value">{{ $goodsReceipt->confirmedBy?->name ?? '-' }} ({{ $goodsReceipt->confirmed_at?->format('d/m/Y H:i') }})</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Summary --}}
    @php
        $totalItems = $goodsReceipt->items->count();
        $totalReceived = $goodsReceipt->items->sum('qty_received');
        $totalDamaged = $goodsReceipt->items->sum('qty_damaged');
        $totalMissing = $goodsReceipt->items->sum('qty_missing');
    @endphp
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Jenis Item</div>
            <div class="summary-value">{{ $totalItems }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Qty Diterima (Baik)</div>
            <div class="summary-value" style="color:#15803d;">{{ number_format($totalReceived, 2, ',', '.') }}</div>
        </div>
        @if($totalDamaged > 0)
        <div class="summary-item">
            <div class="summary-label">Total Qty Rusak</div>
            <div class="summary-value" style="color:#b91c1c;">{{ number_format($totalDamaged, 2, ',', '.') }}</div>
        </div>
        @endif
        @if($totalMissing > 0)
        <div class="summary-item">
            <div class="summary-label">Total Qty Selisih/Hilang</div>
            <div class="summary-value" style="color:#92400e;">{{ number_format($totalMissing, 2, ',', '.') }}</div>
        </div>
        @endif
    </div>

    {{-- Items Table --}}
    <div class="table-section-title">Daftar Barang Diterima</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px;">No.</th>
                <th style="width:60px;">Tipe</th>
                <th>Nama Barang / Item</th>
                <th style="width:80px;">Kode</th>
                <th style="text-align:center;width:75px;">Qty Kirim</th>
                <th style="text-align:center;width:80px;">Diterima</th>
                <th style="text-align:center;width:70px;">Rusak</th>
                <th style="text-align:center;width:75px;">Selisih</th>
                <th style="width:50px;text-align:center;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($goodsReceipt->items as $i => $item)
            @php
                $isTool = $item->isTool();
            @endphp
            <tr>
                <td style="text-align:center;color:#94a3b8;">{{ $i + 1 }}</td>
                <td>
                    <span class="type-badge {{ $isTool ? 'tool' : 'material' }}">
                        {{ $isTool ? 'Alat' : 'Material' }}
                    </span>
                </td>
                <td>
                    <div style="font-weight:600;">{{ $item->item_name }}</div>
                    @php $brand = $isTool ? $item->tool?->brand : $item->material?->brand; @endphp
                    @if($brand)<div style="font-size:10px;color:#64748b;">Merek: {{ $brand }}</div>@endif
                    @if($item->notes)<div style="font-size:10px;color:#64748b;font-style:italic;">Ket: {{ $item->notes }}</div>@endif
                </td>
                <td style="font-size:10.5px;color:#475569;">{{ $item->item_code }}</td>
                <td style="text-align:center;color:#64748b;">{{ $item->qty_shipped > 0 ? number_format($item->qty_shipped, 2, ',', '.') : '-' }}</td>
                <td style="text-align:center;" class="qty-good">{{ number_format($item->qty_received, 2, ',', '.') }}</td>
                <td style="text-align:center;" class="{{ $item->qty_damaged > 0 ? 'qty-damaged' : '' }}">
                    {{ $item->qty_damaged > 0 ? number_format($item->qty_damaged, 2, ',', '.') : '-' }}
                </td>
                <td style="text-align:center;" class="{{ $item->qty_missing > 0 ? 'qty-missing' : '' }}">
                    {{ $item->qty_missing > 0 ? number_format($item->qty_missing, 2, ',', '.') : '-' }}
                    @if($item->qty_missing > 0)
                        <div style="font-size:9px;margin-top:1px;">{{ $item->missing_status === 'resolved' ? 'Selesai' : 'Belum Selesai' }}</div>
                    @endif
                </td>
                <td style="text-align:center;font-weight:600;color:#475569;">{{ $item->item_unit }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;">Tidak ada data item.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Notes --}}
    @if($goodsReceipt->notes)
    <div class="notes-box">
        <div class="notes-label">Catatan Penerimaan:</div>
        <div class="notes-text">{{ $goodsReceipt->notes }}</div>
    </div>
    @endif

    {{-- Signature Section --}}
    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-label">Dibuat Oleh</div>
            <div class="signature-name">{{ $goodsReceipt->creator?->name ?? '___________________' }}</div>
            <div class="signature-role">Admin / Staf Penerimaan</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Penerima Barang</div>
            <div class="signature-name">{{ $goodsReceipt->received_by_name ?? $goodsReceipt->receivedBy?->name ?? '___________________' }}</div>
            <div class="signature-role">Petugas Gudang</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Dikonfirmasi Oleh</div>
            <div class="signature-name">{{ $goodsReceipt->confirmedBy?->name ?? '___________________' }}</div>
            <div class="signature-role">Kepala Gudang / Admin</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="print-footer">
        <div class="footer-text">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</div>
        <div class="footer-watermark">PT Arsikon Bangun Prima — Dokumen Internal</div>
    </div>

</div>
</body>
</html>
