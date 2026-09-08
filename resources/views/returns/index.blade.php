<x-app-layout>
    <x-slot name="title">Pengembalian Material (Returns)</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Pengembalian Material (Returns)</h2>
            <p style="font-size:13px;color:#64748b;">Kelola pengembalian material dari proyek ke gudang pusat</p>
        </div>
        <a href="{{ route('returns.create') }}" class="btn btn-primary">+ Ajukan Pengembalian</a>
    </div>

    <!-- Filter Status -->
    <div class="card mb-4" style="padding: 12px 16px;">
        <form method="GET" action="{{ route('returns.index') }}" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Pengembalian..." class="form-input" style="max-width: 250px;">
            <select name="status" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Disetujui</option>
                <option value="received" {{ request('status')=='received' ? 'selected':'' }}>Diterima (Selesai)</option>
                <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('returns.index') }}" class="btn btn-ghost" style="color: #64748b;">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Retur</th>
                        <th>Dari Gudang</th>
                        <th>Tujuan</th>
                        <th>Alasan</th>
                        <th>Tgl Pengembalian</th>
                        <th>Pemohon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $r)
                        <tr>
                            <td><strong>{{ $r->return_number }}</strong></td>
                            <td>{{ $r->fromWarehouse->name ?? '-' }}</td>
                            <td>{{ $r->toWarehouse->name ?? '-' }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($r->reason ?? 'Lainnya') }}</span></td>
                            <td>{{ $r->return_date ? $r->return_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $r->requester->name ?? '-' }}</td>
                            <td>
                                @php
                                    $badgeClass = match($r->status) {
                                        'received' => 'badge-success',
                                        'approved' => 'badge-primary',
                                        'pending'  => 'badge-warning',
                                        'rejected' => 'badge-danger',
                                        default    => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('returns.show', $r) }}" class="btn btn-sm btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#94a3b8;">
                                Belum ada data pengembalian material.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
            <div style="padding: 12px 16px;">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
