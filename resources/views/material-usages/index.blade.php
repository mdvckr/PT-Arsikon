<x-app-layout>
    <x-slot name="title">Pemakaian Material Lapangan</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Pemakaian Material Lapangan</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Catatan pengeluaran barang & material ke mandor / pekerja proyek</p>
        </div>
        @can('create material usages')
        <a href="{{ route('material-usages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Pengeluaran Material
        </a>
        @endcan
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('material-usages.index') }}" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. Bukti / Mandor / Pekerjaan...">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Gudang / Site</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div style="min-width:140px;">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary" style="height:38px;padding:0 14px;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('material-usages.index') }}" class="btn btn-light border" style="height:38px;padding:0 12px;display:flex;align-items:center;" title="Reset Filter">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Bukti (Bon)</th>
                        <th>Tanggal</th>
                        <th>Gudang / Site</th>
                        <th>Penerima (Mandor/Tukang)</th>
                        <th>Bagian Pekerjaan / Zona</th>
                        <th>Material Dikeluarkan</th>
                        <th>Status</th>
                        <th>Petugas Gudang</th>
                        <th style="width:100px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    <tr style="{{ $usage->status === 'cancelled' ? 'opacity:0.55;' : '' }}">
                        <td>
                            <a href="{{ route('material-usages.show', $usage) }}" class="text-primary fw-600">
                                {{ $usage->usage_number }}
                            </a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $usage->warehouse?->is_central ? 'badge-warning' : 'badge-info' }}">
                                <i class="fas {{ $usage->warehouse?->is_central ? 'fa-building' : 'fa-trowel-bricks' }}"></i>
                                {{ $usage->warehouse?->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $usage->recipient_name }}</div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $usage->job_section ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-purple" style="font-size:12px;">
                                <i class="fas fa-boxes-stacked"></i> {{ $usage->items->count() }} Jenis Item
                            </span>
                        </td>
                        <td>
                            @if($usage->status === 'cancelled')
                                <span class="badge badge-danger" style="font-size:11px;">
                                    <i class="fas fa-ban"></i> Dibatalkan
                                </span>
                            @else
                                <span class="badge badge-success" style="font-size:11px;">
                                    <i class="fas fa-check-circle"></i> Selesai
                                </span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12.5px;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-secondary btn-icon" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($usage->status !== 'cancelled')
                                <a href="{{ route('material-usages.print', $usage) }}" target="_blank" class="btn btn-sm btn-light border btn-icon" title="Cetak Bukti Pengeluaran (Bon)">
                                    <i class="fas fa-print"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">
                            <i class="fas fa-dolly fa-2x mb-2 d-block opacity-40"></i>
                            Belum ada riwayat pengeluaran / pemakaian material lapangan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usages->hasPages())
        <div class="card-footer p-3">
            {{ $usages->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
