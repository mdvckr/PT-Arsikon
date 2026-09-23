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

        .metric-icon {
            font-size: 13px;
            color: #94a3b8;
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

        /* Tool status summary row */
        .tool-status-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 12px 14px;
        }

        .tool-status-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .tool-status-box .val {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .tool-status-box .lbl {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
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
            .tool-status-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            .tool-status-box {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 8px 12px;
            }
            .tool-status-box .lbl {
                margin-top: 0;
            }
        }
    </style>
    @endpush

    @php
        $authUser = auth()->user();
        $activeWh = $authUser->activeWarehouse();
        if ($authUser->hasRole('Owner')) {
            $userRoleText = 'Owner / Direksi';
        } elseif ($authUser->hasRole('Admin Gudang Pusat') || ($authUser->hasRole('Admin') && !$authUser->hasRole('Admin Gudang Proyek'))) {
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
        <span class="text-primary fw-600"><i class="fas fa-home me-1"></i> Beranda</span>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Dashboard Operasional</span>
    </div>

    

    {{-- 4 Primary KPI Metric Cards --}}
    <div class="dash-metrics-grid">
        {{-- Total Material --}}
        <a href="{{ route('materials.index') }}" class="metric-card" title="Buka Master Material">
            <div class="metric-header">
                <span class="metric-label">Total Material</span>
                <i class="fas fa-boxes-stacked metric-icon"></i>
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
                <i class="fas fa-tools metric-icon"></i>
            </div>
            <div class="metric-body">
                <div class="metric-value">{{ number_format($totalTools) }}</div>
                <span class="badge badge-gray" style="font-size:10px;">Unit Alat</span>
            </div>
            <div class="metric-sub">Alat inventaris perusahaan</div>
        </a>

        {{-- Permintaan Pending --}}
        <a href="{{ route('material-requests.index') }}" class="metric-card" title="Buka Permintaan Material">
            <div class="metric-header">
                <span class="metric-label">Permintaan Pending</span>
                <i class="fas fa-clock-rotate-left metric-icon"></i>
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
            <div class="metric-sub">Bon permintaan belum diproses</div>
        </a>

        {{-- Stok Kritis --}}
        <a href="{{ route('inventory.index') }}" class="metric-card" title="Buka Inventori Stok">
            <div class="metric-header">
                <span class="metric-label">Stok Kritis</span>
                <i class="fas {{ $lowStockItems > 0 ? 'fa-triangle-exclamation text-danger' : 'fa-shield-halved' }} metric-icon"></i>
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

    {{-- Quick Action Toolbar --}}
    <div class="dash-actions-bar">
        <div class="dash-actions-title">Aksi Operasional Cepat</div>
        <div class="dash-btn-grid">
            @can('create material requests')
            <a href="{{ route('material-requests.create') }}" class="dash-btn dash-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Permintaan Baru</span>
            </a>
            @endcan

            @can('create distributions')
            <a href="{{ route('distributions.create') }}" class="dash-btn">
                <i class="fas fa-paper-plane text-primary"></i>
                <span>Kirim Surat Jalan</span>
            </a>
            @endcan

            <a href="{{ route('material-usages.create') }}" class="dash-btn">
                <i class="fas fa-arrow-right-from-bracket text-warning"></i>
                <span>Pakai Material</span>
            </a>

            @can('create tool assignments')
            <a href="{{ route('tool-assignments.create') }}" class="dash-btn">
                <i class="fas fa-handshake text-purple"></i>
                <span>Pinjam Alat</span>
            </a>
            @endcan

            <a href="{{ route('goods-receipts.create') }}" class="dash-btn">
                <i class="fas fa-arrow-down-to-bracket text-success"></i>
                <span>Penerimaan Masuk</span>
            </a>

            @can('view reports')
            <a href="{{ route('daily-log.index') }}" class="dash-btn">
                <i class="fas fa-calendar-day text-secondary"></i>
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
                    <i class="fas fa-file-lines text-primary" style="font-size:11.5px;"></i>
                    Permintaan Material Terbaru
                </h3>
                <a href="{{ route('material-requests.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="table-wrap">
                <table class="data-table panel-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:130px;">No. Permintaan</th>
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
                                <div class="text-muted" style="font-size:10.5px;">{{ $req->toWarehouse?->name ?? 'Gudang Tujuan' }}</div>
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
                                Belum ada data permintaan material aktif.
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
                    <i class="fas fa-truck text-primary" style="font-size:11.5px;"></i>
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
                                    <i class="fas fa-arrow-right text-muted" style="font-size:9px;"></i>
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
        {{-- Tool Fleet Status --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fas fa-toolbox text-primary" style="font-size:11.5px;"></i>
                    Status Kesiapan Unit Alat Kerja
                </h3>
                <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;font-weight:600;">
                    Data Peminjaman &rarr;
                </a>
            </div>

            <div class="tool-status-row">
                {{-- Ready --}}
                <div class="tool-status-box" style="border-left:3px solid #059669;">
                    <div class="val" style="color:#059669;">{{ $toolsReady }}</div>
                    <div class="lbl">
                        <i class="fas fa-circle-check text-success" style="font-size:10px;"></i>
                        <span>Siap Pakai</span>
                    </div>
                </div>

                {{-- In Use --}}
                <div class="tool-status-box" style="border-left:3px solid #2563eb;">
                    <div class="val" style="color:#2563eb;">{{ $toolsInUse }}</div>
                    <div class="lbl">
                        <i class="fas fa-user-gear text-primary" style="font-size:10px;"></i>
                        <span>Di Lapangan</span>
                    </div>
                </div>

                {{-- Maintenance --}}
                <div class="tool-status-box" style="border-left:3px solid {{ $toolsMaintenance > 0 ? '#dc2626' : '#cbd5e1' }};">
                    <div class="val" style="color:{{ $toolsMaintenance > 0 ? '#dc2626' : '#64748b' }};">{{ $toolsMaintenance }}</div>
                    <div class="lbl">
                        <i class="fas fa-wrench {{ $toolsMaintenance > 0 ? 'text-danger' : 'text-muted' }}" style="font-size:10px;"></i>
                        <span>Servis / Rusak</span>
                    </div>
                </div>
            </div>

            @if($openOpname > 0)
            <div style="padding:0 14px 14px;">
                <div class="alert alert-warning mb-0" style="font-size:11.5px;padding:8px 12px;border-radius:6px;">
                    <div>Ada <strong>{{ $openOpname }}</strong> agenda Stock Opname terbuka.
                        <a href="{{ route('stock-opnames.index') }}" class="text-warning fw-700"> Selesaikan &rarr;</a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Inventory Breakdown by Category --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fas fa-layer-group text-primary" style="font-size:11.5px;"></i>
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
                                <i class="fas fa-tag text-muted" style="font-size:9px;"></i>
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
