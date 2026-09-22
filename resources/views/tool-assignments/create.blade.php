<x-app-layout>
    <x-slot name="title">Pinjamkan Alat (Input Jumlah & Stok)</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tool-assignments.index') }}">Peminjaman Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Peminjaman per Jumlah Alat</span>
    </div>

    @if ($errors->has('quantities'))
    <div class="alert alert-danger mb-4">
        <i class="fas fa-triangle-exclamation"></i> {{ $errors->first('quantities') }}
    </div>
    @endif

    <form method="POST" action="{{ route('tool-assignments.store') }}">
        @csrf
        <div class="grid grid-3 mb-4" style="grid-template-columns: 2fr 1fr;">
            <!-- Left: Tools grouped by category -->
            <div class="card">
                <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-screwdriver-wrench text-primary"></i>
                        <span class="card-title">Daftar Stok Alat Tersedia</span>
                    </div>
                    <div style="font-size:13px; font-weight:700; color:#2563eb;" id="totalItemBadge">
                        Total: 0 Unit Alat Dipinjam
                    </div>
                </div>

                <div class="card-body" style="padding:16px;">
                    <div class="mb-3">
                        <input type="text" id="toolSearch" class="form-control" placeholder="Cari nama alat atau kategori..." oninput="filterTools()">
                    </div>

                    <div class="table-wrap" style="max-height:520px;overflow-y:auto;">
                        <table class="data-table mb-0" id="toolsTable">
                            <thead>
                                <tr>
                                    <th>Nama Alat</th>
                                    <th style="text-align:center;">Stok Tersedia</th>
                                    <th style="width:140px;text-align:center;">Jumlah Dipinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($toolCategories as $category)
                                @php $groupKey = 'cat-' . $category->id; @endphp
                                <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;">
                                    <td colspan="3" style="padding:9px 16px !important;">
                                        <div class="flex items-center" style="gap:8px;">
                                            <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                            <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">{{ $category->name }}</span>
                                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;">{{ $category->tools->count() }} alat</span>
                                            <span class="text-muted" style="font-size:11px;">{{ $category->tools->sum('stock_available') }} unit tersedia</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($category->tools as $tool)
                                <tr class="tool-row cat-rows {{ $groupKey }}">
                                    <td style="padding-left:28px;">
                                        <div class="fw-600" style="font-size:13px;color:#0f172a;">{{ $tool->name }}</div>
                                        <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $tool->code }}</code>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-success" style="font-size:12px;padding:3px 10px;">
                                            <i class="fas fa-boxes-stacked me-1"></i> {{ $tool->stock_available }} Ready
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $tool->id }}]"
                                               class="form-control qty-input"
                                               min="0" max="{{ $tool->stock_available }}"
                                               value="{{ old('quantities.'.$tool->id, 0) }}"
                                               oninput="validateAndCalculateTotal()"
                                               style="text-align:center;font-weight:700;color:#1e293b;">
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                @endforelse

                                @if($uncategorizedTools->isNotEmpty())
                                <tr class="cat-toggle" data-group="cat-uncategorized" style="background:#f8fafc !important;cursor:pointer;">
                                    <td colspan="3" style="padding:9px 16px !important;">
                                        <div class="flex items-center" style="gap:8px;">
                                            <i class="fas fa-chevron-down cat-chev" style="font-size:10px;color:#64748b;transition:transform .2s;"></i>
                                            <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">Lainnya</span>
                                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;">{{ $uncategorizedTools->count() }} alat</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($uncategorizedTools as $tool)
                                <tr class="tool-row cat-rows cat-uncategorized">
                                    <td style="padding-left:28px;">
                                        <div class="fw-600" style="font-size:13px;color:#0f172a;">{{ $tool->name }}</div>
                                        <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $tool->code }}</code>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-success" style="font-size:12px;padding:3px 10px;">
                                            <i class="fas fa-boxes-stacked me-1"></i> {{ $tool->stock_available }} Ready
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number"
                                               name="quantities[{{ $tool->id }}]"
                                               class="form-control qty-input"
                                               min="0" max="{{ $tool->stock_available }}"
                                               value="{{ old('quantities.'.$tool->id, 0) }}"
                                               oninput="validateAndCalculateTotal()"
                                               style="text-align:center;font-weight:700;color:#1e293b;">
                                    </td>
                                </tr>
                                @endforeach
                                @endif

                                @if($toolCategories->isEmpty() && $uncategorizedTools->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                        Tidak ada stok alat yang tersedia untuk dipinjam saat ini.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Borrowing info form -->
            <div class="card" style="height:fit-content;">
                <div class="card-header">
                    <i class="fas fa-clipboard-user text-primary"></i>
                    <span class="card-title">Informasi Peminjaman</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Peminjam / Worker / Tukang <span class="text-danger">*</span></label>
                        <input type="text" name="borrower_name" class="form-control"
                               placeholder="Ketik nama peminjam (misal: Pak Joko)..."
                               value="{{ old('borrower_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon / Kontak <span class="text-muted" style="font-weight:400;">(Opsional)</span></label>
                        <input type="text" name="borrower_phone" class="form-control"
                               placeholder="Contoh: 0812-3456-7890"
                               value="{{ old('borrower_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Pekerjaan / Site <span class="text-danger">*</span></label>
                        <input type="text" name="location_name" class="form-control"
                               placeholder="Ketik lokasi (misal: Gedung B Lantai 3)..."
                               value="{{ old('location_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gudang Asal Alat</label>
                        <select name="warehouse_id" class="form-control" onchange="window.location.href = '{{ route('tool-assignments.create') }}?warehouse_id=' + this.value;">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $selectedWarehouseId) == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_at" class="form-control" value="{{ old('assigned_at', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimasi Tanggal Kembali <span class="text-muted" style="font-weight:400;">(Opsional)</span></label>
                        <input type="date" name="expected_return_at" class="form-control" value="{{ old('expected_return_at') }}">
                        <div class="text-muted" style="font-size:11px;margin-top:3px;">Dapat dikosongkan jika tidak ada batas waktu pasti.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Tujuan / Keperluan Peminjaman</label>
                        <textarea name="purpose" class="form-control" rows="2" placeholder="Pekerjaan spesifik / lokasi site...">{{ old('purpose') }}</textarea>
                    </div>
                    <div class="flex gap-2" style="flex-direction:column;">
                        <button type="submit" class="btn btn-primary w-full justify-center">
                            <i class="fas fa-paper-plane"></i> Ajukan Peminjaman
                        </button>
                        <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary w-full justify-center">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function validateAndCalculateTotal() {
            var total = 0;
            document.querySelectorAll('.qty-input').forEach(function (input) {
                var max = parseInt(input.getAttribute('max')) || 0;
                var val = parseInt(input.value) || 0;
                if (val < 0) val = 0;
                if (val > max) val = max;
                input.value = val;
                total += val;
            });
            document.getElementById('totalItemBadge').innerText = 'Total: ' + total + ' Unit Alat Dipinjam';
        }

        function filterTools() {
            var keyword = document.getElementById('toolSearch').value.toLowerCase();
            var toggleRows = document.querySelectorAll('.cat-toggle');
            var toolRows   = document.querySelectorAll('#toolsTable tbody tr.tool-row');

            if (!keyword) {
                toolRows.forEach(function(r)   { r.style.display = ''; });
                toggleRows.forEach(function(r) { r.style.display = ''; });
                return;
            }

            toggleRows.forEach(function(r) { r.style.display = 'none'; });

            toolRows.forEach(function (row) {
                var text = row.innerText.toLowerCase();
                if (text.indexOf(keyword) !== -1) {
                    row.style.display = '';
                    var group = Array.from(row.classList).find(function(c) {
                        return c.startsWith('cat-') && c !== 'cat-rows';
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
                document.addEventListener('DOMContentLoaded', function () { initAccordion(); validateAndCalculateTotal(); });
            } else {
                initAccordion(); validateAndCalculateTotal();
            }
        })();
    </script>
    @endpush
</x-app-layout>
