<x-app-layout>
    <x-slot name="title">Permintaan Material</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Permintaan Material</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Pengajuan dan approval permintaan material</p>
        </div>
        @can('create material requests')
        <a href="{{ route('material-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Permintaan
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label class="form-label">No. Permintaan</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="MR-...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Ditolak</option>
                        <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Terpenuhi</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('material-requests.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Permintaan</th>
                        <th>Pemohon</th>
                        <th>Gudang</th>
                        <th>Dibutuhkan</th>
                        <th>Status</th>
                        <th>Diproses Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusMap = [
                            'draft'               => ['badge-gray',   'file',   'Draft'],
                            'submitted'           => ['badge-warning','clock',  'Menunggu Persetujuan'],
                            'approved'            => ['badge-success','check',  'Disetujui'],
                            'partially_fulfilled' => ['badge-info',   'truck',  'Terkirim Sebagian'],
                            'fulfilled'           => ['badge-info',   'truck',  'Terpenuhi'],
                            'rejected'            => ['badge-danger', 'xmark',  'Ditolak'],
                            'cancelled'           => ['badge-gray',   'ban',    'Dibatalkan'],
                        ];
                    @endphp
                    @forelse($requests as $req)
                    @php [$cls, $icon, $label] = $statusMap[$req->status] ?? ['badge-gray', 'question', $req->status]; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('material-requests.show', $req) }}" class="text-primary fw-600">
                                {{ $req->request_number }}
                            </a>
                        </td>
                        <td>{{ $req->requestedBy?->name ?? '-' }}</td>
                        <td>{{ $req->fromWarehouse?->name ?? '-' }}</td>
                        <td>{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d/m/Y') : '-' }}</td>
                        <td><span class="badge {{ $cls }}"><i class="fas fa-{{ $icon }}"></i> {{ $label }}</span></td>
                        <td>{{ $req->approvedBy?->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('material-requests.show', $req) }}" class="btn btn-sm btn-secondary btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-file-circle-plus"></i>
                                <h3>Belum Ada Permintaan</h3>
                                <p>Buat permintaan material pertama.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">{{ $requests->links() }}</div>
        @endif
    </div>
</x-app-layout>
