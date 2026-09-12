<x-app-layout>
    <x-slot name="title">Manajemen Gudang</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Gudang & Lokasi</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola informasi gudang pusat dan gudang proyek</p>
        </div>
        @can('manage warehouses')
        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Gudang
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Gudang</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau lokasi...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Tipe Gudang</label>
                    <select name="type" class="form-control">
                        <option value="">Semua Tipe</option>
                        <option value="main" {{ request('type') === 'main' ? 'selected' : '' }}>Gudang Pusat (Main)</option>
                        <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Gudang Proyek (Project)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Gudang</th>
                        <th>Tipe</th>
                        <th>Proyek Terkait</th>
                        <th>Lokasi / Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $wh)
                    <tr>
                        <td class="fw-600">{{ $wh->name }}</td>
                        <td>
                            @if($wh->is_central || $wh->type === 'central' || $wh->type === 'main')
                                <span class="badge badge-primary"><i class="fas fa-building"></i> Pusat</span>
                            @else
                                <span class="badge badge-info"><i class="fas fa-person-digging"></i> Proyek</span>
                            @endif
                        </td>
                        <td>{{ $wh->project?->name ?? '-' }}</td>
                        <td class="text-muted" style="max-width:250px;font-size:12px;">{{ Str::limit($wh->address ?? $wh->location, 50) ?: '-' }}</td>
                        <td>
                            @if($wh->is_active)
                                <span class="badge badge-success"><i class="fas fa-check-circle" style="font-size:10px;"></i> {{ $wh->status_label }}</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-pause-circle" style="font-size:10px;"></i> {{ $wh->status_label }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @can('manage warehouses')
                                <a href="{{ route('warehouses.edit', $wh) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('warehouses.destroy', $wh) }}" onsubmit="return confirm('Hapus gudang {{ addslashes($wh->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted p-4">Belum ada data gudang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $warehouses->links() }}</div>
        @endif
    </div>
</x-app-layout>
