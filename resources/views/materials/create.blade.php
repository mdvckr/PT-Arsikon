<x-app-layout>
    <x-slot name="title">Tambah Material</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('materials.index') }}">Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Tambah Material</span>
    </div>

    <div class="card" style="max-width:700px;">
        <div class="card-header">
            <i class="fas fa-plus-circle text-primary"></i>
            <span class="card-title">Form Tambah Material</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('materials.store') }}" onsubmit="prepareSubmit()">
                @csrf
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Kode Material <span class="text-danger">*</span></label>
                        <input type="text" name="sku" value="{{ old('sku') }}"
                            class="form-control @error('sku') is-invalid @enderror"
                            placeholder="Contoh: BESI-01" required>
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Material <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Contoh: Tangga / Semen" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Tipe / Spesifikasi</label>
                        <input type="text" name="type" value="{{ old('type') }}"
                            class="form-control @error('type') is-invalid @enderror"
                            placeholder="Contoh: 3 Meter / 50 Kg / 10mm">
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="toggleNewCategory()">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                            <option value="__new__">++ Kategori Baru...</option>
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="new_category_wrap" style="display:none;">
                        <label class="form-label">Nama Kategori Baru</label>
                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Bahan Kimia, Cat, dll">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                    </div>
                    <div>
                        <label class="form-label">Satuan</label>
                        <select name="unit_id" id="unit_select" class="form-control @error('unit_id') is-invalid @enderror" onchange="toggleNewUnit()">
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                            @endforeach
                            <option value="__new__">++ Satuan Baru...</option>
                        </select>
                        @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="new_unit_wrap" style="display:none;">
                        <label class="form-label">Nama Satuan Baru</label>
                        <input type="text" name="new_unit" id="new_unit" value="{{ old('new_unit') }}" class="form-control" placeholder="Contoh: Unit, Zak, Drum, Liter">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Satuan baru akan otomatis dibuat bila belum ada.</div>
                    </div>

                </div>
                <div class="mt-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Deskripsi singkat material...">{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Material
                    </button>
                    <a href="{{ route('materials.index') }}" class="btn btn-secondary">Batal</a>
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
            if (isNew) document.getElementById('new_category').focus();
        }
        function toggleNewUnit() {
            var select = document.getElementById('unit_select');
            var wrap = document.getElementById('new_unit_wrap');
            var isNew = select.value === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            if (isNew) document.getElementById('new_unit').focus();
        }
        function prepareSubmit() {
            var cat = document.getElementById('category_select');
            if (cat.value === '__new__') cat.value = '';
            var unit = document.getElementById('unit_select');
            if (unit.value === '__new__') unit.value = '';
        }
        toggleNewCategory();
        toggleNewUnit();
    </script>
    @endpush
</x-app-layout>
