{{-- ========================================================================= --}}
{{-- DASHBOARD: ADMIN PO PROYEK (SITE PURCHASING & PROJECT NOTES)             --}}
{{-- ========================================================================= --}}

{{-- ROW 1: 4 TOP MODULAR KPI CARDS --}}
<div class="dash-kpi-grid">
    {{-- Card 1: Saldo Kas Operasional Proyek --}}
    <a href="{{ route('purchase-receipts.index') }}" class="glass-erp-card kpi-erp-card" title="Lihat Rekap Pengeluaran & Saldo Kasbon">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Saldo Kas Proyek</span>
                <div class="kpi-icon-lens" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;">
                    <x-lucide-wallet style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="{{ $operationalBalance < 2000000 ? 'color:#dc2626;' : 'color:#059669;' }}">
                <span>Rp {{ number_format($operationalBalance, 0, ',', '.') }}</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                🏢 {{ $project?->name ?? 'Proyek Aktif' }}
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if($operationalBalance < 2000000)
                <span class="badge-erp-pill badge-pill-danger">
                    <x-lucide-alert-triangle style="width:11px;height:11px;" /> Saldo Menipis
                </span>
            @else
                <span class="badge-erp-pill badge-pill-success">
                    <x-lucide-circle-check style="width:11px;height:11px;" /> Saldo Kasbon Cukup
                </span>
            @endif
            <span class="badge-erp-pill badge-pill-gray">Kas Lapangan</span>
        </div>
    </a>

    {{-- Card 2: Nota Belanja Harian (Minggu Ini) --}}
    <a href="{{ route('purchase-receipts.create') }}" class="glass-erp-card kpi-erp-card" title="Catat Nota Belanja Baru">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Nota Belanja Harian (Minggu Ini)</span>
                <div class="kpi-icon-lens" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">
                    <x-lucide-receipt style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>Rp {{ number_format($weeklyReceiptsAmount, 0, ',', '.') }}</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Total Pengeluaran: <span class="text-blue-700 font-extrabold">{{ $weeklyReceiptsCount }} Nota Terdata</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-navy">
                <x-lucide-file-text style="width:11px;height:11px;" /> Belanja Rutin
            </span>
            <span class="badge-erp-pill badge-pill-gray">
                Total: Rp {{ number_format($totalReceiptsAllTime, 0, ',', '.') }}
            </span>
        </div>
    </a>

    {{-- Card 3: Buku Catatan Material (Deposit & Kirim Truk) --}}
    <a href="{{ route('project-notes.index') }}" class="glass-erp-card kpi-erp-card" title="Buka Buku Catatan Material">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Buku Catatan Material (Cor & Besi)</span>
                <div class="kpi-icon-lens" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;">
                    <x-lucide-book-open style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="color:#ea580c;">
                <span>{{ number_format($deliveredVol, 1, ',', '.') }}</span>
                <span class="val-unit">m³ Terkirim</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Sisa Kuota Deposit: <span class="text-emerald-700 font-extrabold">{{ number_format($remainingVol, 1, ',', '.') }} m³</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-amber">
                <x-lucide-layers style="width:11px;height:11px;" /> Truk T1–T7
            </span>
            <span class="badge-erp-pill badge-pill-gray">
                Deposit: {{ number_format($depositVol, 1, ',', '.') }} m³
            </span>
        </div>
    </a>

    {{-- Card 4: Sewa Alat Kerja di Lapangan --}}
    <a href="{{ route('project-notes.index') }}" class="glass-erp-card kpi-erp-card" title="Buka Daftar Sewa Alat">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Sewa Alat Kerja Lapangan</span>
                <div class="kpi-icon-lens" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">
                    <x-lucide-wrench style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>{{ number_format($activeRentalsCount) }}</span>
                <span class="val-unit">Alat Aktif</span>
            </div>
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-top:2px;">
                Biaya Sewa: <span class="text-purple-700 font-extrabold">Rp {{ number_format($totalRentalCost, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-success">
                <x-lucide-circle-check style="width:11px;height:11px;" /> Sedang Beroperasi
            </span>
            <span class="badge-erp-pill badge-pill-gray">Site Project</span>
        </div>
    </a>
</div>

{{-- ROW 2: SITE OPERATIONAL STREAMS & QUICK ACTIONS --}}
<div class="dash-grid-triad">
    {{-- Panel 1: Nota Belanja Harian Terbaru --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-receipt style="width:14px;height:14px;color:#059669;" />
                Nota Belanja Harian Terakhir
            </h3>
            <a href="{{ route('purchase-receipts.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Lihat Semua &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. Struk</th>
                        <th>Toko / Suplier</th>
                        <th style="text-align:right;">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReceipts as $rec)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('purchase-receipts.show', $rec) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                {{ $rec->receipt_number }}
                            </a>
                            <div style="font-size:9.5px;color:#94a3b8;">
                                {{ $rec->receipt_date ? \Carbon\Carbon::parse($rec->receipt_date)->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td>
                            <strong style="color:var(--arsikon-slate-dark);font-size:11px;">{{ $rec->supplier?->name ?? ($rec->supplier_name ?? '-') }}</strong>
                        </td>
                        <td style="text-align:right;font-weight:700;font-variant-numeric:tabular-nums;color:#059669;">
                            Rp {{ number_format($rec->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Belum ada nota belanja kasbon hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 2: Surat Jalan Truk Cor Masuk (T1–T7) --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-truck style="width:14px;height:14px;color:#ea580c;" />
                Surat Jalan Truk Terakhir (T1–T7)
            </h3>
            <a href="{{ route('project-notes.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Buka Buku &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. Surat</th>
                        <th>Truk / Material</th>
                        <th style="text-align:center;">Vol (m³)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                    <tr>
                        <td style="white-space:nowrap;">
                            <strong class="font-mono text-slate-800">{{ $ticket->delivery_note_number }}</strong>
                            <div style="font-size:9.5px;color:#94a3b8;">
                                {{ $ticket->delivery_date ? \Carbon\Carbon::parse($ticket->delivery_date)->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:11px;font-weight:600;color:var(--arsikon-slate-dark);">
                                {{ $ticket->material_specification }}
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:800;color:#1e40af;">
                            {{ number_format($ticket->delivery_volume, 1, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Belum ada pencatatan truk cor masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 3: Pusat Aksi Cepat Lapangan --}}
    <div class="glass-erp-card flex flex-col justify-between">
        <div>
            <div class="stream-panel-header">
                <h3 class="stream-panel-title">
                    <x-lucide-zap style="width:14px;height:14px;color:var(--arsikon-amber);" />
                    Aksi Cepat Staf Proyek
                </h3>
                <span class="badge-erp-pill badge-pill-gray" style="font-size:9.5px;">Site Office</span>
            </div>

            <div class="alert-feed-list">
                @if($operationalBalance < 2000000)
                    <div class="alert-feed-item danger">
                        <x-lucide-alert-triangle />
                        <div>
                            <strong>Saldo Kasbon Perlu Top-up Segera</strong>
                            <div style="font-size:10px;opacity:0.85;">Saldo tersisa Rp {{ number_format($operationalBalance, 0, ',', '.') }}. Segera ajukan permohonan dana ke Admin Pusat.</div>
                        </div>
                    </div>
                @else
                    <div class="alert-feed-item success">
                        <x-lucide-circle-check />
                        <div>
                            <strong>Kasbon Operasional Aman</strong>
                            <div style="font-size:10px;opacity:0.85;">Saldo kas siap digunakan untuk kebutuhan harian lapangan.</div>
                        </div>
                    </div>
                @endif

                @if($activeRentalsCount > 0)
                    <div class="alert-feed-item warning">
                        <x-lucide-wrench />
                        <div>
                            <strong>{{ $activeRentalsCount }} Alat Sewa Aktif di Lapangan</strong>
                            <div style="font-size:10px;opacity:0.85;">Pastikan surat jalan & jam kerja alat tercatat dengan akurat.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4 Primary Action Buttons for Site Purchasing --}}
        <div class="quick-actions-quad">
            <a href="{{ route('purchase-receipts.create') }}" class="action-quad-btn" style="background:#ea580c;color:#fff;border-color:#c2410c;">
                <x-lucide-plus />
                <span>+ Catat Nota</span>
            </a>

            <a href="{{ route('project-notes.index') }}" class="action-quad-btn">
                <x-lucide-book-open />
                <span>Buku Catatan</span>
            </a>

            <a href="{{ route('project-notes.index') }}" class="action-quad-btn">
                <x-lucide-truck />
                <span>Sewa Alat</span>
            </a>

            <a href="{{ route('purchase-receipts.weekly-report') }}" class="action-quad-btn">
                <x-lucide-file-spreadsheet />
                <span>Rekap Excel</span>
            </a>
        </div>
    </div>
</div>

