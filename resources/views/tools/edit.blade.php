<x-app-layout>
    <x-slot name="title">Edit Alat — {{ $tool->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit — {{ $tool->name }}</span>
    </div>

    <div class="card" style="max-width:650px;">
        <div class="card-header">
            <i class="fas fa-pen text-warning"></i> <span class="card-title">Edit Alat</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tools.update', $tool) }}">
                @csrf @method('PUT')
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Kode Alat <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $tool->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                        @error('code')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Alat <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tool->name) }}" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <input list="typesList" name="type" id="type" value="{{ old('type', $tool->type) }}" class="form-control" placeholder="Pilih atau Ketik Kategori..." autocomplete="off">
                        <datalist id="typesList">
                            @foreach($types as $t)
                                <option value="{{ $t }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="form-label">Merk / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $tool->brand) }}" class="form-control">
                    </div>
                </div>

                {{-- Kelola Stok --}}
                <div style="border-top:2px dashed #e2e8f0;margin-top:20px;padding-top:16px;">
                    <div class="fw-600 mb-3" style="font-size:13px;color:#475569;">
                        <i class="fas fa-boxes-stacked text-primary me-1"></i> Kelola Stok
                    </div>
                    <div class="grid grid-2">
                        <div>
                            <label class="form-label">Total Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock_total" value="{{ old('stock_total', $tool->stock_total) }}" class="form-control" min="0" required>
                        </div>
                        <div>
                            <label class="form-label">Stok Tersedia <span class="text-danger">*</span></label>
                            <input type="number" name="stock_available" value="{{ old('stock_available', $tool->stock_available) }}" class="form-control" min="0" required>
                        </div>
                        <div>
                            <label class="form-label">Sedang Dipinjam</label>
                            <input type="number" value="{{ $tool->stock_borrowed }}" class="form-control" disabled style="background:#f1f5f9;">
                        </div>
                        <div>
                            <label class="form-label">Maintenance</label>
                            <input type="number" name="stock_maintenance" value="{{ old('stock_maintenance', $tool->stock_maintenance) }}" class="form-control" min="0">
                        </div>
                        <div>
                            <label class="form-label">Rusak</label>
                            <input type="number" name="stock_damaged" value="{{ old('stock_damaged', $tool->stock_damaged) }}" class="form-control" min="0">
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>

            {{-- Tambah Stok --}}
            @can('edit tools')
            <div style="border-top:2px dashed #e2e8f0;margin-top:20px;padding-top:16px;">
                <form method="POST" action="{{ route('tools.addStock', $tool) }}" class="flex gap-3 items-end" style="flex-wrap:wrap;">
                    @csrf
                    <div>
                        <div class="text-muted mb-1" style="font-size:12px;"><i class="fas fa-boxes-stacked text-success me-1"></i> Stok saat ini: <strong>{{ $tool->stock_total }} unit</strong> · <span class="text-success">{{ $tool->stock_available }} tersedia</span></div>
                        <div class="flex gap-2 items-center">
                            <label class="form-label mb-0">Tambah</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="500" style="width:80px;" required>
                            <label class="form-label mb-0">unit lagi</label>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Stok</button>
                        </div>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
