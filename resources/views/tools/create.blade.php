<x-app-layout>
    <x-slot name="title">Tambah Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Tambah Alat</span>
    </div>

    <div class="card" style="max-width:900px;">
        <div class="card-header" style="border-bottom:1px solid #f1f5f9;padding:16px 20px;">
            <div class="flex items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;color:#2563eb;">
                    <i class="fas fa-plus-circle" style="font-size:14px;"></i>
                </div>
                <div>
                    <h2 class="card-title" style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Form Tambah Alat</h2>
                    <div style="font-size:11.5px;color:#64748b;">Daftarkan inventaris alat baru beserta spesifikasi dan stok awal</div>
                </div>
            </div>
        </div>
        <div class="card-body" style="padding:24px;">
            <form method="POST" action="{{ route('tools.store') }}" onsubmit="prepareSubmit()">
                @csrf
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
                                <option value="{{ $cat->id }}" {{ old('category_id', $selectedCategoryId) == $cat->id ? 'selected' : '' }}>
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

                            {{-- Dropdown 2: Select --}}
                            <div id="type_select_wrap">
                                <select id="type_select" class="form-control @error('type') is-invalid @enderror" onchange="onTypeSelectChange()">
                                    <option value="">-- Pilih Kelompok Alat --</option>
                                    <option value="__new__">++ Ketik Kelompok Baru...</option>
                                </select>
                            </div>

                            {{-- Input Manual Kelompok Baru --}}
                            <div id="type_custom_wrap" style="display:none;">
                                <div style="display:flex;gap:6px;">
                                    <input type="text" id="type_custom_input" class="form-control"
                                        placeholder="Ketik nama kelompok baru..." oninput="onTypeCustomInput()">
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleTypeMode(false)" title="Kembali ke dropdown" style="font-size:12px;white-space:nowrap;padding:4px 10px;">
                                        <i class="fas fa-list me-1"></i> Dropdown
                                    </button>
                                </div>
                            </div>

                            {{-- Hidden / actual value input --}}
                            <input type="hidden" name="type" id="type_input" value="{{ old('type') }}">

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
                            <input type="text" name="size" id="size_input" value="{{ old('size') }}"
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
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control font-monospace @error('code') is-invalid @enderror"
                            placeholder="Contoh: TLS-GEN-001 / MOLEN-01" style="font-weight:600;" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">
                            Nama Alat (Lengkap) <span class="text-danger">*</span>
                            <span id="name_auto_badge" class="badge" style="background:#dbeafe;color:#1d4ed8;font-size:9.5px;font-weight:600;margin-left:6px;display:none;">
                                <i class="fas fa-magic me-1"></i> Auto
                            </span>
                        </label>
                        <input type="text" name="name" id="name_input" value="{{ old('name', request('name')) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Otomatis dari Kelompok + Spesifikasi, bisa diedit" required
                            onfocus="this.dataset.manualEdit='true'">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="text-muted" style="font-size:10.5px;margin-top:3px;">
                            <i class="fas fa-lightbulb me-1 text-warning"></i> Terisi otomatis dari <em>Kelompok Alat + Spesifikasi</em>.
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;">Merk / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                            class="form-control @error('brand') is-invalid @enderror"
                            placeholder="Contoh: Honda, Makita, Bosch, Komatsu">
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                     <div>
                         <label class="form-label" style="font-weight:600;color:#334155;">Lokasi Gudang Penyimpanan</label>
                         @if($singleWarehouse ?? false)
                         <input type="hidden" name="warehouse_id" value="{{ $singleWarehouse->id }}">
                         <input type="text" class="form-control" value="{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}" disabled style="height:38px;border-radius:6px;font-size:13px;background:#f8fafc;">
                         @else
                         <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror">
                             <option value="">-- Pilih Gudang (Opsional) --</option>
                             @foreach($warehouses as $wh)
                             <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                 {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                             </option>
                             @endforeach
                         </select>
                         @endif
                         @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                     </div>
                </div>

                {{-- Stok Awal / Total Unit --}}
                <div class="mt-4 p-3 border rounded" style="background:#f8fafc;border-color:#e2e8f0 !important;">
                    <div class="fw-700 mb-2" style="font-size:13.5px;color:#334155;">
                        <i class="fas fa-boxes-stacked text-primary me-1"></i> Stok Total Unit
                    </div>
                    <div style="max-width:300px;">
                        <label class="form-label" style="font-weight:600;color:#334155;">Jumlah Total Stok Unit <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="stock_total" id="initial_stock_input" value="{{ old('stock_total', 1) }}"
                            class="form-control @error('stock_total') is-invalid @enderror" placeholder="1" required>
                        @error('stock_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="text-muted" style="font-size:11px;margin-top:3px;">Masukkan jumlah unit alat yang tersedia saat ini.</div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" style="font-weight:600;color:#334155;">Catatan / Keterangan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="Deskripsi, kelengkapan aksesoris, atau catatan teknis alat...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-2 mt-4 pt-2 border-top">
                     <button type="submit" class="btn btn-primary">
                         <i class="fas fa-save me-1"></i> Simpan Alat
                     </button>
                     <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                 </div>
             </form>
         </div>
     </div>

     {{-- MANUAL ITEM ADDITION SECTION --}}
     <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;margin-top:20px;">
         <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:12px;">
             <div>
                 <div class="fw-700" style="font-size:14px;color:#0f172a;">+ Tambah Alat Baru (Tanpa Master Data)</div>
                 <div class="text-muted" style="font-size:12px;margin-top:1px;">Tambahkan alat langsung tanpa membuat data master terlebih dahulu</div>
             </div>
             <button type="button" class="btn btn-sm btn-secondary" onclick="toggleManualToolBox()" style="height:32px;font-size:12px;font-weight:600;">
                 <i class="fas fa-plus-circle text-primary"></i> <span>Tambah Alat Baru</span>
             </button>
         </div>
         <div id="manual-tool-box" style="display:none;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:16px 18px;">
             <div class="grid" style="gap:12px;">
                 <div>
                     <label class="form-label" style="font-weight:600;color:#334155;">Kode Alat <span style="color:#ef4444;">*</span></label>
                     <input type="text" name="manual_items[][code]" id="manual_tool_code" class="form-control font-monospace" placeholder="Contoh: TLS-GEN-001" style="height:36px;border-radius:6px;font-size:13px;font-weight:600;">
                 </div>
                 <div>
                     <label class="form-label" style="font-weight:600;color:#334155;">Nama Alat <span style="color:#ef4444;">*</span></label>
                     <input type="text" name="manual_items[][name]" id="manual_tool_name" class="form-control" placeholder="Contoh: Mesin Milling" style="height:36px;border-radius:6px;font-size:13px;">
                 </div>
             </div>
             <div class="grid grid-3" style="gap:12px;margin-top:12px;">
                 <div>
                     <label class="form-label" style="font-weight:600;color:#334155;">Jumlah Stok</label>
                     <input type="number" step="1" min="0" name="manual_items[][stock_total]" id="manual_tool_qty" class="form-control" placeholder="1" style="height:36px;border-radius:6px;font-size:13px;">
                 </div>
                 <div>
                     <label class="form-label" style="font-weight:600;color:#334155;">Lokasi Gudang</label>
                     @if($singleWarehouse ?? false)
                     <input type="hidden" name="manual_items[][warehouse_id]" value="{{ $singleWarehouse->id }}">
                     <input type="text" class="form-control" value="{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}" disabled style="height:36px;border-radius:6px;font-size:13px;background:#f8fafc;">
                     @else
                     <select name="manual_items[][warehouse_id]" class="form-control" style="height:36px;border-radius:6px;font-size:13px;">
                         @foreach($warehouses as $wh)
                         <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                         @endforeach
                         </select>
                     @endif
                 </div>
                 <div>
                     <label class="form-label" style="font-weight:600;color:#334155;">Spesifikasi</label>
                     <input type="text" name="manual_items[][size]" class="form-control" placeholder="Contoh: 5000W / 10 Ton" style="height:36px;border-radius:6px;font-size:13px;">
                 </div>
             </div>
             <div class="flex gap-2 mt-3">
                 <button type="button" class="btn btn-primary btn-sm" onclick="addManualTool()" style="height:32px;font-size:12px;">
                     <i class="fas fa-check"></i> Tambah Alat
                 </button>
                 <button type="button" class="btn btn-secondary btn-sm" onclick="toggleManualToolBox()" style="height:32px;font-size:12px;">
                     <i class="fas fa-times"></i> Tutup
                 </button>
             </div>
         </div>
     </div>

    @push('scripts')
    <script>
        // Data kelompok alat per kategori dari server
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

            // Populate Dropdown 2 (Kelompok Alat select)
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

                hint.innerHTML = '<i class="fas fa-check-circle me-1 text-success"></i> ' + groups.length + ' kelompok tersedia. Klik dropdown di atas untuk memilih.';
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

        // Initialize on page load
        onCategoryChange();

        // ── Manual Tool Section ──
        function toggleManualToolBox() {
            var box = document.getElementById('manual-tool-box');
            box.style.display = box.style.display === 'none' ? 'block' : 'none';
            if (box.style.display === 'block') {
                document.getElementById('manual_tool_code').focus();
            }
        }

        function addManualTool() {
            var code = document.getElementById('manual_tool_code').value.trim();
            var name = document.getElementById('manual_tool_name').value.trim();
            var qty = document.getElementById('manual_tool_qty').value || 0;

            if (!code || !name) {
                alert('Kode dan Nama alat wajib diisi.');
                return;
            }

            // Add hidden inputs for manual items to the form
            var container = document.createElement('div');
            container.style.display = 'none';
            var idx = document.querySelectorAll('[name^="manual_items"][name$="[code]"]').length;
            container.innerHTML = `
                <input type="hidden" name="manual_items[${idx}[code]" value="${code}">
                <input type="hidden" name="manual_items[${idx}[name]" value="${name}">
                <input type="hidden" name="manual_items[${idx}[stock_total]" value="${qty}">
            `;
            document.querySelector('form').appendChild(container);

            alert('Alat baru: "' + name + '" akan ditambahkan saat simpan.');
            document.getElementById('manual_tool_code').value = '';
            document.getElementById('manual_tool_name').value = '';
            document.getElementById('manual_tool_qty').value = '1';
        }
    </script>
    @endpush
</x-app-layout>
