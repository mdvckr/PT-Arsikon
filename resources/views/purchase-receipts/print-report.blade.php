<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $reportTitle }}</title>
<style>
/* ═══════════════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

@page { size: A4 landscape; margin: 8mm 10mm 12mm 10mm; }

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5pt;
    color: #111;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ═══════════════════════════════════════════════════
   SCREEN CONTROLS
═══════════════════════════════════════════════════ */
.no-print {
    position: fixed; top: 14px; right: 18px; z-index: 9999;
    display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;
}
.btn-back    { background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:8px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; }
.btn-other   { background:#0ea5e9; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; }
.btn-print   { background:#dc2626; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; }
.btn-back:hover  { background:#e2e8f0; }
.btn-other:hover { background:#0284c7; }
.btn-print:hover { background:#b91c1c; }

@media print { .no-print { display:none !important; } }

/* ═══════════════════════════════════════════════════
   PAGE
═══════════════════════════════════════════════════ */
.page { width:100%; max-width:275mm; margin:0 auto; padding:2mm 0; }

.doc-title {
    text-align: center; font-size: 11.5pt; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.4px; color: #111; margin-bottom: 6px;
}
.period-badge {
    text-align: center; font-size: 8pt; color: #374151;
    background: #f1f5f9; border: 1px solid #cbd5e1;
    padding: 3px 12px; border-radius: 4px; display: inline-block;
    margin: 0 auto 6px; left: 0; right: 0;
    width: fit-content; margin-left: auto; margin-right: auto;
}
.period-wrap { text-align: center; margin-bottom: 7px; }

/* META */
.meta-table { border-collapse: collapse; font-size: 8pt; margin-bottom: 8px; }
.meta-table td { padding: 1px 4px 1px 0; vertical-align: top; }
.meta-table .lbl { font-weight: 700; white-space: nowrap; min-width: 105px; }
.meta-table .sep { padding: 1px 6px; }

/* ═══════════════════════════════════════════════════
   MAIN TABLE
═══════════════════════════════════════════════════ */
.main-table { width:100%; border-collapse:collapse; font-size:7pt; table-layout:fixed; }

/* Column widths */
.c-no    { width:2.2%;  } .c-tgl   { width:7%;   } .c-proyek{ width:10.5%;} .c-sup  { width:8.5%;  }
.c-mat   { width:9%;    } .c-item  { width:7.5%;  } .c-vol   { width:3%;   } .c-sat  { width:3.3%;  }
.c-hrg   { width:7.5%;  } .c-jml   { width:7.5%;  } .c-det   { width:7.5%; } .c-inp  { width:5.5%;  }
.c-tot   { width:7.5%;  } .c-per   { width:3%;    } .c-ket   { width:5.5%; }

/* HEADER */
.main-table thead tr th {
    background: #c0392b; color: #fff; font-weight: 800;
    text-align: center; vertical-align: middle;
    padding: 4px 2px; border: 1px solid #922b21;
    font-size: 6.5pt; line-height: 1.25; word-break: normal;
    hyphens: none;
}
.main-table thead tr th.c-sat,
.main-table thead tr th.c-per {
    white-space: nowrap;
}

/* BODY */
.main-table tbody tr td {
    vertical-align: middle; padding: 2.5px 3px;
    border: 1px solid #d1d5db; font-size: 6.8pt;
    word-wrap: break-word; overflow-wrap: break-word;
}
.main-table tbody tr:nth-child(odd)  td { background: #fff; }
.main-table tbody tr:nth-child(even) td { background: #fef8f8; }

/* Day subtotal row */
.main-table tbody tr.day-sub td {
    background: #eff6ff !important; font-weight: 700; font-size: 6.8pt;
    border-top: 1.5px solid #93c5fd; color: #1e40af;
}

/* FOOTER */
.main-table tfoot tr td { padding:3px 4px; border:1px solid #d1d5db; font-size:7pt; font-weight:700; }
.foot-pemasukan td  { background:#fef9c3 !important; color:#713f12; border-top:2px solid #eab308; }
.foot-pengeluaran td{ background:#fef9c3 !important; color:#713f12; border-top:1.5px solid #eab308; }
.foot-kekurangan td { background:#fee2e2 !important; color:#7f1d1d; border-top:2px solid #ef4444; }

/* UTILITY */
.tc  { text-align:center; } .tr { text-align:right; } .tl { text-align:left; }
.nw  { white-space:nowrap; } .bold { font-weight:700; } .sm { font-size:6pt; }

/* ═══════════════════════════════════════════════════
   SIGNATURES
═══════════════════════════════════════════════════ */
.date-stamp { text-align:right; font-size:8pt; margin-top:10px; margin-bottom:4px; }
.sig-area   { display:flex; justify-content:space-between; margin-top:4px; }
.sig-box    { text-align:center; width:30%; }
.sig-title  { font-size:7.5pt; color:#374151; }
.sig-gap    { height:36px; }
.sig-name   { font-weight:800; font-size:8pt; color:#0f172a; border-top:1px solid #374151; padding-top:3px; }

.footer-note { text-align:center; font-size:6pt; color:#9ca3af; margin-top:8px; border-top:1px solid #e5e7eb; padding-top:3px; }

/* ═══════════════════════════════════════════════════
   PAGE BREAK (for long reports)
═══════════════════════════════════════════════════ */
@media print {
    .main-table thead { display: table-header-group; }
    .main-table tfoot { display: table-footer-group; }
    tr { page-break-inside: avoid; }
}
</style>
</head>
<body>

@php
    $bulanId = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];

    // ── Urutkan nota berdasarkan tanggal & id ──────────────
    $sortedReceipts = $receipts->sortBy(fn($r) => [$r->receipt_date?->format('Y-m-d'), $r->id]);
    $grandTotal     = (float) $receipts->sum('total_amount');
    $grandItemQty   = $receipts->reduce(fn($carry, $r) => $carry + $r->items->sum('quantity'), 0);

    // ── Konteks tampilan ────────────────────────────────────
    $printedAt     = now()->format('d/m/Y H:i');
    $projectName   = $project?->name ?? '-';
    $projectLoc    = $project?->location ?? '-';
    $tahun         = $periodYear ?? now()->year;
@endphp

{{-- ── TOMBOL KONTROL ─────────────────────────────────── --}}
<div class="no-print">
    <a href="{{ $backUrl }}" class="btn-back">← Kembali</a>
    @if($reportType === 'weekly')
        <a href="{{ $monthlyUrl }}" target="_blank" class="btn-other">📆 Lihat Laporan Bulanan</a>
    @else
        <a href="{{ $weeklyUrl }}" target="_blank" class="btn-other">📅 Lihat Laporan Mingguan</a>
    @endif
    <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="page">

    {{-- ── JUDUL ─────────────────────────────────────────── --}}
    <div class="doc-title">{{ $reportTitle }}</div>
    <div class="period-wrap">
        <span class="period-badge">
            @if($reportType === 'weekly')
                📅 Laporan Mingguan &bull; {{ $periodLabel }}
            @else
                📆 Laporan Bulanan &bull; {{ $periodLabel }}
            @endif
            &bull; {{ $receipts->count() }} Nota &bull; {{ $receipts->sum(fn($r) => $r->items->count()) }} Item
        </span>
    </div>

    {{-- ── META ─────────────────────────────────────────── --}}
    <table class="meta-table">
        <tr>
            <td class="lbl">PEKERJAAN</td>
            <td class="sep">:</td>
            <td>{{ strtoupper($projectName) }}</td>
        </tr>
        <tr>
            <td class="lbl">LOKASI</td>
            <td class="sep">:</td>
            <td>{{ strtoupper($projectLoc) }}</td>
        </tr>
        <tr>
            <td class="lbl">TAHUN ANGGARAN</td>
            <td class="sep">:</td>
            <td>{{ $tahun }}</td>
        </tr>
    </table>

    {{-- ── TABEL ────────────────────────────────────────── --}}
    <table class="main-table">
        <colgroup>
            <col class="c-no"> <col class="c-tgl"> <col class="c-proyek"> <col class="c-sup">
            <col class="c-mat"> <col class="c-item"> <col class="c-vol"> <col class="c-sat">
            <col class="c-hrg"> <col class="c-jml"> <col class="c-det"> <col class="c-inp">
            <col class="c-tot"> <col class="c-per"> <col class="c-ket">
        </colgroup>
        <thead>
            <tr>
                <th class="c-no"    rowspan="2">No</th>
                <th class="c-tgl"   rowspan="2">Tanggal</th>
                <th class="c-proyek" rowspan="2">Nama Proyek</th>
                <th class="c-sup"   rowspan="2">Toko /<br>Supplier</th>
                <th class="c-mat"   rowspan="2">Nama<br>Material</th>
                <th class="c-item"  rowspan="2">Item</th>
                <th class="c-vol"   rowspan="2">Vol</th>
                <th class="c-sat"   rowspan="2">Satuan</th>
                <th class="c-hrg"   rowspan="2">Harga<br>Satuan</th>
                <th class="c-jml"   rowspan="2">Jumlah<br>(Subtotal)</th>
                <th colspan="2" style="text-align:center;">Sumber Dana</th>
                <th class="c-tot"   rowspan="2">Total</th>
                <th class="c-per"   rowspan="2">Perest.</th>
                <th class="c-ket"   rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th class="c-det">Detail</th>
                <th class="c-inp">Input /<br>Nota</th>
            </tr>
        </thead>

        <tbody>
        @forelse($sortedReceipts as $rIdx => $r)
            @php
                $itemCount  = max(1, $r->items->count());
                $dateObj    = $r->receipt_date;
                $tglStr     = $dateObj
                    ? ($dateObj->format('d') . ' ' . ($bulanId[$dateObj->month] ?? '') . ' ' . $dateObj->format('Y'))
                    : '-';
                $rProyek    = $r->project?->name ?? ($r->project_name ?? ($projectName ?? '-'));
                $supName    = $r->supplier_name ?? ($r->supplier?->name ?? '-');
                $noNota     = $r->receipt_number ?? ('NOTA-' . $r->id);
                $notaTotal  = (float) $r->total_amount;
                $items      = $r->items;
            @endphp

            @if($items->isEmpty())
                <tr>
                    <td class="tc">{{ $loop->iteration }}</td>
                    <td class="tc" style="font-size:6.5pt;">{{ $tglStr }}</td>
                    <td style="font-size:6.5pt; font-weight:700;">{{ $rProyek }}</td>
                    <td style="font-size:6.5pt;">{{ $supName }}</td>
                    <td class="bold" style="font-size:6.5pt;">-</td>
                    <td style="font-size:6.5pt;">-</td>
                    <td class="tc">0</td>
                    <td class="tc">-</td>
                    <td class="tr nw">Rp&nbsp;0</td>
                    <td class="tr nw bold">Rp&nbsp;0</td>
                    <td class="tr nw" style="color:#1d4ed8;">Rp&nbsp;0</td>
                    <td class="tc sm">{{ $noNota }}</td>
                    <td class="tr nw bold" style="color:#15803d;">Rp&nbsp;{{ number_format($notaTotal, 0, ',', '.') }}</td>
                    <td class="tc" style="color:#16a34a; font-weight:900; font-size:9pt;">✓</td>
                    <td class="sm">{{ $r->notes ?? '' }}</td>
                </tr>
            @else
                @foreach($items as $idx => $item)
                @php
                    $isFirst  = ($idx === 0);
                    $sub      = (float) $item->subtotal;
                    $qty      = (float) $item->quantity;
                    $harga    = (float) $item->unit_price;
                    $satuan   = $item->unit_name ?: ($item->material?->unit?->symbol ?: 'Bh');
                    $matName  = $item->material_name ?: ($item->material?->name ?: $item->item_name);
                    $itemName = $item->item_name ?: ($item->material?->name ?: '-');
                @endphp
                <tr>
                    {{-- No, Tanggal, Nama Proyek, Toko/Supplier (rowspan per nota) --}}
                    @if($isFirst)
                        <td class="tc" rowspan="{{ $itemCount }}">{{ $loop->parent->iteration }}</td>
                        <td class="tc" rowspan="{{ $itemCount }}" style="font-size:6.5pt;">{{ $tglStr }}</td>
                        <td rowspan="{{ $itemCount }}" style="font-size:6.5pt; font-weight:700;">{{ $rProyek }}</td>
                        <td rowspan="{{ $itemCount }}" style="font-size:6.5pt;">{{ $supName }}</td>
                    @endif

                    {{-- Nama Material --}}
                    <td class="bold" style="font-size:6.5pt;">{{ $matName }}</td>

                    {{-- Item --}}
                    <td style="font-size:6.5pt;">{{ $itemName }}</td>

                    {{-- Vol --}}
                    <td class="tc">{{ number_format($qty, $qty == floor($qty) ? 0 : 2, ',', '.') }}</td>

                    {{-- Satuan --}}
                    <td class="tc">{{ $satuan }}</td>

                    {{-- Harga Satuan --}}
                    <td class="tr nw">Rp&nbsp;{{ number_format($harga, 0, ',', '.') }}</td>

                    {{-- Jumlah (Subtotal) --}}
                    <td class="tr nw bold">Rp&nbsp;{{ number_format($sub, 0, ',', '.') }}</td>

                    {{-- Detail (Sumber Dana) --}}
                    <td class="tr nw" style="color:#1d4ed8;">Rp&nbsp;{{ number_format($sub, 0, ',', '.') }}</td>

                    {{-- Input / Nota --}}
                    @if($isFirst)
                        <td class="tc sm" rowspan="{{ $itemCount }}">{{ $noNota }}</td>
                    @endif

                    {{-- Total (hanya di baris terakhir per nota) --}}
                    <td class="tr nw bold" style="color:#15803d;">
                        @if($loop->last)
                            Rp&nbsp;{{ number_format($notaTotal, 0, ',', '.') }}
                        @endif
                    </td>

                    {{-- Persetujuan --}}
                    @if($isFirst)
                        <td class="tc" rowspan="{{ $itemCount }}" style="color:#16a34a; font-weight:900; font-size:9pt;">✓</td>
                    @endif

                    {{-- Keterangan --}}
                    <td class="sm">{{ $item->notes ?? '' }}</td>
                </tr>
                @endforeach
            @endif
        @empty
            <tr>
                <td colspan="15" class="tc" style="padding:20px; color:#9ca3af;">
                    Tidak ada nota pada periode ini.
                </td>
            </tr>
        @endforelse
        </tbody>

        <tfoot>
            {{-- Total Pemasukan --}}
            <tr class="foot-pemasukan">
                <td colspan="12" class="tl bold" style="padding-left:8px;">
                    Total Pemasukan &nbsp;({{ $receipts->count() }} Nota)
                </td>
                <td class="tr nw bold">Rp&nbsp;{{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            {{-- Total Pengeluaran --}}
            <tr class="foot-pengeluaran">
                <td colspan="12" class="tl bold" style="padding-left:8px;">Total pengeluaran</td>
                <td class="tr nw bold">Rp&nbsp;{{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            {{-- Kekurangan/Kelebihan --}}
            <tr class="foot-kekurangan">
                <td colspan="12" class="tl bold" style="padding-left:8px;">Kekurangan / Kelebihan</td>
                <td class="tr nw bold">Rp&nbsp;0</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── TANDA TANGAN ─────────────────────────────────── --}}
    @php
        $lastReceipt = $receipts->last();
        $tglTtd = $lastReceipt?->receipt_date ?? now();
        $ttdTgl = $tglTtd->format('d') . ' ' . ($bulanId[$tglTtd->month] ?? '') . ' ' . $tglTtd->format('Y');
    @endphp

    <div class="date-stamp">Yogyakarta,&nbsp;{{ $ttdTgl }}</div>

    <div class="sig-area">
        <div class="sig-box">
            <div class="sig-title">Mengetahui,</div>
            <div class="sig-gap"></div>
            <div class="sig-name">____________________</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Menerima dan menyetujui</div>
            <div class="sig-gap"></div>
            <div class="sig-name">____________________</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Yang menerima,</div>
            <div class="sig-gap"></div>
            <div class="sig-name">____________________</div>
        </div>
    </div>

    {{-- ── FOOTER ───────────────────────────────────────── --}}
    <div class="footer-note">
        Dicetak otomatis dari Sistem Manajemen Logistik PT Arsikon
        &bull; {{ $reportTitle }}
        &bull; Periode: {{ $periodLabel }}
        &bull; Dicetak: {{ $printedAt }}
    </div>

</div>
</body>
</html>
