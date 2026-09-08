<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-4" style="margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-boxes-stacked"></i></div>
            <div class="stat-info">
                <div class="label">Total Material</div>
                <div class="value">{{ number_format($totalMaterials) }}</div>
                <div class="sub">Jenis material terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-screwdriver-wrench"></i></div>
            <div class="stat-info">
                <div class="label">Total Alat</div>
                <div class="value">{{ number_format($totalTools) }}</div>
                <div class="sub">Unit alat terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-file-circle-plus"></i></div>
            <div class="stat-info">
                <div class="label">Permintaan Pending</div>
                <div class="value">{{ number_format($pendingRequests) }}</div>
                <div class="sub">Menunggu persetujuan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="stat-info">
                <div class="label">Stok Rendah</div>
                <div class="value">{{ number_format($lowStockItems) }}</div>
                <div class="sub">Item di bawah minimum</div>
            </div>
        </div>
    </div>

    {{-- Row 2: Recent Receipts & Requests --}}
    <div class="grid grid-2" style="margin-bottom:20px;">

        {{-- Recent Goods Receipts --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck-ramp-box text-primary"></i>
                <span class="card-title">Penerimaan Barang Terbaru</span>
                <a href="{{ route('goods-receipts.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Tanda Terima</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReceipts as $receipt)
                        <tr>
                            <td>
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-primary fw-600">
                                    {{ $receipt->receipt_number }}
                                </a>
                            </td>
                            <td>{{ $receipt->supplier?->name ?? '-' }}</td>
                            <td>
                                @if($receipt->status === 'confirmed')
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Dikonfirmasi</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Draft</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted" style="text-align:center;padding:24px;">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Material Requests --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-circle-plus text-primary"></i>
                <span class="card-title">Permintaan Material Terbaru</span>
                <a href="{{ route('material-requests.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Permintaan</th>
                            <th>Pemohon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $req)
                        <tr>
                            <td>
                                <a href="{{ route('material-requests.show', $req) }}" class="text-primary fw-600">
                                    {{ $req->request_number }}
                                </a>
                            </td>
                            <td>{{ $req->requester?->name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending'  => ['badge-warning', 'clock', 'Pending'],
                                        'approved' => ['badge-success', 'check', 'Disetujui'],
                                        'rejected' => ['badge-danger', 'xmark', 'Ditolak'],
                                        'fulfilled'=> ['badge-info', 'truck', 'Terpenuhi'],
                                    ];
                                    [$cls, $icon, $label] = $statusMap[$req->status] ?? ['badge-gray', 'question', $req->status];
                                @endphp
                                <span class="badge {{ $cls }}"><i class="fas fa-{{ $icon }}"></i> {{ $label }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted" style="text-align:center;padding:24px;">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Row 3: Tool Stats & Quick Links --}}
    <div class="grid grid-2">

        {{-- Tool Status --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-screwdriver-wrench text-primary"></i>
                <span class="card-title">Status Alat</span>
                <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-secondary">Detail</a>
            </div>
            <div class="card-body">
                <div class="grid grid-3" style="gap:12px;">
                    @php
                        $toolStatusConfig = [
                            'available'         => ['green',  'check-circle', 'Tersedia'],
                            'in_use'            => ['blue',   'hand-holding', 'Dipinjam'],
                            'under_maintenance' => ['amber',  'wrench',       'Maintenance'],
                        ];
                    @endphp
                    @foreach($toolStatusConfig as $key => [$color, $icon, $label])
                    <div style="text-align:center; padding:16px 12px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
                        <div class="stat-icon {{ $color }}" style="margin:0 auto 8px; width:40px; height:40px; font-size:17px;">
                            <i class="fas fa-{{ $icon }}"></i>
                        </div>
                        <div style="font-size:22px; font-weight:800; color:#0f172a;">{{ $toolStats[$key] ?? 0 }}</div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                @if($openOpname > 0)
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-clipboard-check"></i>
                    <div>Ada <strong>{{ $openOpname }}</strong> Stock Opname yang masih terbuka.
                        <a href="{{ route('stock-opnames.index') }}" class="text-warning fw-600"> Lihat →</a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt text-primary"></i>
                <span class="card-title">Aksi Cepat</span>
            </div>
            <div class="card-body">
                <div class="grid grid-2" style="gap:10px;">
                    @can('create goods receipts')
                    <a href="{{ route('goods-receipts.create') }}" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-truck-ramp-box"></i> Terima Barang
                    </a>
                    @endcan
                    @can('create material requests')
                    <a href="{{ route('material-requests.create') }}" class="btn btn-success w-full" style="justify-content:center;">
                        <i class="fas fa-file-circle-plus"></i> Buat Permintaan
                    </a>
                    @endcan
                    @can('create distributions')
                    <a href="{{ route('distributions.create') }}" class="btn btn-warning w-full" style="justify-content:center;">
                        <i class="fas fa-right-left"></i> Buat Distribusi
                    </a>
                    @endcan
                    @can('create tool assignments')
                    <a href="{{ route('tool-assignments.create') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        <i class="fas fa-hand-holding"></i> Pinjam Alat
                    </a>
                    @endcan
                    @can('create stock opname')
                    <a href="{{ route('stock-opnames.create') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        <i class="fas fa-clipboard-check"></i> Stock Opname
                    </a>
                    @endcan
                    @can('view reports')
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    @endcan
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
