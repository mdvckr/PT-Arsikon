<x-app-layout>
    <x-slot name="title">Peminjaman Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Peminjaman Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Catatan distribusi peminjaman alat ke personal/proyek</p>
        </div>
        @can('create tool assignments')
        <a href="{{ route('tool-assignments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Pinjamkan Alat
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Alat / Peminjam</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama alat...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="assigned" {{ request('status') === 'assigned' || request('status') === 'active' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Alat</th>
                        <th>Peminjam</th>
                        <th>Gudang / Lokasi</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assign)
                    <tr>
                        <td>
                            <div class="fw-600">{{ $assign->tool?->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:11.5px;">{{ $assign->tool?->code ?? '' }}</div>
                        </td>
                        <td>{{ $assign->assignedTo?->name ?? '-' }}</td>
                        <td>{{ $assign->fromWarehouse?->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($assign->assigned_at)->format('d/m/Y') }}</td>
                        <td>
                            @if($assign->expected_return_at)
                                @php
                                    $expDate = \Carbon\Carbon::parse($assign->expected_return_at);
                                    $isOverdue = in_array($assign->status, ['active', 'overdue']) && $expDate->isPast();
                                @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-700' : '' }}">
                                    {{ $expDate->format('d/m/Y') }}
                                    @if($isOverdue) <i class="fas fa-exclamation-triangle"></i> @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
<td>
                        @if($assign->status === 'pending')
                            <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Menunggu Persetujuan</span>
                        @elseif($assign->status === 'returned')
                            <span class="badge badge-success"><i class="fas fa-check"></i> Dikembalikan</span>
                        @elseif($assign->status === 'overdue')
                            <span class="badge badge-danger"><i class="fas fa-clock"></i> Terlambat</span>
                        @elseif($assign->status === 'rejected')
                            <span class="badge badge-danger"><i class="fas fa-xmark"></i> Ditolak</span>
                        @else
                            <span class="badge badge-purple"><i class="fas fa-hand-holding"></i> Dipinjam</span>
                        @endif
                    </td>
                        <td>
                            <a href="{{ route('tool-assignments.show', $assign) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada data peminjaman alat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $assignments->links() }}</div>
        @endif
    </div>
</x-app-layout>
