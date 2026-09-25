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

    @if(isset($selectedMR) && $selectedMR && $selectedMR->items->whereNull('material_id')->isNotEmpty())
    <div class="alert alert-warning mb-4" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 16px;border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:gap:8px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="fas fa-boxes-packing" style="font-size:18px;color:#d97706;"></i>
            <div>
                <strong style="font-size:13px;display:block;">Pengadaan Barang Manual untuk MR #{{ $selectedMR->request_number }}</strong>
                <span style="font-size:11.5px;color:#b45309;">Hanya <strong>item barang manual/khusus</strong> yang dimasukkan ke PO ini karena memerlukan proses pengadaan/pembelian supplier. Barang inventori gudang pusat tetap diproses melalui Surat Jalan Distribusi.</span>
            </div>
        </div>
        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:5px 10px;font-size:11px;font-weight:600;">
            MR: {{ $selectedMR->request_number }} ({{ $selectedMR->items->whereNull('material_id')->count() }} Item Manual)
        </span>
    </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf

        <div class="card mb-4" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Informasi Header PO</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label font-semibold">Supplier / Toko <span class="text-danger">*</span></label>
                    <input type="hidden" name="supplier_id" id="hidden_supplier_id" value="{{ old('supplier_id') }}">

                    <select id="supplier_select" class="form-control" onchange="handleSupplierChange(this)" required>
                        <option value="">-- Pilih Supplier --</option>
                        <optgroup label="Supplier Terdaftar">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-name="{{ $supplier->name }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Input Bebas">
                            <option value="manual" {{ old('supplier_name') && !old('supplier_id') ? 'selected' : '' }}>
                                + Ketik Nama Toko / Supplier Baru (Bebas)
                            </option>
                        </optgroup>
                    </select>

                    <div id="manual_supplier_container" class="mt-2" style="{{ old('supplier_name') && !old('supplier_id') ? 'display:block;' : 'display:none;' }}">
                        <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:8px 10px;font-size:12px;">
                            <label class="text-amber-800 font-semibold block mb-1">
                                <i class="fas fa-store me-1"></i> Nama Toko / Supplier Bebas: <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="supplier_name" id="supplier_name_input" value="{{ old('supplier_name') }}" class="form-control form-control-sm" placeholder="Contoh: Toko Bangunan Berkah, Depot Pasir Jaya...">
                        </div>
                    </div>
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
                <div>
                    <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin-bottom:2px;">Daftar Barang yang Dipesan</h3>
                    <p class="text-muted text-xs">Pilih dari Master Material terdaftar atau dari Permintaan Material (barang baru/manual proyek).</p>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addPoRow()">
                    <i class="fas fa-plus me-1"></i> Tambah Item
                </button>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th style="width: 42%;">Material / Barang <span class="text-danger">*</span></th>
                            <th style="width: 18%;">Jumlah & Satuan <span class="text-danger">*</span></th>
                            <th style="width: 18%;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 17%;">Subtotal (Rp)</th>
                            <th style="width: 5%; text-align:center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="po-items-tbody">
                        @if($selectedPR && $selectedPR->items)
                            @foreach($selectedPR->items as $idx => $prItem)
                            <tr class="po-item-row">
                                <td>
                                    <input type="hidden" name="items[{{ $idx }}][material_id]" class="po-material-id" value="{{ $prItem->material_id }}">
                                    <input type="hidden" name="items[{{ $idx }}][material_request_item_id]" class="po-mr-item-id" value="">
                                    <input type="hidden" name="items[{{ $idx }}][custom_item_name]" class="po-custom-name" value="">
                                    <input type="hidden" name="items[{{ $idx }}][custom_item_unit]" class="po-custom-unit" value="{{ $prItem->material?->unit?->symbol ?? 'unit' }}">

                                    <select class="form-control po-item-select" onchange="handleItemSelect(this)" required>
                                        <optgroup label="Master Data Material">
                                            @foreach($materials as $mat)
                                                <option value="mat:{{ $mat->id }}" data-type="material" data-id="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" data-unit="{{ $mat->unit?->symbol ?? 'unit' }}" {{ $prItem->material_id == $mat->id ? 'selected' : '' }}>
                                                    {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @if(isset($mrCustomItems) && $mrCustomItems->isNotEmpty())
                                        <optgroup label="Permintaan Material Proyek (Item Baru)">
                                            @foreach($mrCustomItems as $mrItem)
                                                <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_fulfilled) }}" data-mr-num="{{ $mrItem->materialRequest?->request_number }}" data-warehouse="{{ $mrItem->materialRequest?->fromWarehouse?->name }}">
                                                    [MR #{{ $mrItem->materialRequest?->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $mrItem->materialRequest?->fromWarehouse?->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                        <optgroup label="Input Bebas">
                                            <option value="manual:new" data-type="manual">+ Input Barang Baru Manual (Ketik Sendiri)</option>
                                        </optgroup>
                                    </select>

                                    <div class="po-extra-container mt-2" style="display:none;"></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $prItem->quantity }}" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                        <span class="po-unit-badge badge badge-secondary" style="font-size:11px;white-space:nowrap;">{{ $prItem->material?->unit?->symbol ?? 'unit' }}</span>
                                    </div>
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
                        @elseif(isset($selectedMR) && $selectedMR && $selectedMR->items->whereNull('material_id')->isNotEmpty())
                            @php
                                // HANYA barang yang diinput manual (tidak ada di inventori pusat) yang masuk ke PO
                                $mrItemsToBuy = $selectedMR->items->whereNull('material_id')->values();
                            @endphp
                            @foreach($mrItemsToBuy as $idx => $mrItem)
                            <tr class="po-item-row">
                                <td>
                                    <input type="hidden" name="items[{{ $idx }}][material_id]" class="po-material-id" value="">
                                    <input type="hidden" name="items[{{ $idx }}][material_request_item_id]" class="po-mr-item-id" value="{{ $mrItem->id }}">
                                    <input type="hidden" name="items[{{ $idx }}][custom_item_name]" class="po-custom-name" value="{{ $mrItem->custom_item_name }}">
                                    <input type="hidden" name="items[{{ $idx }}][custom_item_unit]" class="po-custom-unit" value="{{ $mrItem->custom_item_unit ?? 'unit' }}">

                                    <select class="form-control po-item-select" onchange="handleItemSelect(this)" required>
                                        <optgroup label="Permintaan Material Proyek (Item Manual)">
                                            <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_fulfilled) }}" data-mr-num="{{ $selectedMR->request_number }}" data-warehouse="{{ $selectedMR->fromWarehouse?->name }}" selected>
                                                [MR #{{ $selectedMR->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $selectedMR->fromWarehouse?->name }}
                                            </option>
                                        </optgroup>
                                        <optgroup label="Input Bebas">
                                            <option value="manual:new" data-type="manual">+ Input Barang Baru Manual (Ketik Sendiri)</option>
                                        </optgroup>
                                        <optgroup label="Master Data Material">
                                            @foreach($materials as $mat)
                                                <option value="mat:{{ $mat->id }}" data-type="material" data-id="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" data-unit="{{ $mat->unit?->symbol ?? 'unit' }}">
                                                    {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>

                                    <div class="po-extra-container mt-2" style="display:block;">
                                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:5px 8px;font-size:11px;color:#92400e;">
                                            <i class="fas fa-link me-1"></i> Item manual dari MR <strong>#{{ $selectedMR->request_number }}</strong> ({{ $selectedMR->fromWarehouse?->name }}).
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php $neededQty = max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_fulfilled); @endphp
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $neededQty }}" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                        <span class="po-unit-badge badge badge-secondary" style="font-size:11px;white-space:nowrap;">{{ $mrItem->custom_item_unit ?? 'unit' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="0" class="form-control po-price" oninput="calcPoRow(this)" required>
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
                                    <input type="hidden" name="items[0][material_id]" class="po-material-id" value="">
                                    <input type="hidden" name="items[0][material_request_item_id]" class="po-mr-item-id" value="">
                                    <input type="hidden" name="items[0][custom_item_name]" class="po-custom-name" value="">
                                    <input type="hidden" name="items[0][custom_item_unit]" class="po-custom-unit" value="unit">

                                    <select class="form-control po-item-select" onchange="handleItemSelect(this)" required>
                                        <option value="">-- Pilih Material / Barang yang Dipesan --</option>
                                        <optgroup label="Master Data Material">
                                            @foreach($materials as $mat)
                                                <option value="mat:{{ $mat->id }}" data-type="material" data-id="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" data-unit="{{ $mat->unit?->symbol ?? 'unit' }}">
                                                    {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @if(isset($mrCustomItems) && $mrCustomItems->isNotEmpty())
                                        <optgroup label="Permintaan Material Proyek (Item Baru / Perlu Beli)">
                                            @foreach($mrCustomItems as $mrItem)
                                                <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_fulfilled) }}" data-mr-num="{{ $mrItem->materialRequest?->request_number }}" data-warehouse="{{ $mrItem->materialRequest?->fromWarehouse?->name }}">
                                                    [MR #{{ $mrItem->materialRequest?->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $mrItem->materialRequest?->fromWarehouse?->name }} (Butuh: {{ (float)$mrItem->qty_requested }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                        <optgroup label="Input Bebas">
                                            <option value="manual:new" data-type="manual">+ Input Barang Baru Manual (Ketik Sendiri)</option>
                                        </optgroup>
                                    </select>

                                    <div class="po-extra-container mt-2" style="display:none;"></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                        <span class="po-unit-badge badge badge-secondary" style="font-size:11px;white-space:nowrap;">unit</span>
                                    </div>
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
        let poRowIdx = {{ $selectedPR ? count($selectedPR->items) : (isset($selectedMR) && $selectedMR ? max(1, $selectedMR->items->whereNull('material_id')->count()) : 1) }};

        // Cache options template for adding new rows dynamically
        const optionsTemplateHtml = `
            <option value="">-- Pilih Material / Barang yang Dipesan --</option>
            <optgroup label="Master Data Material">
                @foreach($materials as $mat)
                    <option value="mat:{{ $mat->id }}" data-type="material" data-id="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" data-unit="{{ $mat->unit?->symbol ?? 'unit' }}">
                        {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                    </option>
                @endforeach
            </optgroup>
            @if(isset($mrCustomItems) && $mrCustomItems->isNotEmpty())
            <optgroup label="Permintaan Material Proyek (Item Baru / Perlu Beli)">
                @foreach($mrCustomItems as $mrItem)
                    <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_fulfilled) }}" data-mr-num="{{ $mrItem->materialRequest?->request_number }}" data-warehouse="{{ $mrItem->materialRequest?->fromWarehouse?->name }}">
                        [MR #{{ $mrItem->materialRequest?->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $mrItem->materialRequest?->fromWarehouse?->name }} (Butuh: {{ (float)$mrItem->qty_requested }})
                    </option>
                @endforeach
            </optgroup>
            @endif
            <optgroup label="Input Bebas">
                <option value="manual:new" data-type="manual">+ Input Barang Baru Manual (Ketik Sendiri)</option>
            </optgroup>
        `;

        function handleItemSelect(selectEl) {
            const row = selectEl.closest('tr');
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const type = selectedOpt ? selectedOpt.getAttribute('data-type') : null;
            const extraContainer = row.querySelector('.po-extra-container');
            const unitBadge = row.querySelector('.po-unit-badge');

            const matIdInput = row.querySelector('.po-material-id');
            const mrItemIdInput = row.querySelector('.po-mr-item-id');
            const customNameInput = row.querySelector('.po-custom-name');
            const customUnitInput = row.querySelector('.po-custom-unit');
            const qtyInput = row.querySelector('.po-qty');
            const priceInput = row.querySelector('.po-price');

            // Reset
            matIdInput.value = '';
            mrItemIdInput.value = '';
            customNameInput.value = '';
            customUnitInput.value = 'unit';
            extraContainer.innerHTML = '';
            extraContainer.style.display = 'none';

            if (!type) {
                unitBadge.textContent = 'unit';
                calcPoRow(selectEl);
                return;
            }

            if (type === 'material') {
                const id = selectedOpt.getAttribute('data-id');
                const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                const unit = selectedOpt.getAttribute('data-unit') || 'unit';

                matIdInput.value = id;
                customUnitInput.value = unit;
                unitBadge.textContent = unit;
                if (price > 0 && (!priceInput.value || parseFloat(priceInput.value) === 0)) {
                    priceInput.value = price;
                }
            } else if (type === 'mr') {
                const mrId = selectedOpt.getAttribute('data-mr-id');
                const customName = selectedOpt.getAttribute('data-name');
                const customUnit = selectedOpt.getAttribute('data-unit') || 'unit';
                const qty = parseFloat(selectedOpt.getAttribute('data-qty')) || 1;
                const mrNum = selectedOpt.getAttribute('data-mr-num');
                const warehouse = selectedOpt.getAttribute('data-warehouse');

                mrItemIdInput.value = mrId;
                customNameInput.value = customName;
                customUnitInput.value = customUnit;
                qtyInput.value = qty;
                unitBadge.textContent = customUnit;

                extraContainer.style.display = 'block';
                extraContainer.innerHTML = `
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:6px 10px;font-size:12px;color:#1e40af;">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Item Permintaan Material (MR #${mrNum}):</strong> "${customName}" dari <em>${warehouse}</em>.
                        <div class="mt-1 flex items-center gap-2">
                            <span>Koreksi Satuan jika perlu:</span>
                            <input type="text" class="form-control form-control-sm" style="width:110px;padding:2px 6px;height:24px;font-size:12px;" value="${customUnit}" oninput="syncRowCustomUnit(this, '${row.getAttribute('data-row-id') || ''}')">
                        </div>
                    </div>
                `;
            } else if (type === 'manual') {
                extraContainer.style.display = 'block';
                extraContainer.innerHTML = `
                    <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:8px 10px;font-size:12px;">
                        <span class="text-amber-800 font-semibold block mb-1">
                            <i class="fas fa-edit me-1"></i> Tentukan Nama Barang & Satuan Baru:
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Nama barang yang dibeli..." required oninput="syncManualName(this)">
                            <input type="text" class="form-control form-control-sm" placeholder="Satuan (misal: Zak, Pcs, M)..." value="unit" required oninput="syncManualUnit(this)">
                        </div>
                    </div>
                `;
                unitBadge.textContent = 'unit';
            }

            calcPoRow(selectEl);
        }

        function syncRowCustomUnit(inputEl) {
            const row = inputEl.closest('tr');
            const unit = inputEl.value.trim() || 'unit';
            row.querySelector('.po-custom-unit').value = unit;
            row.querySelector('.po-unit-badge').textContent = unit;
        }

        function syncManualName(inputEl) {
            const row = inputEl.closest('tr');
            row.querySelector('.po-custom-name').value = inputEl.value.trim();
        }

        function syncManualUnit(inputEl) {
            const row = inputEl.closest('tr');
            const unit = inputEl.value.trim() || 'unit';
            row.querySelector('.po-custom-unit').value = unit;
            row.querySelector('.po-unit-badge').textContent = unit;
        }

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
                    <input type="hidden" name="items[${poRowIdx}][material_id]" class="po-material-id" value="">
                    <input type="hidden" name="items[${poRowIdx}][material_request_item_id]" class="po-mr-item-id" value="">
                    <input type="hidden" name="items[${poRowIdx}][custom_item_name]" class="po-custom-name" value="">
                    <input type="hidden" name="items[${poRowIdx}][custom_item_unit]" class="po-custom-unit" value="unit">

                    <select class="form-control po-item-select" onchange="handleItemSelect(this)" required>
                        ${optionsTemplateHtml}
                    </select>

                    <div class="po-extra-container mt-2" style="display:none;"></div>
                </td>
                <td>
                    <div class="flex items-center gap-1">
                        <input type="number" step="0.01" min="0.01" name="items[${poRowIdx}][quantity]" value="1" class="form-control po-qty" oninput="calcPoRow(this)" required>
                        <span class="po-unit-badge badge badge-secondary" style="font-size:11px;white-space:nowrap;">unit</span>
                    </div>
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

        function handleSupplierChange(selectEl) {
            const hiddenId = document.getElementById('hidden_supplier_id');
            const manualContainer = document.getElementById('manual_supplier_container');
            const manualInput = document.getElementById('supplier_name_input');

            if (selectEl.value === 'manual') {
                hiddenId.value = '';
                manualContainer.style.display = 'block';
                manualInput.setAttribute('required', 'required');
                manualInput.focus();
            } else if (selectEl.value) {
                hiddenId.value = selectEl.value;
                manualContainer.style.display = 'none';
                manualInput.removeAttribute('required');
                const opt = selectEl.options[selectEl.selectedIndex];
                manualInput.value = opt ? (opt.getAttribute('data-name') || '') : '';
            } else {
                hiddenId.value = '';
                manualContainer.style.display = 'none';
                manualInput.removeAttribute('required');
                manualInput.value = '';
            }
        }

        // Initialize any initial rows if needed
        document.addEventListener('DOMContentLoaded', function () {
            // Supplier init
            const supSelect = document.getElementById('supplier_select');
            const hiddenSupId = document.getElementById('hidden_supplier_id');
            const manualInput = document.getElementById('supplier_name_input');
            if (hiddenSupId && hiddenSupId.value) {
                supSelect.value = hiddenSupId.value;
            } else if (manualInput && manualInput.value.trim() !== '') {
                supSelect.value = 'manual';
                document.getElementById('manual_supplier_container').style.display = 'block';
                manualInput.setAttribute('required', 'required');
            }

            document.querySelectorAll('.po-item-select').forEach(function(sel) {
                if (sel.value) {
                    handleItemSelect(sel);
                }
            });
            document.querySelectorAll('.po-item-row').forEach(function(r) {
                calcPoRow(r.querySelector('.po-qty'));
            });
        });
    </script>
</x-app-layout>
