<x-app-layout>
    <x-slot name="title">Tambah Alat</x-slot>

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
                    <a href="{{ route('tools.index') }}" class="text-slate-500 hover:text-primary">Alat</a>
                    <span class="breadcrumb-sep mx-1.5 text-slate-400"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                    <span class="text-slate-700 font-medium">Tambah Alat</span>
                </div>
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-tools text-primary"></i> Tambah Alat Baru
                </h2>
                <p class="text-muted" style="font-size:12.5px;margin:2px 0 0 0;">Daftarkan inventaris alat baru beserta spesifikasi dan stok unit</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tools.index') }}" class="btn btn-light border" style="font-size:12.5px;color:#475569;border-radius:7px;padding:7px 14px;display:inline-flex;align-items:center;gap:6px;">
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
        <form method="POST" action="{{ route('tools.store') }}" onsubmit="return prepareSubmit()" id="tool-form" class="material-form-layout">
            @csrf

            {{-- ── KOLOM KIRI: FORM UTAMA ── --}}
            <div class="material-form-main">

                {{-- ── CARD 1: KLASIFIKASI ALAT (3 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-layer-group text-primary"></i> 1. Klasifikasi Alat
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
                                <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;" required>
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $selectedCategoryId) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                    <option value="__new__">✚ Kategori Baru...</option>
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Dropdown 2: Kelompok Alat (Unified Autocomplete Datalist) --}}
                            <div>
                                <label class="form-label mb-0" for="type_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;display:block;">
                                    Kelompok Alat <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="text" name="type" id="type_input" list="type_datalist"
                                       value="{{ old('type') }}"
                                       class="form-control @error('type') is-invalid @enderror"
                                       placeholder="Pilih atau ketik kelompok (misal: Genset, Molen)..."
                                       oninput="onTypeChange(); syncSummaryPreview();"
                                       style="height:38px;border-radius:6px;font-size:13px;" required autocomplete="off">
                                <datalist id="type_datalist">
                                    {{-- Diisi dinamis via JavaScript sesuai kategori --}}
                                </datalist>
                                <div id="type_hint" style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                    Ketik kelompok baru atau pilih rekomendasi
                                </div>
                                @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            {{-- Field 3: Spesifikasi / Ukuran (md:col-span-2 lg:col-span-1) --}}
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="form-label" for="size_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;display:block;">
                                    Spesifikasi / Ukuran / Kapasitas
                                </label>
                                <input type="text" name="size" id="size_input" value="{{ old('size') }}"
                                       class="form-control @error('size') is-invalid @enderror"
                                       placeholder="Contoh: 5000W, 10 Ton, 2.5 HP, 14 Inch"
                                       oninput="onTypeChange(); syncSummaryPreview();" style="height:38px;border-radius:6px;font-size:13px;">
                                @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Kategori Baru (kondisional) --}}
                        <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <label class="form-label" for="new_category" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                            <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Alat Berat, Power Tools, Surveying" style="height:38px;border-radius:6px;background:#fff;max-width:360px;" oninput="syncSummaryPreview()">
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 2: IDENTITAS & LOKASI ALAT (2 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-cube text-primary"></i> 2. Identitas, Merek & Lokasi Alat
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Kode Alat --}}
                            <div>
                                <label class="form-label" for="code_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">KODE ALAT (SKU) <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="code" id="code_input" value="{{ old('code') }}"
                                    class="form-control font-monospace @error('code') is-invalid @enderror"
                                    placeholder="Contoh: TLS-GEN-001 / MOLEN-01"
                                    style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                    oninput="this.value = this.value.toUpperCase(); syncSummaryPreview();" required>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Merek / Brand --}}
                            <div>
                                <label class="form-label" for="brand_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <input type="text" name="brand" id="brand_input" value="{{ old('brand') }}"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    placeholder="Honda, Makita, Bosch, Komatsu..."
                                    oninput="syncSummaryPreview()"
                                    style="height:38px;border-radius:6px;font-size:13px;">
                                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Nama Alat --}}
                            <div>
                                <label class="form-label" for="name_input" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                    NAMA ALAT (LENGKAP) <span style="color:#ef4444;">*</span>
                                    <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                        Otomatis
                                    </span>
                                </label>
                                <input type="text" name="name" id="name_input" value="{{ old('name', request('name')) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Terisi otomatis dari Kelompok + Spesifikasi..."
                                    style="height:38px;border-radius:6px;font-size:13px;"
                                    required onfocus="this.dataset.manualEdit='true'" oninput="syncSummaryPreview()">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                    Terisi otomatis dari Kelompok Alat + Spesifikasi. Edit manual bila perlu.
                                </div>
                            </div>

                            {{-- Lokasi Gudang --}}
                            <div>
                                <label class="form-label" for="warehouse_id" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">LOKASI GUDANG PENYIMPANAN <span style="color:#ef4444;">*</span></label>
                                @if($singleWarehouse ?? false)
                                <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $singleWarehouse->id }}">
                                <input type="text" class="form-control" value="{{ $singleWarehouse->name }} {{ $singleWarehouse->is_central ? '(Pusat)' : '(Proyek)' }}" disabled style="height:38px;border-radius:6px;font-size:13px;background:#f8fafc;">
                                @else
                                @php
                                    $defaultWhId = old('warehouse_id', $warehouses->firstWhere('is_central', true)?->id ?? $warehouses->first()?->id);
                                @endphp
                                <select name="warehouse_id" id="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" onchange="syncSummaryPreview()" style="height:38px;border-radius:6px;font-size:13px;" required>
                                    <option value="">-- Pilih Gudang Penyimpanan --</option>
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $defaultWhId == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                    </option>
                                    @endforeach
                                </select>
                                @endif
                                @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Catatan / Keterangan (Full-width md:col-span-2) --}}
                            <div class="md:col-span-2">
                                <label class="form-label" for="notes_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">CATATAN / KETERANGAN TAMBAHAN <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                                <textarea name="notes" id="notes_input" rows="2" class="form-control @error('notes') is-invalid @enderror" placeholder="Keterangan kelengkapan aksesoris, spesifikasi teknis tambahan, atau nomor seri pabrikan..." style="border-radius:6px;font-size:13px;">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 3: STOK AWAL UNIT (2 KOLOM RESPONSIF) ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-cubes text-primary"></i> 3. Stok Awal Unit
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="form-label" for="initial_stock_input" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">JUMLAH TOTAL STOK UNIT <span style="color:#ef4444;">*</span></label>
                                <input type="number" step="1" min="0" name="stock_total" id="initial_stock_input" value="{{ old('stock_total', 1) }}"
                                    class="form-control @error('stock_total') is-invalid @enderror" placeholder="1" oninput="syncSummaryPreview()" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" required>
                                @error('stock_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">Jumlah unit yang tersedia saat pertama kali didaftarkan.</div>
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;display:block;">STATUS KONDISI AWAL</label>
                                <div style="height:38px;display:flex;align-items:center;padding:0 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;font-size:12.5px;color:#166534;font-weight:600;gap:8px;">
                                    <i class="fas fa-circle-check text-success"></i> Kondisi Siap Digunakan (Baik)
                                </div>
                                <div class="text-muted" style="font-size:11px;margin-top:4px;">Pencatatan kondisi perorangan dapat dikelola saat peminjaman/servis.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CARD 4: TAHAP KEDATANGAN ALAT ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-calendar-alt text-primary"></i> 4. Tahap Kedatangan Alat
                        </div>
                        <button type="button" onclick="addStageRow()" class="btn btn-sm btn-primary" style="height:32px;font-size:12px;font-weight:600;padding:0 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                            <i class="fas fa-plus"></i> Tambah Tahap
                        </button>
                    </div>

                    <div class="edit-card-body">
                        {{-- Info Note Bar --}}
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:14px;font-size:12px;color:#475569;">
                            <i class="fas fa-circle-info text-primary" style="font-size:15px;flex-shrink:0;"></i>
                            <span>Jadwal pengiriman/kedatangan alat bertahap. Status terkunci otomatis sebagai <strong>Rencana</strong> dan berubah otomatis saat alat diterima.</span>
                        </div>

                        {{-- Table Wrapper (Hidden if no rows) --}}
                        <div id="stages_table_wrap" class="overflow-x-auto" style="display:none;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                            <table class="stages-table w-full min-w-[650px]" id="stages_table">
                                <thead>
                                    <tr>
                                        <th style="padding:10px 14px;width:95px;font-weight:700;">Tahap</th>
                                        <th style="padding:10px 14px;width:160px;font-weight:700;">Tanggal Rencana</th>
                                        <th style="padding:10px 14px;width:130px;font-weight:700;text-align:right;">Qty Unit</th>
                                        <th style="padding:10px 14px;width:160px;font-weight:700;">Status (Otomatis)</th>
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
                            <div style="font-size:13.5px;font-weight:600;color:#334155;">Belum ada jadwal tahap kedatangan</div>
                            <div style="font-size:12px;color:#64748b;margin-top:4px;max-width:440px;margin-left:auto;margin-right:auto;">
                                Klik tombol <strong>Tambah Tahap</strong> di atas bila alat kerja ini didatangkan secara bertahap.
                            </div>
                        </div>

                        {{-- Summary Bar (Hidden if no rows) --}}
                        <div id="stages_summary_bar" style="display:none;margin-top:14px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                            <div style="display:flex;align-items:center;gap:18px;font-size:12.5px;">
                                <div>Sudah Masuk: <strong id="summary_received" style="color:#16a34a;font-size:13px;">0</strong> Unit</div>
                                <div>Rencana: <strong id="summary_planned" style="color:#b45309;font-size:13px;">0</strong> Unit</div>
                                <div>Total: <strong id="summary_total" style="color:#0f172a;font-size:13px;">0</strong> Unit</div>
                            </div>
                            <div class="text-muted" style="font-size:11.5px;">
                                <i class="fas fa-lock text-muted me-1"></i> Terkunci otomatis sebagai <strong>Rencana</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── KOLOM KANAN: STICKY PANEL RINGKASAN & AKSI ── --}}
            <div class="material-form-sidebar">

                {{-- CARD RINGKASAN ALAT --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <div class="edit-card-title">
                            <i class="fas fa-tools text-primary"></i> Ringkasan Alat
                        </div>
                    </div>
                    <div class="edit-card-body">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">KODE ALAT</div>
                            <span id="preview_code" class="font-mono font-bold text-sm text-slate-700 bg-slate-100 px-2.5 py-1 rounded inline-block">
                                —
                            </span>
                        </div>

                        <div class="mt-3">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">NAMA ALAT</div>
                            <div id="preview_name" class="font-bold text-slate-900 text-sm leading-snug">
                                Nama Alat Baru
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
                                <span class="text-slate-500">Merek / Brand:</span>
                                <span id="preview_brand" class="text-slate-800 font-semibold truncate max-w-[180px]">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Gudang Penyimpanan:</span>
                                <span id="preview_warehouse" class="text-slate-800 font-semibold truncate max-w-[180px]">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Total Stok Awal:</span>
                                <span id="preview_stock" class="text-slate-800 font-semibold">1 Unit</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Kondisi Awal:</span>
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Baik / Siap Pakai</span>
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
                            Pastikan data klasifikasi, kode alat inventaris, dan lokasi gudang telah sesuai sebelum menyimpan ke sistem.
                        </p>

                        <button type="submit" class="btn btn-primary w-full" style="height:42px;font-size:13.5px;font-weight:700;border-radius:8px;box-shadow:0 3px 8px rgba(37,99,235,0.28);display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i class="fas fa-check"></i> Simpan Alat
                        </button>

                        <a href="{{ route('tools.index') }}" class="btn btn-light border w-full mt-2.5" style="height:38px;font-size:13px;font-weight:600;border-radius:8px;color:#64748b;display:flex;align-items:center;justify-content:center;gap:6px;">
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

        function updateDeleteCategoryBtn() {
            var sel = document.getElementById('category_select');
            var btn = document.getElementById('btn_delete_cat');
            if (!btn) return;
            if (sel && sel.value && sel.value !== '__new__') {
                btn.style.display = 'inline-flex';
                var optText = sel.options[sel.selectedIndex].text;
                btn.title = 'Hapus kategori "' + optText + '" jika salah memasukkannya';
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
                    alert(res.data.message || 'Gagal menghapus kategori. Kategori mungkin masih memiliki data alat kerja.');
                }
            })
            .catch(function(err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus kategori.');
            });
        }

        function onCategoryChange() {
            var select = document.getElementById('category_select');
            var catId = select.value;
            var wrap = document.getElementById('new_category_wrap');
            var isNew = catId === '__new__';
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
                hint.innerHTML = '<span style="color:#16a34a;"><i class="fas fa-check-circle" style="font-size:10px;"></i> ' + groups.length + ' kelompok alat tersedia (klik/ketik)</span>';
            } else if (catId) {
                hint.innerHTML = '<span style="color:#64748b;">Belum ada kelompok di kategori ini. Ketik nama kelompok alat baru.</span>';
            } else {
                hint.textContent = 'Ketik kelompok baru atau pilih rekomendasi';
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
            return true;
        }

        function syncSummaryPreview() {
            var codeVal = document.getElementById('code_input')?.value || '—';
            var nameVal = document.getElementById('name_input')?.value || 'Nama Alat Baru';
            var brandVal = document.getElementById('brand_input')?.value || '-';

            var catSelect = document.getElementById('category_select');
            var catVal = 'Kategori';
            if (catSelect && catSelect.value === '__new__') {
                catVal = document.getElementById('new_category')?.value || 'Kategori Baru';
            } else if (catSelect && catSelect.selectedIndex > 0) {
                catVal = catSelect.options[catSelect.selectedIndex].text;
            }

            var typeVal = document.getElementById('type_input')?.value || 'Kelompok';

            var whSelect = document.getElementById('warehouse_id');
            var whVal = '-';
            if (whSelect) {
                if (whSelect.tagName.toLowerCase() === 'select' && whSelect.selectedIndex > 0) {
                    whVal = whSelect.options[whSelect.selectedIndex].text;
                } else if (whSelect.type === 'hidden') {
                    var whDisabled = document.querySelector('input[disabled]');
                    if (whDisabled && whDisabled.value) {
                        whVal = whDisabled.value;
                    }
                }
            }

            var stockVal = document.getElementById('initial_stock_input')?.value || '1';

            var elCode = document.getElementById('preview_code');
            var elName = document.getElementById('preview_name');
            var elCat = document.getElementById('preview_category');
            var elType = document.getElementById('preview_type');
            var elBrand = document.getElementById('preview_brand');
            var elWh = document.getElementById('preview_warehouse');
            var elStock = document.getElementById('preview_stock');

            if (elCode) elCode.textContent = codeVal.trim() ? codeVal : '—';
            if (elName) elName.textContent = nameVal.trim() ? nameVal : 'Nama Alat Baru';
            if (elCat) elCat.textContent = catVal;
            if (elType) elType.textContent = typeVal.trim() ? typeVal : 'Kelompok';
            if (elBrand) {
                elBrand.textContent = brandVal.trim() ? brandVal : '-';
                elBrand.title = brandVal;
            }
            if (elWh) {
                elWh.textContent = whVal;
                elWh.title = whVal;
            }
            if (elStock) elStock.textContent = (parseInt(stockVal) || 0) + ' Unit';
        }

        // ── Incoming Stages Logic (Locked Badge) ──────────────────────────
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

            var statusBadge = (statusVal === 'received')
                ? `<div style="display:inline-flex;flex-direction:column;gap:3px;">
                       <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;width:fit-content;">
                           <i class="fas fa-circle-check text-success"></i> Sudah Masuk
                       </span>
                   </div>`
                : `<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:6px;" title="Status terkunci otomatis sebagai Rencana dan berubah ke Sudah Masuk saat alat diterima">
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
                    <input type="number" step="1" min="1" name="incoming_stages[${stageIndex}][qty]" value="${qtyVal}"
                           class="form-control form-control-sm stage-qty-input"
                           placeholder="1"
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

        // Initialize on page load
        onCategoryChange();
        syncSummaryPreview();
    </script>
    @endpush
</x-app-layout>
