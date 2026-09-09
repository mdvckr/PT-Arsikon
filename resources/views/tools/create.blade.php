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
            <style>
                .btn-brand { background:#ea580c; color:#fff; }
                .btn-brand:hover { background:#c2410c; color:#fff; }
            </style>
            <form method="POST" action="{{ route('tools.store') }}" onsubmit="prepareSubmit()">
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
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="toggleNewCategory()">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $selectedCategoryId) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                            <option value="__new__">++ Kategori Baru...</option>
                        </select>
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Pilih kategori yang sudah ada, atau buat kategori baru di bawah.</div>
                        @error('category_id')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                    </div>
                    <div id="new_category_wrap" style="display:none;">
                        <label class="form-label">Nama Kategori Baru</label>
                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Alat Berat, Power Tools, dll">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila tidak ada.</div>
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
                    <button type="submit" class="btn btn-brand"><i class="fas fa-save"></i> Simpan Alat</button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleNewCategory() {
            var select = document.getElementById('category_select');
            var wrap = document.getElementById('new_category_wrap');
            var isNew = select.value === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            if (isNew) {
                document.getElementById('new_category').focus();
            }
        }
        function prepareSubmit() {
            var select = document.getElementById('category_select');
            if (select.value === '__new__') {
                select.value = '';
            }
        }
        toggleNewCategory();
    </script>
    @endpush
</x-app-layout>
