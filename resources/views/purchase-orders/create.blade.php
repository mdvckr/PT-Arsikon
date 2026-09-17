<x-app-layout>
    <x-slot name="title">Buat Purchase Order Baru</x-slot>

    <div class="mb-4">
        <a href="{{ route('purchase-orders.index') }}" class="text-muted" style="font-size:13px;">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar PO
        </a>
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">Buat Purchase Order (PO)</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;">
            <strong class="block font-semibold mb-1">Terjadi Kesalahan Input:</strong>
            <ul class="list-disc ms-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf

        <div class="card mb-4" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Informasi Header PO</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label font-semibold">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold">Dari Permintaan Pengadaan (PR)</label>
                    <select name="procurement_request_id" class="form-control">
                        <option value="">-- Tanpa PR (PO Langsung) --</option>
                        @foreach($approvedPRs as $pr)
                            <option value="{{ $pr->id }}" {{ (old('procurement_request_id', request('pr_id')) == $pr->id) ? 'selected' : '' }}>
                                {{ $pr->pr_number }} ({{ $pr->items->count() }} item)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold">Tanggal Order <span class="text-danger">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" class="form-control" required>
                </div>

                <div>
                    <label class="form-label font-semibold">Target Pengiriman</label>
                    <input type="date" name="expected_delivery" value="{{ old('expected_delivery') }}" class="form-control">
                </div>

                <div>
                    <label class="form-label font-semibold">Ketentuan / Terms</label>
                    <input type="text" name="terms" value="{{ old('terms', 'Net 30 Hari') }}" class="form-control" placeholder="Contoh: Net 30, COD...">
                </div>

                <div>
                    <label class="form-label font-semibold">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="form-control" placeholder="Catatan khusus...">
                </div>
            </div>
        </div>

        <div class="card mb-4" style="padding:20px;">
            <div class="flex justify-between items-center mb-3">
                <h3 style="font-size:16px;font-weight:600;color:#1e293b;">Daftar Barang yang Dipesan</h3>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addPoRow()">
                    <i class="fas fa-plus me-1"></i> Tambah Item
                </button>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Material / Barang <span class="text-danger">*</span></th>
                            <th style="width: 20%;">Jumlah <span class="text-danger">*</span></th>
                            <th style="width: 20%;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Subtotal (Rp)</th>
                            <th style="width: 5%; text-align:center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="po-items-tbody">
                        @if($selectedPR && $selectedPR->items)
                            @foreach($selectedPR->items as $idx => $prItem)
                            <tr class="po-item-row">
                                <td>
                                    <select name="items[{{ $idx }}][material_id]" class="form-control" onchange="calcPoRow(this)" required>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" {{ $prItem->material_id == $mat->id ? 'selected' : '' }}>
                                                {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $prItem->quantity }}" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="{{ $prItem->estimated_price ?? 0 }}" class="form-control po-price" oninput="calcPoRow(this)" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control po-subtotal" readonly style="background:#f8fafc;" value="Rp 0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removePoRow(this)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr class="po-item-row">
                                <td>
                                    <select name="items[0][material_id]" class="form-control" onchange="calcPoRow(this)" required>
                                        <option value="">-- Pilih Material --</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}">
                                                {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][unit_price]" value="0" class="form-control po-price" oninput="calcPoRow(this)" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control po-subtotal" readonly style="background:#f8fafc;" value="Rp 0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removePoRow(this)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-6">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                <i class="fas fa-save me-1"></i> Simpan Purchase Order
            </button>
        </div>
    </form>

    <script>
        let poRowIdx = {{ $selectedPR ? count($selectedPR->items) : 1 }};

        function calcPoRow(el) {
            const row = el.closest('tr');
            const qty = parseFloat(row.querySelector('.po-qty').value) || 0;
            const price = parseFloat(row.querySelector('.po-price').value) || 0;
            row.querySelector('.po-subtotal').value = 'Rp ' + (qty * price).toLocaleString('id-ID');
        }

        function addPoRow() {
            const tbody = document.getElementById('po-items-tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'po-item-row';
            newRow.innerHTML = `
                <td>
                    <select name="items[${poRowIdx}][material_id]" class="form-control" onchange="calcPoRow(this)" required>
                        <option value="">-- Pilih Material --</option>
                        @foreach($materials as $mat)
                            <option value="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}">
                                {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" name="items[${poRowIdx}][quantity]" value="1" class="form-control po-qty" oninput="calcPoRow(this)" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${poRowIdx}][unit_price]" value="0" class="form-control po-price" oninput="calcPoRow(this)" required>
                </td>
                <td>
                    <input type="text" class="form-control po-subtotal" readonly style="background:#f8fafc;" value="Rp 0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removePoRow(this)"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(newRow);
            poRowIdx++;
        }

        function removePoRow(btn) {
            const tbody = document.getElementById('po-items-tbody');
            if (tbody.querySelectorAll('.po-item-row').length > 1) {
                btn.closest('tr').remove();
            }
        }
    </script>
</x-app-layout>
