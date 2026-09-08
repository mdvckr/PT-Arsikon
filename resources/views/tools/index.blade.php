<x-app-layout>
    <x-slot name="title">Data Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Inventaris Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola inventaris alat kerja dan status pemakaian</p>
        </div>
        @can('create tools')
        <a href="{{ route('tools.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Alat
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Alat</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama, kode, S/N...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="under_maintenance" {{ request('status') === 'under_maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Alat & Merk</th>
                        <th>Tipe / Specs</th>
                        <th>Kategori</th>
                        <th>S/N</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $condColors = ['good'=>'badge-success', 'damaged'=>'badge-danger', 'under_maintenance'=>'badge-warning'];
                        $condLabels = ['good'=>'Baik', 'damaged'=>'Rusak', 'under_maintenance'=>'Perbaikan'];
                        $statColors = ['available'=>'badge-success', 'in_use'=>'badge-primary', 'under_maintenance'=>'badge-warning'];
                        $statLabels = ['available'=>'Tersedia', 'in_use'=>'Dipinjam', 'under_maintenance'=>'Maintenance'];
                    @endphp
                    @forelse($tools as $tool)
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">{{ $tool->code }}</code></td>
                        <td>
                            <div class="fw-600">{{ $tool->name }}</div>
                            @if($tool->brand)<div class="text-muted" style="font-size:11px;">{{ $tool->brand }}</div>@endif
                        </td>
                        <td><span class="badge badge-secondary">{{ $tool->type ?? '-' }}</span></td>
                        <td>{{ $tool->category?->name ?? '-' }}</td>
                        <td>{{ $tool->serial_number ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $condColors[$tool->condition] ?? 'badge-gray' }}">
                                {{ $condLabels[$tool->condition] ?? $tool->condition }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $statColors[$tool->status] ?? 'badge-gray' }}">
                                {{ $statLabels[$tool->status] ?? $tool->status }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('tools.show', $tool) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail"><i class="fas fa-eye"></i></a>
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                @endcan
                                @can('delete tools')
                                <form method="POST" action="{{ route('tools.destroy', $tool) }}" onsubmit="return confirm('Hapus alat {{ addslashes($tool->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada data alat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tools->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $tools->links() }}</div>
        @endif
    </div>
</x-app-layout>
