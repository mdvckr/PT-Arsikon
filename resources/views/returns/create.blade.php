<x-app-layout>
    <x-slot name="title">Form Pengembalian Material (Return)</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('returns.index') }}">Pengembalian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Form Pengembalian Baru</span>
    </div>

    <form method="POST" action="{{ route('returns.store') }}">
        @csrf

        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Informasi Utama --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-undo-alt text-primary"></i> <span class="card-title">Informasi Pengembalian</span>
                    </div>
                    <div class="card-body">
                        <div class="text-muted mb-3" style="font-size:13px;">
                            Pengembalian sisa material atau barang rusak dari proyek kembali ke Gudang Pusat.
                        </div>

                        <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label class="form-label">Alasan Pengembalian <span class="text-danger">*</span></label>
                                <select name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                                    <option value="excess" {{ old('reason') == 'excess' ? 'selected' : '' }}>Kelebihan Material / Sisa Proyek</option>
                                    <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Material Rusak / Cacat</option>
                                    <option value="wrong_item" {{ old('reason') == 'wrong_item' ? 'selected' : '' }}>Salah Kirim Material</option>
                                    <option value="project_complete" {{ old('reason') == 'project_complete' ? 'selected' : '' }}>Proyek Selesai</option>
                                    <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('reason')
                                    <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label">Tanggal Pengembalian <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="form-control @error('return_date') is-invalid @enderror" required>
                                @error('return_date')
                                    <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Item Material --}}
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <div>
                            <i class="fas fa-boxes-stacked text-primary"></i> <span class="card-title">Item Material yang Dikembalikan</span>
                        </div>
                        <span id="item-count-badge" class="badge badge-primary">1 item</span>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:45%;">Material <span class="text-muted fw-normal" style="font-size:11px;">(Stok Aktif Gudang)</span></th>
                                    <th style="width:20%;">Jumlah (Qty)</th>
                                    <th style="width:25%;">Kondisi</th>
                                    <th style="width:10%;text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="return-items-body">
                                <tr id="row-0">
                                    <td>
                                        <select name="items[0][material_id]" class="form-control material-select" required>
                                            <option value="">-- Pilih Gudang Proyek Dulu --</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control qty-input" min="1" step="1" placeholder="Qty" required>
                                    </td>
                                    <td>
                                        <select name="items[0][condition]" class="form-control" required>
                                            <option value="good">Baik (Dapat Dipakai)</option>
                                            <option value="damaged">Rusak (Perlu Perbaikan)</option>
                                            <option value="unusable">Hancur / Afkir</option>
                                        </select>
                                    </td>
                                    <td style="text-align:right;">
                                        <button type="button" class="btn btn-sm btn-danger btn-icon" disabled onclick="removeRow(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body border-top" style="padding:12px 16px;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">
                            <i class="fas fa-plus"></i> Tambah Material
                        </button>
                    </div>
                </div>

                {{-- Catatan Tambahan --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-sticky-note text-primary"></i> <span class="card-title">Catatan Tambahan</span>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan khusus pengembalian jika ada (opsional)...">{{ old('notes') }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Panel Gudang & Action --}}
            <div class="card" style="position:sticky;top:20px;">
                <div class="card-header">
                    <i class="fas fa-warehouse text-primary"></i> <span class="card-title">Gudang Asal & Tujuan</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Dari Gudang Proyek <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" class="form-control @error('from_warehouse_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Gudang Proyek --</option>
                            @foreach($warehouses as $wh)
                                @if(!$wh->is_central)
                                    <option value="{{ $wh->id }}" {{ old('from_warehouse_id')==$wh->id ? 'selected':'' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('from_warehouse_id')
                            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Tujuan Pengembalian <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" class="form-control @error('to_warehouse_id') is-invalid @enderror" required>
                            @if($central)
                                <option value="{{ $central->id }}" selected>{{ $central->name }} (Pusat)</option>
                            @else
                                @foreach($warehouses as $wh)
                                    @if($wh->is_central)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('to_warehouse_id')
                            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                            <i class="fas fa-paper-plane"></i> Kirim Pengembalian
                        </button>
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        const warehouseInventories = @json($inventories);
        const allMaterials = @json($materials);

        const fromWhSelect = document.querySelector('select[name="from_warehouse_id"]');

        function updateBadgeCount() {
            const rowCount = document.querySelectorAll('#return-items-body tr').length;
            const badge = document.getElementById('item-count-badge');
            if (badge) {
                badge.textContent = `${rowCount} item`;
            }
        }

        function populateMaterialDropdown(selectElement) {
            const whId = fromWhSelect.value;
            selectElement.innerHTML = '';

            if (!whId) {
                selectElement.innerHTML = '<option value="">-- Pilih Gudang Proyek Dulu --</option>';
                return;
            }

            const items = warehouseInventories[whId] || [];
            if (items.length === 0) {
                selectElement.innerHTML = '<option value="">-- Tidak ada stok material di gudang ini --</option>';
                return;
            }

            selectElement.innerHTML = '<option value="">-- Pilih Material --</option>';
            items.forEach(inv => {
                const mat = inv.material;
                if (!mat) return;
                const opt = document.createElement('option');
                opt.value = mat.id;
                const qtyStr = Math.round(parseFloat(inv.quantity));
                const unitStr = mat.unit ? mat.unit.abbreviation : '';
                opt.textContent = `${mat.name} (Tersedia: ${qtyStr} ${unitStr})`;
                opt.dataset.max = qtyStr;
                selectElement.appendChild(opt);
            });
        }

        function updateAllMaterialDropdowns() {
            document.querySelectorAll('.material-select').forEach(select => {
                populateMaterialDropdown(select);
            });
        }

        fromWhSelect.addEventListener('change', updateAllMaterialDropdowns);

        let rowIdx = 1;
        function addRow() {
            const tbody = document.getElementById('return-items-body');
            const row0 = document.getElementById('row-0');
            const newRow = row0.cloneNode(true);
            newRow.id = 'row-' + rowIdx;
            
            const matSelect = newRow.querySelector('.material-select');
            matSelect.name = `items[${rowIdx}][material_id]`;
            populateMaterialDropdown(matSelect);

            const qtyInput = newRow.querySelector('input.qty-input');
            qtyInput.name = `items[${rowIdx}][quantity]`;
            qtyInput.value = '';

            newRow.querySelector('select[name^="items[0][condition]"]').name = `items[${rowIdx}][condition]`;
            
            const btn = newRow.querySelector('button');
            btn.disabled = false;
            
            tbody.appendChild(newRow);
            rowIdx++;
            updateBadgeCount();
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            updateBadgeCount();
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateAllMaterialDropdowns();
            updateBadgeCount();
        });
    </script>
    @endpush
</x-app-layout>


