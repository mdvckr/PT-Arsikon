<x-app-layout>
    <x-slot name="title">Edit PO Lapangan: {{ $materialRequest->request_number }}</x-slot>

    @push('styles')
    <style>
        .form-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .form-section-header {
            padding: 14px 18px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .form-section-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mr-doc-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 18px;
        }

        .mr-doc-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mr-doc-field label {
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 2px;
        }

        .mr-doc-field .form-control {
            height: 38px;
            padding: 7px 12px;
            font-size: 12.5px;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .mr-doc-field .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .mr-item-table th {
            padding: 10px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #475569 !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            white-space: nowrap !important;
        }

        .mr-item-table td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

        .mr-item-table tbody tr.mat-row:hover {
            background-color: #f8fafc;
        }

        .mr-item-table tbody tr.mat-row.row-active {
            background-color: #f0f7ff !important;
        }

        .qty-input {
            text-align: center;
            font-weight: 700;
            color: #0f172a;
            width: 85px;
            height: 32px;
            font-size: 12.5px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0 4px;
            background: #ffffff;
            transition: all 0.15s ease;
        }

        .qty-input:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        /* Sticky Bottom Action Bar */
        .mr-sticky-bar {
            position: sticky;
            bottom: 12px;
            z-index: 40;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        @media (max-width: 992px) {
            .mr-doc-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .mr-doc-grid {
                grid-template-columns: 1fr;
            }
            .mr-sticky-bar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    @endpush

    @php
        $items = $materialRequest->items->values();
    @endphp

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-3" style="font-size:12.5px;">
        <a href="{{ route('material-requests.index') }}" style="color:#64748b;text-decoration:none;">PO Lapangan</a>
        <span class="breadcrumb-sep" style="margin:0 6px;color:#94a3b8;"><x-lucide-chevron-right style="font-size:9px;" /></span>
        <a href="{{ route('material-requests.show', $materialRequest) }}" style="color:#64748b;text-decoration:none;">{{ $materialRequest->request_number }}</a>
        <span class="breadcrumb-sep" style="margin:0 6px;color:#94a3b8;"><x-lucide-chevron-right style="font-size:9px;" /></span>
        <span style="font-weight:600;color:#0f172a;">Edit PO Lapangan</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:10px;padding:12px 16px;font-size:12.5px;">
        <div style="font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
            <x-lucide-triangle-alert class="text-danger" /> Terdapat kesalahan pada formulir:
        </div>
        <ul class="mb-0" style="padding-left:20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="materialRequestForm" method="POST" action="{{ route('material-requests.update', $materialRequest) }}">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Informasi Dokumen & Proyek --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <x-lucide-square-pen />
                    </div>
                    <span>1. Dokumen PO Lapangan: {{ $materialRequest->request_number }}</span>
                </div>
                <div class="text-muted" style="font-size:11.5px;">
                    Perbarui informasi umum dokumen PO Lapangan
                </div>
            </div>

            <div class="mr-doc-grid">
                {{-- Proyek Pemohon --}}
                <div class="mr-doc-field">
                    <label for="warehouse_id">
                        Nama Proyek / Gudang Pemohon <span class="text-danger">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id', $warehouseId) == $wh->id ? 'selected' : '' }}>
                            {{ $wh->project ? $wh->project->name : $wh->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kepada (Tujuan Permintaan: Khusus Pengadaan) --}}
                <div class="mr-doc-field">
                    <label for="source_warehouse_id">
                        Kepada
                    </label>
                    <select name="source_warehouse_id" id="source_warehouse_id" class="form-control" style="background-color:#f8fafc;cursor:not-allowed;" readonly>
                        <option value="" selected>Pengadaan</option>
                    </select>
                </div>


                {{-- Dipesan Oleh --}}
                <div class="mr-doc-field">
                    <label for="requested_as">
                        Dipesan Oleh
                    </label>
                    <select name="requested_as" id="requested_as" class="form-control">
                        @php $currRole = old('requested_as', $materialRequest->requested_as ?: 'Site Engineer'); @endphp
                        <option value="Site Engineer" {{ $currRole === 'Site Engineer' ? 'selected' : '' }}>Site Engineer</option>
                        <option value="Site Manager" {{ $currRole === 'Site Manager' ? 'selected' : '' }}>Site Manager</option>
                        <option value="Project Manager" {{ $currRole === 'Project Manager' ? 'selected' : '' }}>Project Manager</option>
                    </select>
                </div>
            </div>

            {{-- Catatan --}}
            <div style="padding:0 18px 18px 18px;">
                <div class="mr-doc-field">
                    <label for="notes">Catatan Permintaan (Opsional)</label>
                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Catatan tambahan..." value="{{ old('notes', $materialRequest->notes) }}" style="height:36px;font-size:12px;">
                </div>
            </div>
        </div>

        {{-- SECTION 2: Rincian Item Permintaan --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <x-lucide-boxes />
                    </div>
                    <span>2. Rincian Item / Material yang Diminta</span>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addCustomRequestRow()" style="padding:6px 14px;font-size:12px;border-radius:7px;font-weight:700;box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                    <x-lucide-plus class="me-1" /> Tambah Baris Item
                </button>
            </div>

            <div style="padding:14px 18px;">
                <div class="flex justify-between items-center mb-2" style="flex-wrap:wrap;gap:8px;">
                    <div class="text-muted" style="font-size:12px;line-height:1.4;">
                        Periksa dan sesuaikan item barang atau material yang diminta. Anda dapat menambah item baru atau mengubah kuantitas item yang telah diajukan.
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-weight:600;font-size:10.5px;white-space:nowrap;padding:3px 8px;">
                        <x-lucide-arrow-left-right class="me-1" /> Geser tabel jika layar terbatas
                    </span>
                </div>

                {{-- Datalist untuk Autocomplete Material Master --}}
                <datalist id="masterMaterialsList">
                    @if(isset($masterMaterials))
                        @foreach($masterMaterials as $m)
                        <option value="{{ $m->name }}" data-id="{{ $m->id }}" data-unit="{{ $m->unit?->abbreviation ?? $m->unit?->name ?? 'pcs' }}" data-spec="{{ $m->size ?? '' }}">SKU: {{ $m->sku }}</option>
                        @endforeach
                    @endif
                </datalist>

                <div class="table-wrap custom-table-wrap" style="border:1px solid #e2e8f0;border-radius:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;background:#ffffff;box-shadow:inset 0 1px 3px rgba(0,0,0,0.02);">
                    <table class="data-table mb-0" id="customRequestTable" style="font-size:12px;width:100%;min-width:1020px;">
                        <thead>
                            <tr style="background:#f8fafc;font-size:10.5px;text-transform:uppercase;letter-spacing:0.4px;color:#475569;border-bottom:1px solid #cbd5e1;">
                                <th style="min-width:200px;padding:8px 8px;white-space:nowrap;">Nama Barang / Material <span class="text-danger">*</span></th>
                                <th style="width:110px;padding:8px 8px;white-space:nowrap;">Item <span class="text-danger">*</span></th>
                                <th style="min-width:130px;padding:8px 8px;white-space:nowrap;">Spesifikasi / Ukuran</th>
                                <th style="width:80px;text-align:center;padding:8px 6px;white-space:nowrap;">Satuan</th>
                                <th style="width:90px;text-align:center;padding:8px 6px;white-space:nowrap;">Jumlah <span class="text-danger">*</span></th>
                                <th style="width:125px;text-align:center;padding:8px 6px;white-space:nowrap;">Tgl Pakai</th>
                                <th style="min-width:140px;padding:8px 8px;white-space:nowrap;">Alokasi / Zona</th>
                                <th style="min-width:130px;padding:8px 8px;white-space:nowrap;">Catatan</th>
                                <th style="width:40px;text-align:center;padding:8px 4px;"></th>
                            </tr>
                        </thead>
                        <tbody id="customRequestBody">
                            <tr id="customRequestEmpty" style="{{ count($items) > 0 ? 'display:none;' : '' }}">
                                <td colspan="9" class="text-center text-muted" style="padding:24px 16px;font-size:12px;">
                                    <x-lucide-package-open style="font-size:24px;color:#cbd5e1;display:inline-block;margin-bottom:6px;" />
                                    <div>Belum ada item permintaan. Klik tombol <strong>"Tambah Baris Item"</strong> untuk memasukkan barang.</div>
                                </td>
                            </tr>
                            @foreach($items as $cIdx => $item)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding:6px 6px;">
                                    <input type="hidden" name="custom_items[{{ $cIdx }}][material_id]" class="custom-material-id" value="{{ old("custom_items.{$cIdx}.material_id", $item->material_id) }}">
                                    <input type="text" name="custom_items[{{ $cIdx }}][name]" list="masterMaterialsList" class="form-control" value="{{ old("custom_items.{$cIdx}.name", $item->material ? $item->material->name : $item->custom_item_name) }}" placeholder="Ketik / pilih nama material..." required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;" onchange="onItemNameChange(this)">
                                </td>
                                <td style="padding:6px 6px;">
                                    @php $curItemType = old("custom_items.{$cIdx}.item_type", $item->item_type ?? ($item->isTool() ? 'tool' : 'material')); @endphp
                                    <select name="custom_items[{{ $cIdx }}][item_type]" class="form-control custom-item-type-select" onchange="onCustomItemTypeChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;font-weight:600;">
                                        <option value="material" {{ $curItemType === 'material' ? 'selected' : '' }}>Material</option>
                                        <option value="tool" {{ $curItemType === 'tool' ? 'selected' : '' }}>Alat</option>
                                    </select>
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][spec]" class="form-control" value="{{ old("custom_items.{$cIdx}.spec", $item->specification ?: ($item->material?->size ?? '')) }}" placeholder="mis. fc'=25 MPa, 10mm" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][unit]" class="form-control" value="{{ old("custom_items.{$cIdx}.unit", $item->displayUnit()) }}" placeholder="pcs" style="text-align:center;font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 4px;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="number" name="custom_items[{{ $cIdx }}][qty]" class="form-control qty-input custom-qty-input" min="0.01" step="0.01" value="{{ old("custom_items.{$cIdx}.qty", (float) $item->qty_requested) }}" placeholder="0" required style="text-align:center;height:32px;font-size:12px;width:100%;border-radius:6px;padding:4px 4px;" oninput="calculateTotal()">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="date" name="custom_items[{{ $cIdx }}][date]" class="form-control" value="{{ old("custom_items.{$cIdx}.date", $item->usage_date ? \Carbon\Carbon::parse($item->usage_date)->format('Y-m-d') : '') }}" style="font-size:11px;height:32px;border-radius:6px;padding:2px 4px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][location]" class="form-control" value="{{ old("custom_items.{$cIdx}.location", $item->usage_location) }}" placeholder="mis. Lantai 2 / Cor TC" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][notes]" class="form-control" value="{{ old("custom_items.{$cIdx}.notes", $item->notes) }}" placeholder="Catatan..." style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                </td>
                                <td style="text-align:center;padding:6px 4px;">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); checkCustomEmpty(); calculateTotal();" style="width:28px;height:28px;padding:0;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                                        <x-lucide-trash-2 style="font-size:10px;" />
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- STICKY BOTTOM ACTION BAR --}}
        <div class="mr-sticky-bar">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <x-lucide-truck class="text-primary" style="font-size:18px;" />
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Ringkasan Perubahan</div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">
                            <span id="bottomTotalBadge">{{ $materialRequest->items->count() }}</span> Total Item Diminta
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('material-requests.show', $materialRequest) }}" class="btn btn-light border" style="padding:8px 18px;font-size:12.5px;font-weight:600;border-radius:8px;color:#475569;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="padding:9px 24px;font-size:13px;font-weight:700;border-radius:8px;box-shadow:0 2px 8px rgba(37,99,235,0.3);display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-save /> Simpan Perubahan PO Lapangan
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        const categoryGroups = @json($existingGroups ?? []);

        function initCustomTypeOptions(tr, preserveVal = true) {
            const catSelect = tr.querySelector('.custom-item-cat-select');
            const typeWrap = tr.querySelector('.custom-type-group-wrap');
            if (!catSelect || !typeWrap) return;

            const selectWrap = typeWrap.querySelector('.type-select-wrap');
            const selectEl = typeWrap.querySelector('.custom-type-select');
            const manualWrap = typeWrap.querySelector('.type-manual-wrap');
            const manualInput = typeWrap.querySelector('.custom-type-manual-input');
            const hiddenInput = typeWrap.querySelector('.custom-type-hidden');

            const currentVal = (preserveVal ? (hiddenInput.value || '') : '').trim();
            const catId = catSelect.value;
            const groups = (catId && categoryGroups[catId]) ? categoryGroups[catId] : [];

            selectEl.innerHTML = '<option value="">— Pilih Kelompok —</option>';
            let found = false;
            groups.forEach(function(g) {
                const opt = document.createElement('option');
                opt.value = g;
                opt.textContent = g;
                if (currentVal && currentVal.toLowerCase() === g.toLowerCase()) {
                    opt.selected = true;
                    found = true;
                }
                selectEl.appendChild(opt);
            });

            const newOpt = document.createElement('option');
            newOpt.value = '__new__';
            newOpt.textContent = '✚ Ketik Kelompok Baru...';
            selectEl.appendChild(newOpt);

            if (currentVal && !found) {
                selectWrap.style.display = 'none';
                manualWrap.style.display = 'flex';
                manualInput.value = currentVal;
                hiddenInput.value = currentVal;
            } else if (found) {
                manualWrap.style.display = 'none';
                selectWrap.style.display = 'block';
                selectEl.value = currentVal;
                hiddenInput.value = currentVal;
            } else {
                manualWrap.style.display = 'none';
                selectWrap.style.display = 'block';
                selectEl.value = '';
                hiddenInput.value = '';
            }
        }

        function onCustomCategoryChange(catSelect) {
            const tr = catSelect.closest('tr');
            initCustomTypeOptions(tr, false);
        }

        function onCustomTypeSelectChange(selectEl) {
            const typeWrap = selectEl.closest('.custom-type-group-wrap');
            if (!typeWrap) return;
            const hiddenInput = typeWrap.querySelector('.custom-type-hidden');
            if (selectEl.value === '__new__') {
                toggleCustomTypeMode(selectEl, true);
            } else {
                hiddenInput.value = selectEl.value;
            }
        }

        function toggleCustomTypeMode(elem, isManual) {
            const typeWrap = elem.closest('.custom-type-group-wrap');
            if (!typeWrap) return;
            const selectWrap = typeWrap.querySelector('.type-select-wrap');
            const selectEl = typeWrap.querySelector('.custom-type-select');
            const manualWrap = typeWrap.querySelector('.type-manual-wrap');
            const manualInput = typeWrap.querySelector('.custom-type-manual-input');
            const hiddenInput = typeWrap.querySelector('.custom-type-hidden');

            if (isManual) {
                selectWrap.style.display = 'none';
                manualWrap.style.display = 'flex';
                manualInput.value = (hiddenInput.value && hiddenInput.value !== '__new__') ? hiddenInput.value : '';
                manualInput.focus();
                hiddenInput.value = manualInput.value;
            } else {
                manualWrap.style.display = 'none';
                selectWrap.style.display = 'block';
                selectEl.value = '';
                hiddenInput.value = '';
            }
        }

        function onCustomTypeManualInput(manualInput) {
            const typeWrap = manualInput.closest('.custom-type-group-wrap');
            if (!typeWrap) return;
            const hiddenInput = typeWrap.querySelector('.custom-type-hidden');
            hiddenInput.value = manualInput.value.trim();
        }

        function onItemNameChange(input) {
            const row = input.closest('tr');
            if (!row) return;
            const nameVal = input.value.trim().toLowerCase();
            const datalist = document.getElementById('masterMaterialsList');
            const matIdInput = row.querySelector('.custom-material-id');
            const unitInput = row.querySelector('input[name*="[unit]"]');
            const specInput = row.querySelector('input[name*="[spec]"]');
            const typeSelect = row.querySelector('.custom-item-type-select');

            if (!nameVal) {
                if (matIdInput) matIdInput.value = '';
                return;
            }

            let matchedOption = null;
            if (datalist) {
                const options = datalist.querySelectorAll('option');
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value.trim().toLowerCase() === nameVal) {
                        matchedOption = options[i];
                        break;
                    }
                }
            }

            if (matchedOption) {
                const matId = matchedOption.getAttribute('data-id');
                const unit = matchedOption.getAttribute('data-unit');
                const spec = matchedOption.getAttribute('data-spec');

                if (matIdInput) matIdInput.value = matId || '';
                if (unitInput && (!unitInput.value || unitInput.value === 'pcs' || unitInput.value === 'unit')) {
                    unitInput.value = unit || 'pcs';
                }
                if (specInput && !specInput.value && spec) {
                    specInput.value = spec;
                }
                if (typeSelect) {
                    typeSelect.value = 'material';
                }
            } else {
                if (matIdInput) matIdInput.value = '';
            }
        }

        let customIdx = {{ count($items) + 1 }};

        const materialCategoryOptions = `@foreach($materialCategories as $cat)<option value="{{ $cat->id }}">{{ addslashes($cat->name) }}</option>@endforeach`;
        const toolCategoryOptions = `@foreach($toolCategories as $cat)<option value="{{ $cat->id }}">{{ addslashes($cat->name) }}</option>@endforeach`;

        function onCustomItemTypeChange(selectEl) {
            const row = selectEl.closest('tr');
            if (!row) return;
            const itemType = selectEl.value; // 'material' or 'tool'
            const catSelect = row.querySelector('.custom-item-cat-select');
            const unitInput = row.querySelector('input[name*="[unit]"]');
            const qtyInput = row.querySelector('.qty-input');

            if (catSelect) {
                if (itemType === 'tool') {
                    catSelect.innerHTML = `<option value="">Alat Khusus (Otomatis)</option>` + toolCategoryOptions;
                } else {
                    catSelect.innerHTML = `<option value="">Material Khusus (Otomatis)</option>` + materialCategoryOptions;
                }
                if (typeof onCustomCategoryChange === 'function') {
                    onCustomCategoryChange(catSelect);
                }
            }

            if (unitInput) {
                if (itemType === 'tool' && (unitInput.value === 'pcs' || !unitInput.value)) {
                    unitInput.value = 'unit';
                } else if (itemType === 'material' && unitInput.value === 'unit') {
                    unitInput.value = 'pcs';
                }
            }

            if (qtyInput) {
                if (itemType === 'tool') {
                    qtyInput.step = '1';
                    qtyInput.min = '1';
                    if (qtyInput.value && parseFloat(qtyInput.value) > 0) {
                        qtyInput.value = Math.round(parseFloat(qtyInput.value));
                    }
                } else {
                    qtyInput.step = '0.01';
                    qtyInput.min = '0.01';
                }
            }
        }

        function addCustomRequestRow() {
            const tbody = document.getElementById('customRequestBody');
            const empty = document.getElementById('customRequestEmpty');
            if (empty) empty.style.display = 'none';
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';

            tr.innerHTML = `
                <td style="padding:6px 6px;">
                    <input type="hidden" name="custom_items[${customIdx}][material_id]" class="custom-material-id" value="">
                    <input type="text" name="custom_items[${customIdx}][name]" list="masterMaterialsList" class="form-control" placeholder="Ketik / pilih nama material..." required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;" onchange="onItemNameChange(this)">
                </td>
                <td style="padding:6px 6px;">
                    <select name="custom_items[${customIdx}][item_type]" class="form-control custom-item-type-select" onchange="onCustomItemTypeChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;font-weight:600;">
                        <option value="material" selected>Material</option>
                        <option value="tool">Alat</option>
                    </select>
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][spec]" class="form-control" placeholder="mis. fc'=25 MPa, 10mm" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][unit]" class="form-control" placeholder="pcs" value="pcs" style="text-align:center;font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 4px;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="number" name="custom_items[${customIdx}][qty]" class="form-control qty-input custom-qty-input" min="0.01" step="0.01" placeholder="0" required style="text-align:center;height:32px;font-size:12px;width:100%;border-radius:6px;padding:4px 4px;" oninput="calculateTotal()">
                </td>
                <td style="padding:6px 6px;">
                    <input type="date" name="custom_items[${customIdx}][date]" class="form-control" style="font-size:11px;height:32px;border-radius:6px;padding:2px 4px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][location]" class="form-control" placeholder="mis. Lantai 2 / Cor TC" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][notes]" class="form-control" placeholder="Catatan..." style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="text-align:center;padding:6px 4px;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); checkCustomEmpty(); calculateTotal();" style="width:28px;height:28px;padding:0;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                        <x-lucide-trash-2 style="font-size:10px;" />
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            customIdx++;
            calculateTotal();
        }

        function checkCustomEmpty() {
            const tbody = document.getElementById('customRequestBody');
            const empty = document.getElementById('customRequestEmpty');
            if (tbody && empty) {
                const rows = tbody.querySelectorAll('tr:not(#customRequestEmpty)');
                empty.style.display = rows.length === 0 ? '' : 'none';
            }
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('#customRequestBody .qty-input').forEach(input => {
                if (parseFloat(input.value) > 0) total++;
            });
            const bottomBadge = document.getElementById('bottomTotalBadge');
            if (bottomBadge) bottomBadge.textContent = total;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#customRequestBody tr:not(#customRequestEmpty)').forEach(function(tr) {
                initCustomTypeOptions(tr, true);
            });
            calculateTotal();

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const rows = document.querySelectorAll('#customRequestBody tr:not(#customRequestEmpty)');
                    let hasItem = false;
                    rows.forEach(function(row) {
                        const name = row.querySelector('input[name*="[name]"]')?.value.trim();
                        const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
                        if (name && qty > 0) {
                            hasItem = true;
                        }
                    });
                    if (!hasItem) {
                        e.preventDefault();
                        alert('Silakan masukkan minimal 1 barang yang diminta dengan kuantitas lebih dari 0.');
                        return false;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
