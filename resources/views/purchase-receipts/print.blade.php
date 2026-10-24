<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cetak Nota – {{ $purchaseReceipt->receipt_number ?? 'NOTA-'.$purchaseReceipt->id }}</title>
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
   SCREEN-ONLY CONTROLS
═══════════════════════════════════════════════════ */
.no-print {
    position: fixed; top: 14px; right: 18px; z-index: 9999;
    display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;
}
.btn-back    { background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; padding:8px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; }
.btn-weekly  { background:#0ea5e9; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; }
.btn-monthly { background:#8b5cf6; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; }
.btn-print   { background:#dc2626; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; }
.btn-back:hover   { background:#e2e8f0; }
.btn-weekly:hover { background:#0284c7; }
.btn-monthly:hover{ background:#7c3aed; }
.btn-print:hover  { background:#b91c1c; }

@media print { .no-print { display:none !important; } }

/* ═══════════════════════════════════════════════════
   PAGE WRAPPER
═══════════════════════════════════════════════════ */
.page { width:100%; max-width:275mm; margin:0 auto; padding:2mm 0; }

/* ═══════════════════════════════════════════════════
   DOCUMENT TITLE
═══════════════════════════════════════════════════ */
.doc-title {
    text-align: center;
    font-size: 11.5pt;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #111;
    margin-bottom: 7px;
}

/* ═══════════════════════════════════════════════════
   META INFO TABLE
═══════════════════════════════════════════════════ */
.meta-table { border-collapse: collapse; font-size: 8pt; margin-bottom: 8px; }
.meta-table td { padding: 1px 4px 1px 0; vertical-align: top; }
.meta-table .lbl { font-weight: 700; white-space: nowrap; min-width: 100px; }
.meta-table .sep { padding: 1px 6px; }

/* ═══════════════════════════════════════════════════
   MAIN TABLE
═══════════════════════════════════════════════════ */
.main-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7pt;
    table-layout: fixed;
}

/* Column widths (A4 landscape ≈ 275mm usable) */
.c-no      { width: 2.2%;  }
.c-tgl     { width: 7.5%;  }
.c-proyek  { width: 12%;   }
.c-sup     { width: 9%;    }
.c-mat     { width: 9%;    }
.c-item    { width: 7.5%;  }
.c-vol     { width: 3%;    }
.c-sat     { width: 3.3%;  }
.c-hrg     { width: 7.5%;  }
.c-jml     { width: 7.5%;  }
.c-det     { width: 7.5%;  }
.c-inp     { width: 5.5%;  }
.c-tot     { width: 7.5%;  }
.c-per     { width: 3.3%;  }
.c-ket     { width: 7.5%;  }

/* HEADER */
.main-table thead tr th {
    background: #c0392b;
    color: #fff;
    font-weight: 800;
    text-align: center;
    vertical-align: middle;
    padding: 4px 2px;
    border: 1px solid #922b21;
    font-size: 6.5pt;
    line-height: 1.25;
    white-space: normal;
    word-break: normal;
    hyphens: none;
}
.main-table thead tr th.c-sat,
.main-table thead tr th.c-per {
    white-space: nowrap;
}

/* BODY CELLS */
.main-table tbody tr td {
    vertical-align: middle;
    padding: 3px 3px;
    border: 1px solid #d1d5db;
    font-size: 7pt;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.main-table tbody tr:nth-child(odd)  td { background: #fff; }
.main-table tbody tr:nth-child(even) td { background: #fef8f8; }

/* FOOTER */
.main-table tfoot tr td {
    padding: 3px 4px;
    border: 1px solid #d1d5db;
    font-size: 7pt;
    font-weight: 700;
}
.foot-pemasukan td  { background: #fef9c3 !important; color: #713f12; border-top: 2px solid #eab308; }
.foot-pengeluaran td{ background: #fef9c3 !important; color: #713f12; border-top: 1.5px solid #eab308; }
.foot-kekurangan td { background: #fee2e2 !important; color: #7f1d1d; border-top: 2px solid #ef4444; }

/* UTILITY */
.tc  { text-align: center; }
.tr  { text-align: right;  }
.tl  { text-align: left;   }
.nw  { white-space: nowrap;}
.bold{ font-weight: 700;   }
.sm  { font-size: 6pt;     }

/* ═══════════════════════════════════════════════════
   SIGNATURE AREA
═══════════════════════════════════════════════════ */
.date-stamp { text-align: right; font-size: 8pt; margin-top: 10px; margin-bottom: 4px; }
.sig-area   { display: flex; justify-content: space-between; margin-top: 4px; }
.sig-box    { text-align: center; width: 30%; }
.sig-title  { font-size: 7.5pt; color: #374151; }
.sig-gap    { height: 36px; }
.sig-name   {
    font-weight: 800; font-size: 8pt; color: #0f172a;
    border-top: 1px solid #374151; padding-top: 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ═══════════════════════════════════════════════════
   FOOTER NOTE
═══════════════════════════════════════════════════ */
.footer-note {
    text-align: center; font-size: 6pt; color: #9ca3af;
    margin-top: 8px; border-top: 1px solid #e5e7eb; padding-top: 3px;
}
</style>
</head>
<body>

@php
    $receipt      = $purchaseReceipt;
    $project      = $receipt->project;
    $items        = $receipt->items;
    $total        = (float) $receipt->total_amount;
    $itemCount    = $items->count();

    $bulanId = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];

    $tgl          = $receipt->receipt_date ?? now();
    $bulan        = $bulanId[$tgl->month] ?? $tgl->format('F');
    $tahun        = $tgl->format('Y');
    $tanggalStr   = $tgl->format('d') . ' ' . $bulan . ' ' . $tahun;

    $noNota       = $receipt->receipt_number ?? ('NOTA-' . $receipt->id);
    $supplierName = $receipt->supplier_name ?? ($receipt->supplier?->name ?? '-');
    $projectName  = $project?->name ?? '-';
    $projectLoc   = $project?->location ?? '-';

    $docTitle = 'PENGELUARAN KAS OPERASIONAL ' . strtoupper($projectName) . ' BULAN ' . strtoupper($bulan) . ' ' . $tahun;
@endphp

{{-- ── CONTROL BUTTONS (screen only) ─────────────────── --}}
<div class="no-print">
    <a href="{{ route('purchase-receipts.show', $receipt) }}" class="btn-back">← Kembali</a>
    <a href="{{ route('purchase-receipts.print-weekly', $receipt) }}" target="_blank" class="btn-weekly">📅 Laporan Mingguan</a>
    <a href="{{ route('purchase-receipts.print-monthly', $receipt) }}" target="_blank" class="btn-monthly">📆 Laporan Bulanan</a>
    <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="page">

    {{-- ── JUDUL ─────────────────────────────────────── --}}
    <div class="doc-title">{{ $docTitle }}</div>

    {{-- ── META INFO ──────────────────────────────────── --}}
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

    {{-- ── TABEL UTAMA ────────────────────────────────── --}}
    <table class="main-table">
        <colgroup>
            <col class="c-no"> <col class="c-tgl"> <col class="c-proyek"> <col class="c-sup">
            <col class="c-mat"> <col class="c-item"> <col class="c-vol"> <col class="c-sat">
            <col class="c-hrg"> <col class="c-jml"> <col class="c-det"> <col class="c-inp">
            <col class="c-tot"> <col class="c-per"> <col class="c-ket">
        </colgroup>
        <thead>
            <tr>
                <th class="c-no"  rowspan="2">No</th>
                <th class="c-tgl" rowspan="2">Tanggal</th>
                <th class="c-proyek" rowspan="2">Nama Proyek</th>
                <th class="c-sup"    rowspan="2">Toko /<br>Supplier</th>
                <th class="c-mat"    rowspan="2">Nama<br>Material</th>
                <th class="c-item"   rowspan="2">Item</th>
                <th class="c-vol"    rowspan="2">Vol</th>
                <th class="c-sat"    rowspan="2">Satuan</th>
                <th class="c-hrg"    rowspan="2">Harga<br>Satuan</th>
                <th class="c-jml"    rowspan="2">Jumlah<br>(Subtotal)</th>
                <th colspan="2" style="text-align:center;">Sumber Dana</th>
                <th class="c-tot"    rowspan="2">Total</th>
                <th class="c-per"    rowspan="2">Perest.</th>
                <th class="c-ket"    rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th class="c-det">Detail</th>
                <th class="c-inp">Input /<br>Nota</th>
            </tr>
        </thead>
        <tbody>
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
                {{-- No --}}
                <td class="tc">{{ $idx + 1 }}</td>

                {{-- Tanggal, Proyek, Supplier — rowspan karena sama semua --}}
                @if($isFirst)
                    <td class="tc" rowspan="{{ $itemCount }}" style="font-size:6.5pt;">{{ $tanggalStr }}</td>
                    <td rowspan="{{ $itemCount }}" style="font-size:6.5pt; font-weight:700;">{{ $projectName }}</td>
                    <td rowspan="{{ $itemCount }}" style="font-size:6.5pt;">{{ $supplierName }}</td>
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
                <td class="tc sm">{{ $isFirst ? $noNota : '' }}</td>

                {{-- Total (hanya di baris terakhir per nota) --}}
                <td class="tr nw bold" style="color:#15803d;">
                    @if($loop->last)
                        Rp&nbsp;{{ number_format($total, 0, ',', '.') }}
                    @endif
                </td>

                {{-- Persetujuan --}}
                <td class="tc" style="color:#16a34a; font-weight:900; font-size:9pt;">
                    @if($isFirst) ✓ @endif
                </td>

                {{-- Keterangan --}}
                <td class="sm">{{ $item->notes ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            {{-- Total Pemasukan --}}
            <tr class="foot-pemasukan">
                <td colspan="12" class="tl bold" style="padding-left:8px;">Total Pemasukan</td>
                <td class="tr nw bold">Rp&nbsp;{{ number_format($total, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            {{-- Total Pengeluaran --}}
            <tr class="foot-pengeluaran">
                <td colspan="12" class="tl bold" style="padding-left:8px;">Total pengeluaran</td>
                <td class="tr nw bold">Rp&nbsp;{{ number_format($total, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            {{-- Kekurangan / Kelebihan --}}
            <tr class="foot-kekurangan">
                <td colspan="12" class="tl bold" style="padding-left:8px;">Kekurangan / Kelebihan</td>
                <td class="tr nw bold">Rp&nbsp;0</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── TANGGAL & TANDA TANGAN ─────────────────────── --}}
    <div class="date-stamp">Yogyakarta,&nbsp;{{ $tanggalStr }}</div>

    <div class="sig-area">
        <div class="sig-box">
            <div class="sig-title">Mengetahui,</div>
            <div class="sig-gap"></div>
            <div class="sig-name">{{ $receipt->creator?->name ?? '____________________' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Menerima dan menyetujui</div>
            <div class="sig-gap"></div>
            <div class="sig-name">____________________</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Yang menerima,</div>
            <div class="sig-gap"></div>
            <div class="sig-name">{{ \Illuminate\Support\Str::limit($supplierName, 28) }}</div>
        </div>
    </div>

    {{-- ── FOOTER CETAK ───────────────────────────────── --}}
    <div class="footer-note">
        Dicetak otomatis dari Sistem Manajemen Logistik PT Arsikon
        &bull; No. Nota: {{ $noNota }}
        &bull; Proyek: {{ $projectName }}
        &bull; Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>
