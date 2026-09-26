<x-app-layout>
    <x-slot name="title">Buat Permintaan Material</x-slot>

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
            text-align: center;
            font-weight: 700;
            color: #1e293b;
            width: 80px;
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

        @media (max-width: 640px) {
            .page-header-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('material-requests.index') }}">Permintaan Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Buat Permintaan Baru</span>
    </div>

    @if ($errors->has('quantities'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-triangle-exclamation"></i> {{ $errors->first('quantities') }}
    </div>
    @endif

    <form method="POST" action="{{ route('material-requests.store') }}">
        @csrf
        <div class="mr-create-layout mb-3">

            <!-- Left: Materials grouped by category -->
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Daftar Material Tersedia</span>
                    </div>
                    <div>
                        <span id="totalItemBadge" class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                            0 Item Diminta
                        </span>
                    </div>
                </div>

                <div class="card-body" style="padding:12px 14px;">
                    {{-- Search Box --}}
                    <div style="position:relative;margin-bottom:10px;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:11px;"></i>
                        <input type="text" id="materialSearch" class="form-control" placeholder="Cari nama material, model, atau kategori..." oninput="filterMaterials()" style="padding-left:30px;height:32px;border-radius:6px;font-size:12px;border-color:#cbd5e1;">
                    </div>

                    <div class="alert alert-info mb-2" style="font-size:11.5px;padding:8px 12px;border-radius:6px;line-height:1.35;">
                        <i class="fas fa-warehouse" style="font-size:10px;"></i> Stok dari <strong>Gudang Pusat</strong> ({{ $centralWarehouse?->name ?? 'Pusat' }}). Permintaan dikirim dari Pusat ke Proyek pemohon.
                    </div>

                    <div class="table-wrap" style="max-height:500px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;">
                        <table class="data-table mr-item-table mb-0" id="materialsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:200px;padding:7px 10px;">Nama Material & Model</th>
                                    <th style="text-align:center;width:110px;padding:7px 8px;">Ukuran</th>
                                    <th style="text-align:center;width:105px;padding:7px 8px;">Stok Pusat</th>
                                    <th style="text-align:center;width:115px;padding:7px 8px;">Jumlah Diminta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materialCategories as $category)
                                @php $groupKey = 'mcat-' . $category->id; @endphp
                                <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                    <td colspan="4" style="padding:6px 10px !important;">
                                        <div class="flex items-center justify-between" style="width:100%;">
                                            <div class="flex items-center" style="gap:6px;">
                                                <i class="fas fa-chevron-down cat-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                                <span class="fw-700" style="font-size:11.5px;text-transform:uppercase;letter-spacing:.02em;color:#1e293b;">{{ $category->name }}</span>
                                                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:600;">{{ $category->materials->count() }} material</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($category->materials as $mat)
                                @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                                <tr class="mat-row cat-rows {{ $groupKey }}" style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 10px 6px 22px;">
                                        <div class="fw-600" style="font-size:12.5px;color:#0f172a;">{{ $mat->name }}</div>
                                        <div style="display:flex;align-items:center;gap:4px;margin-top:1px;">
                                            @if($mat->type)
                                            <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:9.5px;padding:1px 5px;border-radius:3px;font-weight:600;">{{ $mat->type }}</span>
                                            @endif
                                            <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10px;color:#475569;">{{ $mat->sku }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($mat->size)
                                        <span style="font-size:12px;color:#334155;">{{ $mat->size }}</span>
                                        @else
                                        <span class="text-muted" style="font-size:11px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge" style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:600;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                            {{ number_format($totalStock, 0, ',', '.') }}
                                        </span>
                                        <span class="text-muted" style="font-size:10px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $mat->id }}]"
                                               class="form-control qty-input"
                                               min="0" step="0.01"
                                               value="{{ old('quantities.'.$mat->id, 0) }}"
                                               oninput="calculateTotal()">
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                @endforelse

                                @if($uncategorizedMaterials->isNotEmpty())
                                <tr class="cat-toggle" data-group="mcat-uncategorized" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                    <td colspan="4" style="padding:6px 10px !important;">
                                        <div class="flex items-center" style="gap:6px;">
                                            <i class="fas fa-chevron-down cat-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                            <span class="fw-700" style="font-size:11.5px;text-transform:uppercase;letter-spacing:.02em;color:#1e293b;">Lainnya</span>
                                            <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:600;">{{ $uncategorizedMaterials->count() }} material</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($uncategorizedMaterials as $mat)
                                @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                                <tr class="mat-row cat-rows mcat-uncategorized" style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 10px 6px 22px;">
                                        <div class="fw-600" style="font-size:12.5px;color:#0f172a;">{{ $mat->name }}</div>
                                        <div style="display:flex;align-items:center;gap:4px;margin-top:1px;">
                                            @if($mat->type)
                                            <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:9.5px;padding:1px 5px;border-radius:3px;font-weight:600;">{{ $mat->type }}</span>
                                            @endif
                                            <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10px;color:#475569;">{{ $mat->sku }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($mat->size)
                                        <span style="font-size:12px;color:#334155;">{{ $mat->size }}</span>
                                        @else
                                        <span class="text-muted" style="font-size:11px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge" style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:600;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                            {{ number_format($totalStock, 0, ',', '.') }}
                                        </span>
                                        <span class="text-muted" style="font-size:10px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $mat->id }}]"
                                               class="form-control qty-input"
                                               min="0" step="0.01"
                                               value="{{ old('quantities.'.$mat->id, 0) }}"
                                               oninput="calculateTotal()">
                                    </td>
                                </tr>
                                @endforeach
                                @endif

                                @if($materialCategories->isEmpty() && $uncategorizedMaterials->isEmpty())
                                <tr>
                                    <td colspan="4" style="text-align:center;padding:28px 16px;color:#94a3b8;">
                                        <i class="fas fa-inbox" style="font-size:24px;margin-bottom:6px;display:block;color:#cbd5e1;"></i>
                                        <div style="font-weight:600;font-size:12.5px;color:#64748b;">Tidak ada material yang tersedia</div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Manual items: barang tidak ada di Pusat -->
                    <div class="mt-3" style="border:1.5px dashed #fde68a;border-radius:8px;padding:12px;background:#fffbeb;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-pen-to-square" style="font-size:12px;color:#d97706;"></i>
                                <strong style="font-size:12px;color:#92400e;">Barang Tidak Ada di Pusat — Input Manual</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning" onclick="addCustomRequestRow()" style="padding:4px 10px;font-size:11px;border-radius:5px;font-weight:600;">
                                <i class="fas fa-plus me-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="text-muted" style="font-size:10.5px;line-height:1.35;margin-bottom:8px;">
                            Untuk barang yang <strong>tidak ada di Gudang Pusat</strong> namun dibutuhkan lapangan.
                        </div>
                        <div class="table-wrap" style="border:1px solid #fde68a;border-radius:5px;overflow:hidden;">
                            <table class="data-table mb-0" id="customRequestTable" style="font-size:12px;">
                                <thead>
                                    <tr style="background:#fef3c7;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:#92400e;">
                                        <th style="min-width:180px;padding:6px 8px;">Nama Barang (Manual)</th>
                                        <th style="min-width:140px;padding:6px 8px;">Kategori</th>
                                        <th style="width:75px;text-align:center;padding:6px 8px;">Satuan</th>
                                        <th style="width:85px;text-align:center;padding:6px 8px;">Jumlah</th>
                                        <th style="width:36px;text-align:center;padding:6px 4px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="customRequestBody">
                                    <tr id="customRequestEmpty">
                                        <td colspan="5" class="text-center text-muted" style="padding:10px;font-size:11px;">Belum ada barang manual. Klik "Tambah Baris".</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Request info form -->
            <div class="card sticky-sidebar" style="position:sticky;top:20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-file-lines text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Permintaan</span>
                </div>
                <div class="card-body" style="padding:14px;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Gudang Pemohon <span class="text-danger">*</span>
                        </label>
                        <select name="warehouse_id" class="form-control" required style="font-weight:500;">
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $warehouseId) == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Dibutuhkan Pada <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="needed_at" class="form-control"
                            value="{{ old('needed_at') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Catatan <span class="text-muted" style="font-weight:400;">(Opsional)</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan keperluan...">{{ old('notes') }}</textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px;font-size:13px;font-weight:600;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Permintaan
                        </button>
                        <a href="{{ route('material-requests.index') }}" class="btn btn-light border w-full text-center" style="justify-content:center;padding:7px;font-size:12px;font-weight:500;border-radius:6px;color:#64748b;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let customReqIndex = 0;
        function addCustomRequestRow() {
            const tbody = document.getElementById('customRequestBody');
            const empty = document.getElementById('customRequestEmpty');
            if (empty) empty.style.display = 'none';
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #fef3c7';

            let categoryOptions = `<option value="">Material Khusus Proyek (Otomatis)</option>`;
            @foreach($materialCategories as $cat)
                categoryOptions += `<option value="{{ $cat->id }}">{{ addslashes($cat->name) }}</option>`;
            @endforeach

            tr.innerHTML = `
                <td style="padding:5px 8px;"><input type="text" name="custom_items[${customReqIndex}][name]" class="form-control" placeholder="Contoh: Triplek 9mm 1.2x2.4" required style="font-size:12px;height:30px;border-radius:4px;"></td>
                <td style="padding:5px 6px;">
                    <select name="custom_items[${customReqIndex}][category_id]" class="form-control" style="font-size:11.5px;height:30px;border-radius:4px;">
                        ${categoryOptions}
                    </select>
                </td>
                <td style="padding:5px 6px;"><input type="text" name="custom_items[${customReqIndex}][unit]" class="form-control" placeholder="pcs" value="pcs" style="text-align:center;font-size:12px;height:30px;border-radius:4px;"></td>
                <td style="padding:5px 6px;"><input type="number" name="custom_items[${customReqIndex}][qty]" class="form-control qty-input" min="0.01" step="0.01" placeholder="0" required style="height:30px;" oninput="calculateTotal()"></td>
                <td style="text-align:center;padding:5px 4px;"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); checkCustomEmpty(); calculateTotal();" style="width:24px;height:24px;padding:0;border-radius:4px;display:inline-flex;align-items:center;justify-content:center;"><i class="fas fa-trash" style="font-size:9px;"></i></button></td>
            `;
            tbody.appendChild(tr);
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
            document.getElementById('totalItemBadge').innerText = total + ' Item Diminta';
        }

        function filterMaterials() {
            var keyword = document.getElementById('materialSearch').value.toLowerCase();
            var toggleRows = document.querySelectorAll('.cat-toggle');
            var matRows    = document.querySelectorAll('#materialsTable tbody tr.mat-row');

            if (!keyword) {
                matRows.forEach(function(r)    { r.style.display = ''; });
                toggleRows.forEach(function(r) { r.style.display = ''; });
                return;
            }

            toggleRows.forEach(function(r) { r.style.display = 'none'; });

            matRows.forEach(function (row) {
                var text = row.innerText.toLowerCase();
                if (text.indexOf(keyword) !== -1) {
                    row.style.display = '';
                    var group = Array.from(row.classList).find(function(c) {
                        return c.startsWith('mcat-') && c !== 'cat-rows';
                    });
                    if (group) {
                        var header = document.querySelector('.cat-toggle[data-group="' + group + '"]');
                        if (header) header.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
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
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () { initAccordion(); calculateTotal(); });
            } else {
                initAccordion(); calculateTotal();
            }
        })();
    </script>
    @endpush
</x-app-layout>
