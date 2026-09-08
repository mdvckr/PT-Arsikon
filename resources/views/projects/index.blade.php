<x-app-layout>
    <x-slot name="title">Data Proyek</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Manajemen Proyek Konstruksi</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola data proyek konstruksi yang sedang atau telah berjalan</p>
        </div>
        @can('manage projects')
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Proyek
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Proyek</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama proyek, klien...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Perencanaan</option>
                        <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Berjalan</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Proyek</th>
                        <th>Klien</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Gudang Terkait</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statColors = [
                            'planning'  => 'badge-gray',
                            'ongoing'   => 'badge-primary',
                            'completed' => 'badge-success',
                            'suspended' => 'badge-danger',
                        ];
                        $statLabels = [
                            'planning'  => 'Perencanaan',
                            'ongoing'   => 'Berjalan',
                            'completed' => 'Selesai',
                            'suspended' => 'Ditangguhkan',
                        ];
                    @endphp
                    @forelse($projects as $proj)
                    <tr>
                        <td class="fw-600">{{ $proj->name }}</td>
                        <td>{{ $proj->client_name ?? '-' }}</td>
                        <td>{{ $proj->start_date ? \Carbon\Carbon::parse($proj->start_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $proj->end_date ? \Carbon\Carbon::parse($proj->end_date)->format('d/m/Y') : '-' }}</td>
                        <td><span class="badge badge-gray">{{ $proj->warehouses_count }} Gudang</span></td>
                        <td>
                            <span class="badge {{ $statColors[$proj->status] ?? 'badge-gray' }}">
                                {{ $statLabels[$proj->status] ?? $proj->status }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @can('manage projects')
                                <a href="{{ route('projects.edit', $proj) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('projects.destroy', $proj) }}" onsubmit="return confirm('Hapus proyek {{ addslashes($proj->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada data proyek</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $projects->links() }}</div>
        @endif
    </div>
</x-app-layout>
