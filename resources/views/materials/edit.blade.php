<x-app-layout>
    <x-slot name="title">Edit Material: {{ $material->name }}</x-slot>

    @push('styles')
    <style>
        .edit-material-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1100px;
            margin-bottom: 40px;
        }

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
            min-width: 700px;
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

        .edit-actions-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        @media (max-width: 768px) {
            .grid-3 {
                grid-template-columns: 1fr !important;
            }
            .grid-2 {
                grid-template-columns: 1fr !important;
            }
            .edit-actions-bar {
                flex-direction: column-reverse;
                align-items: stretch;
            }
            .edit-actions-bar > div,
            .edit-actions-bar button,
            .edit-actions-bar a {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
    @endpush

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;font-size:12px;">
                <a href="{{ route('materials.index') }}">Material</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                <a href="{{ route('materials.show', $material) }}">{{ $material->name }}</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                <span>Edit</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-pen-to-square text-primary"></i> Edit Material: {{ $material->name }}
            </h2>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('materials.show', $material) }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:6px 14px;">
                <i class="fas fa-eye me-1"></i> Lihat Detail
            </a>
            <a href="{{ route('materials.index') }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:6px 14px;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
    <div class="alert alert-danger mb-4" style="border-radius:8px;padding:12px 16px;font-size:13px;">
        <i class="fas fa-circle-exclamation me-2"></i>
        <strong>Terdapat kesalahan input:</strong>
        <ul style="margin:4px 0 0 16px;padding:0;">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('materials.update', $material) }}" onsubmit="prepareSubmit()" id="material-form">
        @csrf @method('PUT')

        <div class="edit-material-layout">

            {{-- ── SECTION 1: KLASIFIKASI MATERIAL ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div class="edit-card-title">
                        <i class="fas fa-layer-group text-primary"></i> 1. Klasifikasi Material
                    </div>
                </div>
                <div class="edit-card-body">
                    <div class="grid grid-3" style="gap:16px;">
                        {{-- Dropdown 1: Kategori --}}
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <label class="form-label mb-0" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                    Kategori <span style="color:#ef4444;">*</span>
                                </label>
                                @if(auth()->user()->can('delete categories') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat']))
                                <button type="button" onclick="deleteSelectedCategory()" id="btn_delete_cat" style="display:none;font-size:11px;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;font-weight:600;" title="Hapus kategori yang dipilih jika salah memasukkan">
                                    <i class="fas fa-trash-can me-1"></i> Hapus Kategori
                                </button>
                                @endif
                            </div>
                            <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange()" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">— Pilih Kategori —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $material->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                                <option value="__new__">✚ Kategori Baru...</option>
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown 2: Kelompok Barang --}}
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <label class="form-label mb-0" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                    Kelompok <span style="color:#ef4444;">*</span>
                                </label>
                                <button type="button" id="btn_toggle_custom_type" onclick="toggleTypeMode(true)" style="font-size:11px;color:#2563eb;background:none;border:none;cursor:pointer;padding:0;font-weight:600;">
                                    + Ketik Baru
                                </button>
                            </div>
                            <div id="type_select_wrap">
                                <select id="type_select" class="form-control @error('type') is-invalid @enderror" onchange="onTypeSelectChange()" style="height:38px;border-radius:6px;font-size:13px;">
                                    <option value="">— Pilih Kelompok —</option>
                                    <option value="__new__">✚ Ketik Kelompok Baru...</option>
                                </select>
                            </div>
                            <div id="type_custom_wrap" style="display:none;">
                                <div style="display:flex;gap:6px;">
                                    <input type="text" id="type_custom_input" class="form-control" placeholder="Ketik nama kelompok baru..." oninput="onTypeCustomInput()" style="height:38px;border-radius:6px;font-size:13px;">
                                    <button type="button" onclick="toggleTypeMode(false)" title="Kembali ke dropdown" class="btn btn-light border" style="height:38px;padding:0 12px;border-radius:6px;font-size:12px;color:#64748b;white-space:nowrap;flex-shrink:0;">
                                        Pilih
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="type" id="type_input" value="{{ old('type', $material->type) }}">
                            <div id="type_hint" style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                Pilih kategori dulu
                            </div>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Field 3: Ukuran --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Ukuran / Spesifikasi
                            </label>
                            <input type="text" name="size" id="size_input" value="{{ old('size', $material->size) }}"
                                class="form-control @error('size') is-invalid @enderror"
                                placeholder="Contoh: D13mm × 12m, 50kg, M8"
                                oninput="onTypeChange()" style="height:38px;border-radius:6px;font-size:13px;">
                            @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Kategori Baru (kondisional) --}}
                    <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Bahan Kimia, Cat, Kayu" style="height:38px;border-radius:6px;background:#fff;max-width:360px;">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2: INFORMASI MATERIAL ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div class="edit-card-title">
                        <i class="fas fa-cube text-primary"></i> 2. Informasi & Identitas Material
                    </div>
                </div>
                <div class="edit-card-body">
                    <div class="grid grid-2" style="gap:16px;">
                        {{-- SKU --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">KODE MATERIAL (SKU) <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="sku" value="{{ old('sku', $material->sku) }}"
                                class="form-control font-monospace @error('sku') is-invalid @enderror"
                                placeholder="Contoh: MAT-BES-001"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Merek / Brand --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                            <input type="text" name="brand" id="brand_input" value="{{ old('brand', $material->brand) }}"
                                class="form-control @error('brand') is-invalid @enderror"
                                placeholder="Semen Gresik, Holcim, Krakatau Steel..."
                                style="height:38px;border-radius:6px;font-size:13px;">
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nama Material --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;">
                                NAMA MATERIAL (LENGKAP) <span style="color:#ef4444;">*</span>
                                <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                    Otomatis
                                </span>
                            </label>
                            <input type="text" name="name" id="name_input" value="{{ old('name', $material->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                style="height:38px;border-radius:6px;font-size:13px;"
                                required data-manual-edit="true">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                Edit manual bila perlu nama dagang khusus.
                            </div>
                        </div>

                        {{-- Satuan --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">SATUAN <span style="color:#ef4444;">*</span></label>
                            <select name="unit_id" id="unit_select" class="form-control @error('unit_id') is-invalid @enderror" onchange="toggleNewUnit()" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">— Pilih Satuan —</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $material->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->code }})</option>
                                @endforeach
                                <option value="__new__">✚ Satuan Baru...</option>
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Supplier --}}
                        <div style="grid-column:span 2;">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">SUPPLIER / PEMASOK <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                            <input type="text" name="supplier" id="supplier_input" list="supplier_list"
                                value="{{ old('supplier', $material->supplier?->name ?? $material->supplier_name) }}"
                                class="form-control @error('supplier') is-invalid @enderror"
                                placeholder="Ketik nama supplier atau pilih dari daftar..."
                                style="height:38px;border-radius:6px;font-size:13px;max-width:460px;">
                            <datalist id="supplier_list">
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->name }}">
                                @endforeach
                            </datalist>
                            @error('supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Supplier baru akan otomatis terdaftar bila belum ada di master data.</div>
                        </div>

                        {{-- Satuan Baru --}}
                        <div id="new_unit_wrap" style="display:none;grid-column:span 2;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Nama Satuan Baru</label>
                            <input type="text" name="new_unit" id="new_unit" value="{{ old('new_unit') }}" class="form-control" placeholder="Contoh: Unit, Zak, Drum, Liter" style="height:38px;border-radius:6px;background:#fff;max-width:360px;">
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Satuan baru akan otomatis dibuat bila belum ada.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 3: TAHAP KEDATANGAN BARANG ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div>
                        <div class="edit-card-title">
                            <i class="fas fa-calendar-alt text-primary"></i> 3. Tahap Kedatangan Barang
                        </div>
                        <div class="text-muted" style="font-size:12px;margin-top:2px;">
                            Jadwal kedatangan bertahap. Status terkunci otomatis sebagai <strong>Rencana</strong> dan berubah otomatis saat barang diterima.
                        </div>
                    </div>
                    <button type="button" onclick="addStageRow()" class="btn btn-sm btn-primary" style="height:34px;font-size:12px;font-weight:600;padding:6px 14px;border-radius:6px;">
                        <i class="fas fa-plus me-1"></i> Tambah Tahap
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="stages-table" id="stages_table">
                        <thead>
                            <tr>
                                <th style="padding:10px 14px;width:95px;font-weight:700;">Tahap</th>
                                <th style="padding:10px 14px;width:160px;font-weight:700;">Tanggal Rencana</th>
                                <th style="padding:10px 14px;width:130px;font-weight:700;text-align:right;">Qty Masuk</th>
                                <th style="padding:10px 14px;width:160px;font-weight:700;">Status (Otomatis)</th>
                                <th style="padding:10px 14px;font-weight:700;">Keterangan</th>
                                <th style="padding:10px 14px;width:50px;text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="stages_tbody">
                            {{-- Baris dinamis --}}
                        </tbody>
                    </table>
                    <div id="stages_empty" style="display:none;padding:28px 16px;text-align:center;color:#94a3b8;">
                        <i class="fas fa-calendar-plus" style="font-size:28px;margin-bottom:8px;color:#cbd5e1;display:block;"></i>
                        <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada jadwal tahap kedatangan.</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;">Klik <strong>Tambah Tahap</strong> bila material ini memiliki pengiriman terjadwal.</div>
                    </div>
                </div>

                {{-- Summary Bar --}}
                <div style="padding:12px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="display:flex;align-items:center;gap:16px;font-size:12.5px;">
                        <div>Masuk: <strong id="summary_received" style="color:#16a34a;font-size:13px;">0</strong></div>
                        <div>Rencana: <strong id="summary_planned" style="color:#b45309;font-size:13px;">0</strong></div>
                        <div>Total: <strong id="summary_total" style="color:#0f172a;font-size:13px;">0</strong></div>
                    </div>
                    <div class="text-muted" style="font-size:11.5px;">
                        <i class="fas fa-lock text-muted me-1"></i> Status terkunci otomatis sebagai <strong>Rencana</strong>, dan akan berubah ke <strong>Sudah Masuk</strong> otomatis saat dicatat di menu Penerimaan Barang.
                    </div>
                </div>
            </div>

            {{-- ── ACTION BUTTONS ── --}}
            <div class="edit-actions-bar">
                <a href="{{ route('materials.show', $material) }}" class="btn btn-light border" style="height:38px;padding:0 18px;font-size:13px;font-weight:500;border-radius:7px;color:#64748b;display:inline-flex;align-items:center;">
                    <i class="fas fa-times me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 24px;font-size:13.5px;font-weight:700;border-radius:7px;box-shadow:0 2px 4px rgba(37,99,235,0.25);display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function toggleTypeMode(isCustom) {
            var selectWrap  = document.getElementById('type_select_wrap');
            var customWrap  = document.getElementById('type_custom_wrap');
            var toggleBtn   = document.getElementById('btn_toggle_custom_type');
            var select      = document.getElementById('type_select');
            var customInput = document.getElementById('type_custom_input');
            var hiddenInput = document.getElementById('type_input');

            if (isCustom) {
                selectWrap.style.display = 'none';
                customWrap.style.display = 'block';
                if (toggleBtn) toggleBtn.style.display = 'none';
                if (!customInput.value && hiddenInput.value) customInput.value = hiddenInput.value;
                customInput.focus();
                hiddenInput.value = customInput.value;
            } else {
                selectWrap.style.display = 'block';
                customWrap.style.display = 'none';
                if (toggleBtn) toggleBtn.style.display = '';
                if (hiddenInput.value) select.value = hiddenInput.value;
                if (select.value && select.value !== '__new__') hiddenInput.value = select.value;
            }
            onTypeChange();
        }

        function onTypeSelectChange() {
            var select = document.getElementById('type_select');
            var val    = select.value;
            if (val === '__new__') { toggleTypeMode(true); }
            else { document.getElementById('type_input').value = val; onTypeChange(); }
        }

        function onTypeCustomInput() {
            var customInput = document.getElementById('type_custom_input');
            document.getElementById('type_input').value = customInput.value;
            onTypeChange();
        }

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
            updateDeleteCategoryBtn();
            var catSelect = document.getElementById('category_select');
            var newWrap   = document.getElementById('new_category_wrap');
            var catId     = catSelect.value;

            if (catId === '__new__') {
                newWrap.style.display = 'block';
                document.getElementById('new_category').focus();
            } else {
                newWrap.style.display = 'none';
            }

            var select2    = document.getElementById('type_select');
            var hint       = document.getElementById('type_hint');
            var hiddenType = document.getElementById('type_input');
            var currentVal = hiddenType.value;

            select2.innerHTML = '<option value="">— Pilih Kelompok —</option>';

            if (catId && catId !== '__new__' && existingGroups[catId]) {
                var groups = existingGroups[catId];
                var found  = false;
                groups.forEach(function(g) {
                    var opt       = document.createElement('option');
                    opt.value     = g;
                    opt.textContent = g;
                    if (currentVal && currentVal === g) { opt.selected = true; found = true; }
                    select2.appendChild(opt);
                });
                var newOpt = document.createElement('option');
                newOpt.value = '__new__'; newOpt.textContent = '✚ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);

                hint.textContent = groups.length + ' kelompok tersedia';
                hint.style.color = '#16a34a';

                if (currentVal && !found) {
                    toggleTypeMode(true);
                    document.getElementById('type_custom_input').value = currentVal;
                    document.getElementById('type_input').value = currentVal;
                } else if (found) {
                    toggleTypeMode(false);
                    select2.value = currentVal;
                    document.getElementById('type_input').value = currentVal;
                } else {
                    toggleTypeMode(false);
                    if (select2.value && select2.value !== '__new__') document.getElementById('type_input').value = select2.value;
                }
            } else if (catId) {
                var newOpt = document.createElement('option');
                newOpt.value = '__new__'; newOpt.textContent = '✚ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);
                hint.textContent = 'Belum ada kelompok. Ketik nama baru.';
                hint.style.color = '#2563eb';
                if (currentVal) {
                    document.getElementById('type_custom_input').value = currentVal;
                    document.getElementById('type_input').value = currentVal;
                }
                toggleTypeMode(true);
            } else {
                var newOpt = document.createElement('option');
                newOpt.value = '__new__'; newOpt.textContent = '✚ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);
                hint.textContent = 'Pilih kategori dulu';
                hint.style.color = '#94a3b8';
                toggleTypeMode(false);
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

            var customWrap  = document.getElementById('type_custom_wrap');
            var isCustom    = customWrap && customWrap.style.display !== 'none';
            var hiddenType  = document.getElementById('type_input');
            if (isCustom) {
                var customVal = (document.getElementById('type_custom_input')?.value || '').trim();
                if (customVal) hiddenType.value = customVal;
            } else {
                var selectVal = (document.getElementById('type_select')?.value || '').trim();
                if (selectVal && selectVal !== '__new__') hiddenType.value = selectVal;
            }
        }

        onCategoryChange();
        toggleNewUnit();

        // ── Incoming Stages Logic (Read-only status badge) ──────────────
        var stageIndex = 0;

        function checkEmptyState() {
            var tbody = document.getElementById('stages_tbody');
            var empty = document.getElementById('stages_empty');
            if (empty) empty.style.display = tbody.children.length === 0 ? 'block' : 'none';
        }

        function addStageRow(data) {
            data = data || {};
            var tbody   = document.getElementById('stages_tbody');
            var tr      = document.createElement('tr');
            tr.id       = 'stage_row_' + stageIndex;

            var stageNum  = tbody.children.length + 1;
            var stageVal  = data.stage  !== undefined ? data.stage  : 'T' + stageNum;
            var dateVal   = data.date   || '';
            var qtyVal    = data.qty    !== undefined ? data.qty    : '';
            var statusVal = data.status || 'planned';
            var notesVal  = data.notes  || '';

            var statusBadge = (statusVal === 'received')
                ? `<div style="display:inline-flex;flex-direction:column;gap:3px;">
                       <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;width:fit-content;">
                           <i class="fas fa-circle-check text-success"></i> Sudah Masuk
                       </span>
                       ${data.received_gr ? `<span style="font-size:10px;color:#059669;font-weight:600;"><i class="fas fa-file-invoice me-1"></i> GR #${escapeHtml(data.received_gr)}</span>` : ''}
                   </div>`
                : `<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;" title="Status terkunci otomatis sebagai Rencana dan berubah ke Sudah Masuk saat barang diterima">
                       <i class="fas fa-clock text-warning"></i> Rencana
                   </span>`;

            tr.innerHTML = `
                <td style="padding:10px 12px;vertical-align:middle;">
                    <input type="text" name="incoming_stages[${stageIndex}][stage]" value="${stageVal}"
                           class="form-control form-control-sm font-monospace"
                           placeholder="T${stageNum}"
                           style="height:36px;border-radius:6px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f8fafc;width:75px;text-align:center;">
                </td>
                <td style="padding:10px 12px;vertical-align:middle;">
                    <input type="date" name="incoming_stages[${stageIndex}][date]" value="${dateVal}"
                           class="form-control form-control-sm"
                           style="height:36px;border-radius:6px;font-size:12.5px;">
                </td>
                <td style="padding:10px 12px;vertical-align:middle;">
                    <input type="number" step="any" min="0" name="incoming_stages[${stageIndex}][qty]" value="${qtyVal}"
                           class="form-control form-control-sm stage-qty-input"
                           placeholder="0"
                           style="height:36px;border-radius:6px;font-size:13px;font-weight:700;text-align:right;"
                           oninput="updateStagesSummary()">
                </td>
                <td style="padding:10px 12px;vertical-align:middle;">
                    <input type="hidden" name="incoming_stages[${stageIndex}][status]" value="${statusVal}" class="stage-status-select">
                    ${statusBadge}
                </td>
                <td style="padding:10px 12px;vertical-align:middle;">
                    <input type="text" name="incoming_stages[${stageIndex}][notes]" value="${notesVal}"
                           class="form-control form-control-sm"
                           placeholder="No. SJ / Truk / Keterangan"
                           style="height:36px;border-radius:6px;font-size:12.5px;">
                </td>
                <td style="padding:10px 12px;text-align:center;vertical-align:middle;">
                    <button type="button"
                            onclick="removeStageRow(${stageIndex})"
                            title="Hapus baris tahap"
                            class="btn-delete-stage">
                        <i class="fas fa-trash-can" style="font-size:12px;"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            stageIndex++;
            updateStagesSummary();
            checkEmptyState();
        }

        function removeStageRow(index) {
            var row = document.getElementById('stage_row_' + index);
            if (row) { row.remove(); updateStagesSummary(); checkEmptyState(); }
        }

        function updateStagesSummary() {
            var tbody = document.getElementById('stages_tbody');
            var totalReceived = 0, totalPlanned = 0;

            tbody.querySelectorAll('tr').forEach(function(row) {
                var qtyInput = row.querySelector('.stage-qty-input');
                var statusSel = row.querySelector('.stage-status-select');
                if (!qtyInput || !statusSel) return;
                var qty = parseFloat(qtyInput.value) || 0;
                if (statusSel.value === 'received') {
                    totalReceived += qty;
                } else {
                    totalPlanned += qty;
                }
            });

            var totalAll = totalReceived + totalPlanned;
            var fmt = function(n) { return n.toLocaleString('id-ID'); };

            var elRec = document.getElementById('summary_received');
            var elPln = document.getElementById('summary_planned');
            var elTot = document.getElementById('summary_total');
            if (elRec) elRec.textContent = fmt(totalReceived);
            if (elPln) elPln.textContent = fmt(totalPlanned);
            if (elTot) elTot.textContent = fmt(totalAll);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        // Prefill existing incoming stages
        var existingStages = @json(old('incoming_stages', $material->incoming_stages ?? []));
        if (Array.isArray(existingStages) && existingStages.length > 0) {
            existingStages.forEach(function(stg) {
                addStageRow(stg);
            });
        }
        checkEmptyState();
    </script>
    @endpush
</x-app-layout>
