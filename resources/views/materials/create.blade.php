<x-app-layout>
    <x-slot name="title">Tambah Material</x-slot>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;">
                <a href="{{ route('materials.index') }}">Material</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <span>Tambah Material</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Tambah Material Baru</h2>
        </div>
        <a href="{{ route('materials.index') }}" class="btn btn-light border" style="font-size:13px;color:#475569;">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('materials.store') }}" onsubmit="prepareSubmit()" id="material-form">
        @csrf

        <div style="display:flex;flex-direction:column;gap:16px;max-width:960px;">

            {{-- SECTION 1: KLASIFIKASI MATERIAL --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-layer-group me-1 text-primary"></i> 1. Klasifikasi Material</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-3" style="gap:16px;">
                        {{-- Dropdown 1: Kategori --}}
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <label class="form-label mb-0" for="category_select" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
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
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                                <option value="__new__">✚ Kategori Baru...</option>
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dropdown 2: Kelompok Barang --}}
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <label class="form-label mb-0" for="type_select" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
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
                            <input type="hidden" name="type" id="type_input" value="{{ old('type') }}">
                            <div id="type_hint" style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                Pilih kategori dulu
                            </div>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Field 3: Ukuran --}}
                        <div>
                            <label class="form-label" for="size_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Ukuran / Spesifikasi
                            </label>
                            <input type="text" name="size" id="size_input" value="{{ old('size') }}"
                                class="form-control @error('size') is-invalid @enderror"
                                placeholder="Contoh: D13mm × 12m, 50kg, M8"
                                oninput="onTypeChange()" style="height:38px;border-radius:6px;font-size:13px;">
                            @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Kategori Baru (kondisional) --}}
                    <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                        <label class="form-label" for="new_category" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Bahan Kimia, Cat, Kayu" style="height:38px;border-radius:6px;background:#fff;max-width:360px;">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: INFORMASI MATERIAL --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-cube me-1 text-primary"></i> 2. Informasi & Identitas Material</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-2" style="gap:16px;">
                        {{-- SKU --}}
                        <div>
                            <label class="form-label" for="sku" style="font-size:12px;font-weight:700;color:#475569;">KODE MATERIAL (SKU) <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="sku" value="{{ old('sku') }}"
                                class="form-control font-monospace @error('sku') is-invalid @enderror"
                                placeholder="Contoh: MAT-BES-001"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Merek / Brand --}}
                        <div>
                            <label class="form-label" for="brand_input" style="font-size:12px;font-weight:700;color:#475569;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                            <input type="text" name="brand" id="brand_input" value="{{ old('brand') }}"
                                class="form-control @error('brand') is-invalid @enderror"
                                placeholder="Semen Gresik, Holcim, Krakatau Steel..."
                                style="height:38px;border-radius:6px;font-size:13px;">
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nama Material --}}
                        <div>
                            <label class="form-label" for="name_input" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;">
                                NAMA MATERIAL (LENGKAP) <span style="color:#ef4444;">*</span>
                                <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                    Otomatis
                                </span>
                            </label>
                            <input type="text" name="name" id="name_input" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Terisi otomatis dari Kelompok + Ukuran..."
                                style="height:38px;border-radius:6px;font-size:13px;"
                                required onfocus="this.dataset.manualEdit='true'">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                Terisi otomatis dari Kelompok + Ukuran. Edit manual bila perlu nama dagang khusus.
                            </div>
                        </div>

                        {{-- Satuan --}}
                        <div>
                            <label class="form-label" for="unit_select" style="font-size:12px;font-weight:700;color:#475569;">SATUAN <span style="color:#ef4444;">*</span></label>
                            <select name="unit_id" id="unit_select" class="form-control @error('unit_id') is-invalid @enderror" onchange="toggleNewUnit()" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">— Pilih Satuan —</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->code }})</option>
                                @endforeach
                                <option value="__new__">✚ Satuan Baru...</option>
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Supplier --}}
                        <div style="grid-column:span 2;">
                            <label class="form-label" for="supplier_input" style="font-size:12px;font-weight:700;color:#475569;">SUPPLIER / PEMASOK <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                            <input type="text" name="supplier" id="supplier_input" list="supplier_list"
                                value="{{ old('supplier') }}"
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
                            <label class="form-label" for="new_unit" style="font-size:12px;font-weight:700;color:#334155;">Nama Satuan Baru</label>
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
                        <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-calendar-alt me-1 text-primary"></i> 3. Tahap Kedatangan Barang</div>
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

                    {{-- Empty State --}}
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
                    <div class="text-muted" style="font-size:11.5px;">
                        <i class="fas fa-circle-info text-primary me-1"></i> Status otomatis <strong>Rencana</strong>, dan akan berubah ke <strong>Sudah Masuk</strong> otomatis saat barang diterima.
                    </div>
                </div>
            </div>

            {{-- SECTION 4: STOK AWAL & PENGATURAN STOK --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-warehouse me-1 text-primary"></i> 4. Stok Awal & Pengaturan Stok</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-3" style="gap:16px;">
                        <div>
                            <label class="form-label" for="warehouse_id" style="font-size:12px;font-weight:700;color:#475569;">LOKASI GUDANG</label>
                            @if($singleWarehouse ?? false)
                            <input type="hidden" name="warehouse_id" value="{{ $singleWarehouse->id }}">
                            <select id="warehouse_id" class="form-control" style="height:38px;border-radius:6px;font-size:13px;background:#f8fafc;" disabled>
                                <option value="{{ $singleWarehouse->id }}">{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}</option>
                            </select>
                            @else
                            @php
                                $defaultWarehouseId = old('warehouse_id', $warehouses->firstWhere('is_central', true)?->id ?? $warehouses->first()?->id);
                            @endphp
                            <select name="warehouse_id" id="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;">
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $defaultWarehouseId == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                </option>
                                @endforeach
                            </select>
                            @endif
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="initial_stock_input" style="font-size:12px;font-weight:700;color:#475569;">STOK AWAL</label>
                            <input type="number" step="1" min="0" name="initial_stock" id="initial_stock_input"
                                value="{{ old('initial_stock', 0) }}"
                                class="form-control @error('initial_stock') is-invalid @enderror"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" placeholder="0">
                            @error('initial_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="min_stock" style="font-size:12px;font-weight:700;color:#475569;">MIN. STOK (ALERT)</label>
                            <input type="number" step="1" min="0" name="min_stock" id="min_stock"
                                value="{{ old('min_stock', 0) }}"
                                class="form-control @error('min_stock') is-invalid @enderror"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" placeholder="0">
                            @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- MANUAL ITEM ADDITION SECTION --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div>
                        <div class="fw-700" style="font-size:14px;color:#0f172a;">+ Tambah Material/Alat Baru (Tanpa Master Data)</div>
                        <div class="text-muted" style="font-size:12px;margin-top:1px;">Tambahkan material atau alat langsung tanpa membuat data master terlebih dahulu</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleManualBox()" style="height:32px;font-size:12px;font-weight:600;">
                        <i class="fas fa-plus-circle text-primary"></i> <span>Tambah Baru</span>
                    </button>
                </div>
                <div id="manual-box" style="display:none;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:16px 18px;">
                    <div class="grid" style="gap:12px;">
                        <div>
                            <label class="form-label" for="manual_item_name" style="font-size:12px;font-weight:700;color:#334155;">Nama Material/Alat Baru <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="manual_item_name" class="form-control" placeholder="Contoh: Semen Extra Power 50kg" style="height:36px;border-radius:6px;font-size:13px;">
                        </div>
                        <div>
                            <label class="form-label" for="manual_item_sku" style="font-size:12px;font-weight:700;color:#334155;">Kode SKU <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="manual_item_sku" class="form-control font-monospace" placeholder="Contoh: MAT-XXX-001" style="height:36px;border-radius:6px;font-size:13px;font-weight:600;text-transform:uppercase;" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="grid grid-3" style="gap:12px;margin-top:12px;">
                        <div>
                            <label class="form-label" for="manual_item_unit" style="font-size:12px;font-weight:700;color:#334155;">Satuan</label>
                            <input type="text" id="manual_item_unit" class="form-control" placeholder="Pcs, kg, liter" style="height:36px;border-radius:6px;font-size:13px;">
                        </div>
                        <div>
                            <label class="form-label" for="manual_item_qty" style="font-size:12px;font-weight:700;color:#334155;">Jumlah Stok</label>
                            <input type="number" step="1" min="0" id="manual_item_qty" class="form-control" placeholder="0" style="height:36px;border-radius:6px;font-size:13px;">
                        </div>
                        <div>
                            <label class="form-label" for="manual_item_warehouse" style="font-size:12px;font-weight:700;color:#334155;">Lokasi Gudang</label>
                            @if($singleWarehouse ?? false)
                            <input type="hidden" id="manual_item_warehouse" value="{{ $singleWarehouse->id }}">
                            <input type="text" class="form-control" value="{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}" disabled style="height:36px;border-radius:6px;font-size:13px;background:#f8fafc;">
                            @else
                            <select id="manual_item_warehouse" class="form-control" style="height:36px;border-radius:6px;font-size:13px;">
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="addManualMaterial()" style="height:32px;font-size:12px;">
                            <i class="fas fa-check"></i> Tambah Item
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleManualBox()" style="height:32px;font-size:12px;">
                            <i class="fas fa-times"></i> Tutup
                        </button>
                    </div>

                    {{-- Dynamic Manual Items Container --}}
                    <div id="manual_items_container" style="margin-top:10px;"></div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div style="display:flex;align-items:center;gap:10px;padding:12px 0 4px;">
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 20px;font-size:13px;font-weight:600;border-radius:6px;">
                    Simpan Material
                </button>
                <a href="{{ route('materials.index') }}" class="btn btn-light border" style="height:38px;padding:0 16px;font-size:13px;font-weight:500;border-radius:6px;color:#64748b;">
                    Batal
                </a>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function toggleTypeMode(isCustom) {
            var selectWrap = document.getElementById('type_select_wrap');
            var customWrap = document.getElementById('type_custom_wrap');
            var customBtn  = document.getElementById('btn_toggle_custom_type');
            if (isCustom) {
                selectWrap.style.display = 'none';
                customWrap.style.display = 'block';
                if (customBtn) customBtn.style.display = 'none';
                document.getElementById('type_custom_input').focus();
            } else {
                selectWrap.style.display = 'block';
                customWrap.style.display = 'none';
                if (customBtn) customBtn.style.display = 'inline-block';
            }
        }

        function onTypeSelectChange() {
            var select = document.getElementById('type_select');
            if (select.value === '__new__') {
                toggleTypeMode(true);
                document.getElementById('type_custom_input').value = '';
                document.getElementById('type_input').value = '';
            } else {
                document.getElementById('type_input').value = select.value;
            }
            onTypeChange();
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
            if (sel && sel.value && sel.value !== '__new__') {
                btn.style.display = 'inline-flex';
                var optText = sel.options[sel.selectedIndex].text;
                btn.title = 'Hapus kategori "' + optText + '" jika salah memasukkan';
            } else {
                btn.style.display = 'none';
            }
        }

        function deleteSelectedCategory() {
            var sel = document.getElementById('category_select');
            if (!sel || !sel.value || sel.value === '__new__') return;
            var catId = sel.value;
            var catName = sel.options[sel.selectedIndex].text;

            if (!confirm('Hapus kategori "' + catName + '"? Kategori yang salah dimasukkan akan dihapus dari sistem.')) {
                return;
            }

            var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

            fetch('{{ url("/categories") }}/' + catId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(res) {
                if (res.ok && res.data.success) {
                    alert(res.data.message || 'Kategori berhasil dihapus.');
                    for (var i = 0; i < sel.options.length; i++) {
                        if (sel.options[i].value == catId) {
                            sel.remove(i);
                            break;
                        }
                    }
                    sel.value = '';
                    onCategoryChange();
                } else {
                    alert(res.data.message || 'Gagal menghapus kategori. Kategori mungkin masih memiliki data material.');
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus kategori.');
            });
        }

        function onCategoryChange() {
            var select  = document.getElementById('category_select');
            var catId   = select.value;
            var wrap    = document.getElementById('new_category_wrap');
            var isNew   = catId === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
            updateDeleteCategoryBtn();
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
            var typeVal  = (document.getElementById('type_input').value || '').trim();
            var sizeVal  = (document.getElementById('size_input').value || '').trim();
            var nameInput = document.getElementById('name_input');
            var badge    = document.getElementById('name_auto_badge');

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
            return true;
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
            var statusVal = data.status || 'planned';
            var notesVal  = data.notes  || '';

            var grNotice  = data.received_gr ? `<div style="font-size:10px;color:#059669;font-weight:600;margin-top:2px;"><i class="fas fa-truck-ramp-box"></i> GR #${escapeHtml(data.received_gr)}</div>` : '';

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
                <td style="padding:8px 12px;vertical-align:middle;">
                    <input type="hidden" name="incoming_stages[${stageIndex}][status]" value="${statusVal}" class="stage-status-select">
                    ${statusVal === 'received' ? `
                        <div style="display:inline-flex;flex-direction:column;gap:2px;">
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:5px 10px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-circle-check text-success"></i> Sudah Masuk
                            </span>
                            ${grNotice}
                        </div>
                    ` : `
                        <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:5px 10px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:5px;" title="Status terkunci otomatis sebagai Rencana dan akan berubah ke Sudah Masuk saat barang diterima">
                            <i class="fas fa-clock text-warning"></i> Rencana
                        </span>
                    `}
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

            // Sync initial_stock jika ada tahap received
            var initStockInput = document.getElementById('initial_stock_input');
            if (initStockInput && totalReceived > 0 && (!initStockInput.dataset.manual || initStockInput.dataset.manual === 'false')) {
                initStockInput.value = totalReceived;
            }
        }

        function onStageStatusSelectChange(sel) {
            if (sel.value === 'received') {
                sel.style.color = '#15803d';
                sel.style.background = '#f0fdf4';
                sel.style.borderColor = '#bbf7d0';
            } else {
                sel.style.color = '#b45309';
                sel.style.background = '#fffbeb';
                sel.style.borderColor = '#fde68a';
            }
            updateStagesSummary();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        document.getElementById('initial_stock_input')?.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });

        // Initialize empty state
        checkEmptyState();

        // ── Manual Item Section ──
        var manualItemCount = 0;

        function toggleManualBox() {
            var box = document.getElementById('manual-box');
            box.style.display = box.style.display === 'none' ? 'block' : 'none';
            if (box.style.display === 'block') {
                document.getElementById('manual_item_name')?.focus();
            }
        }

        function addManualMaterial() {
            var nameEl = document.getElementById('manual_item_name');
            var skuEl = document.getElementById('manual_item_sku');
            var unitEl = document.getElementById('manual_item_unit');
            var qtyEl = document.getElementById('manual_item_qty');
            var whEl = document.getElementById('manual_item_warehouse') || document.querySelector('[name="warehouse_id"]');

            var name = nameEl ? nameEl.value.trim() : '';
            var sku = skuEl ? skuEl.value.trim().toUpperCase() : '';
            var unit = (unitEl ? unitEl.value.trim() : '') || 'Pcs';
            var qty = qtyEl ? qtyEl.value : 0;
            var warehouseId = whEl ? whEl.value : '';

            if (!name || !sku) {
                alert('Nama dan Kode SKU untuk item manual wajib diisi.');
                return;
            }

            if (!warehouseId) {
                alert('Pilih gudang terlebih dahulu.');
                return;
            }

            var container = document.getElementById('manual_items_container');
            if (!container) return;

            var rowId = 'manual_row_' + manualItemCount;
            var itemDiv = document.createElement('div');
            itemDiv.id = rowId;
            itemDiv.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#fff;border:1px solid #cbd5e1;border-radius:6px;margin-top:8px;font-size:12.5px;';

            itemDiv.innerHTML = `
                <div>
                    <strong style="color:#0f172a;">${name}</strong>
                    <span class="font-monospace text-muted" style="font-size:11.5px;margin-left:6px;">(${sku})</span>
                    <span style="margin-left:8px;font-size:11px;padding:2px 6px;background:#f1f5f9;border-radius:4px;border:1px solid #e2e8f0;color:#334155;">Stok: ${qty} ${unit}</span>
                    <input type="hidden" name="manual_items[${manualItemCount}][name]" value="${name}">
                    <input type="hidden" name="manual_items[${manualItemCount}][sku]" value="${sku}">
                    <input type="hidden" name="manual_items[${manualItemCount}][unit_id]" value="${unit}">
                    <input type="hidden" name="manual_items[${manualItemCount}][quantity]" value="${qty}">
                    <input type="hidden" name="manual_items[${manualItemCount}][warehouse_id]" value="${warehouseId}">
                </div>
                <button type="button" onclick="document.getElementById('${rowId}').remove()" class="btn btn-sm btn-light border" style="height:26px;padding:0 8px;font-size:11px;color:#dc2626;" title="Hapus">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            `;

            container.appendChild(itemDiv);
            manualItemCount++;

            nameEl.value = '';
            skuEl.value = '';
            if (unitEl) unitEl.value = '';
            if (qtyEl) qtyEl.value = '0';
        }
    </script>
    @endpush
</x-app-layout>
