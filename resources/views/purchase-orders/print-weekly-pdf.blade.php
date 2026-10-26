<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $periodLabel }}</title>
    <style>
        /* ═══════════════════════════════════════════════════
           DOMPDF COMPATIBLE CSS (TABLE-BASED LAYOUT)
        ═══════════════════════════════════════════════════ */
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #111827;
            background: #ffffff;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* HEADER KORPORAT */
        .header-tbl {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-tbl td {
            vertical-align: middle;
        }

        .corp-name {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .corp-sub {
            font-size: 8pt;
            font-weight: bold;
            color: #b45309;
            text-transform: uppercase;
        }

        .corp-addr {
            font-size: 7pt;
            color: #475569;
            margin-top: 2px;
        }

        .badge-code {
            font-size: 7pt;
            font-weight: bold;
            background: #0f172a;
            color: #ffffff;
            padding: 3px 6px;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-sec {
            margin-top: 4px;
            font-size: 6.8pt;
            font-weight: bold;
            padding: 2px 5px;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-official { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-blind    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* JUDUL LAPORAN */
        .title-wrap {
            text-align: center;
            margin-bottom: 8px;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0 0 3px 0;
        }

        .period-pill {
            background: #eff6ff;
            color: #1e40af;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2px 10px;
            border: 1px solid #bfdbfe;
            display: inline-block;
        }

        /* METADATA STRIP */
        .meta-tbl {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 6px;
            font-size: 7pt;
        }
        .meta-tbl td {
            padding: 3px 6px;
        }
        .meta-lbl { font-weight: bold; color: #64748b; }
        .meta-val { font-weight: bold; color: #0f172a; }

        /* DATA TABLE */
        .data-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
        }

        .data-tbl th {
            background: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 4px 3px;
            border: 1px solid #334155;
            font-size: 6.8pt;
            text-transform: uppercase;
        }

        .data-tbl td {
            border: 1px solid #cbd5e1;
            padding: 3px 4px;
            vertical-align: top;
            font-size: 6.8pt;
        }

        .data-tbl tr:nth-child(even) td {
            background: #fcfdfe;
        }

        .col-no      { width: 3%; text-align: center; }
        .col-nopo    { width: 12%; }
        .col-tgl     { width: 7.5%; text-align: center; }
        .col-target  { width: 7.5%; text-align: center; }
        .col-sup     { width: 14%; }
        .col-proyek  { width: 12%; }
        .col-items   { width: 23%; }
        .col-nominal { width: 11%; text-align: right; }
        .col-status  { width: 10%; text-align: center; }

        .item-line {
            margin-bottom: 2px;
            border-bottom: 1px dashed #f1f5f9;
            padding-bottom: 1px;
        }

        .ref-tag {
            font-size: 6pt;
            padding: 1px 3px;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            display: inline-block;
        }

        .badge-status {
            font-size: 6.5pt;
            font-weight: bold;
            padding: 1px 4px;
            text-transform: uppercase;
            display: inline-block;
        }
        .st-draft     { background: #f1f5f9; color: #475569; }
        .st-approved  { background: #dbeafe; color: #1d4ed8; }
        .st-sent      { background: #fef3c7; color: #b45309; }
        .st-received  { background: #dcfce7; color: #15803d; }
        .st-cancelled { background: #fee2e2; color: #b91c1c; }
        .st-closed    { background: #e2e8f0; color: #334155; }

        /* FOOTER ROW */
        .data-tbl tfoot td {
            background: #f1f5f9;
            font-weight: bold;
            border: 1.5px solid #94a3b8;
            padding: 4px 6px;
            font-size: 7.5pt;
        }

        /* TANDA TANGAN */
        .sig-tbl {
            width: 100%;
            margin-top: 12px;
            border-collapse: separate;
            border-spacing: 12px 0;
        }

        .sig-box {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            text-align: center;
            padding: 6px 8px;
            background: #ffffff;
        }

        .sig-role {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
        }

        .sig-subrole {
            font-size: 6.5pt;
            color: #64748b;
        }

        .sig-space {
            height: 44px;
        }

        .sig-name {
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            font-size: 7.2pt;
            font-weight: bold;
            color: #0f172a;
        }

        /* DOC FOOTER */
        .doc-footer-tbl {
            width: 100%;
            margin-top: 8px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
            font-size: 6.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="header-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 70px;">
                @php
                    $logoPath = public_path('assets/Logo-Dashboard.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="max-width: 60px; max-height: 48px;" alt="Logo">
                @endif
            </td>
            <td>
                <div class="corp-name">PT Arsikon Cipta Karya</div>
                <div class="corp-sub">General Contractor & Construction Logistics Management</div>
                <div class="corp-addr">
                    Kantor Pusat & Workshop: Jl. Raya Proyek Logistik, Sleman, D.I. Yogyakarta • Telp: (0274) 880-991 • procurement@arsikon.co.id
                </div>
            </td>
            <td style="width: 200px; text-align: right;">
                <div class="badge-code">FORM-PO-W{{ $weekNumber }}-{{ $yearNumber }}</div>
                <div>
                    @if($hidePrice)
                        <span class="badge-sec badge-blind">LAPORAN LAPANGAN (BLIND)</span>
                    @else
                        <span class="badge-sec badge-official">DOKUMEN RESMI (FINANSIAL)</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- JUDUL --}}
    <div class="title-wrap">
        <div class="doc-title">{{ $reportTitle }}</div>
        <div class="period-pill">{{ $periodLabel }}</div>
    </div>

    {{-- METADATA --}}
    <table class="meta-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 25%;">
                <span class="meta-lbl">Proyek:</span>
                <span class="meta-val">
                    @if(request('project_id') && ($p = $projects->firstWhere('id', request('project_id'))))
                        {{ $p->name }}
                    @else
                        Semua Proyek Konsolidasi
                    @endif
                </span>
            </td>
            <td style="width: 25%;">
                <span class="meta-lbl">Total PO:</span>
                <span class="meta-val">{{ $orders->count() }} Surat Pesanan</span>
            </td>
            <td style="width: 25%;">
                <span class="meta-lbl">Dicetak Oleh:</span>
                <span class="meta-val">{{ auth()->user()->name ?? 'Admin PO' }}</span>
            </td>
            <td style="width: 25%; text-align: right;">
                <span class="meta-lbl">Waktu Cetak:</span>
                <span class="meta-val">{{ $printedAt }}</span>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA --}}
    <table class="data-tbl" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="col-no">#</th>
                <th class="col-nopo">No. Purchase Order</th>
                <th class="col-tgl">Tgl Order</th>
                <th class="col-target">Target Tiba</th>
                <th class="col-sup">Supplier / Vendor</th>
                <th class="col-proyek">Tujuan Kirim / Proyek</th>
                <th class="col-items">Rincian Material & Alat</th>
                <th class="col-nominal">Total Nilai PO</th>
                <th class="col-status">Status</th>
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
                        <strong>{{ $order->po_number }}</strong>
                        @if($order->procurementRequest)
                            <div><span class="ref-tag">PR: {{ $order->procurementRequest->pr_number }}</span></div>
                        @endif
                        @if($linkedMr)
                            <div><span class="ref-tag" style="background:#fef3c7;color:#92400e;">MR: #{{ $linkedMr->request_number }}</span></div>
                        @endif
                    </td>
                    <td class="col-tgl">{{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</td>
                    <td class="col-target">{{ $order->expected_delivery ? $order->expected_delivery->format('d/m/Y') : '-' }}</td>
                    <td class="col-sup">
                        <strong>{{ $order->display_supplier_name ?? $order->supplier?->name ?? '-' }}</strong>
                        @if($order->contact_person)
                            <div style="font-size:6pt;color:#64748b;">CP: {{ $order->contact_person }}</div>
                        @endif
                    </td>
                    <td class="col-proyek">
                        <strong>{{ $projectTarget }}</strong>
                        @if($order->deliveryWarehouse && $order->deliveryWarehouse->name !== $projectTarget)
                            <div style="font-size:6pt;color:#64748b;">Gdg: {{ $order->deliveryWarehouse->name }}</div>
                        @endif
                    </td>
                    <td class="col-items">
                        @if($order->items && $order->items->isNotEmpty())
                            @foreach($order->items as $item)
                                <div class="item-line">
                                    • {{ $item->displayName() }} ({{ format_quantity($item->quantity) }} {{ $item->displayUnit() }})
                                </div>
                            @endforeach
                        @else
                            <span style="color:#94a3b8;font-style:italic;">-</span>
                        @endif
                    </td>
                    <td class="col-nominal">
                        @if($hidePrice)
                            <span style="color:#64748b;">Rp 0</span>
                        @else
                            <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
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
                    <td colspan="9" style="text-align:center;padding:16px 8px;color:#64748b;font-style:italic;">
                        Tidak ada Purchase Order (PO) yang diterbitkan pada {{ $periodLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right;text-transform:uppercase;">
                    TOTAL PENGADAAN MINGGU INI ({{ $orders->count() }} PO)
                </td>
                <td class="col-nominal" style="font-size:8pt;">
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

    {{-- TANDA TANGAN --}}
    @php
        $tglTtd = $targetDate;
        $ttdFormatted = $tglTtd->format('d') . ' ' . ($bulanId[$tglTtd->month] ?? '') . ' ' . $tglTtd->format('Y');
    @endphp

    <div style="text-align:right;font-size:7pt;color:#475569;margin-top:10px;">
        Sleman / Yogyakarta, {{ $ttdFormatted }}
    </div>

    <table class="sig-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sig-box">
                <div class="sig-role">Dibuat Oleh,</div>
                <div class="sig-subrole">Admin PO / Pengadaan Pusat</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ auth()->user()->name ?? 'Admin Pengadaan' }} )</div>
            </td>
            <td class="sig-box">
                <div class="sig-role">Diverifikasi Oleh,</div>
                <div class="sig-subrole">Bagian Logistik & Gudang</div>
                <div class="sig-space"></div>
                <div class="sig-name">( _________________________ )</div>
            </td>
            <td class="sig-box">
                <div class="sig-role">Disetujui Oleh,</div>
                <div class="sig-subrole">Project Manager / Direksi</div>
                <div class="sig-space"></div>
                <div class="sig-name">( _________________________ )</div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <table class="doc-footer-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                Dicetak otomatis dari Sistem Logistik PT Arsikon Cipta Karya • {{ $reportTitle }}
            </td>
            <td style="text-align: right;">
                Periode: {{ $periodLabel }} • Dicetak: {{ $printedAt }}
            </td>
        </tr>
    </table>

</body>
</html>
