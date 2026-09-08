<x-app-layout>
    <x-slot name="title">Data Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Inventaris Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola inventaris alat kerja dan stok pemakaian</p>
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
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau kode alat...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Kategori</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2" style="align-items:flex-end;">
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
                        <th>Kode & Nama Alat</th>
                        <th>Kategori</th>
                        <th>Merk</th>
                        <th style="text-align:center;">Total Stok</th>
                        <th style="text-align:center;">Tersedia</th>
                        <th style="text-align:center;">Dipinjam</th>
                        <th style="text-align:center;">Maintenance</th>
                        <th style="text-align:center;">Rusak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tools as $tool)
                    <tr>
                        <td>
                            <div class="fw-600" style="font-size:13px;">{{ $tool->name }}</div>
                            <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:11px;">{{ $tool->code }}</code>
                        </td>
                        <td>
                            @if($tool->type)
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">{{ $tool->type }}</span>
                            @else
                            <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="font-size:13px;">{{ $tool->brand ?? '-' }}</td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;font-size:15px;color:#0f172a;">{{ $tool->stock_total }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_available > 0)
                            <span class="badge badge-success">{{ $tool->stock_available }}</span>
                            @else
                            <span class="badge" style="background:#f1f5f9;color:#94a3b8;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_borrowed > 0)
                            <span class="badge badge-primary">{{ $tool->stock_borrowed }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_maintenance > 0)
                            <span class="badge badge-warning">{{ $tool->stock_maintenance }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_damaged > 0)
                            <span class="badge badge-danger">{{ $tool->stock_damaged }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-warning btn-icon" title="Edit & Kelola Stok"><i class="fas fa-pen"></i></a>
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
                    <tr><td colspan="9" class="text-center text-muted p-4">Belum ada data alat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tools->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $tools->links() }}</div>
        @endif
    </div>
</x-app-layout>
