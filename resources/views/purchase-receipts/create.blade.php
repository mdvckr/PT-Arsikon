<x-app-layout>
    <x-slot name="title">Catat Nota Pembelian Harian</x-slot>

    <div class="mb-4">
        <a href="{{ route('purchase-receipts.index') }}" class="text-muted" style="font-size:13px;">
            <x-lucide-arrow-left class="me-1" /> Kembali ke Daftar Nota
        </a>
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">Pencatatan Nota Pembelian Harian</h2>
        <p class="text-muted" style="font-size:13px;">Pencatatan belanja operasional langsung di proyek (Bon toko, material darurat, konsumsi/BBM).</p>
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

    <form action="{{ route('purchase-receipts.store') }}" method="POST" enctype="multipart/form-data" id="form-purchase-receipt">
        @csrf

        <div class="card mb-4" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Data Nota Pembelian</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- 1. Pilih Proyek --}}
                <div>
                    <label class="form-label font-semibold">Proyek <span class="text-danger">*</span></label>
                    <select name="project_id" id="project_id_select" class="form-control" required onchange="handleProjectChange(this)">
                        @if($projects->count() > 1)
                            <option value="">-- Pilih Proyek --</option>
                        @endif
                        @foreach($projects as $prj)
                            <option value="{{ $prj->id }}" 
                                    data-balance="{{ (float)$prj->operational_balance }}"
                                    data-name="{{ $prj->name }}"
                                    {{ (old('project_id', $selectedProject?->id) == $prj->id) ? 'selected' : '' }}>
                                {{ $prj->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="project_name" id="project_name_input" value="{{ old('project_name', $selectedProject?->name ?? '') }}">
                    <small id="project_balance_info" class="text-muted block mt-1" style="font-size:11.5px;color:#047857;font-weight:600;">
                        <x-lucide-wallet class="text-emerald-600 me-1" /> Saldo Kas Proyek: <span id="display_project_balance">Rp {{ number_format($currentBalance ?? 0, 0, ',', '.') }}</span>
                    </small>
                </div>

                {{-- 2. Supplier / Toko Pembelian --}}
                <div>
                    <label class="form-label font-semibold">Supplier / Toko Pembelian <span class="text-danger">*</span></label>
                    <input type="text" name="supplier_name" id="supplier_name_input" value="{{ old('supplier_name') }}" placeholder="Contoh: Toko Bangunan Berkah / Depot Pasir" class="form-control" required>
                </div>

                {{-- 3. Tanggal Nota --}}
                <div>
                    <label class="form-label font-semibold">Tanggal Nota <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receipt_date_input" value="{{ old('receipt_date', date('Y-m-d')) }}" class="form-control" required>
                </div>

                {{-- 4. Nomor Nota Physical / Faktur --}}
                <div>
                    <label class="form-label font-semibold">Nomor Nota Physical / Faktur</label>
                    <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Contoh: NOTA-8812 / Bon Toko" class="form-control">
                </div>

                {{-- 5. Status Pembayaran Nota (Otomatis Sudah Dibayar / Instan) --}}
                <div>
                    <label class="form-label font-semibold">Status Pembayaran Nota <span class="text-danger">*</span></label>
                    <input type="hidden" name="payment_status" value="paid">
                    <div class="form-control" style="background:#ecfdf5;color:#065f46;font-weight:700;display:flex;align-items:center;gap:6px;border-color:#a7f3d0;cursor:default;">
                        <x-lucide-circle-check class="text-emerald-600" />
                        <span>Sudah Dibayar (Instan - Potong Kas)</span>
                    </div>
                    <small class="text-muted" style="font-size:11px;margin-top:2px;display:block;">Saldo kas proyek otomatis terpotong saat nota disimpan.</small>
                </div>

                {{-- 6. Upload Foto / Scan Nota Fisik --}}
                <div>
                    <label class="form-label font-semibold">Upload Foto / Scan Nota Fisik</label>
                    <input type="file" name="image" accept="image/*,application/pdf" class="form-control" style="padding:6px;">
                    <small class="text-muted block mt-1" style="font-size:11px;">Format: JPG, PNG, WEBP, PDF (Max: 10MB).</small>
                </div>

                {{-- 7. Catatan / Keterangan --}}
                <div class="md:col-span-3">
                    <label class="form-label font-semibold">Catatan / Keterangan Pembelian</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Peruntukan pembelian material, nama pekerja yang membeli, atau keterangan lainnya..." class="form-control">
                </div>
            </div>
        </div>

        <!-- Section Item Pembelian -->
        <div class="card mb-4" style="padding:20px;">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h3 style="font-size:16px;font-weight:600;color:#1e293b;">Rincian Barang / Material yang Dibeli</h3>
                    <p class="text-muted" style="font-size:12px;">Tambahkan barang sesuai bon / faktur toko fisik.</p>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addItemRow()">
                    <x-lucide-plus class="me-1" /> Tambah Baris Barang
                </button>
            </div>

            <div class="table-wrap">
                <table class="data-table" id="items-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Nama Material <span class="text-danger">*</span></th>
                            <th style="width: 24%;">Item (Jenis yang Dibeli) <span class="text-danger">*</span></th>
                            <th style="width: 11%;">Satuan <span class="text-danger">*</span></th>
                            <th style="width: 10%;">Vol (Qty) <span class="text-danger">*</span></th>
                            <th style="width: 14%;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 14%;">Subtotal (Rp)</th>
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
                                    $matName = $it['material_name'] ?? ($it['item_name'] ?? '');
                                    $itemName = $it['item_name'] ?? '';
                                    $unitName = $it['unit_name'] ?? '';
                                @endphp
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $it['material_id'] ?? '' }}">
                                        <input type="text" name="items[{{ $idx }}][material_name]" list="material-list" value="{{ $matName }}" placeholder="Misal: Triplek, Semen, Besi..." class="form-control" onchange="autoFillUnit(this)" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][item_name]" value="{{ $itemName }}" placeholder="Misal: Triplek 9mm 1.2x1.2..." class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][unit_name]" list="unit-list" value="{{ $unitName }}" placeholder="Lbr/Zak/Kg" class="form-control unit-input" required>
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
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                            <x-lucide-trash-2 />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="item-row">
                                <td>
                                    <input type="hidden" name="items[0][material_id]" value="">
                                    <input type="text" name="items[0][material_name]" list="material-list" placeholder="Misal: Triplek, Semen, Besi..." class="form-control" onchange="autoFillUnit(this)" required>
                                </td>
                                <td>
                                    <input type="text" name="items[0][item_name]" placeholder="Misal: Triplek 9mm 1.2x1.2 / Bon toko..." class="form-control" required>
                                </td>
                                <td>
                                    <input type="text" name="items[0][unit_name]" list="unit-list" placeholder="Lbr/Zak/Kg" class="form-control unit-input" required>
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
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                                        <x-lucide-trash-2 />
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end font-bold" style="font-size:15px;padding-top:14px;">TOTAL BELANJA NOTA:</td>
                            <td colspan="2" style="padding-top:14px;">
                                <strong id="grand-total-display" class="text-success" style="font-size:20px;">Rp 0</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Datalist untuk Material dan Satuan --}}
            <datalist id="material-list">
                @foreach($materials as $mat)
                    <option value="{{ $mat->name }}" data-unit="{{ $mat->unit?->symbol ?? '' }}">{{ $mat->name }}</option>
                @endforeach
            </datalist>

            <datalist id="unit-list">
                <option value="Lbr">Lembar (Lbr)</option>
                <option value="Zak">Zak</option>
                <option value="Pcs">Pcs</option>
                <option value="Bh">Buah (Bh)</option>
                <option value="Kg">Kilogram (Kg)</option>
                <option value="M3">Meter Kubik (M3)</option>
                <option value="M2">Meter Persegi (M2)</option>
                <option value="Meter">Meter (m)</option>
                <option value="Batang">Batang</option>
                <option value="Roll">Roll</option>
                <option value="Kaleng">Kaleng</option>
                <option value="Dus">Dus / Box</option>
                <option value="Set">Set</option>
                <option value="Liter">Liter</option>
            </datalist>

            {{-- Alert Dinamis jika Saldo Kurang --}}
            <div id="saldo_warning_alert" class="alert alert-danger mt-3" style="display:none;background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <x-lucide-triangle-alert style="font-size:18px;" />
                    <div>
                        <strong style="font-size:13px;display:block;">Peringatan: Saldo Kas Proyek Tidak Mencukupi!</strong>
                        <span style="font-size:12px;" id="saldo_warning_text"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mb-6">
            <a href="{{ route('purchase-receipts.index') }}" class="btn btn-light">Batal</a>
            <button type="submit" id="submit_btn" class="btn btn-primary" style="padding:10px 24px;font-weight:700;">
                <x-lucide-save class="me-1" /> Simpan & Potong Saldo Kas
            </button>
        </div>
    </form>

    <script>
        let currentProjectBalance = {{ (float)($currentBalance ?? 0) }};
        let rowIndex = {{ old('items') ? count(old('items')) : 1 }};

        function handleProjectChange(selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            const nameInput = document.getElementById('project_name_input');
            const nameDisplay = document.getElementById('balance_project_name');
            const balanceDisplay = document.getElementById('display_project_balance');

            if (opt && opt.value) {
                currentProjectBalance = parseFloat(opt.dataset.balance) || 0;
                const prjName = opt.dataset.name || opt.text;
                if (nameInput) nameInput.value = prjName;
                if (nameDisplay) nameDisplay.innerText = prjName;
                if (balanceDisplay) balanceDisplay.innerText = 'Rp ' + Math.round(currentProjectBalance).toLocaleString('id-ID');
            } else {
                currentProjectBalance = 0;
                if (nameInput) nameInput.value = '';
                if (nameDisplay) nameDisplay.innerText = 'Proyek';
                if (balanceDisplay) balanceDisplay.innerText = 'Rp 0';
            }
            calculateGrandTotal();
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

            // Cek saldo kas proyek
            const warnAlert = document.getElementById('saldo_warning_alert');
            const warnText = document.getElementById('saldo_warning_text');
            const indicator = document.getElementById('balance_status_indicator');
            const submitBtn = document.getElementById('submit_btn');

            if (total > currentProjectBalance) {
                const diff = total - currentProjectBalance;
                if (warnAlert) {
                    warnAlert.style.display = 'block';
                    warnText.innerText = `Total nota (Rp ${Math.round(total).toLocaleString('id-ID')}) melebihi saldo kas yang tersedia (Rp ${Math.round(currentProjectBalance).toLocaleString('id-ID')}). Kurang: Rp ${Math.round(diff).toLocaleString('id-ID')}.`;
                }
                if (indicator) {
                    indicator.style.background = 'rgba(239, 68, 68, 0.4)';
                    indicator.innerHTML = `<x-lucide-triangle-alert class="text-red-200" /> Saldo Kurang!`;
                }
                if (submitBtn) {
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-danger');
                }
            } else {
                if (warnAlert) warnAlert.style.display = 'none';
                if (indicator) {
                    indicator.style.background = 'rgba(255, 255, 255, 0.18)';
                    indicator.innerHTML = `<x-lucide-zap class="text-amber-300" /> Pembayaran Instan (Potong Kas)`;
                }
                if (submitBtn) {
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.classList.add('btn-primary');
                }
            }
        }

        function autoFillUnit(inputEl) {
            const row = inputEl.closest('tr');
            const unitInput = row.querySelector('.unit-input');
            const itemInput = row.querySelector('input[name*="[item_name]"]');
            if (unitInput && !unitInput.value) {
                const opt = document.querySelector(`#material-list option[value="${CSS.escape(inputEl.value)}"]`);
                if (opt && opt.dataset.unit) {
                    unitInput.value = opt.dataset.unit;
                }
            }
            if (itemInput && !itemInput.value) {
                itemInput.value = inputEl.value;
            }
        }

        function addItemRow() {
            const tbody = document.getElementById('items-tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="items[${rowIndex}][material_id]" value="">
                    <input type="text" name="items[${rowIndex}][material_name]" list="material-list" placeholder="Misal: Triplek, Semen, Besi..." class="form-control" onchange="autoFillUnit(this)" required>
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][item_name]" placeholder="Misal: Triplek 9mm 1.2x1.2..." class="form-control" required>
                </td>
                <td>
                    <input type="text" name="items[${rowIndex}][unit_name]" list="unit-list" placeholder="Lbr/Zak/Kg" class="form-control unit-input" required>
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
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeRow(this)" title="Hapus Baris">
                        <x-lucide-trash-2 />
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
