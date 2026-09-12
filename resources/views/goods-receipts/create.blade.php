<x-app-layout>
    <x-slot name="title">Catat Penerimaan Barang</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Catat Baru</span>
    </div>

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            {{-- Left: Items --}}
            <div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-list text-primary"></i>
                        <span class="card-title">Daftar Item Barang</span>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width:40%;">Material</th>
                                    <th style="width:20%;">Qty</th>
                                    <th style="width:25%;">Harga Satuan (Rp)</th>
                                    <th style="width:10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="row-0">
                                    <td>
                                        <select name="items[0][material_id]" class="form-control" required>
                                            <option value="">Pilih Material</option>
                                            @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}" data-price="{{ $mat->unit_price }}">
                                                {{ $mat->code }} - {{ $mat->name }} ({{ $mat->unit?->abbreviation }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                                    <td><input type="number" name="items[0][unit_price]" class="form-control" min="0" step="100" value="0" required></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeRow(0)" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Header Info --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i>
                    <span class="card-title">Informasi Penerimaan</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                        <input type="date" name="received_at" value="{{ old('received_at', date('Y-m-d')) }}"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Invoice</label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                            class="form-control" placeholder="INV-...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-save"></i> Simpan Penerimaan
                    </button>
                    <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary w-full mt-2" style="justify-content:center;">
                        Batal
                    </a>
                </div>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        let rowCount = 1;
        const materials = @json($materialsJson);

        function buildSelect(idx) {
            let opts = '<option value="">Pilih Material</option>';
            materials.forEach(m => {
                opts += `<option value="${m.id}" data-price="${m.price}">${m.code} - ${m.name} (${m.abbr ?? ''})</option>`;
            });
            return `<select name="items[${idx}][material_id]" class="form-control" required onchange="setPrice(this, ${idx})">${opts}</select>`;
        }

        function setPrice(sel, idx) {
            const price = sel.selectedOptions[0]?.dataset?.price ?? 0;
            document.querySelector(`input[name="items[${idx}][unit_price]"]`).value = price;
        }

        function addRow() {
            const idx = rowCount++;
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.id = `row-${idx}`;
            tr.innerHTML = `
                <td>${buildSelect(idx)}</td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                <td><input type="number" name="items[${idx}][unit_price]" class="form-control" min="0" step="100" value="0" required></td>
                <td><button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeRow(${idx})"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
        }

        function removeRow(idx) {
            const row = document.getElementById(`row-${idx}`);
            if (row) row.remove();
        }

        // Auto-fill price on first row
        document.querySelector('#itemsBody select').addEventListener('change', function() {
            setPrice(this, 0);
        });
    </script>
    @endpush
</x-app-layout>
