<x-app-layout>
    <x-slot name="title">Buat Permintaan Material</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('material-requests.index') }}">Permintaan Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>

    @if ($errors->has('quantities'))
    <div class="alert alert-danger mb-4">
        <i class="fas fa-triangle-exclamation"></i> {{ $errors->first('quantities') }}
    </div>
    @endif

    <form method="POST" action="{{ route('material-requests.store') }}">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 320px;gap:20px;align-items:start;">

            <!-- Left: Materials grouped by category -->
            <div class="card">
                <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-list text-primary"></i>
                        <span class="card-title">Daftar Material Tersedia</span>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:#2563eb;" id="totalItemBadge">
                        Total: 0 Item Diminta
                    </div>
                </div>

                <div class="card-body" style="padding:16px;">
                    <div class="mb-3">
                        <input type="text" id="materialSearch" class="form-control" placeholder="Cari nama material, model, atau kategori..." oninput="filterMaterials()">
                    </div>

                    <div class="table-wrap" style="max-height:520px;overflow-y:auto;">
                        <table class="data-table mb-0" id="materialsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:200px;">Nama Material & Model</th>
                                    <th style="text-align:center;">Ukuran / Dimensi</th>
                                    <th style="text-align:center;">Total Stok</th>
                                    <th style="width:140px;text-align:center;">Jumlah Diminta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materialCategories as $category)
                                @php $groupKey = 'mcat-' . $category->id; @endphp
                                <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;">
                                    <td colspan="4" style="padding:9px 16px !important;">
                                        <div class="flex items-center" style="gap:8px;">
                                            <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                            <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">{{ $category->name }}</span>
                                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;">{{ $category->materials->count() }} material</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($category->materials as $mat)
                                @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                                <tr class="mat-row cat-rows {{ $groupKey }}">
                                    <td style="padding-left:28px;">
                                        <div class="fw-600" style="font-size:13px;color:#0f172a;">{{ $mat->name }}</div>
                                        <div class="flex gap-2 mt-1">
                                            @if($mat->type)
                                            <span class="badge badge-purple" style="font-size:10px;">{{ $mat->type }}</span>
                                            @endif
                                            <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $mat->sku }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($mat->size)
                                        <span style="font-size:13px;color:#334155;">{{ $mat->size }}</span>
                                        @else
                                        <span class="text-muted" style="font-size:12px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="fw-700" style="font-size:14px;color:#0f172a;">{{ number_format($totalStock, 0, ',', '.') }}</span>
                                        <span class="text-muted" style="font-size:11px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $mat->id }}]"
                                               class="form-control qty-input"
                                               min="0" step="0.01"
                                               value="{{ old('quantities.'.$mat->id, 0) }}"
                                               oninput="calculateTotal()"
                                               style="text-align:center;font-weight:700;color:#1e293b;">
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                @endforelse

                                @if($uncategorizedMaterials->isNotEmpty())
                                <tr class="cat-toggle" data-group="mcat-uncategorized" style="background:#f8fafc !important;cursor:pointer;">
                                    <td colspan="4" style="padding:9px 16px !important;">
                                        <div class="flex items-center" style="gap:8px;">
                                            <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                            <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">Lainnya</span>
                                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;">{{ $uncategorizedMaterials->count() }} material</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($uncategorizedMaterials as $mat)
                                @php $totalStock = $mat->inventories->sum('quantity'); @endphp
                                <tr class="mat-row cat-rows mcat-uncategorized">
                                    <td style="padding-left:28px;">
                                        <div class="fw-600" style="font-size:13px;color:#0f172a;">{{ $mat->name }}</div>
                                        <div class="flex gap-2 mt-1">
                                            @if($mat->type)
                                            <span class="badge badge-purple" style="font-size:10px;">{{ $mat->type }}</span>
                                            @endif
                                            <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $mat->sku }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($mat->size)
                                        <span style="font-size:13px;color:#334155;">{{ $mat->size }}</span>
                                        @else
                                        <span class="text-muted" style="font-size:12px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="fw-700" style="font-size:14px;color:#0f172a;">{{ number_format($totalStock, 0, ',', '.') }}</span>
                                        <span class="text-muted" style="font-size:11px;">{{ $mat->unit?->abbreviation ?? $mat->unit?->name }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $mat->id }}]"
                                               class="form-control qty-input"
                                               min="0" step="0.01"
                                               value="{{ old('quantities.'.$mat->id, 0) }}"
                                               oninput="calculateTotal()"
                                               style="text-align:center;font-weight:700;color:#1e293b;">
                                    </td>
                                </tr>
                                @endforeach
                                @endif

                                @if($materialCategories->isEmpty() && $uncategorizedMaterials->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                        Tidak ada material yang tersedia saat ini.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Request info form -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i>
                    <span class="card-title">Informasi Permintaan</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Pemohon <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $warehouseId) == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dibutuhkan Pada <span class="text-danger">*</span></label>
                        <input type="date" name="needed_at" class="form-control"
                            value="{{ old('needed_at') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-paper-plane"></i> Ajukan Permintaan
                    </button>
                    <a href="{{ route('material-requests.index') }}" class="btn btn-secondary w-full mt-2" style="justify-content:center;">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function calculateTotal() {
            var total = 0;
            document.querySelectorAll('.qty-input').forEach(function (input) {
                var val = parseFloat(input.value) || 0;
                if (val < 0) { val = 0; input.value = 0; }
                total += val;
            });
            document.getElementById('totalItemBadge').innerText = 'Total: ' + total + ' Item Diminta';
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
