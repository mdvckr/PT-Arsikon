<x-app-layout>
    <x-slot name="title">Edit Material: {{ $material->name }}</x-slot>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;">
                <a href="{{ route('materials.index') }}">Material</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <a href="{{ route('materials.show', $material) }}">{{ $material->name }}</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <span>Edit</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Edit Material</h2>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">Perbarui informasi spesifikasi dan jadwal kedatangan material</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('materials.show', $material) }}" class="btn btn-light border" style="font-size:13px;color:#475569;">
                <i class="fas fa-eye me-1"></i> Lihat Detail
            </a>
            <a href="{{ route('materials.index') }}" class="btn btn-light border" style="font-size:13px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('materials.update', $material) }}" onsubmit="prepareSubmit()" id="material-form">
        @csrf @method('PUT')

        <div style="display:flex;flex-direction:column;gap:16px;max-width:960px;">

            {{-- SECTION 1: KLASIFIKASI MATERIAL --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">1. Klasifikasi Material</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Tentukan Kategori, Kelompok Barang, dan Ukuran / Spesifikasi</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-3" style="gap:16px;">
                        {{-- Dropdown 1: Kategori --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Kategori <span style="color:#ef4444;">*</span>
                            </label>
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

            {{-- SECTION 2: INFORMASI MATERIAL --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">2. Informasi & Identitas Material</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Kode SKU, nama lengkap, merek, satuan, dan supplier</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-2" style="gap:16px;">
                        {{-- SKU --}}
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">KODE MATERIAL (SKU) <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="sku" value="{{ old('sku', $material->sku) }}"
                                class="form-control font-monospace @error('sku') is-invalid @enderror"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;" required>
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

            {{-- SECTION 3: TAHAP KEDATANGAN BARANG --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div>
                        <div class="fw-700" style="font-size:14px;color:#0f172a;">3. Tahap Kedatangan Barang</div>
                        <div class="text-muted" style="font-size:12px;margin-top:1px;">Catat flow masuk bertahap (sudah masuk maupun rencana)</div>
                    </div>
                    <button type="button" onclick="addStageRow()" class="btn btn-sm btn-secondary" style="height:32px;font-size:12px;font-weight:600;">
                        <i class="fas fa-plus"></i> Tambah Tahap
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%;font-size:12.5px;border-collapse:collapse;min-width:680px;" id="stages_table">
                        <thead>
                            <tr style="background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #e2e8f0;">
                                <th style="padding:9px 14px;width:90px;font-weight:700;">Tahap</th>
                                <th style="padding:9px 14px;width:160px;font-weight:700;">Tanggal</th>
                                <th style="padding:9px 14px;width:130px;font-weight:700;">Qty Masuk</th>
                                <th style="padding:9px 14px;width:170px;font-weight:700;">Status</th>
                                <th style="padding:9px 14px;font-weight:700;">Keterangan</th>
                                <th style="padding:9px 14px;width:46px;"></th>
                            </tr>
                        </thead>
                        <tbody id="stages_tbody">
                            {{-- Baris dinamis --}}
                        </tbody>
                    </table>
                    <div id="stages_empty" style="display:none;padding:24px;text-align:center;color:#94a3b8;">
                        <div style="font-size:13px;">Belum ada tahap. Klik <strong>Tambah Tahap</strong> bila ada jadwal bertahap.</div>
                    </div>
                </div>

                {{-- Summary Bar --}}
                <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:16px;font-size:12px;">
                        <div>Masuk: <strong id="summary_received" style="color:#16a34a;">0</strong></div>
                        <div>Rencana: <strong id="summary_planned" style="color:#b45309;">0</strong></div>
                        <div>Total: <strong id="summary_total" style="color:#0f172a;">0</strong></div>
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        Ubah status ke <em>Sudah Masuk</em> bila barang tiba di lokasi
                    </div>
                </div>
            </div>

            {{-- SECTION 4: CATATAN TAMBAHAN --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">4. Catatan Tambahan</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Deskripsi umum, spesifikasi teknis, atau catatan penting lainnya</div>
                </div>
                <div style="padding:18px;">
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Keterangan umum material, spesifikasi teknis, catatan penting..."
                        style="border-radius:6px;font-size:13px;resize:vertical;">{{ old('description', $material->description) }}</textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div style="display:flex;align-items:center;gap:10px;padding:12px 0 4px;">
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 20px;font-size:13px;font-weight:600;border-radius:6px;">
                    Simpan Perubahan
                </button>
                <a href="{{ route('materials.show', $material) }}" class="btn btn-light border" style="height:38px;padding:0 16px;font-size:13px;font-weight:500;border-radius:6px;color:#64748b;">
                    Batal
                </a>
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

        function onCategoryChange() {
            var select  = document.getElementById('category_select');
            var catId   = select.value;
            var wrap    = document.getElementById('new_category_wrap');
            var isNew   = catId === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            if (isNew) { document.getElementById('new_category').focus(); return; }

            var select2 = document.getElementById('type_select');
            var currentVal = (document.getElementById('type_input').value || '').trim();
            select2.innerHTML = '<option value="">— Pilih Kelompok —</option>';
            var hint = document.getElementById('type_hint');

            if (catId && existingGroups[catId] && existingGroups[catId].length > 0) {
                var groups = existingGroups[catId];
                var found = false;
                groups.forEach(function(g) {
                    var opt = document.createElement('option');
                    opt.value = g; opt.textContent = g;
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

        // ── Incoming Stages Logic ────────────────────────────
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
            tr.style.cssText = 'border-bottom:1px solid #f1f5f9;';

            var stageNum  = tbody.children.length + 1;
            var stageVal  = data.stage  !== undefined ? data.stage  : 'T' + stageNum;
            var dateVal   = data.date   || '';
            var qtyVal    = data.qty    !== undefined ? data.qty    : '';
            var statusVal = data.status || 'received';
            var notesVal  = data.notes  || '';

            tr.innerHTML = `
                <td style="padding:8px 12px;">
                    <input type="text" name="incoming_stages[${stageIndex}][stage]" value="${stageVal}"
                           class="form-control form-control-sm font-monospace"
                           placeholder="T${stageNum}"
                           style="height:34px;border-radius:6px;font-size:12px;font-weight:700;color:#0f172a;background:#f8fafc;width:75px;text-align:center;">
                </td>
                <td style="padding:8px 12px;">
                    <input type="date" name="incoming_stages[${stageIndex}][date]" value="${dateVal}"
                           class="form-control form-control-sm"
                           style="height:34px;border-radius:6px;font-size:12px;">
                </td>
                <td style="padding:8px 12px;">
                    <input type="number" step="any" min="0" name="incoming_stages[${stageIndex}][qty]" value="${qtyVal}"
                           class="form-control form-control-sm stage-qty-input"
                           placeholder="0"
                           style="height:34px;border-radius:6px;font-size:12.5px;font-weight:600;text-align:right;"
                           oninput="updateStagesSummary()">
                </td>
                <td style="padding:8px 12px;">
                    <select name="incoming_stages[${stageIndex}][status]"
                            class="form-control form-control-sm stage-status-select"
                            style="height:34px;border-radius:6px;font-size:12px;"
                            onchange="updateStagesSummary()">
                        <option value="received" ${statusVal === 'received' ? 'selected' : ''}>Sudah Masuk</option>
                        <option value="planned"  ${statusVal === 'planned'  ? 'selected' : ''}>Rencana</option>
                    </select>
                </td>
                <td style="padding:8px 12px;">
                    <input type="text" name="incoming_stages[${stageIndex}][notes]" value="${notesVal}"
                           class="form-control form-control-sm"
                           placeholder="No. SJ / Truk / Keterangan"
                           style="height:34px;border-radius:6px;font-size:12px;">
                </td>
                <td style="padding:8px 12px;text-align:center;">
                    <button type="button"
                            onclick="removeStageRow(${stageIndex})"
                            title="Hapus baris"
                            class="btn btn-sm btn-light border"
                            style="width:28px;height:28px;padding:0;color:#dc2626;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="fas fa-trash" style="font-size:11px;"></i>
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
