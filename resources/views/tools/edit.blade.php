<x-app-layout>
    <x-slot name="title">Edit Alat — {{ $tool->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $tool->name }}</span>
    </div>

    <div class="card" style="max-width:900px;">
        <div class="card-header" style="border-bottom:1px solid #f1f5f9;padding:16px 20px;">
            <div class="flex items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#d97706;">
                    <i class="fas fa-pen" style="font-size:14px;"></i>
                </div>
                <div>
                    <h2 class="card-title" style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Edit Alat</h2>
                    <div style="font-size:11.5px;color:#64748b;">Perbarui detail alat, spesifikasi, dan stok</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:24px;">
            <form method="POST" action="{{ route('tools.update', $tool) }}" onsubmit="prepareSubmit()">
                @csrf @method('PUT')
                {{-- === STEP FLOW: 3-TIER CASCADING === --}}
                <div class="mb-3 p-3 border rounded" style="background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%);border-color:#bfdbfe !important;">
                    <div class="flex items-center gap-2 mb-3">
                        <div style="width:28px;height:28px;border-radius:6px;background:#dbeafe;display:flex;align-items:center;justify-content:center;color:#2563eb;">
                            <i class="fas fa-layer-group" style="font-size:12px;"></i>
                        </div>
                        <div>
                            <span class="fw-700" style="font-size:13px;color:#0f172a;">Klasifikasi Alat (3 Tingkat)</span>
                            <div class="text-muted" style="font-size:11px;">Pilih Kategori → Kelompok Alat → Spesifikasi/Ukuran</div>
                        </div>
                    </div>

                    <div class="grid grid-3" style="gap:14px;">
                        {{-- Dropdown 1: Kategori --}}
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#2563eb;color:#fff;font-size:10px;font-weight:800;margin-right:5px;">1</span>
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange()">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $tool->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                                <option value="__new__">++ Kategori Baru...</option>
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown 2: Kelompok Nama Alat (type) --}}
                        <div>
                            <div class="flex items-center justify-between" style="margin-bottom:4px;">
                                <label class="form-label mb-0" style="font-weight:600;color:#334155;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#16a34a;color:#fff;font-size:10px;font-weight:800;margin-right:5px;">2</span>
                                    Kelompok Alat <span class="text-danger">*</span>
                                </label>
                                <button type="button" id="btn_toggle_custom_type" class="btn btn-link p-0 text-primary" style="font-size:11px;text-decoration:none;" onclick="toggleTypeMode(true)">
                                    <i class="fas fa-pen-to-square me-1"></i>+ Ketik Baru
                                </button>
                            </div>

                            <div id="type_select_wrap">
                                <select id="type_select" class="form-control @error('type') is-invalid @enderror" onchange="onTypeSelectChange()">
                                    <option value="">-- Pilih Kelompok Alat --</option>
                                    <option value="__new__">++ Ketik Kelompok Baru...</option>
                                </select>
                            </div>

                            <div id="type_custom_wrap" style="display:none;">
                                <div style="display:flex;gap:6px;">
                                    <input type="text" id="type_custom_input" class="form-control"
                                        placeholder="Ketik nama kelompok baru..." oninput="onTypeCustomInput()">
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleTypeMode(false)" title="Kembali ke dropdown" style="font-size:12px;white-space:nowrap;padding:4px 10px;">
                                        <i class="fas fa-list me-1"></i> Dropdown
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="type" id="type_input" value="{{ old('type', $tool->type) }}">

                            <div id="type_hint" class="text-muted" style="font-size:10.5px;margin-top:3px;">
                                <i class="fas fa-info-circle me-1"></i> Pilih kategori dulu untuk melihat daftar kelompok alat
                            </div>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Field 3: Ukuran / Kapasitas / Spesifikasi --}}
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#ea580c;color:#fff;font-size:10px;font-weight:800;margin-right:5px;">3</span>
                                Spesifikasi / Ukuran
                            </label>
                            <input type="text" name="size" id="size_input" value="{{ old('size', $tool->size) }}"
                                class="form-control @error('size') is-invalid @enderror"
                                placeholder="Contoh: 5000W / 10 Ton / 2.5 HP / 14 Inch"
                                oninput="onTypeChange()">
                            @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Kategori Baru (kondisional) --}}
                <div id="new_category_wrap" class="mb-3" style="display:none;">
                    <label class="form-label" style="font-weight:600;color:#334155;">Nama Kategori Baru</label>
                    <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Alat Berat, Power Tools, Surveying">
                    <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                </div>

                {{-- Nama Alat & Detail --}}
                <div class="grid grid-2" style="gap:16px;">
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">Kode Alat <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $tool->code) }}"
                            class="form-control font-monospace @error('code') is-invalid @enderror" style="font-weight:600;" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">
                            Nama Alat (Lengkap) <span class="text-danger">*</span>
                            <span id="name_auto_badge" class="badge" style="background:#dbeafe;color:#1d4ed8;font-size:9.5px;font-weight:600;margin-left:6px;display:none;">
                                <i class="fas fa-magic me-1"></i> Auto
                            </span>
                        </label>
                        <input type="text" name="name" id="name_input" value="{{ old('name', $tool->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Otomatis dari Kelompok + Spesifikasi, bisa diedit" required
                            data-manual-edit="true">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">Merk / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $tool->brand) }}"
                            class="form-control @error('brand') is-invalid @enderror"
                            placeholder="Contoh: Honda, Makita, Bosch, Komatsu">
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">Lokasi Gudang Penyimpanan</label>
                        <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror">
                            <option value="">-- Pilih Gudang (Opsional) --</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $tool->current_warehouse_id) == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>



                {{-- Kelola Stok --}}
                <div class="mt-4 p-3 border rounded" style="background:#f8fafc;border-color:#e2e8f0 !important;">
                    <div class="fw-700 mb-2" style="font-size:13.5px;color:#334155;">
                        <i class="fas fa-boxes-stacked text-primary me-1"></i> Rincian Stok Unit
                    </div>
                    <div class="grid grid-3" style="gap:14px;">
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">Total Stok Unit <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" name="stock_total" id="stock_total_input" value="{{ old('stock_total', $tool->stock_total) }}"
                                class="form-control @error('stock_total') is-invalid @enderror" required>
                            @error('stock_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">Stok Tersedia <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" name="stock_available" value="{{ old('stock_available', $tool->stock_available) }}"
                                class="form-control @error('stock_available') is-invalid @enderror" required>
                            @error('stock_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">Sedang Dipinjam</label>
                            <input type="number" value="{{ $tool->stock_borrowed }}" class="form-control" disabled style="background:#e2e8f0;">
                            <div class="text-muted" style="font-size:10px;margin-top:2px;">Otomatis dari peminjaman aktif</div>
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">Dalam Maintenance</label>
                            <input type="number" step="1" min="0" name="stock_maintenance" value="{{ old('stock_maintenance', $tool->stock_maintenance) }}"
                                class="form-control @error('stock_maintenance') is-invalid @enderror">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight:600;color:#334155;">Rusak</label>
                            <input type="number" step="1" min="0" name="stock_damaged" value="{{ old('stock_damaged', $tool->stock_damaged) }}"
                                class="form-control @error('stock_damaged') is-invalid @enderror">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" style="font-weight:600;color:#334155;">Catatan / Keterangan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="Deskripsi, kelengkapan aksesoris, atau catatan teknis alat...">{{ old('notes', $tool->notes) }}</textarea>
                </div>

                <div class="flex gap-2 mt-4 pt-2 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>


        </div>
    </div>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function toggleTypeMode(isCustom) {
            var selectWrap = document.getElementById('type_select_wrap');
            var customWrap = document.getElementById('type_custom_wrap');
            var toggleBtn = document.getElementById('btn_toggle_custom_type');
            var select = document.getElementById('type_select');
            var customInput = document.getElementById('type_custom_input');
            var hiddenInput = document.getElementById('type_input');

            if (isCustom) {
                selectWrap.style.display = 'none';
                customWrap.style.display = 'block';
                if (toggleBtn) toggleBtn.style.display = 'none';
                if (!customInput.value && hiddenInput.value) {
                    customInput.value = hiddenInput.value;
                }
                customInput.focus();
                hiddenInput.value = customInput.value;
            } else {
                selectWrap.style.display = 'block';
                customWrap.style.display = 'none';
                if (toggleBtn) toggleBtn.style.display = '';
                if (hiddenInput.value) {
                    select.value = hiddenInput.value;
                }
                if (select.value && select.value !== '__new__') {
                    hiddenInput.value = select.value;
                }
            }
            onTypeChange();
        }

        function onTypeSelectChange() {
            var select = document.getElementById('type_select');
            var val = select.value;
            if (val === '__new__') {
                toggleTypeMode(true);
            } else {
                document.getElementById('type_input').value = val;
                onTypeChange();
            }
        }

        function onTypeCustomInput() {
            var customInput = document.getElementById('type_custom_input');
            document.getElementById('type_input').value = customInput.value;
            onTypeChange();
        }

        function onCategoryChange() {
            var select = document.getElementById('category_select');
            var catId = select.value;
            var wrap = document.getElementById('new_category_wrap');
            var isNew = catId === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            if (isNew) { document.getElementById('new_category').focus(); return; }

            var select2 = document.getElementById('type_select');
            var currentVal = (document.getElementById('type_input').value || '').trim();
            select2.innerHTML = '<option value="">-- Pilih Kelompok Alat --</option>';
            var hint = document.getElementById('type_hint');

            if (catId && existingGroups[catId] && existingGroups[catId].length > 0) {
                var groups = existingGroups[catId];
                var found = false;
                groups.forEach(function(g) {
                    var opt = document.createElement('option');
                    opt.value = g;
                    opt.textContent = g;
                    if (currentVal && currentVal === g) {
                        opt.selected = true;
                        found = true;
                    }
                    select2.appendChild(opt);
                });
                var newOpt = document.createElement('option');
                newOpt.value = '__new__';
                newOpt.textContent = '++ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);

                hint.innerHTML = '<i class="fas fa-check-circle me-1 text-success"></i> ' + groups.length + ' kelompok tersedia. Klik dropdown untuk memilih.';
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
                    if (select2.value && select2.value !== '__new__') {
                        document.getElementById('type_input').value = select2.value;
                    }
                }
            } else if (catId) {
                var newOpt = document.createElement('option');
                newOpt.value = '__new__';
                newOpt.textContent = '++ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);
                hint.innerHTML = '<i class="fas fa-info-circle me-1 text-primary"></i> Belum ada kelompok di kategori ini. Ketik nama kelompok alat baru.';
                hint.style.color = '#2563eb';
                if (currentVal) {
                    document.getElementById('type_custom_input').value = currentVal;
                    document.getElementById('type_input').value = currentVal;
                }
                toggleTypeMode(true);
            } else {
                var newOpt = document.createElement('option');
                newOpt.value = '__new__';
                newOpt.textContent = '++ Ketik Kelompok Baru...';
                select2.appendChild(newOpt);
                hint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Pilih kategori dulu untuk melihat daftar kelompok alat';
                hint.style.color = '';
                toggleTypeMode(false);
            }
        }

        function onTypeChange() {
            var typeVal = (document.getElementById('type_input').value || '').trim();
            var sizeVal = (document.getElementById('size_input').value || '').trim();
            var nameInput = document.getElementById('name_input');
            var badge = document.getElementById('name_auto_badge');

            if (nameInput.dataset.manualEdit === 'true' && nameInput.value.trim() !== '') return;

            var autoName = '';
            if (typeVal && sizeVal) {
                autoName = typeVal + ' ' + sizeVal;
            } else if (typeVal) {
                autoName = typeVal;
            }

            if (autoName) {
                nameInput.value = autoName;
                badge.style.display = 'inline-flex';
                nameInput.dataset.manualEdit = '';
            } else {
                badge.style.display = 'none';
            }
        }

        function prepareSubmit() {
            var cat = document.getElementById('category_select');
            if (cat && cat.value === '__new__') cat.value = '';

            // Pastikan nilai type_input selalu tersinkronisasi dan tidak pernah kosong bila sudah dipilih/diketik
            var customWrap = document.getElementById('type_custom_wrap');
            var isCustom = customWrap && customWrap.style.display !== 'none';
            var hiddenType = document.getElementById('type_input');
            if (isCustom) {
                var customVal = (document.getElementById('type_custom_input')?.value || '').trim();
                if (customVal) {
                    hiddenType.value = customVal;
                }
            } else {
                var selectVal = (document.getElementById('type_select')?.value || '').trim();
                if (selectVal && selectVal !== '__new__') {
                    hiddenType.value = selectVal;
                }
            }
        }

        onCategoryChange();


    </script>
    @endpush
</x-app-layout>
