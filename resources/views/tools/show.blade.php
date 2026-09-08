<x-app-layout>
    <x-slot name="title">Detail Alat: {{ $tool->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $tool->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:350px 1fr;gap:24px;align-items:start;">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-screwdriver-wrench text-primary"></i> <span class="card-title">Informasi Alat</span>
                @can('edit tools')
                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                @endcan
            </div>
            <div class="card-body">
                @php
                    $condColors = ['good'=>'text-success', 'damaged'=>'text-danger', 'under_maintenance'=>'text-warning'];
                    $condLabels = ['good'=>'Baik', 'damaged'=>'Rusak', 'under_maintenance'=>'Perbaikan'];
                    $statColors = ['available'=>'badge-success', 'in_use'=>'badge-primary', 'under_maintenance'=>'badge-warning'];
                    $statLabels = ['available'=>'Tersedia', 'in_use'=>'Dipinjam', 'under_maintenance'=>'Maintenance'];
                    $rows = [
                        ['Kode', '<code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;">'.$tool->code.'</code>'],
                        ['Nama Alat', $tool->name],
                        ['Kategori', $tool->category?->name ?? '-'],
                        ['Merk / Brand', $tool->brand ?? '-'],
                        ['Serial Number', $tool->serial_number ?? '-'],
                        ['Kondisi', '<span class="fw-700 '.($condColors[$tool->condition]??'').'">'.($condLabels[$tool->condition]??$tool->condition).'</span>'],
                        ['Status', '<span class="badge '.($statColors[$tool->status]??'badge-gray').'">'.($statLabels[$tool->status]??$tool->status).'</span>'],
                        ['Tgl Pembelian', $tool->purchase_date ? \Carbon\Carbon::parse($tool->purchase_date)->format('d/m/Y') : '-'],
                        ['Harga Beli', 'Rp '.number_format($tool->purchase_price, 0, ',', '.')],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted fw-500" style="width:120px;flex-shrink:0;">{{ $label }}</span>
                    <span class="fw-600 text-end">{!! $value !!}</span>
                </div>
                @endforeach
                @if($tool->description)
                <div class="mt-3 p-3 bg-gray-50 rounded" style="background:#f8fafc;border-radius:8px;font-size:13px;">
                    <strong>Deskripsi:</strong><br>{{ $tool->description }}
                </div>
                @endif
            </div>
        </div>

        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hand-holding text-primary"></i> <span class="card-title">Riwayat Peminjaman (10 Terakhir)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Gudang / Lokasi</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->toolAssignments()->latest()->take(10)->get() as $assign)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $assign->assignee?->name ?? '-' }}</div>
                                </td>
                                <td>{{ $assign->warehouse?->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($assign->assigned_at)->format('d/m/Y') }}</td>
                                <td>
                                    @if($assign->status === 'returned')
                                        <span class="badge badge-success">Dikembalikan</span>
                                    @else
                                        <span class="badge badge-warning">Dipinjam</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted p-4">Belum ada riwayat peminjaman</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-wrench text-primary"></i> <span class="card-title">Riwayat Maintenance (5 Terakhir)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
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
                                <td>Rp {{ number_format($maint->cost, 0, ',', '.') }}</td>
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
