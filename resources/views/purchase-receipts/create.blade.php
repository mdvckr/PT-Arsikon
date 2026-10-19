<x-app-layout>
    <x-slot name="title">Catat Nota Pembelian Harian</x-slot>

    <div class="mb-4">
        <a href="{{ route('purchase-receipts.index') }}" class="text-muted" style="font-size:13px;">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Nota
        </a>
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">Pencatatan Nota Pembelian Harian</h2>
        <p class="text-muted" style="font-size:13px;">Isi informasi nota fisik pembelian barang/material harian (Hari 1, Hari 2, dst.)</p>
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

    <div id="po_info_banner" class="alert alert-info mb-4" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;{{ $selectedPO ? 'display:flex;' : 'display:none;' }}align-items:center;justify-content:space-between;gap:8px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="fas fa-receipt" style="font-size:18px;color:#2563eb;"></i>
            <div>
                <strong style="font-size:13px;display:block;">Terkait Purchase Order: <span id="po_banner_num">{{ $selectedPO?->po_number }}</span></strong>
                <span id="po_banner_text" style="font-size:11.5px;color:#1d4ed8;">
                    Rincian barang terisi otomatis berdasarkan PO. Anda dapat menyesuaikan kuantitas atau harga sesuai fisik nota jika diperlukan.
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('purchase-receipts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card mb-4" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Data Nota Pembelian</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label font-semibold">Terkait Purchase Order (PO)</label>
                    <select name="purchase_order_id" id="po_id_select" class="form-control" onchange="handlePoChange(this)">
                        <option value="">-- Tanpa PO (Nota Pembelian Langsung) --</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}" 
                                {{ (old('purchase_order_id', request('po_id')) == $po->id) ? 'selected' : '' }}>
                                {{ $po->po_number }} - {{ $po->supplier?->name ?? $po->supplier_name ?? 'Supplier' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold">Proyek (Opsional)</label>
                    <input type="text" name="project_name" id="project_name_input" value="{{ old('project_name', $defaultProjectName ?? '') }}" placeholder="Ketik nama proyek..." class="form-control">
                </div>

                <div>
                    <label class="form-label font-semibold">Supplier / Toko Pembelian</label>
                    <input type="text" name="supplier_name" id="supplier_name_input" value="{{ old('supplier_name', $defaultSupplierName ?? '') }}" placeholder="Ketik nama toko / supplier..." class="form-control">
                </div>

                <div>
                    <label class="form-label font-semibold">Tanggal Nota <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receipt_date_input" value="{{ old('receipt_date', date('Y-m-d')) }}" class="form-control" required>
                </div>

                <div>
                    <label class="form-label font-semibold">Nomor Nota Physical / Faktur</label>
                    <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Contoh: NOTA-8812 / F-001" class="form-control">
                </div>

                <div>
                    <label class="form-label font-semibold">Status Pembayaran Nota <span class="text-danger">*</span></label>
                    <select name="payment_status" class="form-control" required>
                        <option value="unpaid" {{ old('payment_status') === 'unpaid' ? 'selected' : '' }}>⏳ Belum Dibayar</option>
                        <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>✅ Sudah Dibayar</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="form-label font-semibold">Upload Foto / Scan Nota Fisik</label>
                    <input type="file" name="image" accept="image/*,application/pdf" class="form-control" style="padding:6px;">
                    <small class="text-muted block mt-1">Format: JPG, PNG, WEBP, PDF. Max: 10MB (Foto otomatis dikompres hemat penyimpanan).</small>
                </div>
            </div>
        </div>

        <!-- Section Item Pembelian -->
        <div class="card mb-4" style="padding:20px;">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h3 style="font-size:16px;font-weight:600;color:#1e293b;">Rincian Barang / Material yang Dibeli</h3>
                    <p class="text-muted" style="font-size:12px;">Tambahkan atau sesuaikan baris barang sesuai nota fisik.</p>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addItemRow()">
                    <i class="fas fa-plus me-1"></i> Tambah Baris Barang
                </button>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="items-table">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Material / Nama Barang <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Jumlah <span class="text-danger">*</span></th>
                            <th style="width: 20%;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 20%;">Subtotal (Rp)</th>
                            <th style="width: 8%;">Catatan</th>
                            <th style="width: 5%; text-align:center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @if(old('items'))
                            @foreach(old('items') as $idx => $it)
                                @php
                                    $qty = (float)($it['quantity'] ?? 1);
                                    $price = (float)($it['unit_price'] ?? 0);
                                    $subtotal = $qty * $price;
                                @endphp
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $it['material_id'] ?? '' }}">
                                        <input type="text" name="items[{{ $idx }}][item_name]" value="{{ $it['item_name'] ?? '' }}" placeholder="Ketik nama barang / material..." class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $qty }}" class="form-control qty-input" oninput="calculateRow(this)" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="{{ $price }}" class="form-control price-input" oninput="calculateRow(this)" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp {{ number_format($subtotal, 0, ',', '.') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][notes]" value="{{ $it['notes'] ?? '' }}" placeholder="Keterangan..." class="form-control text-xs">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($selectedPO && $selectedPO->items->isNotEmpty())
                            @foreach($selectedPO->items as $idx => $poItem)
                                @php
                                    $qty = (float)$poItem->quantity;
                                    $price = (float)$poItem->unit_price;
                                    $subtotal = (float)$poItem->subtotal;
                                @endphp
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $poItem->material_id ?? '' }}">
                                        <input type="text" name="items[{{ $idx }}][item_name]" value="{{ $poItem->displayName() }}" placeholder="Ketik nama barang / material..." class="form-control" required>
                                        @if($poItem->displayUnit())
                                            <small class="text-muted" style="font-size:11px;">Satuan: {{ $poItem->displayUnit() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $qty }}" class="form-control qty-input" oninput="calculateRow(this)" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="{{ $price }}" class="form-control price-input" oninput="calculateRow(this)" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp {{ number_format($subtotal, 0, ',', '.') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][notes]" value="{{ $poItem->notes ?? '' }}" placeholder="Keterangan..." class="form-control text-xs">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="item-row">
                                <td>
                                    <input type="hidden" name="items[0][material_id]" value="">
                                    <input type="text" name="items[0][item_name]" placeholder="Ketik nama barang / material..." class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="form-control qty-input" oninput="calculateRow(this)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][unit_price]" value="0" class="form-control price-input" oninput="calculateRow(this)" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp 0">
                                </td>
                                <td>
                                    <input type="text" name="items[0][notes]" placeholder="Keterangan..." class="form-control text-xs">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end font-bold" style="font-size:15px;padding-top:12px;">GRAND TOTAL NOTA:</td>
                            <td colspan="3" style="padding-top:12px;">
                                <strong id="grand-total-display" class="text-success" style="font-size:18px;">Rp 0</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-6">
            <a href="{{ route('purchase-receipts.index') }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                <i class="fas fa-save me-1"></i> Simpan Nota Pembelian
            </button>
        </div>
    </form>

    <script>
        const poItemsMap = @json($poItemsMap ?? []);
        let rowIndex = {{ old('items') ? count(old('items')) : ($selectedPO && $selectedPO->items->isNotEmpty() ? $selectedPO->items->count() : 1) }};

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function calculateRow(element) {
            const row = element.closest('tr');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.subtotal-display').value = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('#items-tbody .item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                total += (qty * price);
            });
            document.getElementById('grand-total-display').innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        }

        function handlePoChange(selectEl) {
            const poId = selectEl.value;
            const supplierInput = document.getElementById('supplier_name_input');
            const projectInput = document.getElementById('project_name_input');
            const banner = document.getElementById('po_info_banner');
            const bannerNum = document.getElementById('po_banner_num');
            const tbody = document.getElementById('items-tbody');

            if (poId && poItemsMap[poId]) {
                const poData = poItemsMap[poId];

                if (supplierInput && poData.supplier_name) {
                    supplierInput.value = poData.supplier_name;
                }
                if (projectInput && poData.project_name) {
                    projectInput.value = poData.project_name;
                }

                if (banner) {
                    banner.style.display = 'flex';
                    if (bannerNum) bannerNum.innerText = poData.po_number;
                }

                tbody.innerHTML = '';
                if (poData.items && poData.items.length > 0) {
                    poData.items.forEach((item, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'item-row';
                        tr.innerHTML = `
                            <td>
                                <input type="hidden" name="items[${idx}][material_id]" value="${item.material_id || ''}">
                                <input type="text" name="items[${idx}][item_name]" value="${escapeHtml(item.item_name)}" placeholder="Ketik nama barang / material..." class="form-control" required>
                                ${item.unit ? `<small class="text-muted" style="font-size:11px;">Satuan: ${escapeHtml(item.unit)}</small>` : ''}
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]" value="${item.quantity}" class="form-control qty-input" oninput="calculateRow(this)" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" value="${item.unit_price}" class="form-control price-input" oninput="calculateRow(this)" required>
                            </td>
                            <td>
                                <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp ${Math.round(item.subtotal).toLocaleString('id-ID')}">
                            </td>
                            <td>
                                <input type="text" name="items[${idx}][notes]" value="${escapeHtml(item.notes)}" placeholder="Keterangan..." class="form-control text-xs">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                    rowIndex = poData.items.length;
                } else {
                    resetSingleEmptyRow();
                }
            } else {
                if (banner) banner.style.display = 'none';
                resetSingleEmptyRow();
            }

            calculateGrandTotal();
        }

        function resetSingleEmptyRow() {
            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = `
                <tr class="item-row">
                    <td>
                        <input type="hidden" name="items[0][material_id]" value="">
                        <input type="text" name="items[0][item_name]" placeholder="Ketik nama barang / material..." class="form-control" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="form-control qty-input" oninput="calculateRow(this)" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[0][unit_price]" value="0" class="form-control price-input" oninput="calculateRow(this)" required>
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp 0">
                    </td>
                    <td>
                        <input type="text" name="items[0][notes]" placeholder="Keterangan..." class="form-control text-xs">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            rowIndex = 1;
        }

        function addItemRow() {
            const tbody = document.getElementById('items-tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="items[${rowIndex}][material_id]" value="">
                    <input type="text" name="items[${rowIndex}][item_name]" placeholder="Ketik nama barang / material..." class="form-control" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" name="items[${rowIndex}][quantity]" value="1" class="form-control qty-input" oninput="calculateRow(this)" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" value="0" class="form-control price-input" oninput="calculateRow(this)" required>
                </td>
                <td>
                    <input type="text" class="form-control subtotal-display" readonly style="background:#f8fafc;font-weight:600;" value="Rp 0">
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][notes]" placeholder="Keterangan..." class="form-control text-xs">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
            rowIndex++;
            calculateGrandTotal();
        }

        function removeRow(btn) {
            const tbody = document.getElementById('items-tbody');
            if (tbody.querySelectorAll('.item-row').length > 1) {
                btn.closest('tr').remove();
                calculateGrandTotal();
            } else {
                alert('Minimal harus mengisi 1 item barang pada nota!');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            calculateGrandTotal();
        });
    </script>
</x-app-layout>
