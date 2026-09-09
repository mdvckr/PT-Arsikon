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
            <!-- Left Side: Table of Tools with Stock & Quantity Input -->
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

                <div class="card-body" style="padding: 16px;">
                    <!-- Filter Quick Search -->
                    <div class="mb-3">
                        <input type="text" id="toolSearch" class="form-control" placeholder="Cari nama alat atau kategori..." onkeyup="filterTools()">
                    </div>

                    <div class="table-wrap" style="max-height: 450px; overflow-y: auto;">
                        <table class="data-table" id="toolsTable">
                            <thead>
                                <tr>
                                    <th>Nama Alat & Kategori</th>
                                    <th style="text-align:center;">Stok Tersedia</th>
                                    <th style="width:140px; text-align:center;">Jumlah Dipinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupedTools as $item)
                                <tr class="tool-row">
                                    <td>
                                        <div class="fw-700 text-slate-800" style="font-size:14px;">{{ $item['name'] }}</div>
                                        <div class="flex gap-2 mt-1">
                                            <span class="badge badge-purple">{{ $item['category'] }}</span>
                                            <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $item['code'] }}</code>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-success" style="font-size:13px; padding: 4px 10px;">
                                            <i class="fas fa-boxes-stacked me-1"></i> {{ $item['stock_available'] }} Ready
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="number" 
                                               name="quantities[{{ $item['id'] }}]" 
                                               class="form-control qty-input" 
                                               min="0" 
                                               max="{{ $item['stock_available'] }}" 
                                               value="{{ old('quantities.'.$item['id'], 0) }}" 
                                               oninput="validateAndCalculateTotal()"
                                               style="text-align:center; font-weight:700; color:#1e293b;">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                        Tidak ada stok alat yang tersedia untuk dipinjam saat ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Target Assignment Form -->
            <div class="card" style="height: fit-content;">
                <div class="card-header">
                    <i class="fas fa-clipboard-user text-primary"></i>
                    <span class="card-title">Informasi Peminjaman</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Peminjam / Worker / Tukang <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="borrower_name" 
                               class="form-control" 
                               placeholder="Ketik nama peminjam (misal: Pak Joko)..." 
                               value="{{ old('borrower_name') }}" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Pekerjaan / Site <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="location_name" 
                               class="form-control" 
                               placeholder="Ketik lokasi (misal: Gedung B Lantai 3)..." 
                               value="{{ old('location_name') }}" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gudang Asal Alat</label>
                        <select name="warehouse_id" id="warehouseSelect" class="form-control" onchange="filterByWarehouse(this.value)">
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
                        <label class="form-label">Estimasi Tanggal Kembali</label>
                        <input type="date" name="expected_return_at" class="form-control" value="{{ old('expected_return_at') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Tujuan / Keperluan Peminjaman</label>
                        <textarea name="purpose" class="form-control" rows="2" placeholder="Pekerjaan spesifik / lokasi site...">{{ old('purpose') }}</textarea>
                    </div>

                    <div class="flex gap-2" style="flex-direction:column;">
                        <button type="submit" class="btn btn-primary w-full justify-center" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Ajukan Peminjaman (Menunggu Persetujuan Admin)
                        </button>
                        <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary w-full justify-center">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        function validateAndCalculateTotal() {
            let total = 0;
            const inputs = document.querySelectorAll('.qty-input');
            inputs.forEach(input => {
                let max = parseInt(input.getAttribute('max')) || 0;
                let val = parseInt(input.value) || 0;

                if (val < 0) val = 0;
                if (val > max) val = max;

                input.value = val;
                total += val;
            });

            document.getElementById('totalItemBadge').innerText = 'Total: ' + total + ' Unit Alat Dipinjam';
        }

        function filterTools() {
            const input = document.getElementById('toolSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#toolsTable tbody tr.tool-row');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Initialize total on load
        document.addEventListener('DOMContentLoaded', validateAndCalculateTotal);
    </script>
</x-app-layout>
