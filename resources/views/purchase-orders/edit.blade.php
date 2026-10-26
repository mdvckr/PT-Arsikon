<x-app-layout>
    <x-slot name="title">Edit Purchase Order {{ $purchaseOrder->po_number }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-muted" style="font-size:13px;">
            <x-lucide-arrow-left class="me-1" /> Kembali ke Detail PO
        </a>
        <div class="flex justify-between items-center flex-wrap gap-2 mt-1">
            <div>
                <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Edit Purchase Order: {{ $purchaseOrder->po_number }}</h2>
                <p class="text-muted text-xs">Ubah rincian supplier, tanggal, gudang tujuan, item barang, kuantitas, harga, dan catatan.</p>
            </div>
            <div>
                <span class="badge badge-{{ $purchaseOrder->status_color }}">{{ $purchaseOrder->status_label }}</span>
            </div>
        </div>
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

    <div id="mr_info_banner_container">
        @if($purchaseOrder->materialRequest)
        <div class="alert alert-warning mb-4" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 16px;border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <x-lucide-boxes style="font-size:18px;color:#d97706;" />
                <div>
                    <strong style="font-size:13px;display:block;">Pengadaan Barang Langsung dari PO Lapangan #{{ $purchaseOrder->materialRequest->request_number }}</strong>
                    <span style="font-size:11.5px;color:#b45309;">
                        Tujuan: <strong>{{ $purchaseOrder->materialRequest->toWarehouse?->project?->name ?? $purchaseOrder->materialRequest->toWarehouse?->name }}</strong>.
                    </span>
                </div>
            </div>
            <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:5px 10px;font-size:11px;font-weight:600;">
                PO Lapangan: {{ $purchaseOrder->materialRequest->request_number }}
            </span>
        </div>
        @endif
    </div>

    <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Informasi Header PO</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label font-semibold">Supplier / Toko <span class="text-danger">*</span></label>
                    <input type="hidden" name="supplier_id" id="hidden_supplier_id" value="{{ old('supplier_id', $purchaseOrder->supplier_id) }}">

                    <select id="supplier_select" class="form-control" onchange="handleSupplierChange(this)" required>
                        <option value="">-- Pilih Supplier --</option>
                        <optgroup label="Supplier Terdaftar">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-name="{{ $supplier->name }}" {{ (old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Input Bebas">
                            <option value="manual" {{ (!old('supplier_id', $purchaseOrder->supplier_id) && (old('supplier_name', $purchaseOrder->supplier_name))) ? 'selected' : '' }}>
                                + Ketik Nama Toko / Supplier Baru (Bebas)
                            </option>
                        </optgroup>
                    </select>

                    <div id="manual_supplier_container" class="mt-2" style="{{ (!old('supplier_id', $purchaseOrder->supplier_id) && (old('supplier_name', $purchaseOrder->supplier_name))) ? 'display:block;' : 'display:none;' }}">
                        <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:8px 10px;font-size:12px;">
                            <label class="text-amber-800 font-semibold block mb-1">
                                <x-lucide-store class="me-1" /> Nama Toko / Supplier Bebas: <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="supplier_name" id="supplier_name_input" value="{{ old('supplier_name', $purchaseOrder->supplier_name) }}" class="form-control form-control-sm" placeholder="Contoh: Toko Bangunan Berkah, Depot Pasir Jaya...">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label font-semibold">Dari PO Lapangan (Permintaan Proyek)</label>
                    <input type="hidden" name="procurement_request_id" id="hidden_procurement_request_id" value="{{ old('procurement_request_id', $purchaseOrder->procurement_request_id) }}">
                    <select name="material_request_id" id="material_request_select" class="form-control" onchange="handleMrHeaderChange(this)">
                        <option value="">-- Tanpa PO Lapangan (PO Mandiri / Restok) --</option>
                        @if(isset($approvedMRs) && $approvedMRs->isNotEmpty())
                            <optgroup label="PO Lapangan Proyek (Membutuhkan Pengadaan)">
                                @foreach($approvedMRs as $mr)
                                    @php
                                        $projName = $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name;
                                        $isSelected = (old('material_request_id', $purchaseOrder->material_request_id) == $mr->id);
                                    @endphp
                                    <option value="{{ $mr->id }}" 
                                        data-type="mr"
                                        data-warehouse-id="{{ $mr->to_warehouse_id }}"
                                        data-mr-num="{{ $mr->request_number }}"
                                        data-project="{{ $projName }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                        #{{ $mr->request_number }} — {{ $projName }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if(isset($approvedPRs) && $approvedPRs->isNotEmpty())
                            <optgroup label="Permintaan Pengadaan PR (Pusat / Legacy)">
                                @foreach($approvedPRs as $pr)
                                    <option value="pr:{{ $pr->id }}" 
                                        data-type="pr"
                                        {{ (old('procurement_request_id', $purchaseOrder->procurement_request_id) == $pr->id) ? 'selected' : '' }}>
                                        [PR] {{ $pr->pr_number }} ({{ $pr->items->count() }} item)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold">Tanggal Order <span class="text-danger">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', $purchaseOrder->order_date ? $purchaseOrder->order_date->format('Y-m-d') : date('Y-m-d')) }}" class="form-control" required>
                </div>

                <div>
                    <label class="form-label font-semibold">
                        <x-lucide-map-pin class="text-danger me-1" /> Alamat Pengiriman (Tujuan Proyek / Gudang) <span class="text-danger">*</span>
                    </label>
                    <select name="delivery_warehouse_id" id="delivery_warehouse_select" class="form-control" onchange="updateDeliveryAddressInfo(this)" required>
                        <option value="">-- Pilih Tujuan Pengiriman --</option>
                        @foreach($warehouses as $wh)
                            @php
                                $proj = $wh->project;
                                $whAddr = $wh->address ?: ($proj?->location ?: 'Alamat belum diatur');
                                $fullLabel = $wh->name . ($proj ? ' — Proyek: ' . $proj->name . ' (' . $whAddr . ')' : ' — ' . $whAddr);
                            @endphp
                            <option value="{{ $wh->id }}"
                                data-address="{{ $wh->address ?? '' }}"
                                data-warehouse-name="{{ $wh->name }}"
                                data-project-name="{{ $proj?->name ?? '' }}"
                                data-project-location="{{ $proj?->location ?? '' }}"
                                {{ (old('delivery_warehouse_id', $purchaseOrder->delivery_warehouse_id) == $wh->id) ? 'selected' : '' }}>
                                {{ $fullLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div id="delivery_address_preview" class="mt-2" style="font-size:11.5px;padding:8px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;color:#0369a1;display:none;line-height:1.5;">
                        <x-lucide-package-check class="me-1" /> <span id="delivery_address_text"></span>
                    </div>
                </div>

                <div>
                    <label class="form-label font-semibold">Target Pengiriman</label>
                    <input type="date" name="expected_delivery" value="{{ old('expected_delivery', $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('Y-m-d') : '') }}" class="form-control">
                </div>

                <div>
                    <label class="form-label font-semibold">Contact Person (CP) Print</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $purchaseOrder->contact_person) }}" class="form-control" placeholder="Contoh: Bpk. Bambang (0812xxxx)">
                    <small class="text-muted block mt-1" style="font-size:11px;">Input bebas nama/kontak untuk bagian "CP :" di lembar cetak PDF PO.</small>
                </div>

                <div>
                    <label class="form-label font-semibold">Penanggung Risiko Pengiriman</label>
                    <select name="risk_bearer" class="form-control">
                        <option value="vendor" {{ old('risk_bearer', $purchaseOrder->risk_bearer) === 'vendor' ? 'selected' : '' }}>Vendor / Supplier (Default)</option>
                        <option value="buyer" {{ old('risk_bearer', $purchaseOrder->risk_bearer) === 'buyer' ? 'selected' : '' }}>Pembeli (PT Arsikon)</option>
                    </select>
                    <small class="text-muted block mt-1" style="font-size:11px;">Tentukan pihak penanggung risiko susut / kehilangan pengiriman.</small>
                </div>

                <div>
                    <label class="form-label font-semibold">Syarat & Ketentuan / Pembayaran</label>
                    <input type="text" name="terms" value="{{ old('terms', $purchaseOrder->terms) }}" class="form-control" placeholder="Contoh: Net 30 hari, Cash on Delivery...">
                </div>

                <div>
                    <label class="form-label font-semibold">Catatan Umum</label>
                    <input type="text" name="notes" value="{{ old('notes', $purchaseOrder->notes) }}" class="form-control" placeholder="Catatan khusus / instruksi pengiriman...">
                </div>
            </div>
        </div>

        <div class="card mb-4" style="padding:20px;">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin-bottom:2px;">Daftar Barang yang Dipesan</h3>
                    <p class="text-muted text-xs">Ubah material, kuantitas, harga satuan, dan catatan spesifikasi untuk masing-masing item.</p>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addPoRow()">
                    <x-lucide-plus class="me-1" /> Tambah Item
                </button>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="po-items-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Material / Barang <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Jumlah & Satuan <span class="text-danger">*</span></th>
                            <th style="width: 16%;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Subtotal (Rp)</th>
                            <th style="width: 19%;">Catatan / Keterangan</th>
                            <th style="width: 5%; text-align:center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="po-items-tbody">
                        @foreach($purchaseOrder->items as $idx => $poItem)
                        @php
                            $isCustom = $poItem->isCustom();
                            $unitStr = $poItem->displayUnit();
                            $itemQty = (float) $poItem->quantity;
                            $unitPrice = (float) $poItem->unit_price;
                            $subtotal = (float) ($poItem->subtotal ?: ($itemQty * $unitPrice));
                        @endphp
                        <tr class="po-item-row">
                            <td>
                                <input type="hidden" name="items[{{ $idx }}][material_id]" class="po-material-id" value="{{ $poItem->material_id }}">
                                <input type="hidden" name="items[{{ $idx }}][tool_id]" class="po-tool-id" value="{{ $poItem->tool_id }}">
                                <input type="hidden" name="items[{{ $idx }}][material_request_item_id]" class="po-mr-item-id" value="{{ $poItem->material_request_item_id }}">
                                <input type="hidden" name="items[{{ $idx }}][procurement_request_item_id]" class="po-pr-item-id" value="{{ $poItem->procurement_request_item_id }}">
                                <input type="hidden" name="items[{{ $idx }}][custom_item_name]" class="po-custom-name" value="{{ $poItem->custom_item_name }}">
                                <input type="hidden" name="items[{{ $idx }}][custom_item_type]" class="po-custom-type" value="{{ $poItem->custom_item_type }}">
                                <input type="hidden" name="items[{{ $idx }}][custom_item_unit]" class="po-custom-unit" value="{{ $unitStr }}">

                                <select class="form-control po-item-select" onchange="handleItemSelect(this)" required>
                                    <option value="">-- Pilih Material / Barang yang Dipesan --</option>
                                    <optgroup label="Master Data Material">
                                        @foreach($materials as $mat)
                                            <option value="mat:{{ $mat->id }}" data-type="material" data-id="{{ $mat->id }}" data-price="{{ $mat->unit_price ?? 0 }}" data-unit="{{ $mat->unit?->symbol ?? 'unit' }}" {{ ($poItem->material_id == $mat->id) ? 'selected' : '' }}>
                                                {{ $mat->name }} ({{ $mat->unit?->symbol ?? 'unit' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @if(isset($mrCustomItems) && $mrCustomItems->isNotEmpty())
                                    <optgroup label="PO Lapangan Proyek (Item Baru / Perlu Beli)">
                                        @foreach($mrCustomItems as $mrItem)
                                            @php
                                                $mrItemQuota = (float)$mrItem->qty_approved > 0
                                                    ? max(0.01, (float)$mrItem->qty_approved - (float)$mrItem->qty_ordered)
                                                    : max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_ordered);
                                                if ($poItem->material_request_item_id == $mrItem->id) {
                                                    $mrItemQuota += (float)$poItem->quantity;
                                                }
                                                $whName = $mrItem->materialRequest?->toWarehouse?->project?->name ?? $mrItem->materialRequest?->toWarehouse?->name ?? 'Proyek';
                                            @endphp
                                            <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ $mrItemQuota }}" data-mr-num="{{ $mrItem->materialRequest?->request_number }}" data-warehouse="{{ $whName }}" {{ ($poItem->material_request_item_id == $mrItem->id) ? 'selected' : '' }}>
                                                [MR #{{ $mrItem->materialRequest?->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $whName }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    <optgroup label="Input Bebas">
                                        <option value="manual:new" data-type="manual" {{ ($isCustom && empty($poItem->material_request_item_id)) ? 'selected' : '' }}>+ Input Barang Baru Manual (Ketik Sendiri)</option>
                                    </optgroup>
                                </select>

                                <div class="po-extra-container mt-2" style="{{ ($isCustom && empty($poItem->material_request_item_id)) ? 'display:block;' : 'display:none;' }}">
                                    @if($isCustom && empty($poItem->material_request_item_id))
                                        <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:8px 10px;font-size:12px;">
                                            <span class="text-amber-800 font-semibold block mb-1">
                                                <x-lucide-pencil class="me-1" /> Nama Barang & Satuan Baru:
                                            </span>
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" class="form-control form-control-sm" placeholder="Nama barang yang dibeli..." value="{{ $poItem->custom_item_name }}" required oninput="syncManualName(this)">
                                                <input type="text" class="form-control form-control-sm" placeholder="Satuan (misal: Zak, Pcs)..." value="{{ $unitStr }}" required oninput="syncManualUnit(this)">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" value="{{ $itemQty }}" class="form-control po-qty" oninput="calcPoRow(this)" required>
                                    <span class="po-unit-badge badge badge-secondary" style="font-size:11px;white-space:nowrap;">{{ $unitStr }}</span>
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="{{ $unitPrice }}" class="form-control po-price" oninput="calcPoRow(this)" required>
                            </td>
                            <td>
                                <input type="text" class="form-control po-subtotal" readonly style="background:#f8fafc;" value="Rp {{ number_format($subtotal, 0, ',', '.') }}">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $idx }}][notes]" value="{{ old('items.'.$idx.'.notes', $poItem->notes) }}" class="form-control form-control-sm po-notes" placeholder="Catatan spesifikasi / item...">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removePoRow(this)"><x-lucide-trash-2 /></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;font-weight:700;">
                            <td colspan="3" class="text-end" style="padding:12px;font-size:14px;">Total Nilai PO:</td>
                            <td colspan="3" style="padding:12px;font-size:15px;color:#0f766e;" id="po-grand-total">
                                Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-6">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                <x-lucide-save class="me-1" /> Simpan Perubahan PO
            </button>
        </div>
    </form>

    <script>
        let poRowIdx = {{ count($purchaseOrder->items) }};
        const mrItemsMap = @json($mrItemsMap ?? []);

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
            <optgroup label="PO Lapangan Proyek (Item Baru / Perlu Beli)">
                @foreach($mrCustomItems as $mrItem)
                    @php
                        $mrItemQuota = (float)$mrItem->qty_approved > 0
                            ? max(0.01, (float)$mrItem->qty_approved - (float)$mrItem->qty_ordered)
                            : max(0.01, (float)$mrItem->qty_requested - (float)$mrItem->qty_ordered);
                        $whName = $mrItem->materialRequest?->toWarehouse?->project?->name ?? $mrItem->materialRequest?->toWarehouse?->name ?? 'Proyek';
                    @endphp
                    <option value="mr:{{ $mrItem->id }}" data-type="mr" data-mr-id="{{ $mrItem->id }}" data-name="{{ $mrItem->custom_item_name }}" data-unit="{{ $mrItem->custom_item_unit ?? 'unit' }}" data-qty="{{ $mrItemQuota }}" data-mr-num="{{ $mrItem->materialRequest?->request_number }}" data-warehouse="{{ $whName }}">
                        [MR #{{ $mrItem->materialRequest?->request_number }}] {{ $mrItem->custom_item_name }} ({{ $mrItem->custom_item_unit ?? 'unit' }}) - {{ $whName }}
                    </option>
                @endforeach
            </optgroup>
            @endif
            <optgroup label="Input Bebas">
                <option value="manual:new" data-type="manual">+ Input Barang Baru Manual (Ketik Sendiri)</option>
            </optgroup>
        `;

        function handleMrHeaderChange(selectEl) {
            const val = selectEl.value;
            const hiddenPrInput = document.getElementById('hidden_procurement_request_id');
            const warehouseSelect = document.querySelector('select[name="delivery_warehouse_id"]');
            const bannerContainer = document.getElementById('mr_info_banner_container');

            if (val.startsWith('pr:')) {
                const prId = val.replace('pr:', '');
                hiddenPrInput.value = prId;
                return;
            } else {
                hiddenPrInput.value = '';
            }

            if (!val) {
                if (bannerContainer) bannerContainer.innerHTML = '';
                return;
            }

            const mrData = mrItemsMap[val];
            if (!mrData) return;

            // Auto-pilih Gudang Tujuan Pengiriman jika belum ada
            if (mrData.to_warehouse_id && warehouseSelect && !warehouseSelect.value) {
                warehouseSelect.value = mrData.to_warehouse_id;
                updateDeliveryAddressInfo(warehouseSelect);
            }

            // Tampilkan banner PO Lapangan
            if (bannerContainer) {
                bannerContainer.innerHTML = `
                    <div class="alert alert-warning mb-4" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 16px;border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <x-lucide-boxes style="font-size:18px;color:#d97706;" />
                            <div>
                                <strong style="font-size:13px;display:block;">Pengadaan Barang Langsung dari PO Lapangan #${mrData.request_number}</strong>
                                <span style="font-size:11.5px;color:#b45309;">
                                    Tujuan Pengiriman: <strong>${mrData.project_name}</strong>.
                                </span>
                            </div>
                        </div>
                        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:5px 10px;font-size:11px;font-weight:600;">
                            PO Lapangan: #${mrData.request_number}
                        </span>
                    </div>
                `;
            }
        }

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
                if (!qtyInput.value || parseFloat(qtyInput.value) === 0) {
                    qtyInput.value = qty;
                }
                unitBadge.textContent = customUnit;

                extraContainer.style.display = 'block';
                extraContainer.innerHTML = `
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:6px 10px;font-size:12px;color:#1e40af;">
                        <x-lucide-info class="me-1" />
                        <strong>Item PO Lapangan (MR #${mrNum}):</strong> "${customName}" untuk <em>${warehouse}</em>.
                        <div class="mt-1 flex items-center gap-2">
                            <span>Koreksi Satuan jika perlu:</span>
                            <input type="text" class="form-control form-control-sm" style="width:110px;padding:2px 6px;height:24px;font-size:12px;" value="${customUnit}" oninput="syncRowCustomUnit(this)">
                        </div>
                    </div>
                `;
            } else if (type === 'manual') {
                extraContainer.style.display = 'block';
                extraContainer.innerHTML = `
                    <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:8px 10px;font-size:12px;">
                        <span class="text-amber-800 font-semibold block mb-1">
                            <x-lucide-pencil class="me-1" /> Tentukan Nama Barang & Satuan Baru:
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
            const subtotal = qty * price;
            row.querySelector('.po-subtotal').value = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

            updateGrandTotal();
        }

        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.po-item-row').forEach(function(row) {
                const qty = parseFloat(row.querySelector('.po-qty').value) || 0;
                const price = parseFloat(row.querySelector('.po-price').value) || 0;
                grandTotal += (qty * price);
            });
            const grandTotalEl = document.getElementById('po-grand-total');
            if (grandTotalEl) {
                grandTotalEl.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
            }
        }

        function addPoRow() {
            const tbody = document.getElementById('po-items-tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'po-item-row';
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="items[${poRowIdx}][material_id]" class="po-material-id" value="">
                    <input type="hidden" name="items[${poRowIdx}][tool_id]" class="po-tool-id" value="">
                    <input type="hidden" name="items[${poRowIdx}][material_request_item_id]" class="po-mr-item-id" value="">
                    <input type="hidden" name="items[${poRowIdx}][custom_item_name]" class="po-custom-name" value="">
                    <input type="hidden" name="items[${poRowIdx}][custom_item_type]" class="po-custom-type" value="">
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
                <td>
                    <input type="text" name="items[${poRowIdx}][notes]" value="" class="form-control form-control-sm po-notes" placeholder="Catatan spesifikasi / item...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removePoRow(this)"><x-lucide-trash-2 /></button>
                </td>
            `;
            tbody.appendChild(newRow);
            poRowIdx++;
            updateGrandTotal();
        }

        function removePoRow(btn) {
            const tbody = document.getElementById('po-items-tbody');
            if (tbody.querySelectorAll('.po-item-row').length <= 1) {
                alert('Purchase Order minimal harus memiliki 1 item barang.');
                return;
            }
            btn.closest('tr').remove();
            updateGrandTotal();
        }

        function updateDeliveryAddressInfo(sel) {
            const preview = document.getElementById('delivery_address_preview');
            const text = document.getElementById('delivery_address_text');
            if (!preview || !text) return;
            if (sel && sel.value) {
                const opt = sel.options[sel.selectedIndex];
                const addr = opt.getAttribute('data-address') || '';
                const projName = opt.getAttribute('data-project-name') || '';
                const projLoc = opt.getAttribute('data-project-location') || '';
                const whName = opt.getAttribute('data-warehouse-name') || opt.text;
                
                let desc = 'Tujuan: <strong>' + whName + '</strong>';
                if (projName) {
                    desc += ' &bull; Proyek: <strong>' + projName + '</strong>';
                }
                if (addr) {
                    desc += ' &bull; Alamat Pengiriman: <em>' + addr + '</em>';
                }
                if (projLoc && (!addr || !addr.toLowerCase().includes(projLoc.toLowerCase()))) {
                    desc += ' (' + projLoc + ')';
                }
                text.innerHTML = desc;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                text.innerHTML = '';
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

        document.addEventListener('DOMContentLoaded', function () {
            // Delivery address init
            const whSelect = document.getElementById('delivery_warehouse_select');
            if (whSelect) {
                updateDeliveryAddressInfo(whSelect);
            }

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

            updateGrandTotal();
        });
    </script>
</x-app-layout>
