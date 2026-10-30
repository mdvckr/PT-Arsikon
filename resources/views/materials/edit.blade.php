<x-app-layout>
    <x-slot name="title">Edit Material: {{ $material->name }}</x-slot>

    @push('styles')
    <style>
        .edit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .edit-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .edit-card-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .edit-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .edit-card-body {
            padding: 20px;
        }

        .stages-table {
            width: 100%;
            font-size: 12.5px;
            border-collapse: collapse;
            min-width: 650px;
        }

        .stages-table thead tr {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid #e2e8f0;
        }

        .stages-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }

        .stages-table tbody tr:hover {
            background: #fcfdfe;
        }

        .btn-delete-stage {
            width: 32px;
            height: 32px;
            padding: 0;
            color: #dc2626;
            background: #ffffff;
            border: 1px solid #fecaca;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-delete-stage:hover {
            background: #fef2f2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
            transform: scale(1.05);
        }
        .material-form-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            align-items: start;
            width: 100%;
        }

        @media (min-width: 1200px) {
            .material-form-layout {
                grid-template-columns: minmax(0, 1fr) 360px;
            }
        }

        .material-form-main {
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
            width: 100%;
        }

        .material-form-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
            width: 100%;
        }

        @media (min-width: 1200px) {
            .material-form-sidebar {
                position: sticky;
                top: 20px;
            }
        }
    </style>
    @endpush

    <div class="w-full">
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <div class="breadcrumb" style="margin-bottom:4px;font-size:12px;">
                    <a href="{{ route('materials.index') }}">Material</a>
                    <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:9px;" /></span>
                    <a href="{{ route('materials.show', $material) }}">{{ $material->name }}</a>
                    <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:9px;" /></span>
                    <span>Edit</span>
                </div>
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                    <x-lucide-square-pen class="text-primary" /> Edit Material: {{ $material->name }}
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('materials.show', $material) }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:7px 14px;display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-eye /> Lihat Detail
                </a>
                <a href="{{ route('materials.index') }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:7px 14px;display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-arrow-left /> Kembali
                </a>
            </div>
        </div>

        @if (isset($errors) && $errors->any())
        <div class="alert alert-danger mb-4" style="border-radius:8px;padding:12px 16px;font-size:13px;">
            <x-lucide-circle-alert class="me-2" />
            <strong>Terdapat kesalahan input:</strong>
            <ul style="margin:4px 0 0 16px;padding:0;">
                @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Main Grid Form --}}
        <form method="POST" action="{{ route('materials.update', $material) }}" onsubmit="prepareSubmit()" id="material-form" class="material-form-layout">
            @csrf @method('PUT')

            {{-- ── KOLOM KIRI: FORM UTAMA ── --}}
            <div class="material-form-main">

                {{-- ── CARD 1: KLASIFIKASI MATERIAL (3 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <x-lucide-layers class="text-primary" /> 1. Klasifikasi Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            {{-- Dropdown 1: Kategori --}}
                            <div>
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <label class="form-label mb-0" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                        Kategori <span style="color:#ef4444;">*</span>
                                    </label>
                                    @if(auth()->user()->can('delete categories') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat']))
                                    <button type="button" onclick="deleteSelectedCategory()" id="btn_delete_cat" style="display:none;font-size:11px;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;font-weight:600;white-space:nowrap;" title="Hapus kategori yang dipilih jika salah memasukkan">
                                        <x-lucide-trash-2 class="me-1" /> Hapus Kategori
                                    </button>
                                    @endif
                                </div>
                                <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $material->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                    <option value="__new__">✚ Kategori Baru...</option>
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Dropdown 2: Kelompok Barang (Autocomplete Datalist) --}}
                            <div>
                                <label class="form-label mb-0" for="type_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;display:block;">
                                    Kelompok <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="text" name="type" id="type_input" list="type_datalist"
                                       value="{{ old('type', $material->type) }}"
                                       class="form-control @error('type') is-invalid @enderror"
                                       placeholder="Pilih atau ketik kelompok (misal: Besi Beton, Semen)..."
                                       oninput="onTypeChange(); syncSummaryPreview();"
                                       style="height:38px;border-radius:6px;font-size:13px;" required autocomplete="off">
                                <datalist id="type_datalist">
                                    {{-- Diisi dinamis via JavaScript sesuai kategori --}}
                                </datalist>
                                <div id="type_hint" style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                    Pilih dari rekomendasi atau ketik kelompok baru
                                </div>
                                @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            {{-- Field 3: Ukuran / Spesifikasi (md:col-span-2 lg:col-span-1) --}}
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;display:block;">
                                    Ukuran / Spesifikasi
                                </label>
                                <input type="text" name="size" id="size_input" value="{{ old('size', $material->size) }}"
                                    class="form-control @error('size') is-invalid @enderror"
                                    placeholder="Contoh: D13mm × 12m, 50kg, M8"
                                    oninput="onTypeChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Kategori Baru (kondisional) --}}
                        <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                            <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Bahan Kimia, Cat, Kayu" style="height:38px;border-radius:6px;background:#fff;max-width:360px;" oninput="syncSummaryPreview()">
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 2: INFORMASI MATERIAL (2 KOLOM RESPONSIF, SUPPLIER FULL-WIDTH) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <x-lucide-box class="text-primary" /> 2. Informasi & Identitas Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- SKU --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">KODE MATERIAL (SKU) <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="sku" id="sku_input" value="{{ old('sku', $material->sku) }}"
                                    class="form-control font-monospace @error('sku') is-invalid @enderror"
                                    placeholder="Contoh: MAT-BES-001"
                                    style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                    oninput="this.value = this.value.toUpperCase(); syncSummaryPreview();" required>
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Merek / Brand --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <input type="text" name="brand" id="brand_input" value="{{ old('brand', $material->brand) }}"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    placeholder="Semen Gresik, Holcim, Krakatau Steel..."
                                    style="height:38px;border-radius:6px;font-size:13px;">
                                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Nama Material --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                    NAMA MATERIAL (LENGKAP) <span style="color:#ef4444;">*</span>
                                    <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                        Otomatis
                                    </span>
                                </label>
                                <input type="text" name="name" id="name_input" value="{{ old('name', $material->name) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    style="height:38px;border-radius:6px;font-size:13px;"
                                    oninput="syncSummaryPreview()"
                                    required data-manual-edit="true">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                    Edit manual bila perlu nama dagang khusus.
                                </div>
                            </div>

                            {{-- Satuan --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">SATUAN <span style="color:#ef4444;">*</span></label>
                                <select name="unit_id" id="unit_select" class="form-control @error('unit_id') is-invalid @enderror" onchange="toggleNewUnit(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                    <option value="">— Pilih Satuan —</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $material->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->code }})</option>
                                    @endforeach
                                    <option value="__new__">✚ Satuan Baru...</option>
                                </select>
                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Supplier (md:col-span-2 -> Penuh kiri-kanan, tidak pincang!) --}}
                            <div class="md:col-span-2">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">SUPPLIER / PEMASOK <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <input type="text" name="supplier" id="supplier_input" list="supplier_list"
                                    value="{{ old('supplier', $material->supplier?->name ?? $material->supplier_name) }}"
                                    class="form-control @error('supplier') is-invalid @enderror"
                                    placeholder="Ketik nama supplier atau pilih dari daftar..."
                                    oninput="syncSummaryPreview()"
                                    style="height:38px;border-radius:6px;font-size:13px;width:100%;">
                                <datalist id="supplier_list">
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->name }}">
                                    @endforeach
                                </datalist>
                                @error('supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">Supplier baru akan otomatis terdaftar bila belum ada di master data.</div>
                            </div>

                            {{-- Satuan Baru (kondisional) --}}
                            <div id="new_unit_wrap" class="md:col-span-2" style="display:none;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Nama Satuan Baru</label>
                                <input type="text" name="new_unit" id="new_unit" value="{{ old('new_unit') }}" class="form-control" placeholder="Contoh: Unit, Zak, Drum, Liter" style="height:38px;border-radius:6px;background:#fff;max-width:360px;" oninput="syncSummaryPreview()">
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">Satuan baru akan otomatis dibuat bila belum ada.</div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

            {{-- ── KOLOM KANAN: STICKY PANEL RINGKASAN & AKSI ── --}}
            <div class="material-form-sidebar">

                {{-- CARD PANEL RINGKASAN MATERIAL --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <x-lucide-info class="text-primary" /> Ringkasan Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">KODE MATERIAL</div>
                            <span id="preview_sku" class="font-mono font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded text-xs border border-slate-200 inline-block">
                                {{ $material->sku }}
                            </span>
                        </div>

                        <div class="mt-3">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">NAMA MATERIAL</div>
                            <div id="preview_name" class="font-bold text-slate-900 text-sm leading-snug">
                                {{ $material->name }}
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                            <span id="preview_category" class="badge" style="background:#eff6ff;color:#2563eb;font-size:11px;padding:3px 8px;border-radius:5px;font-weight:600;border:1px solid #bfdbfe;">
                                {{ $material->category?->name ?? 'Kategori' }}
                            </span>
                            <span id="preview_type" class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:11px;padding:3px 8px;border-radius:5px;font-weight:600;border:1px solid #ddd6fe;">
                                {{ $material->type ?: 'Kelompok' }}
                            </span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-2" style="font-size:12px;">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Satuan:</span>
                                <span id="preview_unit" class="text-slate-800 font-semibold">
                                    {{ $material->unit?->name ?? '-' }} ({{ $material->unit?->code ?? '-' }})
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Supplier:</span>
                                <span id="preview_supplier" class="text-slate-800 font-semibold truncate max-w-[180px]" title="{{ $material->supplier?->name ?? $material->supplier_name ?: '-' }}">
                                    {{ $material->supplier?->name ?? $material->supplier_name ?: '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Terdaftar:</span>
                                <span class="text-slate-600 font-medium text-xs">
                                    {{ $material->created_at ? $material->created_at->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD PANEL AKSI SIMPAN --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <x-lucide-save class="text-primary" /> Aksi Perubahan
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <p class="text-slate-500 text-xs mb-4 leading-relaxed">
                            Pastikan data klasifikasi, kode material, dan nama dagang telah sesuai sebelum menyimpan perubahan.
                        </p>

                        <button type="submit" class="btn btn-primary w-full" style="height:42px;font-size:13.5px;font-weight:700;border-radius:8px;box-shadow:0 3px 8px rgba(37,99,235,0.28);display:flex;align-items:center;justify-content:center;gap:8px;">
                            <x-lucide-check /> Simpan Perubahan
                        </button>

                        <a href="{{ route('materials.show', $material) }}" class="btn btn-light border w-full mt-2.5" style="height:38px;font-size:13px;font-weight:600;border-radius:8px;color:#64748b;display:flex;align-items:center;justify-content:center;gap:6px;">
                            <x-lucide-x /> Batal
                        </a>
                    </div>
                </div>

            </div>

        </form>
    </div>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function updateDeleteCategoryBtn() {
            var sel = document.getElementById('category_select');
            var btn = document.getElementById('btn_delete_cat');
            if (!btn) return;
            var val = sel ? sel.value : '';
            btn.style.display = (val && val !== '__new__') ? 'inline-flex' : 'none';
        }

        function deleteSelectedCategory() {
            var sel = document.getElementById('category_select');
            if (!sel || !sel.value || sel.value === '__new__') return;
            var catId = sel.value;
            var catName = sel.options[sel.selectedIndex].text;

            if (!confirm(`Hapus kategori "${catName}" dari sistem?\n\nKategori hanya dapat dihapus bila tidak ada material yang menggunakannya.`)) {
                return;
            }

            fetch(`{{ url('/categories') }}/${catId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Kategori berhasil dihapus.');
                    sel.remove(sel.selectedIndex);
                    sel.value = '';
                    onCategoryChange();
                } else {
                    alert(data.message || 'Gagal menghapus kategori.');
                }
            })
            .catch(() => alert('Terjadi kesalahan koneksi saat menghapus kategori.'));
        }

        function onCategoryChange() {
            var select  = document.getElementById('category_select');
            var catId   = select.value;
            var wrap    = document.getElementById('new_category_wrap');
            var isNew   = catId === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            updateDeleteCategoryBtn();
            if (isNew) { document.getElementById('new_category').focus(); return; }

            var datalist = document.getElementById('type_datalist');
            datalist.innerHTML = '';
            var hint = document.getElementById('type_hint');

            if (catId && existingGroups[catId] && existingGroups[catId].length > 0) {
                var groups = existingGroups[catId];
                groups.forEach(function(g) {
                    var opt = document.createElement('option');
                    opt.value = g;
                    datalist.appendChild(opt);
                });
                hint.textContent = groups.length + ' kelompok terdaftar di kategori ini. Pilih atau ketik baru.';
                hint.style.color = '#16a34a';
            } else if (catId) {
                hint.textContent = 'Belum ada kelompok di kategori ini. Ketik nama kelompok baru.';
                hint.style.color = '#2563eb';
            } else {
                hint.textContent = 'Pilih kategori dulu';
                hint.style.color = '#94a3b8';
            }
        }

        function onTypeChange() {
            var typeVal   = (document.getElementById('type_input').value || '').trim();
            var sizeVal   = (document.getElementById('size_input').value || '').trim();
            var nameInput = document.getElementById('name_input');
            var badge     = document.getElementById('name_auto_badge');

            if (nameInput.dataset.manualEdit === 'true' && nameInput.value.trim() !== '') return;

            var autoName = typeVal && sizeVal ? typeVal + ' ' + sizeVal : typeVal;
            if (autoName) {
                nameInput.value = autoName;
                badge.style.display = 'inline-flex';
                nameInput.dataset.manualEdit = '';
            } else {
                badge.style.display = 'none';
            }
        }

        function toggleNewUnit() {
            var select = document.getElementById('unit_select');
            var wrap   = document.getElementById('new_unit_wrap');
            var isNew  = select.value === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            if (isNew) document.getElementById('new_unit').focus();
        }

        function prepareSubmit() {
            var cat = document.getElementById('category_select');
            if (cat && cat.value === '__new__') cat.value = '';
            var unit = document.getElementById('unit_select');
            if (unit && unit.value === '__new__') unit.value = '';
            return true;
        }

        function syncSummaryPreview() {
            var skuVal = document.getElementById('sku_input')?.value || '—';
            var nameVal = document.getElementById('name_input')?.value || 'Nama Material';
            
            var catSelect = document.getElementById('category_select');
            var catVal = 'Kategori';
            if (catSelect && catSelect.value === '__new__') {
                catVal = document.getElementById('new_category')?.value || 'Kategori Baru';
            } else if (catSelect && catSelect.selectedIndex > 0) {
                catVal = catSelect.options[catSelect.selectedIndex].text;
            }

            var typeVal = document.getElementById('type_input')?.value || 'Kelompok';
            
            var unitSelect = document.getElementById('unit_select');
            var unitVal = '-';
            if (unitSelect && unitSelect.value === '__new__') {
                unitVal = document.getElementById('new_unit')?.value || 'Satuan Baru';
            } else if (unitSelect && unitSelect.selectedIndex > 0) {
                unitVal = unitSelect.options[unitSelect.selectedIndex].text;
            }

            var supplierVal = document.getElementById('supplier_input')?.value || '-';

            var elSku = document.getElementById('preview_sku');
            var elName = document.getElementById('preview_name');
            var elCat = document.getElementById('preview_category');
            var elType = document.getElementById('preview_type');
            var elUnit = document.getElementById('preview_unit');
            var elSup = document.getElementById('preview_supplier');

            if (elSku) elSku.textContent = skuVal.trim() ? skuVal : '—';
            if (elName) elName.textContent = nameVal.trim() ? nameVal : 'Nama Material';
            if (elCat) elCat.textContent = catVal;
            if (elType) elType.textContent = typeVal.trim() ? typeVal : 'Kelompok';
            if (elUnit) elUnit.textContent = unitVal;
            if (elSup) {
                elSup.textContent = supplierVal.trim() ? supplierVal : '-';
                elSup.title = supplierVal;
            }
        }

        onCategoryChange();
        toggleNewUnit();
        syncSummaryPreview();
    </script>
    @endpush
</x-app-layout>
