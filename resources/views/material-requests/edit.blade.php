<x-app-layout>
    <x-slot name="title">Edit PO Lapangan: {{ $materialRequest->request_number }}</x-slot>

    @push('styles')
    <style>
        .mr-create-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }

        .sticky-sidebar .form-control {
            height: 35px;
            padding: 6px 10px;
            font-size: 12px;
            line-height: 1.4;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #0f172a;
        }

        .sticky-sidebar .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .sticky-sidebar textarea.form-control {
            height: auto !important;
            min-height: 60px;
            line-height: 1.4;
            padding: 8px 10px;
        }

        .mr-item-table th {
            padding: 7px 10px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #fafafa !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .mr-item-table td {
            padding: 6px 10px !important;
            vertical-align: middle !important;
        }

        .qty-input {
            width: 75px;
            text-align: center;
            font-weight: 600;
            height: 30px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            padding: 0 4px;
        }

        .qty-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        @media (max-width: 992px) {
            .mr-create-layout {
                grid-template-columns: 1fr !important;
            }
            .sticky-sidebar {
                position: static !important;
            }
        }
    </style>
    @endpush

    @php
        $existingQtyMap = $materialRequest->items->whereNotNull('material_id')->pluck('qty_requested', 'material_id');
        $existingSpecs = $materialRequest->items->whereNotNull('material_id')->pluck('specification', 'material_id');
        $existingLocations = $materialRequest->items->whereNotNull('material_id')->pluck('usage_location', 'material_id');
        $existingDates = $materialRequest->items->whereNotNull('material_id')->mapWithKeys(fn($it) => [$it->material_id => $it->usage_date ? \Carbon\Carbon::parse($it->usage_date)->format('Y-m-d') : '']);
        $customItems = $materialRequest->items->whereNull('material_id')->values();
    @endphp

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('material-requests.index') }}">PO Lapangan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <a href="{{ route('material-requests.show', $materialRequest) }}">{{ $materialRequest->request_number }}</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Edit PO Lapangan</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:8px;padding:10px 14px;font-size:12px;">
        <div style="font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-triangle-exclamation"></i> Terdapat kesalahan pada formulir:
        </div>
        <ul class="mb-0" style="padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="materialRequestForm" method="POST" action="{{ route('material-requests.update', $materialRequest) }}">
        @csrf
        @method('PUT')
        <div class="mr-create-layout mb-3">

            <!-- Left: Materials list -->
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Daftar Material</span>
                    </div>
                    <div>
                        <span id="totalItemBadge" class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                            {{ $materialRequest->items->count() }} Item Diminta
                        </span>
                    </div>
                </div>

                <div class="card-body" style="padding:10px 14px 6px;">
                    <div style="position:relative;margin-bottom:10px;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;"></i>
                        <input type="text" id="materialSearch" class="form-control" placeholder="Cari nama material, kode, atau spesifikasi..." style="padding-left:28px !important;height:32px;font-size:12px;border-radius:6px;" oninput="filterMaterials()">
                    </div>
                </div>

                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mr-item-table mb-0" id="materialTable">
                        <thead>
                            <tr>
                                <th style="min-width:180px;padding:7px 10px;">Material / SKU</th>
                                <th style="text-align:center;width:80px;padding:7px 8px;">Ukuran</th>
                                <th style="text-align:center;width:90px;padding:7px 8px;">Stok</th>
                                <th style="text-align:center;width:85px;padding:7px 8px;">Jml Order</th>
                                <th style="width:128px;padding:7px 8px;">Spesifikasi</th>
                                <th style="text-align:center;width:108px;padding:7px 8px;">Tgl Pakai</th>
                                <th style="width:122px;padding:7px 8px;">Alokasi / Zona</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialCategories as $category)
                            @php $groupKey = 'mcat-' . $category->id; @endphp
                            <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                <td colspan="7" style="padding:6px 10px !important;">
                                    <div class="flex items-center" style="gap:6px;">
                                        <i class="fas fa-chevron-down cat-chev" style="font-size:9.5px;color:#64748b;"></i>
                                        <span class="fw-700" style="font-size:11.5px;text-transform:uppercase;color:#1e293b;">{{ $category->name }}</span>
                                    </div>
                                </td>
                            </tr>
                            @foreach($category->materials as $mat)
                            @php
                                $totalStock = $mat->inventories->sum('quantity');
                                $existingVal = $existingQtyMap[$mat->id] ?? 0;
                            @endphp
                            <tr class="mat-row cat-rows {{ $groupKey }}" data-material-id="{{ $mat->id }}" style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:6px 10px 6px 22px;">
                                    <div class="fw-600" style="font-size:12.5px;color:#0f172a;">{{ $mat->name }}</div>
                                    <span style="font-size:10px;color:#475569;">{{ $mat->sku }}</span>
                                </td>
                                <td style="text-align:center;font-size:11px;color:#334155;">{{ $mat->size ?: '-' }}</td>
                                <td style="text-align:center;">
                                    <span class="badge" style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:600;padding:2px 6px;border-radius:4px;">
                                        {{ number_format($totalStock, 0, ',', '.') }}
                                    </span>
                                    <span class="text-muted" style="font-size:10px;">{{ $mat->unit?->abbreviation ?? 'unit' }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <input type="number"
                                           name="quantities[{{ $mat->id }}]"
                                           class="form-control qty-input"
                                           min="0" step="0.01"
                                           value="{{ old('quantities.'.$mat->id, $existingVal) }}"
                                           oninput="calculateTotal()">
                                </td>
                                <td style="padding:4px 6px;">
                                    <input type="text"
                                           name="specs[{{ $mat->id }}]"
                                           class="form-control"
                                           placeholder="Spesifikasi"
                                           value="{{ old('specs.'.$mat->id, $existingSpecs[$mat->id] ?? '') }}"
                                           style="height:28px;font-size:11px;padding:2px 6px;">
                                </td>
                                <td style="padding:4px 6px;">
                                    <input type="date"
                                           name="usage_dates[{{ $mat->id }}]"
                                           class="form-control"
                                           value="{{ old('usage_dates.'.$mat->id, $existingDates[$mat->id] ?? '') }}"
                                           style="height:28px;font-size:11px;padding:2px 6px;">
                                </td>
                                <td style="padding:4px 6px;">
                                    <input type="text"
                                           name="usage_locations[{{ $mat->id }}]"
                                           class="form-control"
                                           placeholder="Alokasi / Zona"
                                           value="{{ old('usage_locations.'.$mat->id, $existingLocations[$mat->id] ?? '') }}"
                                           style="height:28px;font-size:11px;padding:2px 6px;">
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Custom Items Section --}}
                <div style="border-top:1px solid #e2e8f0;padding:12px 14px;background:#fffdfa;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span class="fw-700" style="font-size:12px;color:#92400e;">Item Manual / Custom</span>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="addCustomItemRow()" style="font-size:11px;padding:2px 8px;border-radius:4px;">
                            <i class="fas fa-plus me-1"></i> Tambah Item Manual
                        </button>
                    </div>
                    <div id="customItemsContainer">
                        @foreach($customItems as $cIdx => $cItem)
                        <div class="row g-2 mb-2 custom-item-row" style="background:#ffffff;padding:6px;border-radius:6px;border:1px solid #fde68a;">
                            <div class="col-md-3">
                                <input type="text" name="custom_items[{{ $cIdx }}][name]" class="form-control form-control-sm" placeholder="Nama Barang" value="{{ $cItem->custom_item_name }}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" name="custom_items[{{ $cIdx }}][qty]" class="form-control form-control-sm" placeholder="Qty" value="{{ (float) $cItem->qty_requested }}" required oninput="calculateTotal()">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="custom_items[{{ $cIdx }}][unit]" class="form-control form-control-sm" placeholder="Satuan" value="{{ $cItem->custom_item_unit ?: 'unit' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="custom_items[{{ $cIdx }}][spec]" class="form-control form-control-sm" placeholder="Spesifikasi" value="{{ $cItem->specification }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="custom_items[{{ $cIdx }}][location]" class="form-control form-control-sm" placeholder="Zona" value="{{ $cItem->usage_location }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.custom-item-row').remove(); calculateTotal();" style="padding:2px 6px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right: Header Form -->
            <div class="card sticky-sidebar" style="position:sticky;top:20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-pen-to-square text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi PO Lapangan</span>
                </div>
                <div class="card-body" style="padding:14px;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Nama Proyek (Gudang Pemohon) <span class="text-danger">*</span>
                        </label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control" required style="font-weight:500;">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $warehouseId) == $wh->id ? 'selected' : '' }}>{{ $wh->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Sumber Material (Kepada)
                        </label>
                        <select name="source_warehouse_id" id="source_warehouse_id" class="form-control" style="font-weight:500;">
                            <option value="">— Pengadaan / Beli ke Vendor —</option>
                            @foreach($sourceWarehouses as $swh)
                                <option value="{{ $swh->id }}" {{ old('source_warehouse_id', $sourceWarehouseId) == $swh->id ? 'selected' : '' }}>
                                    {{ $swh->name }}{{ $swh->is_central ? ' (Pusat)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Dibutuhkan Tgl
                        </label>
                        <input type="date" name="needed_at" class="form-control"
                            value="{{ old('needed_at', $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Diajukan Sebagai
                        </label>
                        <select name="requested_as" id="requested_as" class="form-control" style="font-weight:500;">
                            @php $currRole = old('requested_as', $materialRequest->requested_as ?: 'Admin Proyek'); @endphp
                            <option value="Admin Proyek" {{ $currRole === 'Admin Proyek' ? 'selected' : '' }}>Admin Proyek</option>
                            <option value="Site Manager" {{ $currRole === 'Site Manager' ? 'selected' : '' }}>Site Manager</option>
                            <option value="Site Engineering" {{ $currRole === 'Site Engineering' ? 'selected' : '' }}>Site Engineering</option>
                            <option value="Karyawan" {{ $currRole === 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Catatan <span class="text-muted" style="font-weight:400;">(Opsional)</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $materialRequest->notes) }}</textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px;font-size:13px;font-weight:600;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('material-requests.show', $materialRequest) }}" class="btn btn-light border w-full text-center" style="justify-content:center;padding:7px;font-size:12px;font-weight:500;border-radius:6px;color:#64748b;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let customIdx = {{ count($customItems) + 1 }};

        function addCustomItemRow() {
            const container = document.getElementById('customItemsContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 custom-item-row';
            row.style.cssText = 'background:#ffffff;padding:6px;border-radius:6px;border:1px solid #fde68a;';
            row.innerHTML = `
                <div class="col-md-3">
                    <input type="text" name="custom_items[${customIdx}][name]" class="form-control form-control-sm" placeholder="Nama Barang" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="custom_items[${customIdx}][qty]" class="form-control form-control-sm" placeholder="Qty" required oninput="calculateTotal()">
                </div>
                <div class="col-md-2">
                    <input type="text" name="custom_items[${customIdx}][unit]" class="form-control form-control-sm" placeholder="Satuan" value="unit">
                </div>
                <div class="col-md-2">
                    <input type="text" name="custom_items[${customIdx}][spec]" class="form-control form-control-sm" placeholder="Spesifikasi">
                </div>
                <div class="col-md-2">
                    <input type="text" name="custom_items[${customIdx}][location]" class="form-control form-control-sm" placeholder="Zona">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.custom-item-row').remove(); calculateTotal();" style="padding:2px 6px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            customIdx++;
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(input => {
                if (parseFloat(input.value) > 0) total++;
            });
            document.querySelectorAll('.custom-item-row input[name$="[qty]"]').forEach(input => {
                if (parseFloat(input.value) > 0) total++;
            });
            const badge = document.getElementById('totalItemBadge');
            if (badge) badge.textContent = `${total} Item Diminta`;
        }

        function filterMaterials() {
            const q = document.getElementById('materialSearch').value.toLowerCase();
            document.querySelectorAll('.mat-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }
    </script>
    @endpush
</x-app-layout>
