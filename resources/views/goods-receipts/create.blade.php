<x-app-layout>
    <x-slot name="title">Catat Penerimaan Barang</x-slot>

    @push('styles')
    <style>
        .receipt-create-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }

        .receipt-items-table th {
            padding: 8px 10px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .receipt-items-table td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
        }

        .receipt-items-table .form-control {
            height: 38px !important;
            padding: 6px 10px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .receipt-items-table .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .sidebar-receipt .form-control {
            height: 36px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .sidebar-receipt .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .sidebar-receipt textarea.form-control {
            height: auto !important;
            min-height: 64px !important;
            padding: 8px 10px !important;
        }

        .btn-delete-row {
            width: 32px !important;
            height: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            color: #dc2626 !important;
            background: #ffffff !important;
            border: 1px solid #fecaca !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            font-size: 11px !important;
        }

        .btn-delete-row:hover {
            background: #fef2f2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
            transform: scale(1.05) !important;
        }

        @media (max-width: 992px) {
            .receipt-create-layout {
                grid-template-columns: 1fr;
            }
            .sidebar-sticky-box {
                position: static !important;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Catat Penerimaan Baru</span>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-truck-ramp-box text-primary" style="font-size:15px;"></i>
                Catat Penerimaan Barang Masuk
            </h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;margin-bottom:0;">
                Input surat jalan / tanda terima barang dari supplier atau purchase order
            </p>
        </div>
        <a href="{{ route('goods-receipts.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-exclamation"></i>
        <div>
            <strong>Terdapat kesalahan pengisian data:</strong>
            <ul style="margin:2px 0 0 16px;padding:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm">
        @csrf
        <div class="receipt-create-layout">

            {{-- ── Kiri: Daftar Item Barang Masuk ── --}}
            <div>
                <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                            <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Rincian Item Barang Diterima</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addRow()" style="border-radius:6px;font-size:11.5px;padding:4px 10px;font-weight:600;">
                            <i class="fas fa-plus me-1"></i> Tambah Item
                        </button>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table receipt-items-table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:220px;">Material / Barang <span class="text-danger">*</span></th>
                                    <th style="width:110px;text-align:center;">Qty Diterima <span class="text-danger">*</span></th>
                                    <th style="width:75px;text-align:center;">Satuan</th>
                                    <th style="width:145px;text-align:right;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                    <th style="width:145px;text-align:right;">Subtotal</th>
                                    <th style="width:40px;text-align:center;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Diisi oleh JS addRow() atau prefill AJAX dari PO --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Total Summary Strip --}}
                    <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                        <div style="font-size:11.5px;color:#64748b;">
                            Total: <strong id="totalItemCount" style="color:#0f172a;">0</strong> jenis item
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:12px;font-weight:600;color:#475569;">Total Nilai:</span>
                            <strong id="grandTotal" style="font-size:15px;color:#2563eb;font-weight:800;">Rp 0</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Kanan: Form Informasi Penerimaan (Sidebar) ── --}}
            <div class="sidebar-receipt sidebar-sticky-box" style="position:sticky;top:20px;">
                <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-file-invoice text-primary" style="font-size:12.5px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Penerimaan</span>
                    </div>
                    <div class="card-body" style="padding:12px 14px;">

                        {{-- Purchase Order (Opsional) --}}
                        <div class="mb-2">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Hubungkan Purchase Order (PO) <span class="text-muted fw-400">(Opsional)</span>
                            </label>
                            <select id="poSelect" name="purchase_order_id" class="form-control" onchange="loadPoItems(this.value)">
                                <option value="">— Tanpa PO (Pembelian Langsung) —</option>
                                @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                    #{{ $po->po_number }} • {{ $po->supplier?->name }}
                                    @if($po->status === 'partial_received') (Sebagian Diterima) @endif
                                </option>
                                @endforeach
                            </select>
                            <div class="text-muted mt-1" style="font-size:10.5px;">
                                <i class="fas fa-circle-info text-primary"></i> Pilih nomor PO untuk mengisi otomatis item & supplier.
                            </div>
                        </div>

                        {{-- Banner Info PO Terpilih --}}
                        <div id="poInfoBanner" class="mb-2" style="display:none;padding:7px 10px;background:#eff6ff;border-radius:6px;border:1px solid #bfdbfe;font-size:11px;color:#1d4ed8;line-height:1.4;">
                            <i class="fas fa-link me-1"></i>
                            <span id="poBannerText"></span>
                        </div>

                        {{-- Supplier --}}
                        <div class="mb-2">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Supplier <span class="text-danger">*</span>
                            </label>
                            <select name="supplier_id" id="supplierSelect" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                <option value="">— Pilih Supplier —</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Gudang Tujuan --}}
                        <div class="mb-2">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Gudang Tujuan <span class="text-danger">*</span>
                            </label>
                            <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" required>
                                <option value="">— Pilih Gudang —</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Tanggal Terima --}}
                        <div class="mb-2">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Tanggal Terima <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="received_at"
                                value="{{ old('received_at', date('Y-m-d')) }}"
                                class="form-control" required>
                        </div>

                        {{-- No. Invoice / Nota --}}
                        <div class="mb-2">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                No. Invoice / Surat Jalan Vendor
                            </label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                class="form-control" placeholder="Contoh: INV-2026-0012">
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-3">
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Catatan Penerimaan
                            </label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Keterangan pengiriman / kondisi fisik barang...">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;font-size:12px;font-weight:600;padding:8px 14px;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.25);">
                            <i class="fas fa-save me-1"></i> Simpan sebagai Draft
                        </button>
                        <a href="{{ route('goods-receipts.index') }}" class="btn btn-light border w-full mt-2" style="justify-content:center;font-size:11.5px;padding:6px 12px;border-radius:6px;color:#64748b;">
                            Batal
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
    const materials = @json($materialsJson);
    let rowCount = 0;

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
                    <option value="">— Pilih Material —</option>
                    ${matOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][quantity]" class="form-control qty-input text-center"
                    value="${qty}" min="0.01" step="0.01" placeholder="0" required
                    data-idx="${idx}" oninput="recalcRow(${idx})" style="font-weight:700;">
            </td>
            <td style="text-align:center;">
                <span class="badge" id="unit-${idx}" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;padding:3px 7px;border-radius:4px;font-weight:600;">
                    ${opts.unit_abbr ?? '—'}
                </span>
            </td>
            <td>
                <input type="number" name="items[${idx}][unit_price]" class="form-control price-input text-right"
                    value="${price}" min="0" step="1" placeholder="0" required
                    data-idx="${idx}" oninput="recalcRow(${idx})" style="text-align:right;">
            </td>
            <td style="text-align:right;">
                <span id="subtotal-${idx}" class="fw-700" style="font-size:12.5px;color:#0f172a;">Rp 0</span>
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-delete-row" onclick="removeRow(${idx})" title="Hapus Baris">
                    <i class="fas fa-trash-can"></i>
                </button>
            </td>
        </tr>`;

        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);

        if (opts.material_id) {
            const sel = document.querySelector(`[name="items[${idx}][material_id]"]`);
            if (sel) onMaterialChange(sel, idx, false);
        }

        if (qty && price) {
            recalcRow(idx);
        } else {
            updateGrandTotal();
        }
    }

    function removeRow(idx) {
        const row = document.getElementById('row-' + idx);
        if (row) row.remove();
        updateGrandTotal();
        const tbody = document.getElementById('itemsBody');
        if (tbody.children.length === 0) {
            addRow();
        }
    }

    function onMaterialChange(sel, idx, autoPrice = true) {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('unit-' + idx).textContent = '—';
            return;
        }
        const price = opt.getAttribute('data-price') ?? 0;
        const abbr  = opt.getAttribute('data-abbr') ?? '—';
        const priceInput = document.querySelector(`[name="items[${idx}][unit_price]"]`);
        if (autoPrice && priceInput && (!priceInput.value || parseFloat(priceInput.value) === 0)) {
            priceInput.value = price;
        }
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
        let count = 0;
        document.querySelectorAll('#itemsBody tr').forEach(tr => {
            const subEl = tr.querySelector('[id^="subtotal-"]');
            if (subEl) {
                const val = subEl.textContent.replace(/[^0-9]/g, '');
                total += parseInt(val) || 0;
                count++;
            }
        });
        document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
        const countEl = document.getElementById('totalItemCount');
        if (countEl) countEl.textContent = count;
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
                if (data.supplier_id && supplierSel) {
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
                    banner.style.display = 'block';
                    bannerText.textContent =
                        `PO Terpilih: ${data.items.length} item dari ${data.supplier_name}. Silakan periksa kuantitas sebelum menyimpan.`;
                } else {
                    banner.style.display = 'block';
                    bannerText.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;"></i> Seluruh item pada PO ini telah diterima secara penuh.';
                }
            })
            .catch(() => alert('Gagal memuat rincian item PO. Silakan coba lagi.'));
    }

    // Mulai dengan 1 baris kosong
    addRow();
    </script>
    @endpush
</x-app-layout>
