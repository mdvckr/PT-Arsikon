<x-app-layout>
    <x-slot name="title">Buat PO Lapangan</x-slot>

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

        .qty-input.qty-warning {
            border-color: #f59e0b !important;
            background-color: #fffbeb !important;
            color: #b45309 !important;
        }

        .qty-input.qty-exceeds {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
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

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-3" style="font-size:12.5px;">
        <a href="{{ route('material-requests.index') }}" style="color:#64748b;text-decoration:none;">PO Lapangan</a>
        <span class="breadcrumb-sep" style="margin:0 6px;color:#94a3b8;"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span style="font-weight:600;color:#0f172a;">Buat PO Lapangan Baru</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:10px;padding:12px 16px;font-size:12.5px;">
        <div style="font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-triangle-exclamation text-danger"></i> Terdapat kesalahan pada formulir PO Lapangan:
        </div>
        <ul class="mb-0" style="padding-left:20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="materialRequestForm" method="POST" action="{{ route('material-requests.store') }}">
        @csrf

        {{-- SECTION 1: Informasi Dokumen & Proyek --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <span>1. Dokumen &amp; Tujuan Permintaan</span>
                </div>
                <div class="text-muted" style="font-size:11.5px;">
                    Lengkapi parameter dokumen PO Lapangan sebelum memilih material
                </div>
            </div>

            <div class="mr-doc-grid">
                {{-- Proyek Pemohon --}}
                <div class="mr-doc-field">
                    <label for="warehouse_id">
                        Nama Proyek / Gudang Pemohon <span class="text-danger">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required onchange="syncWarehouseOptions()">
                        <option value="">Pilih Proyek Pemohon</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id', $warehouseId) == $wh->id ? 'selected' : '' }}>
                            {{ $wh->project ? $wh->project->name : $wh->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                    <div class="text-danger" style="font-size:11px;margin-top:2px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Sumber Material --}}
                <div class="mr-doc-field">
                    <label for="source_warehouse_id">
                        Kepada <span class="text-muted" style="font-weight:400;">(Sumber Material)</span>
                    </label>
                    <select name="source_warehouse_id" id="source_warehouse_id" class="form-control" onchange="onSourceWarehouseChange(this.value, this.options[this.selectedIndex].text); syncWarehouseOptions();">
                        <option value="" {{ (string)old('source_warehouse_id', $defaultSourceWarehouseId) === '' ? 'selected' : '' }}>— Pengadaan —</option>
                        @php $isCentralGroupStarted = false; $isProjectGroupStarted = false; @endphp
                        @foreach($sourceWarehouses as $swh)
                            @if($swh->is_central && !$isCentralGroupStarted)
                                <optgroup label="── Gudang Pusat ──">
                                @php $isCentralGroupStarted = true; @endphp
                            @elseif(!$swh->is_central && !$isProjectGroupStarted)
                                @if($isCentralGroupStarted) </optgroup> @endif
                                <optgroup label="── Gudang Proyek ──">
                                @php $isProjectGroupStarted = true; @endphp
                            @endif
                            <option value="{{ $swh->id }}" {{ (string)old('source_warehouse_id', $defaultSourceWarehouseId) === (string)$swh->id ? 'selected' : '' }}>
                                {{ $swh->name }}{{ $swh->is_central ? ' (Pusat)' : '' }}
                            </option>
                        @endforeach
                        @if($isProjectGroupStarted) </optgroup> @elseif($isCentralGroupStarted) </optgroup> @endif
                    </select>
                    <div id="warehouse-conflict-warning" style="display:none;font-size:11px;color:#dc2626;margin-top:3px;font-weight:600;">
                        <i class="fas fa-triangle-exclamation"></i> Sumber material tidak boleh sama dengan proyek pemohon!
                    </div>
                    @error('source_warehouse_id')
                    <div class="text-danger" style="font-size:11px;margin-top:2px;">{{ $message }}</div>
                    @enderror
                </div>


                {{-- Diajukan Sebagai --}}
                <div class="mr-doc-field">
                    <label for="requested_as">
                        Diajukan Sebagai
                    </label>
                    <select name="requested_as" id="requested_as" class="form-control">
                        <option value="Admin Proyek" {{ old('requested_as', 'Admin Proyek') === 'Admin Proyek' ? 'selected' : '' }}>Admin Proyek</option>
                        <option value="Site Manager" {{ old('requested_as') === 'Site Manager' ? 'selected' : '' }}>Site Manager</option>
                        <option value="Site Engineering" {{ old('requested_as') === 'Site Engineering' ? 'selected' : '' }}>Site Engineering</option>
                        <option value="Karyawan" {{ old('requested_as') === 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>
                </div>
            </div>

            {{-- Catatan Tambahan Pemohon & Alert Info Sumber --}}
            <div style="padding:0 18px 18px 18px;">
                <div class="mr-doc-field mb-3">
                    <label for="notes">Catatan Tambahan Permintaan (Opsional)</label>
                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Contoh: Kebutuhan mendesak untuk pengecoran lantai 3 zona B..." value="{{ old('notes') }}" style="height:36px;font-size:12px;">
                </div>

                <div class="alert alert-info mb-0" id="source-wh-info" style="font-size:12px;padding:10px 14px;border-radius:8px;line-height:1.4;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-warehouse text-primary"></i>
                        <span id="source-wh-text">
                            @if(empty($defaultSourceWarehouseId))
                                Ditujukan ke: <strong>Pengadaan / Beli ke Vendor</strong>. Kolom stok menampilkan referensi stok <strong>{{ $centralWarehouse?->name ?? 'Gudang Pusat' }}</strong>.
                            @else
                                Stok dari <strong>{{ $sourceWarehouses->firstWhere('id', $defaultSourceWarehouseId)?->name ?? 'Gudang Pusat' }}</strong>. Permintaan dikirim ke Proyek pemohon.
                            @endif
                        </span>
                    </div>
                    <div id="source-wh-loading" style="display:none;font-size:11.5px;color:#2563eb;font-weight:600;">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data stok terbaru...
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Daftar Material Katalog Master --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <span>2. Daftar Material Tersedia (Katalog Master)</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span id="totalItemBadge" class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:12px;padding:5px 12px;border-radius:14px;border:1px solid #bfdbfe;">
                        0 Item Diminta
                    </span>
                </div>
            </div>

            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <div style="flex:1;min-width:240px;position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                        <input type="text" id="materialSearch" class="form-control" placeholder="Cari nama material, kode SKU, spesifikasi..." oninput="filterMaterials()" style="padding-left:36px;height:38px;border-radius:8px;font-size:12.5px;border:1px solid #cbd5e1;">
                    </div>
                    <div style="width:240px;">
                        <select id="categoryFilter" class="form-control" onchange="filterMaterials()" style="height:38px;border-radius:8px;font-size:12.5px;border:1px solid #cbd5e1;font-weight:600;color:#334155;">
                            <option value="">Semua Kategori</option>
                            @foreach($materialCategories as $cat)
                            <option value="mcat-{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-wrap" style="max-height:550px;overflow-y:auto;overflow-x:auto;">
                <table class="data-table mr-item-table mb-0" id="materialsTable">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr>
                            <th style="min-width:220px;">Nama Material &amp; SKU</th>
                            <th style="text-align:center;width:80px;">Ukuran</th>
                            <th style="text-align:center;width:95px;" id="stock-col-header">Stok Sumber</th>
                            <th style="text-align:center;width:100px;">Jml Diminta</th>
                            <th style="width:130px;">Spesifikasi</th>
                            <th style="text-align:center;width:115px;">Tgl Pakai</th>
                            <th style="width:130px;">Alokasi / Zona</th>
                            <th style="min-width:140px;">Catatan Baris</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materialCategories as $category)
                        @php $groupKey = 'mcat-' . $category->id; @endphp
                        <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                            <td colspan="8" style="padding:8px 14px !important;">
                                <div class="flex items-center justify-between" style="width:100%;">
                                    <div class="flex items-center" style="gap:8px;">
                                        <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                        <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;">{{ $category->name }}</span>
                                        <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700;">{{ $category->materials->count() }} material</span>
                                    </div>
                                    <span class="text-muted" style="font-size:11px;">Klik untuk menutup/membuka kategori</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($category->materials as $mat)
                        @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                        <tr class="mat-row cat-rows {{ $groupKey }}" data-material-id="{{ $mat->id }}" data-stock="{{ $totalStock }}" style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px 8px 24px;">
                                <div class="fw-700" style="font-size:13px;color:#0f172a;">{{ $mat->name }}</div>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                    @if($mat->type)
                                     <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:10px;padding:1px 5px;border-radius:3px;font-weight:600;">{{ $mat->type }}</span>
                                    @endif
                                    <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10.5px;color:#64748b;">SKU: {{ $mat->sku }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($mat->size)
                                <span style="font-size:12px;color:#334155;font-weight:500;">{{ $mat->size }}</span>
                                @else
                                <span class="text-muted" style="font-size:11px;">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="badge stock-badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;padding:3px 7px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                    {{ number_format($totalStock, 0, ',', '.') }}
                                </span>
                                <div class="text-muted" style="font-size:10px;margin-top:1px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</div>
                            </td>
                            <td style="text-align:center;">
                                <input type="number"
                                       name="quantities[{{ $mat->id }}]"
                                       class="form-control qty-input"
                                       min="0" step="0.01"
                                       value="{{ old('quantities.'.$mat->id, 0) }}"
                                       oninput="validateQtyRow(this); calculateTotal(); markActiveRow(this);">
                            </td>
                            <td>
                                <input type="text"
                                       name="specs[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="mis. fc'=25 MPa"
                                       value="{{ old('specs.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td style="text-align:center;">
                                <input type="date"
                                       name="usage_dates[{{ $mat->id }}]"
                                       class="form-control"
                                       value="{{ old('usage_dates.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 6px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="usage_locations[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="mis. Kolom Lt 2"
                                       value="{{ old('usage_locations.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="item_notes[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="Catatan..."
                                       value="{{ old('item_notes.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                        </tr>
                        @endforeach
                        @empty
                        @endforelse

                        @if($uncategorizedMaterials->isNotEmpty())
                        <tr class="cat-toggle" data-group="mcat-uncategorized" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                            <td colspan="8" style="padding:8px 14px !important;">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;">Lainnya</span>
                                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700;">{{ $uncategorizedMaterials->count() }} material</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($uncategorizedMaterials as $mat)
                        @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                        <tr class="mat-row cat-rows mcat-uncategorized" data-material-id="{{ $mat->id }}" data-stock="{{ $totalStock }}" style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px 8px 24px;">
                                <div class="fw-700" style="font-size:13px;color:#0f172a;">{{ $mat->name }}</div>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                    @if($mat->type)
                                    <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:10px;padding:1px 5px;border-radius:3px;font-weight:600;">{{ $mat->type }}</span>
                                    @endif
                                    <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10.5px;color:#64748b;">SKU: {{ $mat->sku }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($mat->size)
                                <span style="font-size:12px;color:#334155;font-weight:500;">{{ $mat->size }}</span>
                                @else
                                <span class="text-muted" style="font-size:11px;">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="badge stock-badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;padding:3px 7px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                    {{ number_format($totalStock, 0, ',', '.') }}
                                </span>
                                <div class="text-muted" style="font-size:10px;margin-top:1px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</div>
                            </td>
                            <td style="text-align:center;">
                                <input type="number"
                                       name="quantities[{{ $mat->id }}]"
                                       class="form-control qty-input"
                                       min="0" step="0.01"
                                       value="{{ old('quantities.'.$mat->id, 0) }}"
                                       oninput="validateQtyRow(this); calculateTotal(); markActiveRow(this);">
                            </td>
                            <td>
                                <input type="text"
                                       name="specs[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="mis. fc'=25 MPa"
                                       value="{{ old('specs.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td style="text-align:center;">
                                <input type="date"
                                       name="usage_dates[{{ $mat->id }}]"
                                       class="form-control"
                                       value="{{ old('usage_dates.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 6px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="usage_locations[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="mis. Kolom Lt 2"
                                       value="{{ old('usage_locations.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="item_notes[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="Catatan..."
                                       value="{{ old('item_notes.'.$mat->id) }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION 3: Barang Tidak Ada di Pusat — Input Manual Proyek --}}
        <div class="form-section-card" style="border:1.5px dashed #fcd34d;background:#fffdfa;">
            <div class="form-section-header" style="background:#fffbeb;border-bottom:1px solid #fde68a;">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <span style="color:#92400e;">3. Barang Khusus / Input Manual Proyek</span>
                </div>
                <button type="button" class="btn btn-warning btn-sm" onclick="addCustomRequestRow()" style="padding:6px 14px;font-size:12px;border-radius:7px;font-weight:700;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                    <i class="fas fa-plus me-1"></i> Tambah Baris Manual
                </button>
            </div>

            <div style="padding:14px 18px;">
                <div class="flex justify-between items-center mb-2" style="flex-wrap:wrap;gap:8px;">
                    <div class="text-muted" style="font-size:12px;line-height:1.4;">
                        Gunakan tabel ini untuk barang yang <strong>belum terdaftar pada katalog Gudang Pusat</strong> namun sangat dibutuhkan di lapangan. Item ini dapat langsung diproses pengadaannya oleh Admin PO.
                    </div>
                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:600;font-size:10.5px;white-space:nowrap;padding:3px 8px;">
                        <i class="fas fa-arrows-left-right me-1"></i> Geser tabel jika layar terbatas
                    </span>
                </div>

                <div class="table-wrap custom-table-wrap" style="border:1px solid #fde68a;border-radius:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;background:#ffffff;box-shadow:inset 0 1px 3px rgba(0,0,0,0.02);">
                    <table class="data-table mb-0" id="customRequestTable" style="font-size:12px;width:100%;min-width:1020px;">
                        <thead>
                            <tr style="background:#fef3c7;font-size:10.5px;text-transform:uppercase;letter-spacing:0.4px;color:#92400e;">
                                <th style="min-width:160px;padding:8px 8px;white-space:nowrap;">Nama Barang (Manual) <span class="text-danger">*</span></th>
                                <th style="min-width:110px;padding:8px 8px;white-space:nowrap;">Spesifikasi</th>
                                <th style="min-width:140px;padding:8px 8px;white-space:nowrap;">Kategori</th>
                                <th style="min-width:140px;padding:8px 8px;white-space:nowrap;">Kelompok</th>
                                <th style="width:65px;text-align:center;padding:8px 6px;white-space:nowrap;">Satuan</th>
                                <th style="width:75px;text-align:center;padding:8px 6px;white-space:nowrap;">Jumlah <span class="text-danger">*</span></th>
                                <th style="width:120px;text-align:center;padding:8px 6px;white-space:nowrap;">Tgl Pakai</th>
                                <th style="min-width:130px;padding:8px 8px;white-space:nowrap;">Alokasi / Zona</th>
                                <th style="min-width:120px;padding:8px 8px;white-space:nowrap;">Catatan</th>
                                <th style="width:40px;text-align:center;padding:8px 4px;"></th>
                            </tr>
                        </thead>
                        <tbody id="customRequestBody">
                            <tr id="customRequestEmpty" style="{{ old('custom_items') && count(old('custom_items')) > 0 ? 'display:none;' : '' }}">
                                <td colspan="10" class="text-center text-muted" style="padding:16px;font-size:12px;">
                                    <i class="fas fa-box-open" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                                    Belum ada barang manual ditambahkan. Klik tombol <strong>"Tambah Baris Manual"</strong> jika diperlukan.
                                </td>
                            </tr>
                            @if(old('custom_items'))
                                @foreach(old('custom_items') as $cIdx => $cItem)
                                <tr style="border-bottom: 1px solid #fef3c7;">
                                    <td style="padding:6px 6px;">
                                        <input type="text" name="custom_items[{{ $cIdx }}][name]" class="form-control" value="{{ $cItem['name'] ?? '' }}" placeholder="Contoh: Triplek 9mm 1.2x2.4" required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="text" name="custom_items[{{ $cIdx }}][spec]" class="form-control" value="{{ $cItem['spec'] ?? '' }}" placeholder="mis. fc'=25 MPa" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <select name="custom_items[{{ $cIdx }}][category_id]" class="form-control custom-item-cat-select" onchange="onCustomCategoryChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;">
                                            <option value="">Material Khusus (Otomatis)</option>
                                            @foreach($materialCategories as $cat)
                                                <option value="{{ $cat->id }}" {{ ($cItem['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <div class="custom-type-group-wrap" data-index="{{ $cIdx }}" style="width:100%;">
                                            <div class="type-select-wrap">
                                                <select class="form-control custom-type-select" onchange="onCustomTypeSelectChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;">
                                                    <option value="">— Pilih Kelompok —</option>
                                                    <option value="__new__">✚ Ketik Kelompok Baru...</option>
                                                </select>
                                            </div>
                                            <div class="type-manual-wrap" style="display:none;align-items:center;gap:4px;">
                                                <input type="text" class="form-control custom-type-manual-input" placeholder="Ketik kelompok..." oninput="onCustomTypeManualInput(this)" style="font-size:11.5px;height:32px;border-radius:6px;padding:4px 6px;flex:1;min-width:0;">
                                                <button type="button" class="btn btn-light border" onclick="toggleCustomTypeMode(this, false)" title="Kembali ke dropdown pilihan" style="height:32px;padding:0 8px;font-size:11px;color:#64748b;border-radius:6px;flex-shrink:0;">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="custom_items[{{ $cIdx }}][type]" class="custom-type-hidden" value="{{ $cItem['type'] ?? '' }}">
                                        </div>
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="text" name="custom_items[{{ $cIdx }}][unit]" class="form-control" value="{{ $cItem['unit'] ?? 'pcs' }}" placeholder="pcs" style="text-align:center;font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 4px;">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="number" name="custom_items[{{ $cIdx }}][qty]" class="form-control qty-input" min="0.01" step="0.01" value="{{ $cItem['qty'] ?? 0 }}" placeholder="0" required style="text-align:center;height:32px;font-size:12px;width:100%;border-radius:6px;padding:4px 4px;" oninput="calculateTotal()">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="date" name="custom_items[{{ $cIdx }}][date]" class="form-control" value="{{ $cItem['date'] ?? '' }}" style="font-size:11px;height:32px;border-radius:6px;padding:2px 4px;width:100%;">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="text" name="custom_items[{{ $cIdx }}][location]" class="form-control" value="{{ $cItem['location'] ?? '' }}" placeholder="mis. Lantai 2 / Cor TC" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                    </td>
                                    <td style="padding:6px 6px;">
                                        <input type="text" name="custom_items[{{ $cIdx }}][notes]" class="form-control" value="{{ $cItem['notes'] ?? '' }}" placeholder="Catatan..." style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                    </td>
                                    <td style="text-align:center;padding:6px 4px;">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); checkCustomEmpty(); calculateTotal();" style="width:28px;height:28px;padding:0;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-trash" style="font-size:10px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- STICKY BOTTOM ACTION BAR --}}
        <div class="mr-sticky-bar">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-cart-flatbed text-primary" style="font-size:18px;"></i>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Ringkasan Permintaan</div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;">
                            <span id="bottomTotalBadge">0</span> Total Item Terpilih
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('material-requests.index') }}" class="btn btn-light border" style="padding:8px 18px;font-size:12.5px;font-weight:600;border-radius:8px;color:#475569;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="padding:9px 24px;font-size:13px;font-weight:700;border-radius:8px;box-shadow:0 2px 8px rgba(37,99,235,0.3);display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-paper-plane"></i> Ajukan PO Lapangan
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        // ============================================================
        // Sinkronisasi Dropdown: Cegah Pemilihan Gudang yang Sama
        // ============================================================
        function syncWarehouseOptions() {
            var whSelect = document.getElementById('warehouse_id');
            var srcSelect = document.getElementById('source_warehouse_id');
            var whVal = whSelect ? whSelect.value : '';
            var srcVal = srcSelect ? srcSelect.value : '';

            var conflictWarning = document.getElementById('warehouse-conflict-warning');
            if (whVal && srcVal && parseInt(whVal) === parseInt(srcVal)) {
                if (conflictWarning) conflictWarning.style.display = 'block';
                if (srcSelect) srcSelect.style.borderColor = '#ef4444';
                if (whSelect) whSelect.style.borderColor = '#ef4444';
            } else {
                if (conflictWarning) conflictWarning.style.display = 'none';
                if (srcSelect) srcSelect.style.borderColor = '';
                if (whSelect) whSelect.style.borderColor = '';
            }

            // Disable pilihan gudang pemohon di dropdown sumber
            if (srcSelect) {
                Array.from(srcSelect.options).forEach(function(opt) {
                    if (!opt.value) return;
                    if (whVal && parseInt(opt.value) === parseInt(whVal)) {
                        opt.disabled = true;
                        opt.style.color = '#94a3b8';
                    } else {
                        opt.disabled = false;
                        opt.style.color = '';
                    }
                });
            }

            // Disable pilihan sumber di dropdown pemohon
            if (whSelect) {
                Array.from(whSelect.options).forEach(function(opt) {
                    if (!opt.value) return;
                    if (srcVal && parseInt(opt.value) === parseInt(srcVal)) {
                        opt.disabled = true;
                        opt.style.color = '#94a3b8';
                    } else {
                        opt.disabled = false;
                        opt.style.color = '';
                    }
                });
            }
        }

        // ============================================================
        // Validasi Kuantitas Input vs Stok Gudang Sumber & Highlight Row
        // ============================================================
        function markActiveRow(input) {
            var row = input.closest('.mat-row');
            if (!row) return;
            var val = parseFloat(input.value) || 0;
            if (val > 0) {
                row.classList.add('row-active');
            } else {
                row.classList.remove('row-active');
            }
        }

        function validateQtyRow(input) {
            var val = parseFloat(input.value) || 0;
            if (val < 0) {
                val = 0;
                input.value = 0;
            }
            var row = input.closest('.mat-row');
            if (!row) return;
            var stock = parseFloat(row.getAttribute('data-stock')) || 0;

            input.classList.remove('qty-warning', 'qty-exceeds');
            input.removeAttribute('title');

            var srcSelect = document.getElementById('source_warehouse_id');
            var isPengadaan = !srcSelect || !srcSelect.value;

            // Peringatan stok hanya berlaku bila sumbernya adalah Gudang tertentu (bukan Pengadaan / Beli ke Vendor)
            if (!isPengadaan && val > 0) {
                if (stock <= 0) {
                    input.classList.add('qty-exceeds');
                    input.title = 'Perhatian: Stok material di gudang sumber saat ini kosong (0)';
                } else if (val > stock) {
                    input.classList.add('qty-warning');
                    input.title = 'Perhatian: Jumlah yang diminta (' + val + ') melebihi stok yang tersedia (' + stock + ')';
                }
            }
        }

        // ============================================================
        // AJAX: Update stok saat Sumber Gudang berubah
        // ============================================================
        var centralWhId = '{{ $centralWarehouse?->id }}';
        function onSourceWarehouseChange(warehouseId, warehouseName) {
            var targetWhId = warehouseId || centralWhId;
            var loadingEl = document.getElementById('source-wh-loading');
            var textEl = document.getElementById('source-wh-text');
            var colHeaderEl = document.getElementById('stock-col-header');

            if (!warehouseId) {
                if (textEl) {
                    textEl.innerHTML = 'Ditujukan ke: <strong>Pengadaan / Beli ke Vendor</strong>. Kolom stok menampilkan referensi stok <strong>{{ $centralWarehouse?->name ?? 'Gudang Pusat' }}</strong>.';
                }
                if (colHeaderEl) {
                    colHeaderEl.textContent = 'Stok Pusat';
                }
            } else {
                if (textEl) {
                    textEl.innerHTML = 'Stok dari <strong>' + warehouseName + '</strong>. Permintaan dikirim ke Proyek pemohon.';
                }
                if (colHeaderEl) {
                    colHeaderEl.textContent = 'Stok Gudang';
                }
            }

            if (!targetWhId) return;
            if (loadingEl) loadingEl.style.display = '';

            fetch('{{ route('material-requests.stock') }}?warehouse_id=' + targetWhId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(stocks) {
                // Update setiap badge stok di tabel & update data-stock attribute
                document.querySelectorAll('.mat-row').forEach(function(row) {
                    var matId = row.getAttribute('data-material-id');
                    if (!matId) return;
                    var stockBadge = row.querySelector('.stock-badge');
                    var qty = stocks[matId] !== undefined ? parseFloat(stocks[matId]) : 0;
                    row.setAttribute('data-stock', qty);
                    if (stockBadge) {
                        stockBadge.textContent = qty.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                        // Warna berdasarkan stok
                        if (qty <= 0) {
                            stockBadge.style.background = '#fef2f2';
                            stockBadge.style.color = '#b91c1c';
                        } else if (qty < 10) {
                            stockBadge.style.background = '#fffbeb';
                            stockBadge.style.color = '#b45309';
                        } else {
                            stockBadge.style.background = '#ecfdf5';
                            stockBadge.style.color = '#047857';
                        }
                    }
                    var input = row.querySelector('.qty-input');
                    if (input) validateQtyRow(input);
                });
                if (loadingEl) loadingEl.style.display = 'none';
            })
            .catch(function() {
                if (loadingEl) loadingEl.style.display = 'none';
            });
        }

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

        let customReqIndex = {{ count(old('custom_items', [])) }};
        function addCustomRequestRow() {
            const tbody = document.getElementById('customRequestBody');
            const empty = document.getElementById('customRequestEmpty');
            if (empty) empty.style.display = 'none';
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #fef3c7';

            let categoryOptions = `<option value="">Material Khusus (Otomatis)</option>`;
            @foreach($materialCategories as $cat)
                categoryOptions += `<option value="{{ $cat->id }}">{{ addslashes($cat->name) }}</option>`;
            @endforeach

            tr.innerHTML = `
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customReqIndex}][name]" class="form-control" placeholder="Contoh: Triplek 9mm 1.2x2.4" required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customReqIndex}][spec]" class="form-control" placeholder="mis. fc'=25 MPa" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <select name="custom_items[${customReqIndex}][category_id]" class="form-control custom-item-cat-select" onchange="onCustomCategoryChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;">
                        ${categoryOptions}
                    </select>
                </td>
                <td style="padding:6px 6px;">
                    <div class="custom-type-group-wrap" data-index="${customReqIndex}" style="width:100%;">
                        <div class="type-select-wrap">
                            <select class="form-control custom-type-select" onchange="onCustomTypeSelectChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;">
                                <option value="">— Pilih Kelompok —</option>
                                <option value="__new__">✚ Ketik Kelompok Baru...</option>
                            </select>
                        </div>
                        <div class="type-manual-wrap" style="display:none;align-items:center;gap:4px;">
                            <input type="text" class="form-control custom-type-manual-input" placeholder="Ketik kelompok..." oninput="onCustomTypeManualInput(this)" style="font-size:11.5px;height:32px;border-radius:6px;padding:4px 6px;flex:1;min-width:0;">
                            <button type="button" class="btn btn-light border" onclick="toggleCustomTypeMode(this, false)" title="Kembali ke dropdown pilihan" style="height:32px;padding:0 8px;font-size:11px;color:#64748b;border-radius:6px;flex-shrink:0;">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        <input type="hidden" name="custom_items[${customReqIndex}][type]" class="custom-type-hidden" value="">
                    </div>
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customReqIndex}][unit]" class="form-control" placeholder="pcs" value="pcs" style="text-align:center;font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 4px;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="number" name="custom_items[${customReqIndex}][qty]" class="form-control qty-input" min="0.01" step="0.01" placeholder="0" required style="text-align:center;height:32px;font-size:12px;width:100%;border-radius:6px;padding:4px 4px;" oninput="calculateTotal()">
                </td>
                <td style="padding:6px 6px;">
                    <input type="date" name="custom_items[${customReqIndex}][date]" class="form-control" style="font-size:11px;height:32px;border-radius:6px;padding:2px 4px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customReqIndex}][location]" class="form-control" placeholder="mis. Lantai 2 / Cor TC" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customReqIndex}][notes]" class="form-control" placeholder="Catatan..." style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="text-align:center;padding:6px 4px;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); checkCustomEmpty(); calculateTotal();" style="width:28px;height:28px;padding:0;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="fas fa-trash" style="font-size:10px;"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            initCustomTypeOptions(tr, false);
            customReqIndex++;
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
            var total = 0;
            document.querySelectorAll('.qty-input').forEach(function (input) {
                var val = parseFloat(input.value) || 0;
                if (val < 0) { val = 0; input.value = 0; }
                total += val;
            });
            var text = total.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2}) + ' Item Diminta';
            var totalBadge = document.getElementById('totalItemBadge');
            var bottomBadge = document.getElementById('bottomTotalBadge');
            if (totalBadge) totalBadge.innerText = text;
            if (bottomBadge) bottomBadge.innerText = total.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
        }

        function filterMaterials() {
            var keyword = document.getElementById('materialSearch').value.toLowerCase();
            var selectedCat = document.getElementById('categoryFilter') ? document.getElementById('categoryFilter').value : '';
            var toggleRows = document.querySelectorAll('.cat-toggle');
            var matRows    = document.querySelectorAll('#materialsTable tbody tr.mat-row');

            toggleRows.forEach(function(header) {
                var group = header.getAttribute('data-group');
                if (selectedCat && group !== selectedCat) {
                    header.style.display = 'none';
                } else {
                    header.style.display = '';
                }
            });

            matRows.forEach(function (row) {
                var text = row.innerText.toLowerCase();
                var matchesSearch = !keyword || text.indexOf(keyword) !== -1;
                var group = Array.from(row.classList).find(function(c) {
                    return c.startsWith('mcat-') && c !== 'cat-rows';
                });
                var matchesCat = !selectedCat || group === selectedCat;

                if (matchesSearch && matchesCat) {
                    row.style.display = '';
                    if (group) {
                        var header = document.querySelector('.cat-toggle[data-group="' + group + '"]');
                        if (header) header.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function initFormValidation() {
            var form = document.getElementById('materialRequestForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                var whSelect = document.getElementById('warehouse_id');
                var srcSelect = document.getElementById('source_warehouse_id');
                var whVal = whSelect ? whSelect.value : '';
                var srcVal = srcSelect ? srcSelect.value : '';

                if (!whVal) {
                    e.preventDefault();
                    alert('Silakan pilih Proyek Pemohon terlebih dahulu.');
                    if (whSelect) whSelect.focus();
                    return false;
                }

                if (whVal && srcVal && parseInt(whVal) === parseInt(srcVal)) {
                    e.preventDefault();
                    alert('Validasi Gagal: Sumber material tidak boleh sama dengan proyek pemohon!');
                    var conflictWarning = document.getElementById('warehouse-conflict-warning');
                    if (conflictWarning) conflictWarning.style.display = 'block';
                    if (srcSelect) srcSelect.focus();
                    return false;
                }

                // Cek apakah ada minimal 1 item diminta (master atau custom)
                var totalMaster = 0;
                document.querySelectorAll('#materialsTable .qty-input').forEach(function(input) {
                    totalMaster += parseFloat(input.value) || 0;
                });

                var customRows = document.querySelectorAll('#customRequestBody tr:not(#customRequestEmpty)');
                var totalCustom = 0;
                customRows.forEach(function(row) {
                    var qtyInput = row.querySelector('.qty-input');
                    var nameInput = row.querySelector('input[name*="[name]"]');
                    if (qtyInput && nameInput && nameInput.value.trim() !== '') {
                        totalCustom += parseFloat(qtyInput.value) || 0;
                    }
                });

                if (totalMaster <= 0 && totalCustom <= 0) {
                    e.preventDefault();
                    alert('Silakan masukkan minimal 1 barang yang diminta (isi kuantitas pada daftar material atau tambahkan barang manual).');
                    return false;
                }
            });
        }

        (function () {
            function initAccordion() {
                document.querySelectorAll('.cat-toggle').forEach(function (row) {
                    var newRow = row.cloneNode(true);
                    row.parentNode.replaceChild(newRow, row);
                    var group = newRow.getAttribute('data-group');
                    newRow.addEventListener('click', function () {
                        var collapsed = newRow.classList.toggle('collapsed');
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            r.style.display = collapsed ? 'none' : '';
                        });
                        var chev = newRow.querySelector('.cat-chev');
                        if (chev) chev.style.transform = collapsed ? 'rotate(-90deg)' : '';
                    });
                });
            }

            function setupInitial() {
                initAccordion();
                syncWarehouseOptions();
                initFormValidation();
                calculateTotal();
                // Validasi baris kuantitas awal jika ada input dari old()
                document.querySelectorAll('#materialsTable .qty-input').forEach(function(input) {
                    if (parseFloat(input.value) > 0) {
                        validateQtyRow(input);
                        markActiveRow(input);
                    }
                });
                // Inisialisasi baris custom item kelompok
                document.querySelectorAll('#customRequestBody tr:not(#customRequestEmpty)').forEach(function(tr) {
                    initCustomTypeOptions(tr, true);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupInitial);
            } else {
                setupInitial();
            }
        })();
    </script>
    @endpush
</x-app-layout>
