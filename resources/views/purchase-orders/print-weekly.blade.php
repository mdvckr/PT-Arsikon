<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} - {{ $periodLabel }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ═══════════════════════════════════════════════════
           RESET & PRINT BASE (A4 LANDSCAPE)
        ═══════════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            background: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ═══════════════════════════════════════════════════
           SCREEN TOOLBAR CONTROLS
        ═══════════════════════════════════════════════════ */
        .no-print {
            position: fixed;
            top: 12px;
            right: 16px;
            z-index: 9999;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            border: 1px solid #cbd5e1;
        }

        .btn-ctl {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .btn-back   { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
        .btn-back:hover { background: #e2e8f0; }

        .btn-nav    { background: #f8fafc; color: #1e293b; border-color: #cbd5e1; font-weight: 700; }
        .btn-nav:hover  { background: #e2e8f0; }

        .btn-mode-official { background: #0f766e; color: #fff; }
        .btn-mode-blind    { background: #475569; color: #fff; }

        .btn-pdf    { background: #0284c7; color: #fff; }
        .btn-pdf:hover  { background: #0369a1; }

        .btn-print  { background: #b91c1c; color: #fff; }
        .btn-print:hover { background: #991b1b; }

        .select-filter {
            padding: 5px 8px;
            font-size: 11px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .sheet { box-shadow: none !important; margin: 0 !important; border: none !important; width: 100% !important; max-width: 100% !important; }
        }

        /* ═══════════════════════════════════════════════════
           DOCUMENT CONTAINER / SHEET
        ═══════════════════════════════════════════════════ */
        .sheet {
            background: #ffffff;
            width: 285mm;
            margin: 60px auto 30px auto;
            padding: 10mm 12mm;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }

        /* ═══════════════════════════════════════════════════
           HEADER KORPORAT
        ═══════════════════════════════════════════════════ */
        .corp-header {
            display: flex;
            align-items: stretch;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 8px;
        }

        .corp-logo-col {
            width: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-right: 12px;
        }

        .corp-logo-col img {
            max-width: 70px;
            max-height: 55px;
            object-fit: contain;
        }

        .corp-info-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.35;
        }

        .corp-name {
            font-size: 13.5pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .corp-subtitle {
            font-size: 8pt;
            font-weight: 700;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .corp-address {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        .corp-doc-col {
            min-width: 190px;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
        }

        .doc-badge-code {
            font-size: 7pt;
            font-weight: 800;
            background: #0f172a;
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .doc-security-badge {
            margin-top: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .doc-security-official { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .doc-security-blind    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* ═══════════════════════════════════════════════════
           JUDUL LAPORAN & PERIOD BADGE
        ═══════════════════════════════════════════════════ */
        .report-title-area {
            text-align: center;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 11.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0f172a;
        }

        .period-pill {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            font-size: 8pt;
            font-weight: 700;
            padding: 3px 14px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
            margin-top: 3px;
        }

        /* ═══════════════════════════════════════════════════
           METADATA STRIP
        ═══════════════════════════════════════════════════ */
        .meta-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 7.5pt;
            margin-bottom: 8px;
            color: #334155;
        }

        .meta-item { display: flex; gap: 4px; }
        .meta-lbl  { font-weight: 700; color: #64748b; }
        .meta-val  { font-weight: 600; color: #0f172a; }

        /* ═══════════════════════════════════════════════════
           TABEL REKAPITULASI PO UTAMA
        ═══════════════════════════════════════════════════ */
        .po-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
            table-layout: fixed;
        }

        .po-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            text-align: center;
            vertical-align: middle;
            padding: 5px 4px;
            border: 1px solid #334155;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .po-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: top;
            font-size: 7.2pt;
            word-wrap: break-word;
        }

        .po-table tbody tr:nth-child(even) td {
            background: #fcfdfe;
        }

        .po-table tr {
            page-break-inside: avoid;
        }

        /* Kolom Widths */
        .col-no      { width: 3%; text-align: center; }
        .col-nopo    { width: 11%; }
        .col-tgl     { width: 7.5%; text-align: center; }
        .col-target  { width: 7.5%; text-align: center; }
        .col-sup     { width: 14%; }
        .col-proyek  { width: 12%; }
        .col-items   { width: 23%; }
        .col-nominal { width: 12%; text-align: right; }
        .col-status  { width: 10%; text-align: center; }

        .item-row-entry {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 2px;
            padding-bottom: 2px;
            border-bottom: 1px dashed #f1f5f9;
        }
        .item-row-entry:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .item-name { font-weight: 600; color: #1e293b; }
        .item-qty  { font-weight: 700; color: #475569; white-space: nowrap; margin-left: 4px; }

        .po-ref-tag {
            display: inline-block;
            font-size: 6.5pt;
            padding: 1px 4px;
            border-radius: 3px;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            margin-top: 2px;
        }

        .badge-status {
            display: inline-block;
            font-size: 6.8pt;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .st-draft     { background: #f1f5f9; color: #475569; }
        .st-approved  { background: #dbeafe; color: #1d4ed8; }
        .st-sent      { background: #fef3c7; color: #b45309; }
        .st-received  { background: #dcfce7; color: #15803d; }
        .st-cancelled { background: #fee2e2; color: #b91c1c; }
        .st-closed    { background: #e2e8f0; color: #334155; }

        /* FOOTER ROW TOTAL */
        .po-table tfoot tr td {
            background: #f1f5f9;
            font-weight: 800;
            border: 1.5px solid #94a3b8;
            padding: 6px 8px;
            font-size: 8pt;
        }

        /* ═══════════════════════════════════════════════════
           SIGNATURE SECTION
        ═══════════════════════════════════════════════════ */
        .sig-section {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sig-box {
            width: 30%;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-align: center;
            padding: 8px 10px;
            background: #ffffff;
        }

        .sig-role {
            font-size: 7.8pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 2px;
        }

        .sig-subrole {
            font-size: 6.8pt;
            color: #64748b;
        }

        .sig-space {
            height: 52px;
        }

        .sig-name {
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* ═══════════════════════════════════════════════════
           DOC FOOTER NOTES
        ═══════════════════════════════════════════════════ */
        .doc-footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 6.8pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    {{-- TOOLBAR KONTROL LAYAR (HIDDEN ON PRINT) --}}
    <div class="no-print">
        <a href="{{ route('purchase-orders.index') }}" class="btn-ctl btn-back">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        {{-- Navigasi Minggu --}}
        <div style="display:flex; align-items:center; gap:2px; background:#f1f5f9; padding:2px; border-radius:6px; border:1px solid #cbd5e1;">
            <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['date' => $prevWeekDate])) }}" 
               class="btn-ctl btn-nav" title="Minggu Sebelumnya (W{{ $startOfWeek->copy()->subDays(7)->weekOfMonth }})">
                <i class="fa fa-chevron-left"></i> W{{ $startOfWeek->copy()->subDays(7)->weekOfMonth }}
            </a>
            
            <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['date' => $currentWeekDate])) }}" 
               class="btn-ctl {{ $targetDate->isCurrentWeek() ? 'btn-mode-official' : 'btn-nav' }}" title="Minggu Ini">
                Minggu Ini (W{{ \Carbon\Carbon::now()->weekOfMonth }})
            </a>

            <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['date' => $nextWeekDate])) }}" 
               class="btn-ctl btn-nav" title="Minggu Berikutnya (W{{ $startOfWeek->copy()->addDays(7)->weekOfMonth }})">
                W{{ $startOfWeek->copy()->addDays(7)->weekOfMonth }} <i class="fa fa-chevron-right"></i>
            </a>
        </div>

        {{-- Date Picker --}}
        <form method="GET" action="{{ route('purchase-orders.weekly-report') }}" style="display:inline-flex; align-items:center; gap:4px;">
            @if(request('mode')) <input type="hidden" name="mode" value="{{ request('mode') }}"> @endif
            @if(request('project_id')) <input type="hidden" name="project_id" value="{{ request('project_id') }}"> @endif
            <input type="date" name="date" value="{{ $targetDate->format('Y-m-d') }}" class="select-filter" onchange="this.form.submit()" title="Pilih Tanggal Tertentu">
        </form>

        {{-- Mode Harga --}}
        @if($userCanViewPrice)
            <div style="display:flex; align-items:center; gap:2px; background:#f1f5f9; padding:2px; border-radius:6px; border:1px solid #cbd5e1;">
                <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['mode' => 'official'])) }}" 
                   class="btn-ctl {{ !$hidePrice ? 'btn-mode-official' : 'btn-nav' }}" title="Cetak dengan Nominal Harga">
                    <i class="fa fa-tag"></i> Resmi
                </a>
                <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['mode' => 'blind'])) }}" 
                   class="btn-ctl {{ $hidePrice ? 'btn-mode-blind' : 'btn-nav' }}" title="Cetak Tanpa Harga (Blind PO)">
                    <i class="fa fa-shield-halved"></i> Lapangan (Blind)
                </a>
            </div>
        @endif

        {{-- Direct PDF via DomPDF --}}
        <a href="{{ route('purchase-orders.weekly-report', array_merge(request()->query(), ['format' => 'pdf', 'action' => 'download'])) }}" 
           class="btn-ctl btn-pdf" title="Unduh File PDF Dokumen">
            <i class="fa fa-file-pdf"></i> Unduh PDF
        </a>

        {{-- Print Browser --}}
        <button type="button" onclick="window.print()" class="btn-ctl btn-print">
            <i class="fa fa-print"></i> Cetak Dokumen
        </button>
    </div>

    {{-- KERTAS CETAK (A4 LANDSCAPE) --}}
    <div class="sheet">

        {{-- 1. KOP SURAT PT ARSIKON --}}
        <div class="corp-header">
            <div class="corp-logo-col">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo PT Arsikon" onerror="this.style.display='none'">
            </div>
            <div class="corp-info-col">
                <div class="corp-name">PT ARSIKON CIPTA KARYA</div>
                <div class="corp-subtitle">General Contractor & Construction Logistics Management</div>
                <div class="corp-address">
                    Jl. Pasir Luhur No.1, Taranan, Sinduharjo, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581
                </div>
            </div>
            <div class="corp-doc-col">
                <span class="doc-badge-code">FORM-PO-W{{ $weekNumber }}-{{ $yearNumber }}</span>
                @if($hidePrice)
                    <span class="doc-security-badge doc-security-blind">
                        <i class="fa fa-shield-halved"></i> LAPORAN LAPANGAN (BLIND)
                    </span>
                @else
                    <span class="doc-security-badge doc-security-official">
                        <i class="fa fa-check-circle"></i> DOKUMEN RESMI (FINANSIAL)
                    </span>
                @endif
            </div>
        </div>

        {{-- 2. JUDUL DOKUMEN & PERIODE --}}
        <div class="report-title-area">
            <h1 class="report-title">{{ $reportTitle }}</h1>
            <div class="period-pill">
                <i class="fa fa-calendar-week" style="margin-right:4px;"></i> {{ $periodLabel }}
            </div>
        </div>

        {{-- 3. METADATA STRIP --}}
        <div class="meta-strip">
            <div class="meta-item">
                <span class="meta-lbl">Proyek:</span>
                <span class="meta-val">
                    @if(request('project_id') && ($p = $projects->firstWhere('id', request('project_id'))))
                        {{ $p->name }}
                    @else
                        Semua Proyek Konsolidasi
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-lbl">Total PO:</span>
                <span class="meta-val">{{ $orders->count() }} Surat Pesanan</span>
            </div>
            <div class="meta-item">
                <span class="meta-lbl">Dicetak Oleh:</span>
                <span class="meta-val">{{ auth()->user()->name ?? 'Admin PO' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-lbl">Waktu Cetak:</span>
                <span class="meta-val">{{ $printedAt }}</span>
            </div>
        </div>

        {{-- 4. TABEL DATA PO MINGGUAN --}}
        <table class="po-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th class="col-nopo">No. Purchase Order</th>
                    <th class="col-tgl">Tgl Order</th>
                    <th class="col-target">Target Tiba</th>
                    <th class="col-sup">Supplier / Vendor</th>
                    <th class="col-proyek">Tujuan Kirim / Proyek</th>
                    <th class="col-items">Rincian Material & Alat Dipesan</th>
                    <th class="col-nominal">Total Nilai PO</th>
                    <th class="col-status">Status PO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $idx => $order)
                    @php
                        $linkedMr = $order->materialRequest ?? $order->items->firstWhere('material_request_item_id', '!=', null)?->materialRequestItem?->materialRequest;
                        $projectTarget = $order->project?->name ?? $order->deliveryWarehouse?->name ?? '-';
                        $statusClass = match($order->status) {
                            'approved' => 'st-approved',
                            'sent'     => 'st-sent',
                            'received' => 'st-received',
                            'cancelled'=> 'st-cancelled',
                            'closed'   => 'st-closed',
                            default    => 'st-draft'
                        };
                    @endphp
                    <tr>
                        <td class="col-no">{{ $idx + 1 }}</td>
                        <td class="col-nopo">
                            <strong style="color:#0f172a;font-size:7.5pt;">{{ $order->po_number }}</strong>
                            @if($order->procurementRequest)
                                <div><span class="po-ref-tag">PR: {{ $order->procurementRequest->pr_number }}</span></div>
                            @endif
                            @if($linkedMr)
                                <div><span class="po-ref-tag" style="background:#fef3c7;color:#92400e;">MR: #{{ $linkedMr->request_number }}</span></div>
                            @endif
                        </td>
                        <td class="col-tgl">{{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</td>
                        <td class="col-target">{{ $order->expected_delivery ? $order->expected_delivery->format('d/m/Y') : '-' }}</td>
                        <td class="col-sup">
                            <strong>{{ $order->display_supplier_name ?? $order->supplier?->name ?? '-' }}</strong>
                            @if($order->contact_person)
                                <div style="font-size:6.5pt;color:#64748b;">CP: {{ $order->contact_person }}</div>
                            @endif
                        </td>
                        <td class="col-proyek">
                            <strong>{{ $projectTarget }}</strong>
                            @if($order->deliveryWarehouse && $order->deliveryWarehouse->name !== $projectTarget)
                                <div style="font-size:6.5pt;color:#64748b;">Gudang: {{ $order->deliveryWarehouse->name }}</div>
                            @endif
                        </td>
                        <td class="col-items">
                            @if($order->items && $order->items->isNotEmpty())
                                @foreach($order->items as $item)
                                    <div class="item-row-entry">
                                        <span class="item-name">• {{ $item->displayName() }}</span>
                                        <span class="item-qty">{{ format_quantity($item->quantity) }} {{ $item->displayUnit() }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span style="color:#94a3b8;font-style:italic;">Tidak ada rincian item</span>
                            @endif
                        </td>
                        <td class="col-nominal">
                            @if($hidePrice)
                                <span style="color:#64748b;font-weight:700;">Rp 0</span>
                                <div style="font-size:6pt;color:#94a3b8;font-style:italic;">(Dirahasiakan)</div>
                            @else
                                <strong style="color:#0f172a;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                            @endif
                        </td>
                        <td class="col-status">
                            <span class="badge-status {{ $statusClass }}">
                                {{ $order->status_label ?? ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:24px 10px;color:#64748b;font-style:italic;">
                            Tidak ada Purchase Order (PO) yang diterbitkan pada {{ $periodLabel }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;text-transform:uppercase;letter-spacing:0.4px;">
                        TOTAL PENGADAAN MINGGU INI ({{ $orders->count() }} PURCHASE ORDER)
                    </td>
                    <td class="col-nominal" style="font-size:8.5pt;color:#0f172a;">
                        @if($hidePrice)
                            Rp 0
                        @else
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- 5. PENGESAHAN BERJENJANG --}}
        @php
            $tglTtd = $targetDate;
            $ttdFormatted = $tglTtd->format('d') . ' ' . ($bulanId[$tglTtd->month] ?? '') . ' ' . $tglTtd->format('Y');
        @endphp

        <div style="text-align:right;font-size:7.5pt;color:#475569;margin-top:14px;margin-bottom:4px;">
            Sleman / Yogyakarta, {{ $ttdFormatted }}
        </div>

        <div class="sig-section">
            <div class="sig-box">
                <div class="sig-role">Dibuat Oleh,</div>
                <div class="sig-subrole">Admin PO / Pengadaan Pusat</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ auth()->user()->name ?? 'Admin Pengadaan' }} )</div>
            </div>

            <div class="sig-box">
                <div class="sig-role">Diverifikasi Oleh,</div>
                <div class="sig-subrole">Bagian Logistik & Pergudangan</div>
                <div class="sig-space"></div>
                <div class="sig-name">( _________________________ )</div>
            </div>

            <div class="sig-box">
                <div class="sig-role">Disetujui Oleh,</div>
                <div class="sig-subrole">Project Manager / Direksi</div>
                <div class="sig-space"></div>
                <div class="sig-name">( _________________________ )</div>
            </div>
        </div>

        {{-- 6. FOOTER CETAK --}}
        <div class="doc-footer">
            <div>
                Dicetak otomatis dari Sistem Logistik PT Arsikon Cipta Karya • {{ $reportTitle }}
            </div>
            <div>
                Periode: {{ $periodLabel }} • Dicetak: {{ $printedAt }}
            </div>
        </div>

    </div>

</body>
</html>
