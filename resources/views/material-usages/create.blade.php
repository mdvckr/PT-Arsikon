<x-app-layout>
    <x-slot name="title">Catat Pengeluaran Material Lapangan</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Catat Pengeluaran Baru</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <i class="fas fa-triangle-exclamation"></i>
        <div>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('material-usages.store') }}" id="usageForm" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Menyimpan...'; }">
        @csrf

        <div class="grid grid-3 mb-4" style="grid-template-columns: 1.8fr 1fr; gap: 20px;">
            <!-- Left Card: Material Items to Release -->
            <div class="card">
                <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-boxes-packing text-primary"></i>
                        <span class="card-title">Daftar Material yang Dikeluarkan</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border text-primary fw-600" onclick="addRow()">
                        <i class="fas fa-plus"></i> Tambah Material
                    </button>
                </div>

                <div class="card-body" style="padding:16px;">
                    <div class="table-wrap">
                        <table class="data-table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:240px;">Material <span class="text-danger">*</span></th>
                                    <th style="width:120px;text-align:center;">Stok Gudang</th>
                                    <th style="width:140px;text-align:center;">Jumlah Keluar <span class="text-danger">*</span></th>
                                    <th style="min-width:140px;">Keterangan Item</th>
                                    <th style="width:50px;text-align:center;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Row template injected via JS -->
                            </tbody>
                        </table>
                    </div>

                    @if(empty($materialsData))
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada material dengan stok tersedia (> 0) di <strong>{{ $selectedWarehouse?->name }}</strong>. Pastikan stok sudah diterima atau dipindahkan terlebih dahulu.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Card: Form Details -->
            <div class="card" style="height:fit-content;">
                <div class="card-header">
                    <i class="fas fa-clipboard-list text-primary"></i>
                    <span class="card-title">Informasi Pengeluaran</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Sumber <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouseSelect" class="form-control" onchange="window.location.href='{{ route('material-usages.create') }}?warehouse_id=' + this.value">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $selectedWarehouse?->id == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                            @endforeach
                        </select>
                        <div class="text-muted" style="font-size:11px;margin-top:3px;">
                            * Mengganti gudang akan memuat stok material di gudang tersebut.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tarik dari Nomor Permintaan (MR) <span class="text-muted" style="font-weight:400;">(Opsional)</span></label>
                        <select name="material_request_id" id="mrSelect" class="form-control" onchange="autoFillFromMR(this)">
                            <option value="">-- Pilih Permintaan Material (Disetujui) --</option>
                            @foreach($approvedMRs as $mr)
                            <option value="{{ $mr->id }}" data-items='@json($mr->items)'>
                                {{ $mr->request_number }} - {{ $mr->toWarehouse?->name ?? 'Gudang' }} ({{ $mr->items->count() }} item)
                            </option>
                            @endforeach
                        </select>
                        <div class="text-muted" style="font-size:11px;margin-top:3px;">
                            Memilih MR akan mengisi rincian material yang disetujui secara otomatis.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Penerima / Mandor / Tukang <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_name" class="form-control"
                               placeholder="Contoh: Pak Supri (Mandor Besi)"
                               value="{{ old('recipient_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pekerjaan / Zona Lapangan <span class="text-muted" style="font-weight:400;">(Disarankan)</span></label>
                        <input type="text" name="job_section" class="form-control"
                               placeholder="Contoh: Pengecoran Kolom Lt. 2 Zona Barat"
                               value="{{ old('job_section') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="usage_date" class="form-control"
                               value="{{ old('usage_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan bon/surat perintah kerja...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-2" style="flex-direction:column;">
                        <button type="submit" class="btn btn-primary w-full justify-center" id="submitBtn">
                            <i class="fas fa-check-circle"></i> Simpan & Potong Stok
                        </button>
                        <a href="{{ route('material-usages.index') }}" class="btn btn-secondary w-full justify-center">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Material Data for Client-Side Dropdown -->
    <script>
        const availableMaterials = @json($materialsData);

        let rowIndex = 0;

        function addRow(preselectedId = null, qtyVal = '') {
            const tbody = document.getElementById('itemsBody');
            const rowId = 'row-' + rowIndex;

            let optionsHtml = '<option value="">-- Pilih Material --</option>';
            availableMaterials.forEach(m => {
                const selected = preselectedId && preselectedId == m.id ? 'selected' : '';
                optionsHtml += `<option value="${m.id}" data-stock="${m.stock}" data-unit="${m.unit}" ${selected}>${m.name} (${m.code}) - Stok: ${m.stock} ${m.unit}</option>`;
            });

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.innerHTML = `
                <td>
                    <select name="items[${rowIndex}][material_id]" class="form-control material-select" required onchange="onMaterialChange(this, '${rowId}')">
                        ${optionsHtml}
                    </select>
                </td>
                <td style="text-align:center;">
                    <span class="stock-badge badge badge-gray" id="${rowId}-stock">-</span>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="number" name="items[${rowIndex}][quantity]" id="${rowId}-qty" class="form-control qty-input text-center"
                               step="0.01" min="0.01" placeholder="0" value="${qtyVal}" required oninput="validateQty('${rowId}')">
                        <span class="unit-label text-muted" id="${rowId}-unit" style="font-size:12px;min-width:30px;">-</span>
                    </div>
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Keterangan (opsional)">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeRow('${rowId}')" title="Hapus Item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;

            if (preselectedId) {
                const sel = tr.querySelector('.material-select');
                onMaterialChange(sel, rowId);
            }
        }

        function onMaterialChange(select, rowId) {
            const selectedOpt = select.options[select.selectedIndex];
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');

            if (selectedOpt && selectedOpt.value) {
                const stock = parseFloat(selectedOpt.getAttribute('data-stock') || 0);
                const unit = selectedOpt.getAttribute('data-unit') || '';

                stockBadge.className = 'stock-badge badge badge-info';
                stockBadge.innerText = stock + ' ' + unit;
                unitLabel.innerText = unit;
                qtyInput.max = stock;
            } else {
                stockBadge.className = 'stock-badge badge badge-gray';
                stockBadge.innerText = '-';
                unitLabel.innerText = '-';
                qtyInput.removeAttribute('max');
            }
            validateQty(rowId);
        }

        function validateQty(rowId) {
            const qtyInput = document.getElementById(rowId + '-qty');
            const max = parseFloat(qtyInput.max || 0);
            const val = parseFloat(qtyInput.value || 0);

            if (max > 0 && val > max) {
                qtyInput.classList.add('is-invalid');
                qtyInput.title = 'Jumlah melebihi stok tersedia (' + max + ')';
            } else {
                qtyInput.classList.remove('is-invalid');
                qtyInput.removeAttribute('title');
            }
        }

        function removeRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
            }
            if (document.getElementById('itemsBody').children.length === 0) {
                addRow();
            }
        }

        function autoFillFromMR(select) {
            const selectedOpt = select.options[select.selectedIndex];
            const itemsData = selectedOpt.getAttribute('data-items');
            if (!itemsData) return;

            try {
                const items = JSON.parse(itemsData);
                const tbody = document.getElementById('itemsBody');
                tbody.innerHTML = ''; // Clear existing rows

                items.forEach(it => {
                    const materialId = it.material_id;
                    const qty = parseFloat(it.qty_approved || it.quantity || 1);
                    addRow(materialId, qty);
                });
            } catch (e) {
                console.error(e);
            }
        }

        // Initialize with 1 empty row on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (availableMaterials.length > 0) {
                addRow();
            }
        });
    </script>
</x-app-layout>
