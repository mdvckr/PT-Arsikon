<x-app-layout>
    <x-slot name="title">Tambah Material</x-slot>

    @push('styles')
    <style>
        .edit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .edit-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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

    {{-- Main Container (Full Width) --}}
    <div class="w-full">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <div class="breadcrumb" style="margin-bottom:4px;font-size:12px;">
                    <a href="{{ route('materials.index') }}" class="text-slate-500 hover:text-primary">Material</a>
                    <span class="breadcrumb-sep mx-1.5 text-slate-400"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                    <span class="text-slate-700 font-medium">Tambah Material</span>
                </div>
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-plus-circle text-primary"></i> Tambah Material Baru
                </h2>
                <p class="text-muted" style="font-size:12.5px;margin:2px 0 0 0;">Daftarkan spesifikasi, satuan, dan informasi stok awal material</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('materials.index') }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:7px 14px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-arrow-left"></i> Kembali
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

        {{-- Main Grid Form --}}
        <form method="POST" action="{{ route('materials.store') }}" onsubmit="return prepareSubmit()" id="material-form" class="material-form-layout">
            @csrf

            {{-- ── KOLOM KIRI: FORM UTAMA ── --}}
            <div class="material-form-main">

                {{-- ── CARD 1: KLASIFIKASI MATERIAL (3 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-layer-group text-primary"></i> 1. Klasifikasi Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            {{-- Dropdown 1: Kategori --}}
                            <div>
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <label class="form-label mb-0" for="category_select" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                        Kategori <span style="color:#ef4444;">*</span>
                                    </label>
                                    @if(auth()->user()->can('delete categories') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat']))
                                    <button type="button" onclick="deleteSelectedCategory()" id="btn_delete_cat" style="display:none;font-size:11px;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;font-weight:600;white-space:nowrap;" title="Hapus kategori yang dipilih jika salah memasukkan">
                                        <i class="fas fa-trash-can me-1"></i> Hapus Kategori
                                    </button>
                                    @endif
                                </div>
                                <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
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
                                    <button type="button" id="btn_toggle_custom_type" onclick="toggleTypeMode(true)" style="font-size:11px;color:#2563eb;background:none;border:none;cursor:pointer;padding:0;font-weight:600;white-space:nowrap;">
                                        + Ketik Baru
                                    </button>
                                </div>
                                <div id="type_select_wrap">
                                    <select id="type_select" class="form-control @error('type') is-invalid @enderror" onchange="onTypeSelectChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                        <option value="">— Pilih Kelompok —</option>
                                        <option value="__new__">✚ Ketik Kelompok Baru...</option>
                                    </select>
                                </div>
                                <div id="type_custom_wrap" style="display:none;">
                                    <div style="display:flex;gap:6px;">
                                        <input type="text" id="type_custom_input" class="form-control" placeholder="Ketik nama kelompok baru..." oninput="onTypeCustomInput(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
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

                            {{-- Field 3: Ukuran / Spesifikasi (md:col-span-2 lg:col-span-1) --}}
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="form-label" for="size_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;display:block;">
                                    Ukuran / Spesifikasi
                                </label>
                                <input type="text" name="size" id="size_input" value="{{ old('size') }}"
                                    class="form-control @error('size') is-invalid @enderror"
                                    placeholder="Contoh: D13mm × 12m, 50kg, M8"
                                    oninput="onTypeChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Kategori Baru (kondisional) --}}
                        <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <label class="form-label" for="new_category" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                            <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Bahan Kimia, Cat, Kayu" style="height:38px;border-radius:6px;background:#fff;max-width:360px;" oninput="syncSummaryPreview()">
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 2: INFORMASI MATERIAL (2 KOLOM RESPONSIF, SUPPLIER FULL-WIDTH) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-cube text-primary"></i> 2. Informasi & Identitas Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- SKU --}}
                            <div>
                                <label class="form-label" for="sku_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">KODE MATERIAL (SKU) <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="sku" id="sku_input" value="{{ old('sku') }}"
                                    class="form-control font-monospace @error('sku') is-invalid @enderror"
                                    placeholder="Contoh: MAT-BES-001"
                                    style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                    oninput="this.value = this.value.toUpperCase(); syncSummaryPreview();" required>
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Merek / Brand --}}
                            <div>
                                <label class="form-label" for="brand_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <input type="text" name="brand" id="brand_input" value="{{ old('brand') }}"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    placeholder="Semen Gresik, Holcim, Krakatau Steel..."
                                    style="height:38px;border-radius:6px;font-size:13px;">
                                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Nama Material --}}
                            <div>
                                <label class="form-label" for="name_input" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                    NAMA MATERIAL (LENGKAP) <span style="color:#ef4444;">*</span>
                                    <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                        Otomatis
                                    </span>
                                </label>
                                <input type="text" name="name" id="name_input" value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Terisi otomatis dari Kelompok + Ukuran..."
                                    style="height:38px;border-radius:6px;font-size:13px;"
                                    oninput="syncSummaryPreview()"
                                    required onfocus="this.dataset.manualEdit='true'">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                    Terisi otomatis dari Kelompok + Ukuran. Edit manual bila perlu nama dagang khusus.
                                </div>
                            </div>

                            {{-- Satuan --}}
                            <div>
                                <label class="form-label" for="unit_select" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">SATUAN <span style="color:#ef4444;">*</span></label>
                                <select name="unit_id" id="unit_select" class="form-control @error('unit_id') is-invalid @enderror" onchange="toggleNewUnit(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                    <option value="">— Pilih Satuan —</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->code }})</option>
                                    @endforeach
                                    <option value="__new__">✚ Satuan Baru...</option>
                                </select>
                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Supplier (md:col-span-2 -> Penuh kiri-kanan, tidak pincang!) --}}
                            <div class="md:col-span-2">
                                <label class="form-label" for="supplier_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">SUPPLIER / PEMASOK <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <input type="text" name="supplier" id="supplier_input" list="supplier_list"
                                    value="{{ old('supplier') }}"
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
                                <label class="form-label" for="new_unit" style="font-size:12px;font-weight:700;color:#334155;">Nama Satuan Baru</label>
                                <input type="text" name="new_unit" id="new_unit" value="{{ old('new_unit') }}" class="form-control" placeholder="Contoh: Unit, Zak, Drum, Liter" style="height:38px;border-radius:6px;background:#fff;max-width:360px;" oninput="syncSummaryPreview()">
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">Satuan baru akan otomatis dibuat bila belum ada.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 3: TAHAP KEDATANGAN BARANG ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-calendar-alt text-primary"></i> 3. Tahap Kedatangan Barang
                        </div>
                        <button type="button" onclick="addStageRow()" class="btn btn-sm btn-primary" style="height:32px;font-size:12px;font-weight:600;padding:0 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                            <i class="fas fa-plus"></i> Tambah Tahap
                        </button>
                    </div>

                    <div class="edit-card-body">
                        {{-- Info Note Bar --}}
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:14px;font-size:12px;color:#475569;">
                            <i class="fas fa-circle-info text-primary" style="font-size:15px;flex-shrink:0;"></i>
                            <span>Jadwal kedatangan bertahap. Status terkunci otomatis sebagai <strong>Rencana</strong> dan berubah otomatis saat barang diterima.</span>
                        </div>

                        {{-- Table Wrapper (Hidden if no rows) --}}
                        <div id="stages_table_wrap" class="overflow-x-auto" style="display:none;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                            <table class="stages-table w-full min-w-[650px]" id="stages_table">
                                <thead>
                                    <tr>
                                        <th style="padding:10px 14px;width:95px;font-weight:700;">Tahap</th>
                                        <th style="padding:10px 14px;width:160px;font-weight:700;">Tanggal</th>
                                        <th style="padding:10px 14px;width:130px;font-weight:700;text-align:right;">Qty Masuk</th>
                                        <th style="padding:10px 14px;width:170px;font-weight:700;">Status</th>
                                        <th style="padding:10px 14px;font-weight:700;">Keterangan</th>
                                        <th style="padding:10px 14px;width:50px;text-align:center;"></th>
                                    </tr>
                                </thead>
                                <tbody id="stages_tbody">
                                    {{-- Baris dinamis ditambahkan via JavaScript --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Empty State (Shown if no rows) --}}
                        <div id="stages_empty" style="display:none;padding:32px 16px;text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;">
                            <div style="width:42px;height:42px;border-radius:50%;background:#e2e8f0;display:inline-flex;align-items:center;justify-content:center;color:#64748b;margin-bottom:10px;">
                                <i class="fas fa-calendar-plus" style="font-size:18px;"></i>
                            </div>
                            <div style="font-size:13.5px;font-weight:600;color:#334155;">Belum ada tahap kedatangan</div>
                            <div style="font-size:12px;color:#64748b;margin-top:4px;max-width:440px;margin-left:auto;margin-right:auto;">
                                Klik tombol <strong>Tambah Tahap</strong> di atas bila material didatangkan secara bertahap.
                            </div>
                        </div>

                        {{-- Summary Bar (Hidden if no rows) --}}
                        <div id="stages_summary_bar" style="display:none;margin-top:14px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                            <div style="display:flex;align-items:center;gap:18px;font-size:12.5px;">
                                <div>Sudah Masuk: <strong id="summary_received" style="color:#16a34a;font-size:13px;">0</strong></div>
                                <div>Rencana: <strong id="summary_planned" style="color:#b45309;font-size:13px;">0</strong></div>
                                <div>Total: <strong id="summary_total" style="color:#0f172a;font-size:13px;">0</strong></div>
                            </div>
                            <div class="text-muted" style="font-size:11.5px;">
                                <i class="fas fa-circle-info text-primary me-1"></i> Status otomatis <strong>Rencana</strong>, dan berubah ke <strong>Sudah Masuk</strong> saat penerimaan (GR).
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 4: STOK AWAL & PENGATURAN STOK (3 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-warehouse text-primary"></i> 4. Stok Awal & Pengaturan Stok
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="form-label" for="warehouse_id" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">LOKASI GUDANG</label>
                                @if($singleWarehouse ?? false)
                                <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $singleWarehouse->id }}">
                                <select class="form-control" style="height:38px;border-radius:6px;font-size:13px;background:#f8fafc;" disabled>
                                    <option value="{{ $singleWarehouse->id }}">{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}</option>
                                </select>
                                @else
                                @php
                                    $defaultWarehouseId = old('warehouse_id', $warehouses->firstWhere('is_central', true)?->id ?? $warehouses->first()?->id);
                                @endphp
                                <select name="warehouse_id" id="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" onchange="syncSummaryPreview()" style="height:38px;border-radius:6px;font-size:13px;">
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
                                <label class="form-label" for="initial_stock_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">STOK AWAL</label>
                                <input type="number" step="1" min="0" name="initial_stock" id="initial_stock_input"
                                    value="{{ old('initial_stock', 0) }}"
                                    class="form-control @error('initial_stock') is-invalid @enderror"
                                    oninput="syncSummaryPreview()"
                                    style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" placeholder="0">
                                @error('initial_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="form-label" for="min_stock" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">MIN. STOK (ALERT)</label>
                                <input type="number" step="1" min="0" name="min_stock" id="min_stock"
                                    value="{{ old('min_stock', 0) }}"
                                    class="form-control @error('min_stock') is-invalid @enderror"
                                    oninput="syncSummaryPreview()"
                                    style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" placeholder="0">
                                @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── KOLOM KANAN: STICKY SUMMARY & ACTION BUTTONS ── --}}
            <div class="material-form-sidebar">

                {{-- CARD RINGKASAN MATERIAL --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-boxes-stacked text-primary"></i> Ringkasan Material
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">KODE SKU</div>
                            <span id="preview_sku" class="font-mono font-bold text-sm text-slate-700 bg-slate-100 px-2.5 py-1 rounded inline-block">
                                —
                            </span>
                        </div>

                        <div class="mt-3">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">NAMA MATERIAL</div>
                            <div id="preview_name" class="font-bold text-slate-900 text-sm leading-snug">
                                Nama Material Baru
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                            <span id="preview_category" class="badge" style="background:#eff6ff;color:#2563eb;font-size:11px;padding:3px 8px;border-radius:5px;font-weight:600;border:1px solid #bfdbfe;">
                                Kategori
                            </span>
                            <span id="preview_type" class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:11px;padding:3px 8px;border-radius:5px;font-weight:600;border:1px solid #ddd6fe;">
                                Kelompok
                            </span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-2" style="font-size:12px;">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Satuan:</span>
                                <span id="preview_unit" class="text-slate-800 font-semibold">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Supplier:</span>
                                <span id="preview_supplier" class="text-slate-800 font-semibold truncate max-w-[180px]" title="-">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Gudang Awal:</span>
                                <span id="preview_warehouse" class="text-slate-800 font-semibold truncate max-w-[180px]">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Stok Awal:</span>
                                <span id="preview_stock" class="text-slate-800 font-semibold">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Min. Stok:</span>
                                <span id="preview_min_stock" class="text-slate-800 font-semibold">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD PANEL AKSI SIMPAN --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-floppy-disk text-primary"></i> Aksi Penyimpanan
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <p class="text-slate-500 text-xs mb-4 leading-relaxed">
                            Pastikan data klasifikasi, spesifikasi, dan stok material telah sesuai sebelum menyimpan ke sistem.
                        </p>

                        <button type="submit" class="btn btn-primary w-full" style="height:42px;font-size:13.5px;font-weight:700;border-radius:8px;box-shadow:0 3px 8px rgba(37,99,235,0.28);display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i class="fas fa-check"></i> Simpan Material
                        </button>

                        <a href="{{ route('materials.index') }}" class="btn btn-light border w-full mt-2.5" style="height:38px;font-size:13px;font-weight:600;border-radius:8px;color:#64748b;display:flex;align-items:center;justify-content:center;gap:6px;">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>

            </div>

        </form>
    </div>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function toggleTypeMode(isCustom) {
            var selectWrap = document.getElementById('type_select_wrap');
            var customWrap = document.getElementById('type_custom_wrap');
            var customBtn  = document.getElementById('btn_toggle_custom_type');
            var select     = document.getElementById('type_select');
            var customInput = document.getElementById('type_custom_input');
            var hiddenInput = document.getElementById('type_input');

            if (isCustom) {
                selectWrap.style.display = 'none';
                customWrap.style.display = 'block';
                if (customBtn) customBtn.style.display = 'none';
                if (!customInput.value && hiddenInput.value) customInput.value = hiddenInput.value;
                customInput.focus();
                hiddenInput.value = customInput.value;
            } else {
                selectWrap.style.display = 'block';
                customWrap.style.display = 'none';
                if (customBtn) customBtn.style.display = '';
                if (hiddenInput.value) select.value = hiddenInput.value;
                if (select.value && select.value !== '__new__') hiddenInput.value = select.value;
            }
            onTypeChange();
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
            return true;
        }

        function syncSummaryPreview() {
            var skuVal = document.getElementById('sku_input')?.value || '—';
            var nameVal = document.getElementById('name_input')?.value || 'Nama Material Baru';
            
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

            var whSelect = document.getElementById('warehouse_id');
            var whVal = '-';
            if (whSelect) {
                if (whSelect.tagName.toLowerCase() === 'select' && whSelect.selectedIndex >= 0) {
                    whVal = whSelect.options[whSelect.selectedIndex].text;
                } else if (whSelect.type === 'hidden') {
                    var whDisabled = document.querySelector('select[disabled]');
                    if (whDisabled && whDisabled.selectedIndex >= 0) {
                        whVal = whDisabled.options[whDisabled.selectedIndex].text;
                    }
                }
            }

            var initStock = document.getElementById('initial_stock_input')?.value || '0';
            var minStock = document.getElementById('min_stock')?.value || '0';

            var elSku = document.getElementById('preview_sku');
            var elName = document.getElementById('preview_name');
            var elCat = document.getElementById('preview_category');
            var elType = document.getElementById('preview_type');
            var elUnit = document.getElementById('preview_unit');
            var elSup = document.getElementById('preview_supplier');
            var elWh = document.getElementById('preview_warehouse');
            var elStock = document.getElementById('preview_stock');
            var elMinStock = document.getElementById('preview_min_stock');

            if (elSku) elSku.textContent = skuVal.trim() ? skuVal : '—';
            if (elName) elName.textContent = nameVal.trim() ? nameVal : 'Nama Material Baru';
            if (elCat) elCat.textContent = catVal;
            if (elType) elType.textContent = typeVal.trim() ? typeVal : 'Kelompok';
            if (elUnit) elUnit.textContent = unitVal;
            if (elSup) {
                elSup.textContent = supplierVal.trim() ? supplierVal : '-';
                elSup.title = supplierVal;
            }
            if (elWh) {
                elWh.textContent = whVal;
                elWh.title = whVal;
            }
            if (elStock) elStock.textContent = parseFloat(initStock).toLocaleString('id-ID');
            if (elMinStock) elMinStock.textContent = parseFloat(minStock).toLocaleString('id-ID');
        }

        onCategoryChange();
        toggleNewUnit();
        syncSummaryPreview();

        // ── Incoming Stages Logic ────────────────────────────
        var stageIndex = 0;

        function checkEmptyState() {
            var tbody   = document.getElementById('stages_tbody');
            var empty   = document.getElementById('stages_empty');
            var tblWrap = document.getElementById('stages_table_wrap');
            var summary = document.getElementById('stages_summary_bar');
            var hasRows = tbody && tbody.children.length > 0;

            if (empty) empty.style.display = hasRows ? 'none' : 'block';
            if (tblWrap) tblWrap.style.display = hasRows ? 'block' : 'none';
            if (summary) summary.style.display = hasRows ? 'flex' : 'none';
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

            var grNotice  = data.received_gr ? `<div style="font-size:10px;color:#059669;font-weight:600;margin-top:2px;"><i class="fas fa-truck-ramp-box"></i> GR #${escapeHtml(data.received_gr)}</div>` : '';

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
                    ${statusVal === 'received' ? `
                        <div style="display:inline-flex;flex-direction:column;gap:3px;">
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-circle-check text-success"></i> Sudah Masuk
                            </span>
                            ${grNotice}
                        </div>
                    ` : `
                        <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;" title="Status terkunci otomatis sebagai Rencana dan akan berubah ke Sudah Masuk saat barang diterima">
                            <i class="fas fa-clock text-warning"></i> Rencana
                        </span>
                    `}
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

            // Sync initial_stock jika ada tahap received
            var initStockInput = document.getElementById('initial_stock_input');
            if (initStockInput && totalReceived > 0 && (!initStockInput.dataset.manual || initStockInput.dataset.manual === 'false')) {
                initStockInput.value = totalReceived;
                syncSummaryPreview();
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        document.getElementById('initial_stock_input')?.addEventListener('input', function() {
            this.dataset.manual = 'true';
            syncSummaryPreview();
        });

        // Prefill existing/old incoming stages if available
        var oldStages = @json(old('incoming_stages', []));
        if (Array.isArray(oldStages) && oldStages.length > 0) {
            oldStages.forEach(function(stg) {
                addStageRow(stg);
            });
        }
        checkEmptyState();
    </script>
    @endpush
</x-app-layout>
