<x-app-layout>
    <x-slot name="title">Detail Alat: {{ $tool->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $tool->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:360px 1fr;gap:24px;align-items:start;">

        <div>
            <div class="card mb-4">
                <div class="card-header flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-screwdriver-wrench text-primary"></i> <span class="card-title">Informasi Alat</span>
                    </div>
                    @can('edit tools')
                    <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                    @endcan
                </div>
                <div class="card-body">
                    @php
                        $rows = [
                            ['Kode Alat', '<code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-weight:600;">'.$tool->code.'</code>'],
                            ['Nama Alat', $tool->name],
                            ['Kelompok Alat', $tool->type ? '<span class="badge" style="background:#e0e7ff;color:#4338ca;">'.$tool->type.'</span>' : '-'],
                            ['Spesifikasi / Ukuran', $tool->size ? '<span class="badge bg-light text-dark border"><i class="fas fa-ruler-combined me-1"></i>'.$tool->size.'</span>' : '-'],
                            ['Kategori', $tool->category?->name ?? '-'],
                            ['Merk / Brand', $tool->brand ?? '-'],
                            ['Gudang', $tool->currentWarehouse?->name ?? 'Gudang Pusat / Default'],
                            ['Tgl Terdaftar', $tool->created_at ? $tool->created_at->format('d M Y, H:i') : '-'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                        <span class="text-muted fw-500" style="width:130px;flex-shrink:0;">{{ $label }}</span>
                        <span class="fw-600 text-end">{!! $value !!}</span>
                    </div>
                    @endforeach

                    {{-- Status Stok Card --}}
                    <div class="mt-3 p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="fw-700 mb-2" style="font-size:12.5px;color:#334155;">
                            <i class="fas fa-boxes-stacked text-primary me-1"></i> Ringkasan Stok Bulk
                        </div>
                        <div class="grid grid-2" style="gap:8px;font-size:12px;">
                            <div class="p-2 rounded" style="background:#ffffff;border:1px solid #e2e8f0;">
                                <div class="text-muted" style="font-size:10.5px;">Total Stok</div>
                                <div class="fw-700" style="font-size:15px;color:#0f172a;">{{ $tool->stock_total }} <span style="font-size:11px;font-weight:normal;">unit</span></div>
                            </div>
                            <div class="p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="text-success" style="font-size:10.5px;font-weight:600;">Tersedia</div>
                                <div class="fw-700 text-success" style="font-size:15px;">{{ $tool->stock_available }} <span style="font-size:11px;font-weight:normal;">unit</span></div>
                            </div>
                            <div class="p-2 rounded" style="background:#fff7ed;border:1px solid #fed7aa;">
                                <div style="color:#c2410c;font-size:10.5px;font-weight:600;">Dipinjam</div>
                                <div class="fw-700" style="color:#c2410c;font-size:15px;">{{ $tool->stock_borrowed }} <span style="font-size:11px;font-weight:normal;">unit</span></div>
                            </div>
                            <div class="p-2 rounded" style="background:#fef2f2;border:1px solid #fecaca;">
                                <div class="text-danger" style="font-size:10.5px;font-weight:600;">Maint. / Rusak</div>
                                <div class="fw-700 text-danger" style="font-size:15px;">{{ $tool->stock_maintenance + $tool->stock_damaged }} <span style="font-size:11px;font-weight:normal;">unit</span></div>
                            </div>
                        </div>
                    </div>

                    @if($tool->notes)
                    <div class="mt-3 p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:12.5px;">
                        <strong>Catatan / Keterangan:</strong><br>
                        <div style="margin-top:3px;color:#475569;line-height:1.4;">{{ $tool->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            {{-- Tahap Kedatangan Barang --}}
            <div class="card mb-4">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck-ramp-box text-primary"></i> <span class="card-title">Jadwal & Riwayat Tahap Kedatangan (Flow T1, T2...)</span>
                    </div>
                    @if(!empty($tool->incoming_stages))
                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                        {{ count($tool->incoming_stages) }} Tahap
                    </span>
                    @endif
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">Tahap</th>
                                <th>Tanggal</th>
                                <th style="text-align:center;">Qty Unit</th>
                                <th>Status</th>
                                <th>Keterangan / Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($tool->incoming_stages) && count($tool->incoming_stages) > 0)
                                @foreach($tool->incoming_stages as $stg)
                                @php $isReceived = ($stg['status'] ?? 'received') === 'received'; @endphp
                                <tr>
                                    <td><strong class="font-monospace" style="color:#2563eb;">{{ $stg['stage'] ?? ('T'.($loop->iteration)) }}</strong></td>
                                    <td>{{ !empty($stg['date']) ? \Carbon\Carbon::parse($stg['date'])->format('d M Y') : '-' }}</td>
                                    <td style="text-align:center;"><strong>{{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }}</strong> unit</td>
                                    <td>
                                        <span class="badge" style="background:{{ $isReceived ? '#f0fdf4' : '#fffbeb' }};color:{{ $isReceived ? '#166534' : '#b45309' }};border:1px solid {{ $isReceived ? '#bbf7d0' : '#fde68a' }};">
                                            <i class="fas {{ $isReceived ? 'fa-check-circle text-success' : 'fa-clock text-warning' }} me-1"></i>
                                            {{ $isReceived ? 'Sudah Masuk' : 'Rencana Kedatangan' }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $stg['notes'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="5" class="text-center text-muted p-3">Belum ada rincian tahap kedatangan bertahap yang dicatat.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Peminjaman --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hand-holding text-primary"></i> <span class="card-title">Riwayat Peminjaman (10 Terakhir)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th>Peminjam / Proyek</th>
                                <th>Gudang Asal</th>
                                <th style="text-align:center;">Qty</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->assignments()->latest()->take(10)->get() as $assign)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $assign->assignedToUser?->name ?? ($assign->notes ?? '-') }}</div>
                                    @if($assign->project)
                                    <div class="text-muted" style="font-size:11px;"><i class="fas fa-building me-1"></i>{{ $assign->project->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $assign->fromWarehouse?->name ?? '-' }}</td>
                                <td style="text-align:center;"><strong>{{ $assign->quantity ?? 1 }}</strong> unit</td>
                                <td>{{ \Carbon\Carbon::parse($assign->assigned_at)->format('d/m/Y') }}</td>
                                <td>
                                    @if($assign->status === 'returned')
                                        <span class="badge badge-success">Dikembalikan</span>
                                    @elseif($assign->status === 'active')
                                        <span class="badge badge-primary">Dipinjam</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($assign->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted p-4">Belum ada riwayat peminjaman untuk alat ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Riwayat Maintenance --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-wrench text-primary"></i> <span class="card-title">Riwayat Maintenance (5 Terakhir)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->maintenances()->latest()->take(5)->get() as $maint)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($maint->start_date)->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($maint->type) }}</td>
                                <td>
                                    @if($maint->status === 'completed')
                                        <span class="badge badge-success">Selesai</span>
                                    @else
                                        <span class="badge badge-warning">Proses</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($maint->cost ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted p-4">Belum ada riwayat maintenance</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
