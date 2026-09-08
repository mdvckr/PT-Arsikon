<x-app-layout>
    <x-slot name="title">Tambah Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Tambah Baru</span>
    </div>

    <div class="card" style="max-width:650px;">
        <div class="card-header">
            <i class="fas fa-plus-circle text-primary"></i> <span class="card-title">Form Tambah Alat</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tools.store') }}">
                @csrf
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Kode Alat <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="mis. GEN / MOLEN" required>
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kode unik untuk jenis alat ini</div>
                        @error('code')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Alat <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', request('name')) }}" class="form-control" placeholder="Contoh: Genset Silent 5000W" required>
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <input list="typesList" name="type" id="type" value="{{ old('type', request('type')) }}" class="form-control" placeholder="Pilih atau Ketik Kategori..." autocomplete="off">
                        <datalist id="typesList">
                            @foreach($types as $t)
                                <option value="{{ $t }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="form-label">Merk / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}" class="form-control" placeholder="Honda, Makita, dll">
                    </div>
                    <div>
                        <label class="form-label">Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock_total" value="{{ old('stock_total', 1) }}" class="form-control" min="0" max="9999" required>
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Jumlah total unit yang tersedia di gudang</div>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Alat</button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
