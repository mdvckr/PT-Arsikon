<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Page Header --}}
    @php
        $authUser = auth()->user();
        $activeWh = $authUser->activeWarehouse();
        if ($authUser->hasRole('Owner')) {
            $userRoleText = 'Owner / Direksi';
            $wsTypeText = 'Multi-Gudang (Akses Seluruh Proyek)';
        } elseif ($authUser->hasRole('Admin Gudang Pusat') || ($authUser->hasRole('Admin') && !$authUser->hasRole('Admin Gudang Proyek'))) {
            $userRoleText = 'Admin Gudang Pusat';
            $wsTypeText = 'Logistik Sentral Jakarta';
        } elseif ($authUser->hasRole('Admin Gudang Proyek')) {
            $userRoleText = 'Admin Gudang Proyek';
            $wsTypeText = 'Site ' . ($activeWh?->project?->name ?? 'Lapangan');
        } elseif ($authUser->hasRole('Admin PO')) {
            $userRoleText = 'Admin Pengadaan (PO)';
            $wsTypeText = 'Divisi Pembelian & Supplier';
        } else {
            $userRoleText = $authUser->roles->first()?->name ?? 'Petugas';
            $wsTypeText = 'Operasional Gudang';
        }
    @endphp

    <div class="flex items-center justify-between mb-4" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 class="fw-700" style="font-size: 20px; color: #0f172a; margin: 0;">Dashboard Operasional</h2>
            <p class="text-muted" style="font-size: 13px; margin-top: 4px;">Ringkasan inventaris & status transaksi pada workspace aktif.</p>
        </div>
        <div class="flex items-center gap-2" style="background:#ffffff; border:1px solid #e2e8f0; padding:6px 14px; border-radius:30px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:12px; color:#475569;">
                <span class="text-muted">Peran:</span> <strong style="color:#0f172a;">{{ $userRoleText }}</strong>
            </div>
            <span style="color:#cbd5e1;">|</span>
            <div style="font-size:12px; color:#475569;">
                <span class="text-muted">Workspace:</span>
                <span class="badge {{ ($activeWh?->is_central ?? false) ? 'badge-primary' : 'badge-info' }}" style="font-size:11px;">
                    <i class="{{ ($activeWh?->is_central ?? false) ? 'fas fa-building' : 'fas fa-helmet-safety' }}" style="font-size:10px;"></i>
                    {{ $activeWh?->name ?? 'Gudang Pusat' }}
                </span>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-4" style="margin-bottom: 20px;">
        <div class="stat-card">
            <div class="stat-info">
                <div class="label">Total Material</div>
                <div class="value">{{ number_format($totalMaterials) }}</div>
                <div class="sub">Jenis material terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="label">Total Alat</div>
                <div class="value">{{ number_format($totalTools) }}</div>
                <div class="sub">Unit alat terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="label">Permintaan Pending</div>
                <div class="value">{{ number_format($pendingRequests) }}</div>
                <div class="sub">Menunggu persetujuan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="label">Stok Rendah</div>
                <div class="value" style="{{ $lowStockItems > 0 ? 'color: #dc2626;' : '' }}">{{ number_format($lowStockItems) }}</div>
                <div class="sub">Item di bawah batas minimum</div>
            </div>
        </div>
    </div>

    {{-- Row 2: Recent Material Requests --}}
    <div style="margin-bottom: 20px;">
        {{-- Recent Material Requests --}}
        <div class="card">
            <div class="card-header">
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
                            <td>{{ $req->requestedBy?->name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending'  => ['badge-warning', 'Pending'],
                                        'approved' => ['badge-success', 'Disetujui'],
                                        'rejected' => ['badge-danger', 'Ditolak'],
                                        'fulfilled'=> ['badge-info', 'Terpenuhi'],
                                    ];
                                    [$cls, $label] = $statusMap[$req->status] ?? ['badge-gray', $req->status];
                                @endphp
                                <span class="badge {{ $cls }}">{{ $label }}</span>
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
                <span class="card-title">Status Alat</span>
                <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-secondary">Detail</a>
            </div>
            <div class="card-body">
                <div class="grid grid-3" style="gap:12px;">
                    @php
                        $toolStatusConfig = [
                            'available'         => 'Tersedia',
                            'in_use'            => 'Dipinjam',
                            'under_maintenance' => 'Maintenance',
                        ];
                    @endphp
                    @foreach($toolStatusConfig as $key => $label)
                    <div style="text-align:center; padding:16px 12px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                        <div style="font-size:24px; font-weight:700; color:#0f172a;">{{ $toolStats[$key] ?? 0 }}</div>
                        <div style="font-size:12px; color:#64748b; margin-top:4px;">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                @if($openOpname > 0)
                <div class="alert alert-warning mt-4" style="font-size:13px;">
                    <div>Ada <strong>{{ $openOpname }}</strong> Stock Opname yang masih terbuka.
                        <a href="{{ route('stock-opnames.index') }}" class="text-warning fw-600"> Lihat &rarr;</a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Aksi Cepat</span>
            </div>
            <div class="card-body">
                <div class="grid grid-2" style="gap:10px;">
                    @can('create material requests')
                    <a href="{{ route('material-requests.create') }}" class="btn btn-primary w-full" style="justify-content:center;">
                        Buat Permintaan
                    </a>
                    @endcan
                    @can('create distributions')
                    <a href="{{ route('distributions.create') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        Buat Distribusi
                    </a>
                    @endcan
                    @can('create tool assignments')
                    <a href="{{ route('tool-assignments.create') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        Pinjam Alat
                    </a>
                    @endcan
                    @can('create stock opname')
                    <a href="{{ route('stock-opnames.create') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        Stock Opname
                    </a>
                    @endcan
                    @can('view reports')
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                        Laporan
                    </a>
                    @endcan
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
