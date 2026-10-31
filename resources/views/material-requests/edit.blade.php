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
        $existingQtyMap = $materialRequest->items->whereNotNull('material_id')->pluck('qty_requested', 'material_id');
        $existingSpecs = $materialRequest->items->whereNotNull('material_id')->pluck('specification', 'material_id');
        $existingLocations = $materialRequest->items->whereNotNull('material_id')->pluck('usage_location', 'material_id');
        $existingNotes = $materialRequest->items->whereNotNull('material_id')->pluck('notes', 'material_id');
        $existingDates = $materialRequest->items->whereNotNull('material_id')->mapWithKeys(fn($it) => [$it->material_id => $it->usage_date ? \Carbon\Carbon::parse($it->usage_date)->format('Y-m-d') : '']);
        $customItems = $materialRequest->items->whereNull('material_id')->values();
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

                {{-- Sumber Material --}}
                <div class="mr-doc-field">
                    <label for="source_warehouse_id">
                        Kepada <span class="text-muted" style="font-weight:400;">(Sumber Material)</span>
                    </label>
                    <select name="source_warehouse_id" id="source_warehouse_id" class="form-control">
                        <option value="" {{ (string)old('source_warehouse_id', $sourceWarehouseId) === '' ? 'selected' : '' }}>&mdash; Pengadaan / Beli ke Vendor &mdash;</option>
                        @foreach($sourceWarehouses as $swh)
                            <option value="{{ $swh->id }}" {{ (string)old('source_warehouse_id', $sourceWarehouseId) === (string)$swh->id ? 'selected' : '' }}>
                                {{ $swh->name }}{{ $swh->is_central ? ' (Pusat)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- Diajukan Sebagai --}}
                <div class="mr-doc-field">
                    <label for="requested_as">
                        Diajukan Sebagai
                    </label>
                    <select name="requested_as" id="requested_as" class="form-control">
                        @php $currRole = old('requested_as', $materialRequest->requested_as ?: 'Admin Proyek'); @endphp
                        <option value="Admin Proyek" {{ $currRole === 'Admin Proyek' ? 'selected' : '' }}>Admin Proyek</option>
                        <option value="Site Manager" {{ $currRole === 'Site Manager' ? 'selected' : '' }}>Site Manager</option>
                        <option value="Site Engineering" {{ $currRole === 'Site Engineering' ? 'selected' : '' }}>Site Engineering</option>
                        <option value="Karyawan" {{ $currRole === 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
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

        {{-- SECTION 2: Item Manual / Custom --}}
        <div class="form-section-card" style="border:1.5px dashed #fcd34d;background:#fffdfa;">
            <div class="form-section-header" style="background:#fffbeb;border-bottom:1px solid #fde68a;">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <x-lucide-square-pen />
                    </div>
                    <span style="color:#92400e;">2. Item Khusus / Input Manual Proyek</span>
                </div>
                <button type="button" class="btn btn-warning btn-sm" onclick="addCustomRequestRow()" style="padding:6px 14px;font-size:12px;border-radius:7px;font-weight:700;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                    <x-lucide-plus class="me-1" /> Tambah Baris Manual
                </button>
            </div>

            <div style="padding:14px 18px;">
                <div class="flex justify-between items-center mb-2" style="flex-wrap:wrap;gap:8px;">
                    <div class="text-muted" style="font-size:12px;line-height:1.4;">
                        Gunakan tabel ini untuk barang yang <strong>belum terdaftar pada katalog Gudang Pusat</strong> namun sangat dibutuhkan di lapangan.
                    </div>
                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:600;font-size:10.5px;white-space:nowrap;padding:3px 8px;">
                        <x-lucide-arrow-left-right class="me-1" /> Geser tabel jika layar terbatas
                    </span>
                </div>

                <div class="table-wrap custom-table-wrap" style="border:1px solid #fde68a;border-radius:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;background:#ffffff;box-shadow:inset 0 1px 3px rgba(0,0,0,0.02);">
                    <table class="data-table mb-0" id="customRequestTable" style="font-size:12px;width:100%;min-width:1020px;">
                        <thead>
                            <tr style="background:#fef3c7;font-size:10.5px;text-transform:uppercase;letter-spacing:0.4px;color:#92400e;">
                                <th style="min-width:160px;padding:8px 8px;white-space:nowrap;">Nama Barang (Manual) <span class="text-danger">*</span></th>
                                <th style="width:105px;padding:8px 8px;white-space:nowrap;">Item <span class="text-danger">*</span></th>
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
                            <tr id="customRequestEmpty" style="{{ count($customItems) > 0 ? 'display:none;' : '' }}">
                                <td colspan="11" class="text-center text-muted" style="padding:16px;font-size:12px;">
                                    <x-lucide-package-open style="font-size:20px;color:#cbd5e1;display:inline-block;margin-bottom:4px;" />
                                    Belum ada barang manual ditambahkan. Klik tombol <strong>"Tambah Baris Manual"</strong> jika diperlukan.
                                </td>
                            </tr>
                            @foreach($customItems as $cIdx => $cItem)
                            <tr style="border-bottom: 1px solid #fef3c7;">
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][name]" class="form-control" value="{{ old("custom_items.{$cIdx}.name", $cItem->custom_item_name) }}" placeholder="Contoh: Triplek 9mm 1.2x2.4" required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;">
                                </td>
                                <td style="padding:6px 6px;">
                                    @php $curItemType = old("custom_items.{$cIdx}.item_type", $cItem->item_type ?? 'material'); @endphp
                                    <select name="custom_items[{{ $cIdx }}][item_type]" class="form-control custom-item-type-select" onchange="onCustomItemTypeChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;font-weight:600;">
                                        <option value="material" {{ $curItemType === 'material' ? 'selected' : '' }}>Material</option>
                                        <option value="tool" {{ $curItemType === 'tool' ? 'selected' : '' }}>Alat</option>
                                    </select>
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][spec]" class="form-control" value="{{ old("custom_items.{$cIdx}.spec", $cItem->specification) }}" placeholder="mis. fc'=25 MPa" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <select name="custom_items[{{ $cIdx }}][category_id]" class="form-control custom-item-cat-select" onchange="onCustomCategoryChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;">
                                        @if($curItemType === 'tool')
                                            <option value="">Alat Khusus (Otomatis)</option>
                                            @foreach($toolCategories as $cat)
                                                <option value="{{ $cat->id }}" {{ old("custom_items.{$cIdx}.category_id", $cItem->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="">Material Khusus (Otomatis)</option>
                                            @foreach($materialCategories as $cat)
                                                <option value="{{ $cat->id }}" {{ old("custom_items.{$cIdx}.category_id", $cItem->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        @endif
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
                                                <x-lucide-list />
                                            </button>
                                        </div>
                                        <input type="hidden" name="custom_items[{{ $cIdx }}][type]" class="custom-type-hidden" value="{{ old("custom_items.{$cIdx}.type", $cItem->custom_item_type) }}">
                                    </div>
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][unit]" class="form-control" value="{{ old("custom_items.{$cIdx}.unit", $cItem->custom_item_unit ?: 'unit') }}" placeholder="pcs" style="text-align:center;font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 4px;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="number" name="custom_items[{{ $cIdx }}][qty]" class="form-control qty-input custom-qty-input" min="0.01" step="0.01" value="{{ old("custom_items.{$cIdx}.qty", (float) $cItem->qty_requested) }}" placeholder="0" required style="text-align:center;height:32px;font-size:12px;width:100%;border-radius:6px;padding:4px 4px;" oninput="calculateTotal()">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="date" name="custom_items[{{ $cIdx }}][date]" class="form-control" value="{{ old("custom_items.{$cIdx}.date", $cItem->usage_date ? \Carbon\Carbon::parse($cItem->usage_date)->format('Y-m-d') : '') }}" style="font-size:11px;height:32px;border-radius:6px;padding:2px 4px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][location]" class="form-control" value="{{ old("custom_items.{$cIdx}.location", $cItem->usage_location) }}" placeholder="mis. Lantai 2 / Cor TC" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                                </td>
                                <td style="padding:6px 6px;">
                                    <input type="text" name="custom_items[{{ $cIdx }}][notes]" class="form-control" value="{{ old("custom_items.{$cIdx}.notes", $cItem->notes) }}" placeholder="Catatan..." style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
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

        {{-- SECTION 3: Daftar Material Katalog Master --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                        <x-lucide-boxes />
                    </div>
                    <span>3. Rincian Material Katalog Master</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span id="totalItemBadge" class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:12px;padding:5px 12px;border-radius:14px;border:1px solid #bfdbfe;">
                        {{ $materialRequest->items->count() }} Item Diminta
                    </span>
                </div>
            </div>

            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;background:#ffffff;">
                <div style="position:relative;max-width:400px;">
                    <x-lucide-search style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;" />
                    <input type="text" id="materialSearch" class="form-control" placeholder="Cari nama material, kode, atau spesifikasi..." style="padding-left:36px;height:38px;font-size:12.5px;border-radius:8px;border:1px solid #cbd5e1;" oninput="filterMaterials()">
                </div>
            </div>

            <div class="table-wrap" style="max-height:550px;overflow-y:auto;overflow-x:auto;">
                <table class="data-table mr-item-table mb-0" id="materialTable">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr>
                            <th style="min-width:220px;">Material &amp; SKU</th>
                            <th style="text-align:center;width:80px;">Ukuran</th>
                            <th style="text-align:center;width:90px;">Stok</th>
                            <th style="text-align:center;width:100px;">Jml Order</th>
                            <th style="width:130px;">Spesifikasi</th>
                            <th style="text-align:center;width:115px;">Tgl Pakai</th>
                            <th style="width:130px;">Alokasi / Zona</th>
                            <th style="min-width:140px;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialCategories as $category)
                        @php $groupKey = 'mcat-' . $category->id; @endphp
                        <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                            <td colspan="8" style="padding:8px 14px !important;">
                                <div class="flex items-center" style="gap:8px;">
                                    <x-lucide-chevron-down class="cat-chev" style="font-size:10px;color:#64748b;" />
                                    <span class="fw-700" style="font-size:12px;text-transform:uppercase;color:#0f172a;">{{ $category->name }}</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($category->materials as $mat)
                        @php
                            $totalStock = $mat->inventories->sum('quantity');
                            $existingVal = $existingQtyMap[$mat->id] ?? 0;
                        @endphp
                        <tr class="mat-row cat-rows {{ $groupKey }} {{ $existingVal > 0 ? 'row-active' : '' }}" data-material-id="{{ $mat->id }}" style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px 8px 24px;">
                                <div class="fw-700" style="font-size:13px;color:#0f172a;">{{ $mat->name }}</div>
                                <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10.5px;color:#64748b;">SKU: {{ $mat->sku }}</span>
                            </td>
                            <td style="text-align:center;font-size:12px;color:#334155;">{{ $mat->size ?: '-' }}</td>
                            <td style="text-align:center;">
                                <span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;padding:3px 7px;border-radius:4px;">
                                    {{ number_format($totalStock, 0, ',', '.') }}
                                </span>
                                <div class="text-muted" style="font-size:10px;">{{ $mat->unit?->abbreviation ?? 'unit' }}</div>
                            </td>
                            <td style="text-align:center;">
                                <input type="number"
                                       name="quantities[{{ $mat->id }}]"
                                       class="form-control qty-input"
                                       min="0" step="0.01"
                                       value="{{ old('quantities.'.$mat->id, $existingVal) }}"
                                       oninput="calculateTotal(); markActiveRow(this);">
                            </td>
                            <td>
                                <input type="text"
                                       name="specs[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="Spesifikasi"
                                       value="{{ old('specs.'.$mat->id, $existingSpecs[$mat->id] ?? '') }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td style="text-align:center;">
                                <input type="date"
                                       name="usage_dates[{{ $mat->id }}]"
                                       class="form-control"
                                       value="{{ old('usage_dates.'.$mat->id, $existingDates[$mat->id] ?? '') }}"
                                       style="height:32px;font-size:11.5px;padding:3px 6px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="usage_locations[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="Alokasi / Zona"
                                       value="{{ old('usage_locations.'.$mat->id, $existingLocations[$mat->id] ?? '') }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                            <td>
                                <input type="text"
                                       name="item_notes[{{ $mat->id }}]"
                                       class="form-control"
                                       placeholder="Catatan..."
                                       value="{{ old('item_notes.'.$mat->id, $existingNotes[$mat->id] ?? '') }}"
                                       style="height:32px;font-size:11.5px;padding:3px 8px;border-radius:6px;">
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
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

        let customIdx = {{ count($customItems) + 1 }};

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
            tr.style.borderBottom = '1px solid #fef3c7';

            tr.innerHTML = `
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][name]" class="form-control" placeholder="Contoh: Triplek 9mm 1.2x2.4" required style="font-size:12px;height:32px;border-radius:6px;width:100%;padding:4px 8px;">
                </td>
                <td style="padding:6px 6px;">
                    <select name="custom_items[${customIdx}][item_type]" class="form-control custom-item-type-select" onchange="onCustomItemTypeChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;font-weight:600;">
                        <option value="material" selected>Material</option>
                        <option value="tool">Alat</option>
                    </select>
                </td>
                <td style="padding:6px 6px;">
                    <input type="text" name="custom_items[${customIdx}][spec]" class="form-control" placeholder="mis. fc'=25 MPa" style="font-size:12px;height:32px;border-radius:6px;padding:4px 8px;width:100%;">
                </td>
                <td style="padding:6px 6px;">
                    <select name="custom_items[${customIdx}][category_id]" class="form-control custom-item-cat-select" onchange="onCustomCategoryChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;cursor:pointer;">
                        <option value="">Material Khusus (Otomatis)</option>
                        ${materialCategoryOptions}
                    </select>
                </td>
                <td style="padding:6px 6px;">
                    <div class="custom-type-group-wrap" data-index="${customIdx}" style="width:100%;">
                        <div class="type-select-wrap">
                            <select class="form-control custom-type-select" onchange="onCustomTypeSelectChange(this)" style="font-size:11.5px;height:32px;border-radius:6px;width:100%;padding:2px 6px;">
                                <option value="">— Pilih Kelompok —</option>
                                <option value="__new__">✚ Ketik Kelompok Baru...</option>
                            </select>
                        </div>
                        <div class="type-manual-wrap" style="display:none;align-items:center;gap:4px;">
                            <input type="text" class="form-control custom-type-manual-input" placeholder="Ketik kelompok..." oninput="onCustomTypeManualInput(this)" style="font-size:11.5px;height:32px;border-radius:6px;padding:4px 6px;flex:1;min-width:0;">
                            <button type="button" class="btn btn-light border" onclick="toggleCustomTypeMode(this, false)" title="Kembali ke dropdown pilihan" style="height:32px;padding:0 8px;font-size:11px;color:#64748b;border-radius:6px;flex-shrink:0;">
                                <x-lucide-list />
                            </button>
                        </div>
                        <input type="hidden" name="custom_items[${customIdx}][type]" class="custom-type-hidden" value="">
                    </div>
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
            initCustomTypeOptions(tr, false);
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
            document.querySelectorAll('#materialTable .qty-input').forEach(input => {
                if (parseFloat(input.value) > 0) total++;
            });
            document.querySelectorAll('#customRequestBody .custom-qty-input').forEach(input => {
                if (parseFloat(input.value) > 0) total++;
            });
            const badge = document.getElementById('totalItemBadge');
            const bottomBadge = document.getElementById('bottomTotalBadge');
            if (badge) badge.textContent = `${total} Item Diminta`;
            if (bottomBadge) bottomBadge.textContent = total;
        }

        function filterMaterials() {
            const q = document.getElementById('materialSearch').value.toLowerCase();
            document.querySelectorAll('.mat-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#customRequestBody tr:not(#customRequestEmpty)').forEach(function(tr) {
                initCustomTypeOptions(tr, true);
            });
        });
    </script>
    @endpush
</x-app-layout>
