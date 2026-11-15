{{-- ========================================================================= --}}
{{-- DASHBOARD: ADMIN PENGADAAN PUSAT (PROCUREMENT & VENDOR COMMAND CENTER)     --}}
{{-- ========================================================================= --}}

{{-- ROW 1: 4 TOP MODULAR KPI CARDS --}}
<div class="dash-kpi-grid">
    {{-- Card 1: Purchase Order (PO Vendor) --}}
    <a href="{{ route('purchase-orders.index') }}" class="glass-erp-card kpi-erp-card" title="Kelola Purchase Order Vendor">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">PO Vendor (Pusat)</span>
                <div class="kpi-icon-lens" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">
                    <x-lucide-file-text style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>{{ number_format($totalPOActive) }}</span>
                <span class="val-unit">PO Aktif Berjalan</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Total PO Bulan Ini: <span class="text-blue-700 font-extrabold">Rp {{ number_format($totalPOAmountMonth, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if($poSubmittedCount > 0)
                <span class="badge-erp-pill badge-pill-amber">
                    <x-lucide-clock style="width:11px;height:11px;" /> {{ $poSubmittedCount }} Butuh Approval
                </span>
            @endif
            <span class="badge-erp-pill badge-pill-navy">
                <x-lucide-truck style="width:11px;height:11px;" /> {{ $poWaitingDelivery }} Menunggu Kirim
            </span>
            <span class="badge-erp-pill badge-pill-success">
                <x-lucide-circle-check style="width:11px;height:11px;" /> {{ $poCompletedMonth }} Selesai
            </span>
        </div>
    </a>

    {{-- Card 2: PO Lapangan Masuk (Permintaan Proyek) --}}
    <a href="{{ route('material-requests.index') }}" class="glass-erp-card kpi-erp-card" title="Review Permintaan Material dari Lapangan">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">PO Lapangan (Permintaan Proyek)</span>
                <div class="kpi-icon-lens" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;">
                    <x-lucide-clipboard-list style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="{{ $pendingFieldRequests > 0 ? 'color:#ea580c;' : '' }}">
                <span>{{ number_format($pendingFieldRequests) }}</span>
                <span class="val-unit">Permintaan Pending</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Kesiapan: <span class="text-emerald-700 font-extrabold">{{ $approvedFieldRequests }} Disetujui</span> Siap Dibelikan
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if($pendingFieldRequests > 0)
                <span class="badge-erp-pill badge-pill-amber">
                    <x-lucide-alert-circle style="width:11px;height:11px;" /> Butuh Tindak Lanjut PO
                </span>
            @else
                <span class="badge-erp-pill badge-pill-success">
                    <x-lucide-circle-check style="width:11px;height:11px;" /> Antrean Bersih
                </span>
            @endif
            <span class="badge-erp-pill badge-pill-gray">
                Dari Site Office
            </span>
        </div>
    </a>

    {{-- Card 3: Buku Catatan Proyek (Deposit & Sewa Alat) --}}
    <a href="{{ route('project-notes.index') }}" class="glass-erp-card kpi-erp-card" title="Buka Rekap Catatan Material & Sewa Alat">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Buku Catatan Proyek</span>
                <div class="kpi-icon-lens" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">
                    <x-lucide-book-open style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>{{ number_format($totalDeliveredVol, 1, ',', '.') }}</span>
                <span class="val-unit">m³ Terkirim (Cor/Besi)</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Sisa Kuota Deposit: <span class="text-emerald-700 font-extrabold">{{ number_format($totalRemainingVol, 1, ',', '.') }} m³</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-navy">
                <x-lucide-layers style="width:11px;height:11px;" /> {{ $totalLedgersCount }} Buku Catatan
            </span>
            <span class="badge-erp-pill badge-pill-gray">
                <x-lucide-wrench style="width:11px;height:11px;" /> {{ $activeToolRentalsCount }} Sewa Alat Aktif
            </span>
        </div>
    </a>

    {{-- Card 4: Nota Pembelian Harian Lapangan --}}
    <a href="{{ route('purchase-receipts.index') }}" class="glass-erp-card kpi-erp-card" title="Daftar Nota Pembelian Lapangan">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Nota Pembelian Harian (Kas Proyek)</span>
                <div class="kpi-icon-lens" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;">
                    <x-lucide-receipt style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="color:#059669;">
                <span>Rp {{ number_format($weeklyReceiptsAmount, 0, ',', '.') }}</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Pengeluaran Minggu Ini ({{ $weeklyReceiptsCount }} Struk Terdata)
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-success">
                <x-lucide-wallet style="width:11px;height:11px;" /> Kas Operasional
            </span>
            @if($pendingPaymentsCount > 0)
                <span class="badge-erp-pill badge-pill-amber">
                    {{ $pendingPaymentsCount }} Bayar Pending
                </span>
            @endif
        </div>
    </a>
</div>

{{-- ROW 2: PROCUREMENT ANALYTICS & VENDOR FINANCIAL MATRIX --}}
<div class="dash-grid-analytics">
    {{-- Left: 4-Week Spend Comparison (PO Vendor vs Nota Lapangan) --}}
    <div class="glass-erp-card">
        <div class="analytics-card-header">
            <h3 class="analytics-card-title">
                <x-lucide-bar-chart-3 style="width:15px;height:15px;color:var(--arsikon-amber);" />
                Tren Belanja Pengadaan (4 Minggu Terakhir)
            </h3>
            <div class="chart-timeframe-tag">
                <x-lucide-calendar style="width:11px;height:11px;" />
                Periode Mingguan
            </div>
        </div>

        <div class="mutation-chart-container" style="padding:16px 20px;">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div>
                    <span style="font-size:11px;color:var(--arsikon-slate-muted);font-weight:600;text-transform:uppercase;">Alokasi Pengeluaran</span>
                    <div style="font-size:16px;font-weight:800;color:var(--arsikon-slate-dark);">
                        PO Suplier vs Belanja Harian Kas Proyek
                    </div>
                </div>
                <div class="chart-legend-wrap">
                    <span><span class="chart-legend-bullet" style="background:#1e40af;"></span> PO Vendor (Pusat)</span>
                    <span><span class="chart-legend-bullet" style="background:#ea580c;"></span> Nota Harian (Site)</span>
                </div>
            </div>

            {{-- Pure SVG Visual Comparison Bars --}}
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($spendTimeline as $st)
                    @php
                        $maxVal = max(array_column($spendTimeline, 'total_spend'));
                        $maxVal = $maxVal > 0 ? $maxVal : 1;
                        $poWidth = round(($st['po_spend'] / $maxVal) * 100);
                        $receiptWidth = round(($st['receipt_spend'] / $maxVal) * 100);
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            <span>Minggu ke-{{ $st['week_number'] }}</span>
                            <span class="font-extrabold text-slate-900 dark:text-white">
                                Total: Rp {{ number_format($st['total_spend'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden flex border border-slate-200 dark:border-slate-700">
                            @if($st['po_spend'] > 0)
                                <div style="width:{{ $poWidth }}%;background:#1e40af;" title="PO Vendor: Rp {{ number_format($st['po_spend'], 0, ',', '.') }}"></div>
                            @endif
                            @if($st['receipt_spend'] > 0)
                                <div style="width:{{ $receiptWidth }}%;background:#ea580c;" title="Nota Lapangan: Rp {{ number_format($st['receipt_spend'], 0, ',', '.') }}"></div>
                            @endif
                            @if($st['total_spend'] == 0)
                                <div style="width:100%;background:#e2e8f0;"></div>
                            @endif
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 mt-1" style="font-size:10px;">
                            <span>PO Vendor: Rp {{ number_format($st['po_spend'], 0, ',', '.') }}</span>
                            <span>Nota Lapangan: Rp {{ number_format($st['receipt_spend'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Status Keuangan & Tagihan Vendor --}}
    <div class="glass-erp-card">
        <div class="analytics-card-header">
            <h3 class="analytics-card-title">
                <x-lucide-shield-alert style="width:15px;height:15px;color:#1e40af;" />
                Status Tagihan & Monitoring Vendor
            </h3>
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="height:25px;padding:0 8px;font-size:10.5px;font-weight:700;">
                Buka Keuangan &rarr;
            </a>
        </div>

        <div class="fleet-matrix-wrap">
            <div class="fleet-matrix-header">
                <div>
                    <div class="text-xs text-slate-500 font-semibold">Sisa Tagihan PO Vendor (Unpaid):</div>
                    <div class="fleet-matrix-total text-rose-600" style="font-size:18px;">
                        Rp {{ number_format($unpaidPOAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    @if($pendingPaymentsCount > 0)
                        <span class="badge-erp-pill badge-pill-amber">
                            <x-lucide-clock style="width:11px;height:11px;" /> {{ $pendingPaymentsCount }} Bayar Pending
                        </span>
                    @else
                        <span class="badge-erp-pill badge-pill-success">
                            <x-lucide-circle-check style="width:11px;height:11px;" /> Pembayaran Tertib
                        </span>
                    @endif
                </div>
            </div>

            <div class="fleet-status-table">
                {{-- Row 1: Buku Catatan Material Berjalan --}}
                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">
                            <x-lucide-truck style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Suplier Cor Readymix & Besi</div>
                            <div class="desc">PT. Solusi Bangun Beton (Dynamix)</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num text-blue-700">{{ number_format($totalDeliveredVol, 1, ',', '.') }} <span class="unit">m³</span></div>
                        <div class="pct" style="color:#059669;">Sisa {{ number_format($totalRemainingVol, 1, ',', '.') }} m³</div>
                    </div>
                </div>

                {{-- Row 2: Sewa Alat Kerja --}}
                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:#fdf4ff;color:#c026d3;border:1px solid #f5d0fe;">
                            <x-lucide-wrench style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Sewa Alat Berat Proyek</div>
                            <div class="desc">Concrete Pump, Vibrator, dll.</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num text-purple-700">{{ $activeToolRentalsCount }} <span class="unit">Alat</span></div>
                        <div class="pct" style="color:#475569;">Rp {{ number_format($totalToolRentalCost, 0, ',', '.') }}</div>
                    </div>
                </div>

                {{-- Row 3: Approval Gerbang Direksi --}}
                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                            <x-lucide-user-check style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Approval PO oleh Owner / PM</div>
                            <div class="desc">Antrean persetujuan PO besar</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num text-amber-700">{{ $poSubmittedCount }} <span class="unit">PO</span></div>
                        <div class="pct" style="color:{{ $poSubmittedCount > 0 ? '#b45309' : '#059669' }};">
                            {{ $poSubmittedCount > 0 ? 'Menunggu' : 'Lengkap' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: TRIPLE OPERATIONAL STREAM & QUICK ACTION CENTER --}}
<div class="dash-grid-triad">
    {{-- Panel 1: Purchase Order Vendor Terbaru --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-file-text style="width:14px;height:14px;color:#1e40af;" />
                PO Vendor Terbaru
            </h3>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Lihat Semua &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Vendor Suplier</th>
                        <th style="text-align:right;">Nominal (Rp)</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPOs as $po)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td>
                            <strong style="color:var(--arsikon-slate-dark);font-size:11.5px;">{{ $po->supplier?->name ?? ($po->supplier_name ?? '-') }}</strong>
                            <div style="font-size:10px;color:var(--arsikon-slate-muted);">
                                Tgl: {{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td style="text-align:right;font-weight:700;font-variant-numeric:tabular-nums;">
                            Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                        </td>
                        <td style="text-align:center;">
                            @php
                                $statusMap = [
                                    'draft'     => ['badge-pill-gray', 'Draft'],
                                    'submitted' => ['badge-pill-amber', 'Submitted'],
                                    'approved'  => ['badge-pill-navy', 'Approved'],
                                    'sent'      => ['badge-pill-navy', 'Sent'],
                                    'completed' => ['badge-pill-success', 'Selesai'],
                                ];
                                [$cls, $lbl] = $statusMap[$po->status] ?? ['badge-pill-gray', $po->status];
                            @endphp
                            <span class="badge-erp-pill {{ $cls }}">{{ $lbl }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Belum ada Purchase Order vendor yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 2: Permintaan PO Lapangan Masuk --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-clipboard-list style="width:14px;height:14px;color:var(--arsikon-amber);" />
                PO Lapangan Masuk (Proyek)
            </h3>
            <a href="{{ route('material-requests.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Lihat Semua &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. Request</th>
                        <th>Pemohon / Proyek</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFieldRequests as $req)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('material-requests.show', $req) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                {{ $req->request_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:var(--arsikon-slate-dark);font-size:11.5px;">{{ $req->requestedBy?->name ?? '-' }}</div>
                            <div style="font-size:10px;color:var(--arsikon-slate-muted);">{{ $req->toWarehouse?->display_name ?? 'Gudang Tujuan' }}</div>
                        </td>
                        <td style="text-align:center;">
                            @php
                                $statusMap = [
                                    'pending'  => ['badge-pill-amber', 'Pending'],
                                    'approved' => ['badge-pill-success', 'Disetujui'],
                                    'rejected' => ['badge-pill-danger', 'Ditolak'],
                                    'fulfilled'=> ['badge-pill-navy', 'Terpenuhi'],
                                ];
                                [$cls, $lbl] = $statusMap[$req->status] ?? ['badge-pill-gray', $req->status];
                            @endphp
                            <span class="badge-erp-pill {{ $cls }}">{{ $lbl }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Tidak ada antrean PO Lapangan baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 3: Pusat Aksi Pengadaan (Quick Actions) --}}
    <div class="glass-erp-card flex flex-col justify-between">
        <div>
            <div class="stream-panel-header">
                <h3 class="stream-panel-title">
                    <x-lucide-zap style="width:14px;height:14px;color:var(--arsikon-amber);" />
                    Aksi Pengadaan Cepat
                </h3>
                <span class="badge-erp-pill badge-pill-gray" style="font-size:9.5px;">Pusat Komando</span>
            </div>

            <div class="alert-feed-list">
                @if($pendingFieldRequests > 0)
                    <a href="{{ route('material-requests.index') }}" class="alert-feed-item warning" style="text-decoration:none;">
                        <x-lucide-clock />
                        <div>
                            <strong>{{ $pendingFieldRequests }} PO Lapangan Butuh Tindak Lanjut</strong>
                            <div style="font-size:10px;opacity:0.85;">Verifikasi kebutuhan material site untuk diterbitkan PO Vendor.</div>
                        </div>
                    </a>
                @endif

                @if($poSubmittedCount > 0)
                    <a href="{{ route('purchase-orders.index') }}" class="alert-feed-item warning" style="text-decoration:none;">
                        <x-lucide-alert-circle />
                        <div>
                            <strong>{{ $poSubmittedCount }} PO Menunggu Persetujuan Direksi</strong>
                            <div style="font-size:10px;opacity:0.85;">Segera koordinasikan dengan Owner/PM untuk approval.</div>
                        </div>
                    </a>
                @endif

                @if($pendingFieldRequests === 0 && $poSubmittedCount === 0)
                    <div class="alert-feed-item success">
                        <x-lucide-shield-check />
                        <div>
                            <strong>Pengadaan Berjalan Terkendali</strong>
                            <div style="font-size:10px;opacity:0.9;">Semua permintaan proyek dan PO suplier telah diproses.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4 Primary Action Buttons tailored for Procurement --}}
        <div class="quick-actions-quad">
            <a href="{{ route('purchase-orders.create') }}" class="action-quad-btn" style="background:#1e40af;color:#fff;border-color:#1e3a8a;">
                <x-lucide-plus />
                <span>+ PO Vendor</span>
            </a>

            <a href="{{ route('project-notes.index') }}" class="action-quad-btn">
                <x-lucide-book-open />
                <span>Buku Catatan</span>
            </a>

            <a href="{{ route('purchase-receipts.index') }}" class="action-quad-btn">
                <x-lucide-receipt />
                <span>Nota Harian</span>
            </a>

            <a href="{{ route('payments.index') }}" class="action-quad-btn">
                <x-lucide-banknote />
                <span>Keuangan</span>
            </a>
        </div>
    </div>
</div>

