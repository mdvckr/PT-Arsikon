<x-app-layout>
    <x-slot name="title">Catat Penerimaan Barang</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Catat Baru</span>
    </div>

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 360px;gap:20px;align-items:start;">

            {{-- ── Kiri: Daftar Item ── --}}
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
                                    <th style="width:38%;">Material</th>
                                    <th style="width:15%;">Qty Diterima</th>
                                    <th style="width:10%;">Satuan</th>
                                    <th style="width:24%;">Harga Satuan (Rp)</th>
                                    <th style="width:13%;">Subtotal</th>
                                    <th style="width:5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Diisi oleh JS (addRow) atau prefill dari PO --}}
                            </tbody>
                        </table>
                    </div>
                    {{-- Total --}}
                    <div style="padding:12px 20px;border-top:1px solid #f1f5f9;text-align:right;">
                        <span class="text-muted" style="font-size:13px;margin-right:12px;">Total Nilai:</span>
                        <strong id="grandTotal" style="font-size:16px;color:#2563eb;">Rp 0</strong>
                    </div>
                </div>
            </div>

            {{-- ── Kanan: Info Penerimaan ── --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i>
                    <span class="card-title">Informasi Penerimaan</span>
                </div>
                <div class="card-body">

                    {{-- Link ke PO (opsional) --}}
                    <div class="mb-3">
                        <label class="form-label">Purchase Order (Opsional)</label>
                        <select id="poSelect" name="purchase_order_id" class="form-control" onchange="loadPoItems(this.value)">
                            <option value="">— Tanpa PO / Pembelian Langsung —</option>
                            @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}">
                                {{ $po->po_number }} — {{ $po->supplier?->name }}
                                @if($po->status === 'partial_received') (Sebagian Diterima) @endif
                            </option>
                            @endforeach
                        </select>
                        <div class="text-muted mt-1" style="font-size:12px;">
                            <i class="fas fa-info-circle"></i> Pilih PO untuk mengisi item secara otomatis.
                        </div>
                    </div>

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <input type="text" 
                            list="supplierOptions" 
                            name="supplier_name" 
                            id="supplierInput" 
                            class="form-control @error('supplier_name') is-invalid @enderror" 
                            value="{{ old('supplier_name') }}" 
                            placeholder="Pilih atau ketik supplier..." 
                            required>
                        <datalist id="supplierOptions">
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->name }}">
                            @endforeach
                        </datalist>
                        @error('supplier_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gudang Tujuan --}}
                    <div class="mb-3">
                        <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                                @if($wh->is_central) <span>(Pusat)</span> @else <span>(Proyek)</span> @endif
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tanggal Terima --}}
                    <div class="mb-3">
                        <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                        <input type="date" name="received_at"
                            value="{{ old('received_at', date('Y-m-d')) }}"
                            class="form-control" required>
                    </div>

                    {{-- No. Invoice --}}
                    <div class="mb-3">
                        <label class="form-label">No. Invoice / Nota</label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                            class="form-control" placeholder="INV-…">
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Catatan tambahan…">{{ old('notes') }}</textarea>
                    </div>

                    <div id="poInfoBanner" class="mb-3" style="display:none;padding:10px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe;font-size:13px;color:#1d4ed8;">
                        <i class="fas fa-link"></i>
                        <strong id="poBannerText"></strong>
                    </div>

                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-save"></i> Simpan sebagai Draft
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
    const materials = @json($materialsJson);
    let rowCount = 0;

    // ── Render baris item ───────────────────────────────────────────────────
    function addRow(opts = {}) {
        const idx = rowCount++;
        const matOptions = materials.map(m =>
            `<option value="${m.id}" data-price="${m.price ?? 0}" data-abbr="${m.abbr ?? ''}"
                ${opts.material_id == m.id ? 'selected' : ''}>
                ${m.name} (${m.code ?? ''})
            </option>`
        ).join('');

        const poItemInput = opts.purchase_order_item_id
            ? `<input type="hidden" name="items[${idx}][purchase_order_item_id]" value="${opts.purchase_order_item_id}">`
            : '';

        const qty   = opts.qty ?? '';
        const price = opts.price ?? '';

        const row = `
        <tr id="row-${idx}">
            <td>
                ${poItemInput}
                <select name="items[${idx}][material_id]" class="form-control" required
                    onchange="onMaterialChange(this, ${idx})">
                    <option value="">Pilih Material</option>
                    ${matOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][quantity]" class="form-control qty-input"
                    value="${qty}" min="0.01" step="0.01" required
                    data-idx="${idx}" onchange="recalcRow(${idx})">
            </td>
            <td>
                <span class="unit-label text-muted" id="unit-${idx}" style="font-size:13px;">
                    ${opts.unit_abbr ?? '—'}
                </span>
            </td>
            <td>
                <input type="number" name="items[${idx}][unit_price]" class="form-control price-input"
                    value="${price}" min="0" step="1" required
                    data-idx="${idx}" onchange="recalcRow(${idx})">
            </td>
            <td>
                <span id="subtotal-${idx}" class="fw-600" style="font-size:13px;">Rp 0</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger btn-icon"
                    onclick="removeRow(${idx})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);

        if (qty && price) recalcRow(idx);
    }

    function removeRow(idx) {
        const row = document.getElementById('row-' + idx);
        if (row) row.remove();
        updateGrandTotal();
    }

    function onMaterialChange(sel, idx) {
        const opt = sel.options[sel.selectedIndex];
        const price = opt.getAttribute('data-price') ?? 0;
        const abbr  = opt.getAttribute('data-abbr') ?? '—';
        document.querySelector(`[name="items[${idx}][unit_price]"]`).value = price;
        document.getElementById('unit-' + idx).textContent = abbr;
        recalcRow(idx);
    }

    function recalcRow(idx) {
        const qty   = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value) || 0;
        const price = parseFloat(document.querySelector(`[name="items[${idx}][unit_price]"]`)?.value) || 0;
        const sub   = qty * price;
        const el = document.getElementById('subtotal-' + idx);
        if (el) el.textContent = 'Rp ' + sub.toLocaleString('id-ID');
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
            const val = el.textContent.replace(/[^0-9]/g, '');
            total += parseInt(val) || 0;
        });
        document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    // ── Load items dari PO via AJAX ────────────────────────────────────────
    function loadPoItems(poId) {
        const banner = document.getElementById('poInfoBanner');
        const bannerText = document.getElementById('poBannerText');
        const supplierSel = document.getElementById('supplierSelect');

        if (!poId) {
            banner.style.display = 'none';
            return;
        }

        fetch(`{{ url('/goods-receipts/po-items') }}/${poId}`)
            .then(r => r.json())
            .then(data => {
                // Pilih supplier secara otomatis
                if (data.supplier_id) {
                    supplierSel.value = data.supplier_id;
                }

                // Clear & re-populate items
                document.getElementById('itemsBody').innerHTML = '';
                rowCount = 0;

                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        addRow({
                            material_id: item.material_id,
                            purchase_order_item_id: item.purchase_order_item_id,
                            qty: item.qty_remaining,
                            price: item.unit_price,
                            unit_abbr: item.unit_abbr,
                        });
                    });
                    banner.style.display = '';
                    bannerText.textContent =
                        `PO terpilih: ${data.items.length} item dari ${data.supplier_name}. Periksa qty sebelum menyimpan.`;
                } else {
                    banner.style.display = '';
                    bannerText.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;"></i> Semua item PO ini sudah diterima penuh.';
                }
            })
            .catch(() => alert('Gagal memuat item PO. Silakan coba lagi.'));
    }

    // Mulai dengan 1 baris kosong
    addRow();
    </script>
    @endpush
</x-app-layout>
