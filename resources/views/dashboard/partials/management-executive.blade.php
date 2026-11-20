{{-- ========================================================================= --}}
{{-- DASHBOARD: EXECUTIVE MANAGEMENT (OWNER, SUPER ADMIN, ADMIN PUSAT)        --}}
{{-- ========================================================================= --}}

{{-- ROW 1: 4 TOP MODULAR EXECUTIVE METRIC CARDS --}}
<div class="dash-kpi-grid">
    {{-- Card 1: Total Pengeluaran Pengadaan & Proyek --}}
    <div class="glass-erp-card kpi-erp-card">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Total Komitmen Pengadaan</span>
                <div class="kpi-icon-lens" style="background:var(--arsikon-navy-soft);border-color:rgba(30,58,138,0.2);color:var(--arsikon-navy);">
                    <x-lucide-wallet style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="font-size:23px;color:var(--arsikon-navy);">
                <span>Rp {{ number_format($totalProcurementSpend ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-gray" title="Akumulasi PO Vendor + Nota Belanja + Sewa Alat">
                <x-lucide-layers style="width:11px;height:11px;" />
                PO + Nota + Sewa
            </span>
            <span class="badge-erp-pill badge-pill-info">
                {{ $activeProjectsCount ?? 0 }} Proyek Aktif
            </span>
        </div>
    </div>

    {{-- Card 2: Purchase Order Vendor Pusat --}}
    <a href="{{ route('purchase-orders.index') }}" class="glass-erp-card kpi-erp-card" title="Lihat Purchase Orders Vendor">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">PO Vendor (Pusat)</span>
                <div class="kpi-icon-lens" style="background:var(--arsikon-amber-soft);border-color:rgba(234,88,12,0.2);color:var(--arsikon-amber);">
                    <x-lucide-shopping-cart style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="font-size:23px;color:var(--arsikon-amber);">
                <span>Rp {{ number_format($totalPOAmount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if(($pendingApprovalPOCount ?? 0) > 0)
                <span class="badge-erp-pill badge-pill-danger">
                    <x-lucide-clock style="width:11px;height:11px;" />
                    {{ $pendingApprovalPOCount }} PO Menunggu Persetujuan
                </span>
            @else
                <span class="badge-erp-pill badge-pill-success">
                    <x-lucide-check-circle-2 style="width:11px;height:11px;" />
                    Semua PO Disetujui
                </span>
            @endif
        </div>
    </a>

    {{-- Card 3: Nota Belanja Lapangan (Cash Proyek) --}}
    <a href="{{ route('purchase-receipts.index') }}" class="glass-erp-card kpi-erp-card" title="Lihat Rekap Nota Pembelian Lapangan">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Nota Belanja Lapangan</span>
                <div class="kpi-icon-lens" style="background:var(--arsikon-emerald-soft);border-color:rgba(5,150,105,0.2);color:var(--arsikon-emerald);">
                    <x-lucide-receipt style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="font-size:23px;color:var(--arsikon-emerald);">
                <span>Rp {{ number_format($totalReceiptAmount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-success">
                <x-lucide-check-check style="width:11px;height:11px;" />
                Belanja Langsung Lapangan
            </span>
        </div>
    </a>

    {{-- Card 4: Sewa Alat & Armada Operasional --}}
    <a href="{{ route('project-notes.index') }}" class="glass-erp-card kpi-erp-card" title="Lihat Rekap Sewa Alat Proyek">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Total Sewa Alat Proyek</span>
                <div class="kpi-icon-lens" style="background:#fdf4ff;border-color:rgba(192,38,211,0.2);color:#a21caf;">
                    <x-lucide-truck style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="font-size:23px;color:#a21caf;">
                <span>Rp {{ number_format($totalToolRentalAmount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-gray">
                Alat Kerja
            </span>
        </div>
    </a>
</div>

{{-- APPROVAL ALERT IF PENDING PO --}}
@if(($pendingApprovalPOCount ?? 0) > 0)
<div class="glass-erp-card" style="padding:14px 18px;margin-bottom:16px;background:linear-gradient(135deg, rgba(254,242,242,0.9), rgba(255,255,255,0.95));border-left:4px solid var(--arsikon-rose);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:36px;height:36px;border-radius:8px;background:rgba(220,38,38,0.1);display:flex;align-items:center;justify-content:center;color:var(--arsikon-rose);flex-shrink:0;">
            <x-lucide-alert-circle style="width:20px;height:20px;" />
        </div>
        <div>
            <div style="font-weight:700;font-size:13.5px;color:var(--arsikon-slate-dark);">
                Terdapat {{ $pendingApprovalPOCount }} Purchase Order Menunggu Approval Manajemen
            </div>
            <div style="font-size:12px;color:var(--arsikon-slate-muted);">
                Total nilai komitmen yang menunggu persetujuan: <strong>Rp {{ number_format($pendingApprovalPOAmount ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>
    <a href="{{ route('purchase-orders.index', ['status' => 'submitted']) }}" style="padding:7px 14px;background:var(--arsikon-rose);color:#fff;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <span>Tinjau PO Sekarang</span>
        <x-lucide-arrow-right style="width:14px;height:14px;" />
    </a>
</div>
@endif

{{-- ROW 2: PROJECT PORTFOLIO & LOG HARIAN MONITORING --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
    {{-- Project Daily Log & Portfolio Monitoring Table --}}
    <div class="glass-erp-card" style="padding:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:12px;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--arsikon-amber-soft);display:flex;align-items:center;justify-content:center;color:var(--arsikon-amber);">
                    <x-lucide-clipboard-check style="width:18px;height:18px;" />
                </div>
                <div>
                    <h2 style="font-size:14px;font-weight:800;color:var(--arsikon-slate-dark);margin:0;line-height:1.2;">
                        Matriks Portofolio &amp; Log Harian Proyek
                    </h2>
                    <div style="font-size:11px;color:var(--arsikon-slate-muted);margin-top:2px;">
                        Monitoring status pengisian log & aktivitas operasional harian lapangan
                    </div>
                </div>
                {{-- Compliance Badge Pill --}}
                <div style="margin-left:4px;">
                    @if(($projectsLoggedTodayCount ?? 0) === ($totalActiveProjectsCount ?? 0) && ($totalActiveProjectsCount ?? 0) > 0)
                        <span class="badge-erp-pill badge-pill-success" style="font-size:10.5px;padding:3px 9px;">
                            <x-lucide-check-circle-2 style="width:11px;height:11px;" />
                            Semua Proyek ({{ $projectsLoggedTodayCount }}/{{ $totalActiveProjectsCount }}) Terisi Hari Ini
                        </span>
                    @elseif(($projectsLoggedTodayCount ?? 0) > 0)
                        <span class="badge-erp-pill badge-pill-info" style="font-size:10.5px;padding:3px 9px;">
                            <x-lucide-activity style="width:11px;height:11px;" />
                            {{ $projectsLoggedTodayCount }} dari {{ $totalActiveProjectsCount }} Proyek Terisi Hari Ini
                        </span>
                    @else
                        <span class="badge-erp-pill badge-pill-gray" style="font-size:10.5px;padding:3px 9px;">
                            <x-lucide-clock style="width:11px;height:11px;" />
                            Belum Ada Log Hari Ini (0/{{ $totalActiveProjectsCount ?? 0 }})
                        </span>
                    @endif
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px;">
                <a href="{{ route('daily-log.index') }}" style="font-size:11.5px;color:var(--arsikon-amber);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                    <span>Buka Rekap Daily Log</span>
                    <x-lucide-arrow-right style="width:12px;height:12px;" />
                </a>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="erp-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1.5px solid #e2e8f0;background:#f8fafc;">
                        <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--arsikon-slate-muted);text-align:left;white-space:nowrap;width:32%;">Proyek &amp; Site</th>
                        <th style="padding:10px 12px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--arsikon-slate-muted);text-align:left;white-space:nowrap;width:20%;">Status Log Hari Ini</th>
                        <th style="padding:10px 12px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--arsikon-slate-muted);text-align:left;white-space:nowrap;width:24%;">Aktivitas &amp; Nota Lapangan</th>
                        <th style="padding:10px 12px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--arsikon-slate-muted);text-align:center;white-space:nowrap;width:10%;">Alat Aktif</th>
                        <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--arsikon-slate-muted);text-align:right;white-space:nowrap;width:14%;">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects ?? [] as $proj)
                        <tr style="border-bottom:1px solid rgba(241,245,249,0.95);transition:background 0.15s ease;" onmouseover="this.style.background='rgba(248,250,252,0.9)'" onmouseout="this.style.background='transparent'">
                            {{-- Proyek & Site --}}
                            <td style="padding:12px 14px;vertical-align:middle;">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:nowrap;">
                                    <span style="font-size:13px;font-weight:700;color:var(--arsikon-slate-dark);line-height:1.3;">
                                        {{ $proj->name }}
                                    </span>
                                    <span style="font-size:9.5px;padding:1px 5px;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:4px;font-weight:700;color:#475569;white-space:nowrap;flex-shrink:0;">
                                        {{ $proj->code ?? 'PRJ' }}
                                    </span>
                                </div>
                                <div style="font-size:11px;color:var(--arsikon-slate-muted);margin-top:3px;display:flex;align-items:center;gap:4px;white-space:nowrap;">
                                    <x-lucide-map-pin style="width:11px;height:11px;color:#94a3b8;flex-shrink:0;" />
                                    <span>{{ $proj->location ?? 'Site Project' }}</span>
                                    @if($proj->warehouse)
                                        <span style="color:#cbd5e1;">&bull;</span>
                                        <span style="color:#64748b;">{{ $proj->warehouse->name }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Log Hari Ini --}}
                            <td style="padding:12px 12px;vertical-align:middle;white-space:nowrap;">
                                @if($proj->has_log_today)
                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:9999px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;font-weight:700;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#059669;"></span>
                                        <span>Terisi Hari Ini</span>
                                    </span>
                                    <div style="font-size:10px;color:#059669;font-weight:600;margin-top:3px;">
                                        Aktif di lapangan
                                    </div>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:9999px;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;font-size:11px;font-weight:600;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#cbd5e1;"></span>
                                        <span>Belum Ada Log</span>
                                    </span>
                                    <div style="font-size:10px;color:#94a3b8;margin-top:3px;">
                                        @if($proj->last_activity_date)
                                            Terakhir: {{ \Carbon\Carbon::parse($proj->last_activity_date)->format('d/m/Y') }}
                                        @else
                                            Belum ada catatan
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Aktivitas & Nota Lapangan Hari Ini --}}
                            <td style="padding:12px 12px;vertical-align:middle;">
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    {{-- Material Row --}}
                                    <div style="display:flex;align-items:center;gap:6px;font-size:11.5px;white-space:nowrap;">
                                        <span style="color:#64748b;display:inline-flex;align-items:center;gap:3px;font-size:10.5px;min-width:65px;">
                                            <x-lucide-package style="width:11px;height:11px;color:#94a3b8;" /> Material:
                                        </span>
                                        @if(($proj->today_usages_count ?? 0) > 0)
                                            <strong style="color:var(--arsikon-navy);font-weight:700;">{{ $proj->today_usages_count }} Mutasi Keluar</strong>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">0 mutasi</span>
                                        @endif
                                    </div>

                                    {{-- Nota Belanja Row --}}
                                    <div style="display:flex;align-items:center;gap:6px;font-size:11.5px;white-space:nowrap;">
                                        <span style="color:#64748b;display:inline-flex;align-items:center;gap:3px;font-size:10.5px;min-width:65px;">
                                            <x-lucide-receipt style="width:11px;height:11px;color:#94a3b8;" /> Nota:
                                        </span>
                                        @if(($proj->today_receipts_count ?? 0) > 0)
                                            <strong style="color:var(--arsikon-emerald);font-weight:700;">Rp {{ number_format($proj->today_receipts_amount ?? 0, 0, ',', '.') }}</strong>
                                            <span style="font-size:10px;color:#059669;font-weight:600;">({{ $proj->today_receipts_count }} Nota Belanja)</span>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">Rp 0 (0 Nota Belanja)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Alat Aktif --}}
                            <td style="padding:12px 12px;vertical-align:middle;text-align:center;white-space:nowrap;">
                                @if(($proj->active_tools_count ?? 0) > 0)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;background:#f5f3ff;border:1px solid #ddd6fe;color:#7c3aed;font-size:11px;font-weight:700;">
                                        <x-lucide-wrench style="width:11px;height:11px;" />
                                        <span>{{ $proj->active_tools_count }} Alat</span>
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#94a3b8;font-weight:500;">0 Alat</span>
                                @endif
                            </td>

                            {{-- Aksi Manajemen --}}
                            <td style="padding:12px 14px;vertical-align:middle;text-align:right;white-space:nowrap;">
                                <div style="display:inline-flex;align-items:center;gap:6px;">
                                    @if($proj->warehouse)
                                        <a href="{{ route('daily-log.index', ['warehouse_id' => $proj->warehouse->id, 'date' => now()->toDateString()]) }}" 
                                           style="height:29px;padding:0 9px;background:var(--arsikon-amber-soft);color:var(--arsikon-amber-dark);border:1px solid rgba(234,88,12,0.3);border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all 0.15s ease;"
                                           title="Buka Log Harian Proyek Hari Ini"
                                           onmouseover="this.style.background='var(--arsikon-amber)';this.style.color='#fff'"
                                           onmouseout="this.style.background='var(--arsikon-amber-soft)';this.style.color='var(--arsikon-amber-dark)'">
                                            <x-lucide-calendar-check style="width:12px;height:12px;" />
                                            <span>Log Harian</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('project-notes.index', ['project_id' => $proj->id]) }}" 
                                       style="height:29px;padding:0 8px;background:#ffffff;border:1px solid #cbd5e1;color:#475569;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all 0.15s ease;"
                                       title="Buka Buku Catatan Deposit & Sewa Alat"
                                       onmouseover="this.style.borderColor='var(--arsikon-amber)';this.style.color='var(--arsikon-amber)'"
                                       onmouseout="this.style.borderColor='#cbd5e1';this.style.color='#475569'">
                                        <x-lucide-book-open style="width:12px;height:12px;" />
                                        <span>Buku</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:32px 16px;text-align:center;color:var(--arsikon-slate-muted);">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                    <x-lucide-folder-x style="width:32px;height:32px;color:#cbd5e1;" />
                                    <div style="font-size:13px;font-weight:700;color:var(--arsikon-slate-dark);">Belum Ada Proyek Terdaftar</div>
                                    <div style="font-size:11.5px;color:#94a3b8;">Tambahkan proyek aktif baru untuk mulai memantau isian log harian.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Executive Fast Actions & Analytics Shortcut --}}
    <div style="display:flex;flex-direction:column;gap:14px;">
        {{-- Quick Management Action Card --}}
        <div class="glass-erp-card" style="padding:18px;">
            <div style="font-size:13px;font-weight:800;color:var(--arsikon-slate-dark);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                <x-lucide-command style="width:16px;height:16px;color:var(--arsikon-amber);" />
                <span>Pusat Kendali Manajemen</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('daily-log.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.7);border:1px solid rgba(226,232,240,0.8);border-radius:8px;text-decoration:none;color:var(--arsikon-slate-dark);font-size:12.5px;font-weight:600;transition:all 0.15s ease;">
                    <div style="width:28px;height:28px;border-radius:6px;background:var(--arsikon-navy-soft);display:flex;align-items:center;justify-content:center;color:var(--arsikon-navy);">
                        <x-lucide-calendar-check style="width:14px;height:14px;" />
                    </div>
                    <span>Log Harian Proyek (Daily Log)</span>
                </a>

                <a href="{{ route('purchase-orders.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.7);border:1px solid rgba(226,232,240,0.8);border-radius:8px;text-decoration:none;color:var(--arsikon-slate-dark);font-size:12.5px;font-weight:600;transition:all 0.15s ease;">
                    <div style="width:28px;height:28px;border-radius:6px;background:var(--arsikon-amber-soft);display:flex;align-items:center;justify-content:center;color:var(--arsikon-amber);">
                        <x-lucide-file-text style="width:14px;height:14px;" />
                    </div>
                    <span>Daftar PO Vendor</span>
                </a>

                <a href="{{ route('project-notes.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.7);border:1px solid rgba(226,232,240,0.8);border-radius:8px;text-decoration:none;color:var(--arsikon-slate-dark);font-size:12.5px;font-weight:600;transition:all 0.15s ease;">
                    <div style="width:28px;height:28px;border-radius:6px;background:var(--arsikon-navy-soft);display:flex;align-items:center;justify-content:center;color:var(--arsikon-navy);">
                        <x-lucide-book-open style="width:14px;height:14px;" />
                    </div>
                    <span>Buku Catatan Proyek</span>
                </a>

                <a href="{{ route('purchase-receipts.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.7);border:1px solid rgba(226,232,240,0.8);border-radius:8px;text-decoration:none;color:var(--arsikon-slate-dark);font-size:12.5px;font-weight:600;transition:all 0.15s ease;">
                    <div style="width:28px;height:28px;border-radius:6px;background:var(--arsikon-emerald-soft);display:flex;align-items:center;justify-content:center;color:var(--arsikon-emerald);">
                        <x-lucide-receipt style="width:14px;height:14px;" />
                    </div>
                    <span>Nota Pembelian Harian</span>
                </a>

                <a href="{{ route('project-notes.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.7);border:1px solid rgba(226,232,240,0.8);border-radius:8px;text-decoration:none;color:var(--arsikon-slate-dark);font-size:12.5px;font-weight:600;transition:all 0.15s ease;">
                    <div style="width:28px;height:28px;border-radius:6px;background:#fdf4ff;display:flex;align-items:center;justify-content:center;color:#a21caf;">
                        <x-lucide-truck style="width:14px;height:14px;" />
                    </div>
                    <span>Catatan Sewa Alat Proyek</span>
                </a>
            </div>
        </div>

        {{-- Executive Financial Summary Card --}}
        <div class="glass-erp-card" style="padding:16px;background:linear-gradient(135deg, rgba(255,255,255,0.9), rgba(248,250,252,0.95));">
            <div style="font-size:11.5px;font-weight:700;text-transform:uppercase;color:var(--arsikon-slate-muted);margin-bottom:8px;">
                Komposisi Belanja
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--arsikon-slate-muted);">PO Vendor:</span>
                    <strong style="color:var(--arsikon-slate-dark);">Rp {{ number_format($totalPOAmount ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--arsikon-slate-muted);">Nota Belanja:</span>
                    <strong style="color:var(--arsikon-slate-dark);">Rp {{ number_format($totalReceiptAmount ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--arsikon-slate-muted);">Sewa Alat:</span>
                    <strong style="color:var(--arsikon-slate-dark);">Rp {{ number_format($totalToolRentalAmount ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
