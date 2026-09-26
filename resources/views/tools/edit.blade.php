<x-app-layout>
    <x-slot name="title">Edit Alat — {{ $tool->name }}</x-slot>

    @push('styles')
    <style>
        .edit-tool-layout {
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

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;font-size:12px;">
                <a href="{{ route('tools.index') }}">Alat</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                <a href="{{ route('tools.show', $tool) }}">{{ $tool->name }}</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                <span>Edit</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-pen-to-square text-warning" style="color:#d97706;"></i> Edit Alat: {{ $tool->name }}
            </h2>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">Perbarui detail alat, spesifikasi, rincian unit stok, dan tahapan kedatangan</p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('tools.show', $tool) }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:12.5px;color:#475569;display:inline-flex;align-items:center;gap:6px;border-radius:7px;">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('tools.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:12.5px;color:#475569;display:inline-flex;align-items:center;gap:6px;border-radius:7px;">
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

    <form method="POST" action="{{ route('tools.update', $tool) }}" onsubmit="prepareSubmit()" id="tool-edit-form">
        @csrf
        @method('PUT')

        <div class="edit-tool-layout">

            {{-- ── SECTION 1: KLASIFIKASI ALAT ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div class="edit-card-title">
                        <i class="fas fa-layer-group text-primary"></i> 1. Klasifikasi Alat
                    </div>
                    <span class="text-muted" style="font-size:12px;">Kategori, kelompok, dan spesifikasi alat</span>
                </div>
                <div class="edit-card-body">
                    <div class="grid grid-3" style="gap:16px;">
                        {{-- Kategori --}}
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
                            <select name="category_id" id="category_select" class="form-control @error('category_id') is-invalid @enderror" onchange="onCategoryChange()" style="height:38px;border-radius:6px;font-size:13px;" required>
                                <option value="">— Pilih Kategori —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $tool->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                                <option value="__new__">✚ Kategori Baru...</option>
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Kelompok Alat --}}
                        <div>
                            <label class="form-label" for="type_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Kelompok Alat <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text" name="type" id="type_input" list="type_datalist"
                                   value="{{ old('type', $tool->type) }}"
                                   class="form-control @error('type') is-invalid @enderror"
                                   placeholder="Pilih atau ketik kelompok (misal: Genset, Molen)..."
                                   oninput="onTypeChange()"
                                   style="height:38px;border-radius:6px;font-size:13px;" required autocomplete="off">
                            <datalist id="type_datalist">
                                {{-- Diisi dinamis sesuai kategori --}}
                            </datalist>
                            <div id="type_hint" style="font-size:11px;color:#94a3b8;margin-top:4px;">
                                Ketik kelompok baru atau pilih rekomendasi
                            </div>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Spesifikasi / Ukuran --}}
                        <div>
                            <label class="form-label" for="size_input" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Spesifikasi / Ukuran
                            </label>
                            <input type="text" name="size" id="size_input" value="{{ old('size', $tool->size) }}"
                                   class="form-control @error('size') is-invalid @enderror"
                                   placeholder="Contoh: 5000W, 10 Ton, 2.5 HP, 14 Inch"
                                   oninput="onTypeChange()" style="height:38px;border-radius:6px;font-size:13px;">
                            @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Kategori Baru (kondisional) --}}
                    <div id="new_category_wrap" style="display:none;margin-top:14px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                        <label class="form-label" for="new_category" style="font-size:12px;font-weight:700;color:#334155;">Nama Kategori Baru</label>
                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="form-control" placeholder="Contoh: Alat Berat, Power Tools, Surveying" style="height:38px;border-radius:6px;background:#fff;max-width:360px;">
                        <div class="text-muted" style="font-size:11px;margin-top:4px;">Kategori baru akan otomatis dibuat bila belum ada.</div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2: IDENTITAS & LOKASI ALAT ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div class="edit-card-title">
                        <i class="fas fa-helmet-safety text-warning" style="color:#d97706;"></i> 2. Identitas & Lokasi Alat
                    </div>
                    <span class="text-muted" style="font-size:12px;">Kode unik, nama lengkap, merek, dan gudang</span>
                </div>
                <div class="edit-card-body">
                    <div class="grid grid-2" style="gap:16px;">
                        {{-- Kode Alat --}}
                        <div>
                            <label class="form-label" for="code" style="font-size:12px;font-weight:700;color:#475569;">KODE ALAT <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $tool->code) }}"
                                class="form-control font-monospace @error('code') is-invalid @enderror"
                                placeholder="Contoh: TLS-GEN-001 / MOLEN-01"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;text-transform:uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Merek / Brand --}}
                        <div>
                            <label class="form-label" for="brand" style="font-size:12px;font-weight:700;color:#475569;">MEREK / BRAND <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                            <input type="text" name="brand" value="{{ old('brand', $tool->brand) }}"
                                class="form-control @error('brand') is-invalid @enderror"
                                placeholder="Honda, Makita, Bosch, Komatsu..."
                                style="height:38px;border-radius:6px;font-size:13px;">
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nama Alat --}}
                        <div>
                            <label class="form-label" for="name_input" style="font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;">
                                NAMA ALAT (LENGKAP) <span style="color:#ef4444;">*</span>
                                <span id="name_auto_badge" style="display:none;padding:1px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:600;border:1px solid #e2e8f0;">
                                    Otomatis
                                </span>
                            </label>
                            <input type="text" name="name" id="name_input" value="{{ old('name', $tool->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Otomatis dari Kelompok + Spesifikasi, bisa diedit"
                                style="height:38px;border-radius:6px;font-size:13px;"
                                required data-manual-edit="true">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">
                                Terisi otomatis dari Kelompok Alat + Spesifikasi. Edit manual bila perlu.
                            </div>
                        </div>

                        {{-- Lokasi Gudang --}}
                        <div>
                            <label class="form-label" for="warehouse_id" style="font-size:12px;font-weight:700;color:#475569;">LOKASI GUDANG PENYIMPANAN</label>
                            <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">-- Pilih Gudang (Opsional) --</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id', $tool->current_warehouse_id) == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 3: RINCIAN STOK UNIT & CATATAN ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div class="edit-card-title">
                        <i class="fas fa-boxes-stacked text-primary"></i> 3. Rincian Stok Unit & Catatan
                    </div>
                    <span class="text-muted" style="font-size:12px;">Jumlah unit aktif, kondisi fisik, dan catatan</span>
                </div>
                <div class="edit-card-body">
                    <div class="grid grid-3" style="gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">TOTAL STOK UNIT <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="1" min="0" name="stock_total" id="stock_total_input" value="{{ old('stock_total', $tool->stock_total) }}"
                                class="form-control @error('stock_total') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:700;" required>
                            @error('stock_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">STOK TERSEDIA (SIAP PAKAI) <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="1" min="0" name="stock_available" id="stock_available_input" value="{{ old('stock_available', $tool->stock_available) }}"
                                class="form-control @error('stock_available') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:700;color:#16a34a;" oninput="recalculateTotalStock()" required>
                            @error('stock_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">SEDANG DIPINJAM</label>
                            <input type="number" id="stock_borrowed_input" value="{{ $tool->stock_borrowed }}" class="form-control" disabled style="height:38px;border-radius:6px;font-size:13px;background:#f1f5f9;color:#64748b;font-weight:700;">
                            <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Otomatis dari peminjaman aktif</div>
                        </div>
                    </div>

                    <div class="grid grid-2" style="gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">DALAM MAINTENANCE</label>
                            <input type="number" step="1" min="0" name="stock_maintenance" id="stock_maintenance_input" value="{{ old('stock_maintenance', $tool->stock_maintenance) }}"
                                class="form-control @error('stock_maintenance') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;color:#d97706;" oninput="recalculateTotalStock()">
                            @error('stock_maintenance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">RUSAK</label>
                            <input type="number" step="1" min="0" name="stock_damaged" id="stock_damaged_input" value="{{ old('stock_damaged', $tool->stock_damaged) }}"
                                class="form-control @error('stock_damaged') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;color:#dc2626;" oninput="recalculateTotalStock()">
                            @error('stock_damaged')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">CATATAN / KETERANGAN TAMBAHAN <span style="font-weight:400;color:#94a3b8;font-size:11px;">(Opsional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Deskripsi, kelengkapan aksesoris, atau catatan teknis alat..." style="border-radius:6px;font-size:13px;resize:vertical;">{{ old('notes', $tool->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 4: TAHAP KEDATANGAN ALAT ── --}}
            <div class="edit-card">
                <div class="edit-card-header">
                    <div>
                        <div class="edit-card-title">
                            <i class="fas fa-calendar-alt text-warning" style="color:#d97706;"></i> 4. Tahap Kedatangan Alat
                        </div>
                        <div class="text-muted" style="font-size:12px;margin-top:2px;">
                            Jadwal kedatangan bertahap alat kerja. Status terkunci otomatis sebagai <strong>Rencana</strong> dan berubah otomatis saat barang diterima.
                        </div>
                    </div>
                    <button type="button" onclick="addStageRow()" class="btn btn-sm btn-warning" style="height:34px;font-size:12px;font-weight:700;padding:6px 14px;border-radius:6px;color:#fff;background:#d97706;border:none;">
                        <i class="fas fa-plus me-1"></i> Tambah Tahap
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="stages-table" id="stages_table">
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
                            {{-- Baris dinamis --}}
                        </tbody>
                    </table>
                    <div id="stages_empty" style="display:none;padding:28px 16px;text-align:center;color:#94a3b8;">
                        <i class="fas fa-calendar-plus" style="font-size:28px;margin-bottom:8px;color:#cbd5e1;display:block;"></i>
                        <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada jadwal tahap kedatangan alat ini.</div>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;">Klik <strong>Tambah Tahap</strong> bila alat ini memiliki jadwal pengiriman fisik terencana.</div>
                    </div>
                </div>

                {{-- Summary Bar --}}
                <div style="padding:12px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="display:flex;align-items:center;gap:16px;font-size:12.5px;">
                        <div>Masuk: <strong id="summary_received" style="color:#16a34a;font-size:13px;">0</strong> Unit</div>
                        <div>Rencana: <strong id="summary_planned" style="color:#b45309;font-size:13px;">0</strong> Unit</div>
                        <div>Total: <strong id="summary_total" style="color:#0f172a;font-size:13px;">0</strong> Unit</div>
                    </div>
                    <div class="text-muted" style="font-size:11.5px;">
                        <i class="fas fa-lock text-muted me-1"></i> Status terkunci otomatis sebagai <strong>Rencana</strong>, dan akan berubah ke <strong>Sudah Masuk</strong> otomatis saat dicatat di menu Penerimaan Barang.
                    </div>
                </div>
            </div>

            {{-- ── ACTION BUTTONS ── --}}
            <div class="edit-actions-bar">
                <a href="{{ route('tools.show', $tool) }}" class="btn btn-light border" style="height:38px;padding:0 18px;font-size:13px;font-weight:500;border-radius:7px;color:#64748b;display:inline-flex;align-items:center;">
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

        function recalculateTotalStock() {
            var avail = parseInt(document.getElementById('stock_available_input').value) || 0;
            var borrowed = parseInt(document.getElementById('stock_borrowed_input').value) || 0;
            var maint = parseInt(document.getElementById('stock_maintenance_input').value) || 0;
            var damaged = parseInt(document.getElementById('stock_damaged_input').value) || 0;
            var totalInput = document.getElementById('stock_total_input');
            if (totalInput) {
                totalInput.value = avail + borrowed + maint + damaged;
            }
        }

        function prepareSubmit() {
            recalculateTotalStock();
            var cat = document.getElementById('category_select');
            if (cat && cat.value === '__new__') cat.value = '';
        }

        // ── Incoming Stages Logic (Locked Badge) ──────────────────────────
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
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        // Prefill existing incoming stages
        var existingStages = @json(old('incoming_stages', $tool->incoming_stages ?? []));
        if (Array.isArray(existingStages) && existingStages.length > 0) {
            existingStages.forEach(function(stg) {
                addStageRow(stg);
            });
        }
        checkEmptyState();

        // Initialize on page load
        onCategoryChange();
    </script>
    @endpush
</x-app-layout>
