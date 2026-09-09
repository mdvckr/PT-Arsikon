<x-app-layout>
    <x-slot name="title">Data Distribusi (Surat Jalan)</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Distribusi & Surat Jalan</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola pengiriman barang antar gudang (Gudang Pusat ke Proyek)</p>
        </div>
        @can('create distributions')
        <a href="{{ route('distributions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Surat Jalan
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari No. Surat Jalan</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="SJ-...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Dalam Pengiriman</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai / Diterima</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('distributions.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Surat Jalan</th>
                        <th>Dari Gudang</th>
                        <th>Tujuan</th>
                        <th>Tgl Kirim</th>
                        <th>No. Permintaan (Ref)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusMap = [
                            'draft'     => ['badge-warning', 'clock', 'Draft'],
                            'in_transit'=> ['badge-primary', 'truck', 'Dalam Pengiriman'],
                            'completed' => ['badge-success', 'check-double', 'Selesai'],
                            'cancelled' => ['badge-gray', 'ban', 'Dibatalkan'],
                        ];
                    @endphp
                    @forelse($distributions as $dist)
                    @php [$cls, $icon, $label] = $statusMap[$dist->status] ?? ['badge-gray', 'question', $dist->status]; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('distributions.show', $dist) }}" class="text-primary fw-600">
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>{{ $dist->fromWarehouse?->name ?? '-' }}</td>
                        <td>{{ $dist->toWarehouse?->name ?? '-' }}</td>
                        <td>{{ $dist->delivery_date ? \Carbon\Carbon::parse($dist->delivery_date)->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($dist->materialRequest)
                            <a href="{{ route('material-requests.show', $dist->materialRequest) }}" class="text-muted" style="font-size:12.5px;">
                                <i class="fas fa-link"></i> {{ $dist->materialRequest->request_number }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $cls }}"><i class="fas fa-{{ $icon }}"></i> {{ $label }}</span></td>
                        <td>
                            <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-secondary btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada data distribusi / surat jalan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($distributions->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $distributions->links() }}</div>
        @endif
    </div>
</x-app-layout>
