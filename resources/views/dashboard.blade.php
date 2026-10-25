<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @push('styles')
    <style>
        /* Main Dashboard Header */
        .dash-header {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .dash-title-group h1 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.3;
            letter-spacing: -0.01em;
        }

        .dash-title-group p {
            font-size: 12px;
            color: #64748b;
            margin: 2px 0 0;
        }

        .dash-meta-tags {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .dash-tag {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        /* KPI Metric Cards */
        .dash-metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .metric-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            color: inherit;
        }

        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .metric-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
        }

        .metric-icon,
        svg.metric-icon {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            font-size: 14px;
            color: #94a3b8;
            flex-shrink: 0 !important;
        }

        .metric-body {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 6px;
        }

        .metric-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .metric-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Quick Action Toolbar */
        .dash-actions-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .dash-actions-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .dash-btn-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
        }

        .dash-btn {
            height: 36px;
            padding: 0 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .dash-btn:hover {
            border-color: #94a3b8;
            background: #f8fafc;
            color: #0f172a;
        }

        .dash-btn.dash-btn-primary {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .dash-btn.dash-btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }

        /* Section Layout */
        .dash-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .dash-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dash-panel-header {
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .dash-panel-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Data Tables Inside Panels */
        .panel-table th {
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            padding: 7px 10px !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .panel-table td {
            font-size: 11.5px !important;
            padding: 7px 10px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        /* Tool Fleet & Operational Readiness */
        .fleet-overview-wrap {
            padding: 14px 16px 10px;
        }

        .fleet-ratio-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fleet-ratio-track {
            height: 7px;
            border-radius: 9999px;
            background: #f1f5f9;
            display: flex;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .fleet-ratio-seg {
            height: 100%;
            transition: width 0.3s ease;
        }

        .fleet-status-list {
            display: flex;
            flex-direction: column;
        }

        .fleet-status-row {
            padding: 9px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f1f5f9;
            transition: background-color 0.15s ease;
        }

        .fleet-status-row:hover {
            background-color: #f8fafc;
        }

        .fleet-item-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .fleet-lens-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            flex-shrink: 0;
        }

        .fleet-item-text .main {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }

        .fleet-item-text .sub {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .fleet-item-val {
            text-align: right;
            flex-shrink: 0;
        }

        .fleet-item-val .num {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .fleet-item-val .unit {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }

        .fleet-item-val .pct {
            font-size: 10.5px;
            font-weight: 700;
        }

        .fleet-panel-footer {
            padding: 9px 16px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
        }

        .fleet-panel-footer a {
            text-decoration: none;
            transition: color 0.15s ease;
        }

        /* Category Breakdown */
        .category-row {
            padding: 8px 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .category-row:last-child {
            border-bottom: none;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dash-metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .dash-btn-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .dash-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dash-header {
                flex-direction: column;
                align-items: stretch;
                padding: 12px;
                gap: 8px;
            }
            .dash-meta-tags {
                width: 100%;
            }
            .dash-metrics-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            .metric-card {
                padding: 10px 12px;
            }
            .metric-value {
                font-size: 20px;
            }
            .dash-btn-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }
            .dash-btn {
                font-size: 11px;
                height: 34px;
            }
            .fleet-status-row {
                padding: 8px 12px;
            }
        }
    </style>
    @endpush

    @php
        $authUser = auth()->user();
        $activeWh = $authUser->activeWarehouse();
        if ($authUser->hasRole('Owner')) {
            $userRoleText = 'Owner / Direksi';
        } elseif ($authUser->hasRole('Admin Pusat')) {
            $userRoleText = 'Admin Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Pusat')) {
            $userRoleText = 'Admin Gudang Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Proyek')) {
            $userRoleText = 'Admin Gudang Proyek';
        } elseif ($authUser->hasRole('Admin PO')) {
            $userRoleText = 'Admin Pengadaan (PO)';
        } else {
            $userRoleText = $authUser->roles->first()?->name ?? 'Petugas Logistik';
        }
    @endphp

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <span class="text-primary fw-600"><x-lucide-home class="me-1 inline-block" style="width:13px;height:13px;" /> Beranda</span>
        <span class="breadcrumb-sep"><x-lucide-chevron-right class="inline-block" style="width:10px;height:10px;" /></span>
        <span>Dashboard Operasional</span>
    </div>

    

    {{-- 4 Primary KPI Metric Cards --}}
    <div class="dash-metrics-grid">
        {{-- Total Material --}}
        <a href="{{ route('materials.index') }}" class="metric-card" title="Buka Master Material">
            <div class="metric-header">
                <span class="metric-label">Total Material</span>
                <x-lucide-boxes class="metric-icon" style="width:16px;height:16px;" />
            </div>
            <div class="metric-body">
                <div class="metric-value">{{ number_format($totalMaterials) }}</div>
                <span class="badge badge-gray" style="font-size:10px;">Item Aktif</span>
            </div>
            <div class="metric-sub">Material terdaftar di katalog</div>
        </a>

        {{-- Total Alat Kerja --}}
        <a href="{{ route('tools.index') }}" class="metric-card" title="Buka Master Alat">
            <div class="metric-header">
                <span class="metric-label">Total Alat Kerja</span>
                <x-lucide-wrench class="metric-icon" style="width:16px;height:16px;" />
            </div>
            <div class="metric-body">
                <div class="metric-value">{{ number_format($totalTools) }}</div>
                <span class="badge badge-gray" style="font-size:10px;">Unit Alat</span>
            </div>
            <div class="metric-sub">Alat inventaris perusahaan</div>
        </a>

        {{-- Permintaan Pending --}}
        <a href="{{ route('material-requests.index') }}" class="metric-card" title="Buka PO Lapangan">
            <div class="metric-header">
                <span class="metric-label">PO Lapangan Pending</span>
                <x-lucide-history class="metric-icon" style="width:16px;height:16px;" />
            </div>
            <div class="metric-body">
                <div class="metric-value" style="{{ $pendingRequests > 0 ? 'color:#ea580c;' : '' }}">
                    {{ number_format($pendingRequests) }}
                </div>
                @if($pendingRequests > 0)
                <span class="badge badge-warning" style="font-size:10px;">Menunggu Approval</span>
                @else
                <span class="badge badge-gray" style="font-size:10px;">Terkendali</span>
                @endif
            </div>
            <div class="metric-sub">PO Lapangan belum diproses</div>
        </a>

        {{-- Stok Kritis --}}
        <a href="{{ route('inventory.index') }}" class="metric-card" title="Buka Inventori Stok">
            <div class="metric-header">
                <span class="metric-label">Stok Kritis</span>
                @if($lowStockItems > 0)
                    <x-lucide-triangle-alert class="metric-icon text-danger" style="width:16px;height:16px;" />
                @else
                    <x-lucide-shield-check class="metric-icon text-success" style="width:16px;height:16px;" />
                @endif
            </div>
            <div class="metric-body">
                <div class="metric-value" style="{{ $lowStockItems > 0 ? 'color:#dc2626;' : 'color:#059669;' }}">
                    {{ number_format($lowStockItems) }}
                </div>
                @if($lowStockItems > 0)
                <span class="badge badge-danger" style="font-size:10px;">Perlu Restock</span>
                @else
                <span class="badge badge-success" style="font-size:10px;">Batas Aman</span>
                @endif
            </div>
            <div class="metric-sub">Di bawah level minimum stok</div>
        </a>
    </div>

    {{-- T13: Monitoring Jadwal Pengiriman Barang --}}
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
        <a href="{{ route('goods-receipts.create') }}" class="metric-card" title="Buka Form Penerimaan Barang">
            <div class="metric-header">
                <span class="metric-label">Kedatangan Hari Ini</span>
                <x-lucide-package-check class="metric-icon" style="width:18px;height:18px;color:#2563eb;" />
            </div>
            <div class="metric-body">
                <div class="metric-value" style="color: #2563eb;">{{ number_format($incomingTodayCount ?? 0) }}</div>
                <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size:10px; font-weight:600;">Rencana Tiba</span>
            </div>
            <div class="metric-sub">Jadwal pengiriman PO diagendakan hari ini</div>
        </a>

        <a href="{{ route('goods-receipts.create') }}" class="metric-card" title="Buka Form Penerimaan Barang">
            <div class="metric-header">
                <span class="metric-label">Jadwal Terlambat</span>
                <x-lucide-triangle-alert class="metric-icon" style="width:18px;height:18px;{{ ($incomingLateCount ?? 0) > 0 ? 'color:#dc2626;' : 'color:#94a3b8;' }}" />
            </div>
            <div class="metric-body">
                <div class="metric-value" style="{{ ($incomingLateCount ?? 0) > 0 ? 'color:#dc2626;' : 'color:#059669;' }}">
                    {{ number_format($incomingLateCount ?? 0) }}
                </div>
                @if(($incomingLateCount ?? 0) > 0)
                    <span class="badge" style="background: #fef2f2; color: #b91c1c; font-size:10px; font-weight:600;">Perlu Tindakan Segera</span>
                @else
                    <span class="badge" style="background: #f0fdf4; color: #15803d; font-size:10px; font-weight:600;">Tepat Waktu</span>
                @endif
            </div>
            <div class="metric-sub">Jadwal melewati batas waktu & belum diterima</div>
        </a>
    </div>

    {{-- Quick Action Toolbar --}}
    <div class="dash-actions-bar">
        <div class="dash-actions-title">Aksi Operasional Cepat</div>
        <div class="dash-btn-grid">
            @can('create material requests')
            <a href="{{ route('material-requests.create') }}" class="dash-btn dash-btn-primary">
                <x-lucide-plus />
                <span>PO Lapangan Baru</span>
            </a>
            @endcan

            @can('create distributions')
            <a href="{{ route('distributions.create') }}" class="dash-btn">
                <x-lucide-send class="text-primary" />
                <span>Kirim Surat Jalan</span>
            </a>
            @endcan

            <a href="{{ route('material-usages.create') }}" class="dash-btn">
                <x-lucide-package-minus class="text-warning" />
                <span>Pakai Material</span>
            </a>

            @can('create tool assignments')
            <a href="{{ route('tool-assignments.create') }}" class="dash-btn">
                <x-lucide-handshake class="text-purple" />
                <span>Pinjam Alat</span>
            </a>
            @endcan

            <a href="{{ route('goods-receipts.create') }}" class="dash-btn">
                <x-lucide-arrow-down-to-dot class="text-success" />
                <span>Penerimaan Masuk</span>
            </a>

            @can('view reports')
            <a href="{{ route('daily-log.index') }}" class="dash-btn">
                <x-lucide-calendar-days class="text-secondary" />
                <span>Log Harian</span>
            </a>
            @endcan
        </div>
    </div>

    {{-- Dual Activity Section: Requests & Distributions --}}
    <div class="dash-grid-2">
        {{-- Recent Requests --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <x-lucide-file-text class="text-primary inline-block me-1" style="width:13px;height:13px;" />
                    PO Lapangan Terbaru
                </h3>
                <a href="{{ route('material-requests.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="table-wrap">
                <table class="data-table panel-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:130px;">No. PO Lapangan</th>
                            <th>Pemohon / Proyek</th>
                            <th style="width:100px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $req)
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('material-requests.show', $req) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                    {{ $req->request_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $req->requestedBy?->name ?? '-' }}</div>
                                <div class="text-muted" style="font-size:10.5px;">{{ $req->toWarehouse?->display_name ?? 'Gudang Tujuan' }}</div>
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $statusMap = [
                                        'pending'  => ['badge-warning', 'Pending'],
                                        'approved' => ['badge-success', 'Disetujui'],
                                        'rejected' => ['badge-danger', 'Ditolak'],
                                        'fulfilled'=> ['badge-info', 'Terpenuhi'],
                                    ];
                                    [$cls, $label] = $statusMap[$req->status] ?? ['badge-gray', $req->status];
                                @endphp
                                <span class="badge {{ $cls }}" style="font-size:10px;padding:2px 7px;">{{ $label }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted text-center" style="padding:22px 14px;font-size:12px;">
                                Belum ada data PO Lapangan aktif.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Distributions --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <x-lucide-truck class="text-primary inline-block me-1" style="width:13px;height:13px;" />
                    Distribusi Surat Jalan Terbaru
                </h3>
                <a href="{{ route('distributions.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="table-wrap">
                <table class="data-table panel-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:130px;">No. Surat Jalan</th>
                            <th>Rute Pengiriman</th>
                            <th style="width:100px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDistributions as $dist)
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('distributions.show', $dist) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-size:11.5px;color:#0f172a;display:flex;align-items:center;gap:4px;">
                                    <span>{{ $dist->fromWarehouse?->name ?? 'Pusat' }}</span>
                                    <x-lucide-arrow-right class="text-muted inline-block" style="width:10px;height:10px;" />
                                    <strong>{{ $dist->toWarehouse?->name ?? 'Proyek' }}</strong>
                                </div>
                                <div class="text-muted" style="font-size:10.5px;">
                                    {{ $dist->driver_name ? 'Supir: ' . $dist->driver_name : 'Tgl: ' . ($dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '-') }}
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($dist->status === 'received')
                                    <span class="badge badge-success" style="font-size:10px;padding:2px 7px;">Diterima</span>
                                @else
                                    <span class="badge badge-primary" style="font-size:10px;padding:2px 7px;">Dalam Rute</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted text-center" style="padding:22px 14px;font-size:12px;">
                                Belum ada transaksi surat jalan pengiriman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tool Fleet & Category Composition --}}
    <div class="dash-grid-2">
        {{-- Tool Fleet Status (Human/Enterprise ERP Style) --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <x-lucide-wrench class="text-primary inline-block me-1" style="width:13px;height:13px;" />
                    Status Kesiapan Unit Alat Kerja
                </h3>
                <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Data Peminjaman &rarr;
                </a>
            </div>

            @php
                $totalFleet = $toolsReady + $toolsInUse + $toolsMaintenance;
                $readyPct = $totalFleet > 0 ? round(($toolsReady / $totalFleet) * 100, 1) : 0;
                $inUsePct = $totalFleet > 0 ? round(($toolsInUse / $totalFleet) * 100, 1) : 0;
                $maintPct = $totalFleet > 0 ? round(($toolsMaintenance / $totalFleet) * 100, 1) : 0;
            @endphp

            <div class="fleet-overview-wrap">
                <div class="fleet-ratio-header">
                    <div>
                        <span style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">Total Armada</span>
                        <div style="font-size:15px;font-weight:800;color:#0f172a;line-height:1.2;">
                            {{ number_format($totalFleet) }} <span style="font-size:12px;font-weight:600;color:#64748b;">Unit Fisik</span>
                        </div>
                    </div>
                    <div>
                        @if($totalFleet > 0 && $toolsMaintenance === 0)
                            <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;font-weight:700;padding:3px 8px;">
                                <x-lucide-circle-check class="me-1 inline-block" style="width:12px;height:12px;" /> {{ $readyPct }}% Siap Operasi
                            </span>
                        @elseif($toolsMaintenance > 0)
                            <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;font-size:11px;font-weight:700;padding:3px 8px;">
                                <x-lucide-triangle-alert class="me-1 inline-block" style="width:12px;height:12px;" /> {{ $toolsMaintenance }} Perlu Servis
                            </span>
                        @else
                            <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:11px;font-weight:600;padding:3px 8px;">
                                Belum Ada Unit
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Segmented Readiness Bar --}}
                <div class="fleet-ratio-track" title="Kesiapan Armada: {{ $toolsReady }} Siap, {{ $toolsInUse }} Di Lapangan, {{ $toolsMaintenance }} Servis">
                    @if($totalFleet > 0)
                        @if($toolsReady > 0)
                            <div class="fleet-ratio-seg" style="background:#059669;width:{{ $readyPct }}%;"></div>
                        @endif
                        @if($toolsInUse > 0)
                            <div class="fleet-ratio-seg" style="background:#2563eb;width:{{ $inUsePct }}%;"></div>
                        @endif
                        @if($toolsMaintenance > 0)
                            <div class="fleet-ratio-seg" style="background:#dc2626;width:{{ $maintPct }}%;"></div>
                        @endif
                    @else
                        <div class="fleet-ratio-seg" style="background:#e2e8f0;width:100%;"></div>
                    @endif
                </div>
            </div>

            {{-- Breakdown List (Natural Row Structure) --}}
            <div class="fleet-status-list">
                {{-- Row 1: Siap Pakai --}}
                <div class="fleet-status-row">
                    <div class="fleet-item-info">
                        <div class="fleet-lens-icon" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;">
                            <x-lucide-circle-check style="width:14px;height:14px;" />
                        </div>
                        <div class="fleet-item-text">
                            <div class="main">Siap Pakai (Tersedia)</div>
                            <div class="sub">Standby di gudang, siap dipakai</div>
                        </div>
                    </div>
                    <div class="fleet-item-val">
                        <div>
                            <span class="num">{{ number_format($toolsReady) }}</span>
                            <span class="unit">Unit</span>
                        </div>
                        <div class="pct" style="color:#059669;">{{ $readyPct }}%</div>
                    </div>
                </div>

                {{-- Row 2: Di Lapangan --}}
                <div class="fleet-status-row">
                    <div class="fleet-item-info">
                        <div class="fleet-lens-icon" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;">
                            <x-lucide-user-cog style="width:14px;height:14px;" />
                        </div>
                        <div class="fleet-item-text">
                            <div class="main">Sedang di Lapangan</div>
                            <div class="sub">Aktif dipinjam personil / proyek</div>
                        </div>
                    </div>
                    <div class="fleet-item-val">
                        <div>
                            <span class="num">{{ number_format($toolsInUse) }}</span>
                            <span class="unit">Unit</span>
                        </div>
                        <div class="pct" style="color:#2563eb;">{{ $inUsePct }}%</div>
                    </div>
                </div>

                {{-- Row 3: Servis / Rusak --}}
                <div class="fleet-status-row">
                    <div class="fleet-item-info">
                        <div class="fleet-lens-icon" style="background:{{ $toolsMaintenance > 0 ? '#fef2f2' : '#f8fafc' }};color:{{ $toolsMaintenance > 0 ? '#dc2626' : '#94a3b8' }};border:1px solid {{ $toolsMaintenance > 0 ? '#fecaca' : '#e2e8f0' }};">
                            <x-lucide-wrench style="width:14px;height:14px;" />
                        </div>
                        <div class="fleet-item-text">
                            <div class="main">Servis / Perbaikan</div>
                            <div class="sub">Rusak, kalibrasi, atau perawatan</div>
                        </div>
                    </div>
                    <div class="fleet-item-val">
                        <div>
                            <span class="num" style="{{ $toolsMaintenance > 0 ? 'color:#dc2626;' : '' }}">{{ number_format($toolsMaintenance) }}</span>
                            <span class="unit">Unit</span>
                        </div>
                        <div class="pct" style="color:{{ $toolsMaintenance > 0 ? '#dc2626' : '#94a3b8' }};">{{ $maintPct }}%</div>
                    </div>
                </div>
            </div>

            @if($openOpname > 0)
            <div style="padding:10px 16px;">
                <div class="alert alert-warning mb-0" style="font-size:11px;padding:7px 10px;border-radius:6px;display:flex;align-items:center;justify-content:space-between;">
                    <div><x-lucide-clipboard-check class="me-1 inline-block" style="width:13px;height:13px;" /> <strong>{{ $openOpname }}</strong> Stock Opname terbuka</div>
                    <a href="{{ route('stock-opnames.index') }}" class="text-warning fw-700" style="text-decoration:none;">Periksa &rarr;</a>
                </div>
            </div>
            @endif

            <div class="fleet-panel-footer">
                <a href="{{ route('tools.index') }}" style="color:#2563eb;">
                    <x-lucide-boxes class="me-1 inline-block" style="width:13px;height:13px;" /> Katalog Alat
                </a>
                @can('create tool assignments')
                <a href="{{ route('tool-assignments.create') }}" style="color:#0f172a;">
                    <x-lucide-plus class="text-primary me-1 inline-block" style="width:13px;height:13px;" /> Pinjamkan Alat
                </a>
                @endcan
            </div>
        </div>

        {{-- Inventory Breakdown by Category --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <x-lucide-layers class="text-primary inline-block me-1" style="width:13px;height:13px;" />
                    Komposisi Stok Material per Kategori
                </h3>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Inventori &rarr;
                </a>
            </div>

            <div style="padding:4px 0;">
                @php
                    $totalCatSum = $inventoryByCategory->sum();
                @endphp
                @forelse($inventoryByCategory as $catName => $qtySum)
                    @php
                        $percentage = $totalCatSum > 0 ? round(($qtySum / $totalCatSum) * 100, 1) : 0;
                    @endphp
                    <div class="category-row">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;">
                            <span style="font-size:11.5px;font-weight:600;color:#1e293b;display:flex;align-items:center;gap:5px;">
                                <x-lucide-tag class="text-muted inline-block" style="width:11px;height:11px;" />
                                {{ $catName }}
                            </span>
                            <span style="font-size:11px;color:#475569;font-weight:600;">
                                {{ number_format($qtySum) }} unit <span class="text-muted">({{ $percentage }}%)</span>
                            </span>
                        </div>
                        <div style="height:5px;border-radius:3px;background:#f1f5f9;overflow:hidden;">
                            <div style="height:100%;border-radius:3px;background:#2563eb;width:{{ min(100, $percentage) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div style="padding:22px 14px;text-align:center;color:#94a3b8;font-size:12px;">
                        Belum ada stok material tercatat pada workspace ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
