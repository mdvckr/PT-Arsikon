<x-app-layout>
    <x-slot name="title">Data Proyek</x-slot>

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(37,99,235,0.12);color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px;">
                <i class="fas fa-building-columns"></i>
            </div>
            <div>
                <h2 class="fw-800" style="font-size:22px;color:#0f172a;line-height:1.2;">Data Proyek Konstruksi</h2>
                <p class="text-muted" style="font-size:13px;margin:2px 0 0 0;">Kelola data proyek konstruksi beserta site pergudangan terkait.</p>
            </div>
        </div>
        @if(auth()->user()->can('projects.manage') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.create') }}" class="btn btn-primary" style="border-radius:9px;padding:9px 18px;font-weight:700;">
                <i class="fas fa-plus-circle"></i> Tambah Proyek & Gudang
            </a>
        </div>
        @endif
    </div>

    <!-- TAB SWITCHER GUDANG & PROYEK -->
    <div class="flex items-center gap-2 mb-4" style="background:#f1f5f9;padding:5px;border-radius:12px;width:fit-content;border:1px solid #e2e8f0;">
        <a href="{{ route('warehouses.index') }}" class="btn btn-sm" style="border-radius:9px;font-weight:600;padding:8px 18px;display:flex;align-items:center;gap:8px;color:#475569;background:transparent;border:none;">
            <i class="fas fa-warehouse"></i>
            <span>Data Gudang</span>
            <span class="badge" style="background:#e2e8f0;color:#334155;font-size:11px;padding:2px 7px;border-radius:10px;">{{ \App\Models\Warehouse::count() }}</span>
        </a>
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary" style="border-radius:9px;font-weight:700;padding:8px 18px;display:flex;align-items:center;gap:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <i class="fas fa-building-columns"></i>
            <span>Data Proyek</span>
            <span class="badge" style="background:rgba(255,255,255,0.25);color:#fff;font-size:11px;padding:2px 7px;border-radius:10px;">{{ \App\Models\Project::count() }}</span>
        </a>
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
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Perencanaan</option>
                        <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Berjalan</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
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
                                @if(auth()->user()->can('projects.manage') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
                                <a href="{{ route('projects.edit', $proj) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('projects.destroy', $proj) }}" onsubmit="return confirm('Hapus proyek {{ addslashes($proj->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
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
