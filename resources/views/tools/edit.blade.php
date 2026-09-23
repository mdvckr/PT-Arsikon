<x-app-layout>
    <x-slot name="title">Edit Alat — {{ $tool->name }}</x-slot>

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;">
                <a href="{{ route('tools.index') }}">Alat</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <span>Edit: {{ $tool->name }}</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Edit Alat</h2>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">Perbarui detail alat, spesifikasi, dan stok</p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('tools.show', $tool) }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;color:#475569;display:inline-flex;align-items:center;gap:6px;border-radius:6px;">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('tools.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;color:#475569;display:inline-flex;align-items:center;gap:6px;border-radius:6px;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('tools.update', $tool) }}" onsubmit="prepareSubmit()" id="tool-edit-form">
        @csrf
        @method('PUT')

        <div style="display:flex;flex-direction:column;gap:16px;max-width:960px;">

            {{-- SECTION 1: KLASIFIKASI ALAT --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">1. Klasifikasi Alat</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Tentukan Kategori, Kelompok Alat, dan Ukuran / Spesifikasi</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-3" style="gap:16px;">
                        {{-- Kategori --}}
                        <div>
                            <label class="form-label" for="category_select" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                                Kategori <span style="color:#ef4444;">*</span>
                            </label>
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

                        {{-- Kelompok Alat (Unified: 1 field, autocomplete datalist, no duplicate options) --}}
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

            {{-- SECTION 2: IDENTITAS & LOKASI ALAT --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">2. Identitas & Lokasi Alat</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Kode alat, nama lengkap, merek, dan lokasi gudang</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-2" style="gap:16px;">
                        {{-- Kode Alat --}}
                        <div>
                            <label class="form-label" for="code" style="font-size:12px;font-weight:700;color:#475569;">KODE ALAT <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $tool->code) }}"
                                class="form-control font-monospace @error('code') is-invalid @enderror"
                                placeholder="Contoh: TLS-GEN-001 / MOLEN-01"
                                style="height:38px;border-radius:6px;font-size:13px;font-weight:600;background:#f8fafc;" required>
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

            {{-- SECTION 3: RINCIAN STOK UNIT & CATATAN --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">3. Rincian Stok Unit & Catatan</div>
                    <div class="text-muted" style="font-size:12px;margin-top:1px;">Perbarui data unit stok dan kondisi fisik alat</div>
                </div>
                <div style="padding:18px;">
                    <div class="grid grid-3" style="gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">TOTAL STOK UNIT <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="1" min="0" name="stock_total" id="stock_total_input" value="{{ old('stock_total', $tool->stock_total) }}"
                                class="form-control @error('stock_total') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" required>
                            @error('stock_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">STOK TERSEDIA <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="1" min="0" name="stock_available" value="{{ old('stock_available', $tool->stock_available) }}"
                                class="form-control @error('stock_available') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" required>
                            @error('stock_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">SEDANG DIPINJAM</label>
                            <input type="number" value="{{ $tool->stock_borrowed }}" class="form-control" disabled style="height:38px;border-radius:6px;font-size:13px;background:#f1f5f9;color:#64748b;">
                            <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Otomatis dari peminjaman aktif</div>
                        </div>
                    </div>

                    <div class="grid grid-2" style="gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">DALAM MAINTENANCE</label>
                            <input type="number" step="1" min="0" name="stock_maintenance" value="{{ old('stock_maintenance', $tool->stock_maintenance) }}"
                                class="form-control @error('stock_maintenance') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;">
                            @error('stock_maintenance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;">RUSAK</label>
                            <input type="number" step="1" min="0" name="stock_damaged" value="{{ old('stock_damaged', $tool->stock_damaged) }}"
                                class="form-control @error('stock_damaged') is-invalid @enderror" style="height:38px;border-radius:6px;font-size:13px;">
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

            {{-- ACTION BUTTONS --}}
            <div style="display:flex;align-items:center;gap:10px;padding:4px 0;">
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 20px;font-size:13px;font-weight:600;border-radius:6px;">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('tools.index') }}" class="btn btn-light border" style="height:38px;padding:0 16px;font-size:13px;font-weight:500;border-radius:6px;color:#64748b;">
                    Batal
                </a>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        var existingGroups = @json($existingGroups ?? []);

        function onCategoryChange() {
            var select = document.getElementById('category_select');
            var catId = select.value;
            var wrap = document.getElementById('new_category_wrap');
            var isNew = catId === '__new__';
            wrap.style.display = isNew ? 'block' : 'none';
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
        }

        // Initialize on page load
        onCategoryChange();
    </script>
    @endpush
</x-app-layout>
