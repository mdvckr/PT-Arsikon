<x-app-layout>
    <x-slot name="title">Data Material</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Material</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola semua material konstruksi terdaftar</p>
        </div>
        @can('create materials')
        <a href="{{ route('materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Material
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Nama atau kode material...">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('materials.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Material</th>
                        <th>Tipe / Specs</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Supplier</th>

                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $i => $m)
                    <tr>
                        <td class="text-muted">{{ $materials->firstItem() + $i }}</td>
                        <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">{{ $m->code }}</code></td>
                        <td>
                            <div class="fw-600">{{ $m->name }}</div>
                            @if($m->description)
                            <div class="text-muted" style="font-size:11.5px;">{{ Str::limit($m->description, 50) }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-secondary">{{ $m->type ?? '-' }}</span></td>
                        <td>{{ $m->category?->name ?? '-' }}</td>
                        <td>{{ $m->unit?->abbreviation ?? $m->unit?->name ?? '-' }}</td>
                        <td>{{ $m->supplier?->name ?? '-' }}</td>

                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('materials.show', $m) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit materials')
                                <a href="{{ route('materials.edit', $m) }}" class="btn btn-sm btn-warning btn-icon" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endcan
                                @can('delete materials')
                                <form method="POST" action="{{ route('materials.destroy', $m) }}"
                                    onsubmit="return confirm('Hapus material {{ $m->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-boxes-stacked"></i>
                                <h3>Belum Ada Material</h3>
                                <p>Tambahkan material pertama untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materials->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
            {{ $materials->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
