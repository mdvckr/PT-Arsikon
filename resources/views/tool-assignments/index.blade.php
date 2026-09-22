<x-app-layout>
    <x-slot name="title">Peminjaman Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Peminjaman Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Catatan distribusi peminjaman alat terkelompok ke personal/proyek</p>
        </div>
        @can('create tool assignments')
        <a href="{{ route('tool-assignments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Pinjamkan Alat
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-4">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label class="form-label">Cari No. Pinjam / Peminjam / Alat</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No pinjam, nama peminjam, atau nama alat...">
                </div>
                <div style="min-width:170px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="active" {{ request('status') === 'active' || request('status') === 'assigned' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Pinjam</th>
                        <th>Peminjam</th>
                        <th>Gudang / Lokasi Proyek</th>
                        <th>Daftar Alat</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td>
                            <a href="{{ route('tool-assignments.show', $loan->id) }}" class="text-primary fw-600">
                                {{ $loan->loan_number }}
                            </a>
                            <div class="text-muted" style="font-size:11px;">Oleh: {{ $loan->assignedBy?->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $loan->borrower_name }}</div>
                            @if($loan->borrower_phone)
                            <div class="text-muted" style="font-size:11px;"><i class="fas fa-phone" style="font-size:9px;"></i> {{ $loan->borrower_phone }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $loan->location_name }}</div>
                            @if($loan->fromWarehouse)
                            <div class="text-muted" style="font-size:11px;">Asal: {{ $loan->fromWarehouse->name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;max-width:240px;">
                                @php
                                    $itemCount = $loan->items->count();
                                    $totalUnits = $loan->items->sum('quantity');
                                @endphp
                                <div class="fw-600" style="font-size:12.5px;color:#1e293b;">
                                    <i class="fas fa-toolbox text-muted me-1"></i> {{ $itemCount }} Jenis ({{ $totalUnits }} Unit)
                                </div>
                                <div class="text-muted" style="font-size:11.5px;line-height:1.3;">
                                    @foreach($loan->items->take(2) as $it)
                                        <span>• {{ $it->tool?->name ?? 'Alat' }} ({{ $it->quantity }}x)</span><br>
                                    @endforeach
                                    @if($itemCount > 2)
                                        <span style="color:#64748b;font-style:italic;">+ {{ $itemCount - 2 }} alat lainnya...</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($loan->assigned_at)->format('d/m/Y') }}</td>
                        <td>
                            @if($loan->expected_return_at)
                                @php
                                    $expDate = \Carbon\Carbon::parse($loan->expected_return_at);
                                    $isOverdue = in_array($loan->status, ['active', 'overdue']) && $expDate->isPast();
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
                            @if($loan->status === 'pending')
                                <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Menunggu Persetujuan</span>
                            @elseif($loan->status === 'returned')
                                <span class="badge badge-success"><i class="fas fa-check"></i> Dikembalikan</span>
                            @elseif($loan->status === 'overdue')
                                <span class="badge badge-danger"><i class="fas fa-clock"></i> Terlambat</span>
                            @elseif($loan->status === 'rejected')
                                <span class="badge badge-danger"><i class="fas fa-xmark"></i> Ditolak</span>
                            @elseif($loan->status === 'cancelled')
                                <span class="badge badge-gray"><i class="fas fa-ban"></i> Dibatalkan</span>
                            @else
                                <span class="badge badge-purple"><i class="fas fa-hand-holding"></i> Dipinjam</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tool-assignments.show', $loan->id) }}" class="btn btn-sm btn-secondary btn-icon" title="Lihat Detail Transaksi">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state text-center p-4">
                                <i class="fas fa-toolbox" style="font-size:32px;color:#cbd5e1;margin-bottom:8px;"></i>
                                <h4 class="text-muted" style="font-size:15px;">Belum Ada Catatan Peminjaman Alat</h4>
                                <p class="text-muted" style="font-size:12px;">Mulai pinjamkan alat dengan tombol 'Pinjamkan Alat'.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div style="padding:12px 20px;border-top:1px solid #f1f5f9;">{{ $loans->links() }}</div>
        @endif
    </div>
</x-app-layout>
