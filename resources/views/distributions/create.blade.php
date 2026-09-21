<x-app-layout>
    <x-slot name="title">Buat Surat Jalan / Pengiriman Barang & Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi & Surat Jalan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Surat Jalan Baru</span>
    </div>

    @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-exclamation-circle" style="font-size:16px;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:6px;">
            <i class="fas fa-exclamation-triangle"></i> Periksa kembali data formulir:
        </div>
        <ul style="margin:0 0 0 20px;padding:0;font-size:13px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div id="ajax-alert" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;"></div>

    @php
        $mrs = [];
        foreach ($materialRequests as $mr) {
            $its = [];
            foreach ($mr->items as $it) {
                $remaining = (float) $it->qty_approved - (float) $it->qty_fulfilled;
                if ($remaining <= 0) {
                    continue;
                }
                $its[] = [
                    'material_id' => $it->material_id,
                    'name'        => $it->material?->name ?? '',
                    'code'        => $it->material?->code ?? '',
                    'unit'        => $it->material?->unit?->abbreviation ?? '',
                    'remaining'   => $remaining,
                ];
            }
            if (count($its) === 0) {
                continue;
            }
            $mrs[] = [
                'id'                => $mr->id,
                'number'            => $mr->request_number,
                'from_warehouse_id' => $mr->from_warehouse_id,
                'to_warehouse_id'   => $mr->to_warehouse_id,
                'from_warehouse'    => $mr->fromWarehouse?->name ?? '',
                'to_warehouse'      => $mr->toWarehouse?->name ?? '',
                'items'             => $its,
            ];
        }
        $tas = [];
        foreach ($toolAssignments as $ta) {
            $tas[] = [
                'id'                => $ta->id,
                'number'            => $ta->assignment_number,
                'tool_id'           => $ta->tool_id,
                'tool_name'         => $ta->tool?->name ?? '',
                'tool_code'         => $ta->tool?->code ?? '',
                'quantity'          => (int) $ta->quantity,
                'from_warehouse_id' => $ta->from_warehouse_id,
                'from_warehouse'    => $ta->fromWarehouse?->name ?? '',
                'status'            => $ta->status,
                'notes'             => $ta->notes ?? '',
            ];
        }
        $whs = [];
        foreach ($warehouses as $w) {
            $whs[] = ['id' => $w->id, 'name' => $w->name, 'is_central' => (bool) $w->is_central];
        }
        $matsJson = $materials->map(fn($m) => [
            'id'   => $m->id,
            'name' => $m->name . ($m->type ? " [{$m->type}]" : ''),
            'code' => $m->code,
            'unit' => $m->unit?->abbreviation ?? 'pcs',
        ])->values();
        $toolsJson = $tools->map(fn($t) => [
            'id'        => $t->id,
            'name'      => $t->name . ($t->serial_number ? " (S/N: {$t->serial_number})" : ''),
            'code'      => $t->code,
            'available' => (int) $t->stock_available,
        ])->values();
        $toolsDropdownJson = $toolsForDropdown ?? collect([]);
    @endphp

    <form id="distribution-form" method="POST" action="{{ route('distributions.store') }}">
        @csrf

        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Pilih Sumber --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice text-primary"></i> <span class="card-title">Pilih Sumber Permintaan (Otomatis)</span>
                    </div>
                    <div class="card-body">
                        <div class="text-muted mb-3" style="font-size:13px;">
                            Pilih <strong>Permintaan Material</strong> yang telah disetujui dan/atau centang <strong>Peminjaman Alat</strong> untuk mengisi daftar barang secara otomatis. Anda juga dapat menambahkan item secara manual di bawah.
                        </div>

                        <label class="form-label fw-600">Permintaan Material <span class="text-muted" style="font-weight:400;">(yang sudah disetujui)</span></label>
                        <select name="material_request_id" id="mr-select" class="form-control">
                            <option value="">-- Pilih Permintaan Material (Opsional) --</option>
                            @foreach($mrs as $mr)
                            <option value="{{ $mr['id'] }}"
                                    {{ old('material_request_id') == $mr['id'] ? 'selected' : '' }}
                                    data-from="{{ $mr['from_warehouse_id'] }}"
                                    data-to="{{ $mr['to_warehouse_id'] }}">
                                {{ $mr['number'] }} — {{ $mr['from_warehouse'] }} → {{ $mr['to_warehouse'] }}
                            </option>
                            @endforeach
                        </select>

                        <label class="form-label mt-4 fw-600">Peminjaman Alat <span class="text-muted" style="font-weight:400;">(pending / telah disetujui)</span></label>
                        @if(count($tas) > 0)
                        <div class="chip-list" id="ta-list" style="display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($tas as $ta)
                            <label class="chip" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:13px;background:#f8fafc;transition:all 0.2s;">
                                <input type="checkbox" class="ta-check" value="{{ $ta['id'] }}"
                                       data-id="{{ $ta['id'] }}" data-tool-id="{{ $ta['tool_id'] }}"
                                       data-tool-name="{{ $ta['tool_name'] }}"
                                       data-tool-code="{{ $ta['tool_code'] }}"
                                       data-qty="{{ $ta['quantity'] }}"
                                       data-from="{{ $ta['from_warehouse_id'] }}"
                                       data-from-name="{{ $ta['from_warehouse'] }}">
                                <span>
                                    <strong>{{ $ta['tool_name'] }}</strong>
                                    <span class="text-muted" style="font-size:11.5px;">
                                        ({{ $ta['number'] }}) · jml {{ $ta['quantity'] }} unit · {{ $ta['from_warehouse'] }}
                                        · @if($ta['status'] === 'pending')<span style="color:#b45309;font-weight:600;">menunggu persetujuan</span>@else<span style="color:#7c3aed;font-weight:600;">disetujui</span>@endif
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <div class="text-muted" style="font-size:13px;padding:8px 12px;background:#f8fafc;border-radius:6px;border:1px dashed #cbd5e1;">
                            <i class="fas fa-info-circle text-muted"></i> Belum ada pengajuan peminjaman alat yang siap dipilih. (Bisa tambahkan alat secara manual di bawah).
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Preview Item & Tambah Manual --}}
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-boxes-stacked text-primary"></i>
                            <span class="card-title">Barang / Alat yang Dikirim</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span id="item-count-badge" class="badge badge-gray">0 item</span>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleManualBox()" style="display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-plus-circle text-primary"></i> <span>Tambah Item Manual</span>
                            </button>
                        </div>
                    </div>

                    {{-- Manual Adder Box --}}
                    <div id="manual-box" style="display:none;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:16px 20px;">
                        <div style="font-weight:600;font-size:13.5px;margin-bottom:12px;display:flex;align-items:center;gap:6px;color:#1e293b;">
                            <i class="fas fa-cart-plus text-primary"></i> Tambah Item Manual ke Surat Jalan
                        </div>
                        <div class="grid" style="grid-template-columns:140px 1fr 120px auto;gap:12px;align-items:end;">
                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;">Tipe Item</label>
                                <select id="manual-type" class="form-control" onchange="updateManualSelect()">
                                    <option value="material">Material</option>
                                    <option value="tool">Alat Kerja</option>
                                    <option value="custom">Item Custom</option>
                                </select>
                            </div>
                            <div id="manual-select-container">
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;">Pilih Barang / Alat</label>
                                <select id="manual-item-select" class="form-control">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                            <div id="manual-custom-container" style="display:none;">
                                <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;">
                                    <div>
                                        <label class="form-label" style="font-size:12px;margin-bottom:4px;">Nama Barang / Item <span class="text-danger">*</span></label>
                                        <input type="text" id="manual-custom-name" class="form-control" placeholder="Contoh: Terpal Plastik Biru 4x6">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:12px;margin-bottom:4px;">Satuan</label>
                                        <input type="text" id="manual-custom-unit" class="form-control" placeholder="pcs" value="pcs">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;">Jumlah (Qty)</label>
                                <input type="number" id="manual-qty" class="form-control" placeholder="Qty" min="0.01" step="0.01" value="1">
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="addManualItem()" style="height:38px;">
                                    <i class="fas fa-check"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div id="manual-hint" class="text-muted" style="font-size:11.5px;margin-top:8px;"></div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:12%;">Tipe</th>
                                    <th style="width:48%;">Barang / Alat</th>
                                    <th style="width:30%;">Jumlah (Qty)</th>
                                    <th style="width:10%;text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="empty-row">
                                    <td colspan="4" class="text-center text-muted p-4">
                                        <i class="fas fa-clipboard-list" style="font-size:24px;margin-bottom:8px;display:block;opacity:0.4;"></i>
                                        Pilih <strong>Permintaan Material / Peminjaman Alat</strong> di atas atau klik <strong>Tambah Item Manual</strong> untuk memasukkan barang/alat yang akan dikirim.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tanggal & Catatan --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-truck text-primary"></i> <span class="card-title">Informasi Pengiriman</span>
                    </div>
                    <div class="card-body">
                        <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                            </div>
                            <div>
                                <label class="form-label">Nama Supir / Kurir</label>
                                <input type="text" name="driver_name" value="{{ old('driver_name') }}" placeholder="Contoh: Pak Budi" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">No. Kendaraan (Plat)</label>
                                <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="Contoh: B 1234 CD" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">Catatan Pengiriman</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Pengiriman material tahap 1" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gudang & Submit --}}
            <div class="card" style="position:sticky;top:20px;">
                <div class="card-header">
                    <i class="fas fa-warehouse text-primary"></i> <span class="card-title">Rute Gudang</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Asal (Pengirim) <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" id="from-warehouse" class="form-control" required>
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gudang Tujuan (Penerima) <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" id="to-warehouse" class="form-control" required>
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-muted mb-4" style="font-size:12px;line-height:1.4;">
                        <i class="fas fa-info-circle text-primary"></i> Surat jalan yang dibuat berstatus <strong>Draft</strong>. Barang akan resmi terpotong dan transit setelah surat jalan dikirim.
                    </div>
                    <button type="submit" id="btn-submit" class="btn btn-primary w-full" style="justify-content:center;padding:12px;font-size:14px;font-weight:600;">
                        <i class="fas fa-paper-plane"></i> Buat Surat Jalan
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        const mrs = @json($mrs);
        const tas = @json($tas);
        const whs = @json($whs);
        const allMaterials = @json($matsJson);
        const allTools = @json($toolsJson);
        const allToolsDropdown = @json($toolsDropdownJson);

        const itemsBody       = document.getElementById('itemsBody');
        const emptyRow        = document.getElementById('empty-row');
        const mrSelect        = document.getElementById('mr-select');
        const fromSelect      = document.getElementById('from-warehouse');
        const toSelect        = document.getElementById('to-warehouse');
        const manualBox       = document.getElementById('manual-box');
        const manualType      = document.getElementById('manual-type');
        const manualItemSelect= document.getElementById('manual-item-select');
        const manualQty       = document.getElementById('manual-qty');
        const manualHint      = document.getElementById('manual-hint');
        const distForm        = document.getElementById('distribution-form');
        const btnSubmit       = document.getElementById('btn-submit');
        const ajaxAlert       = document.getElementById('ajax-alert');

        let rowIndex = 0;

        function centralWarehouse(excludeId) {
            const pick = whs.find(w => w.is_central && w.id !== excludeId)
                      || whs.find(w => w.id !== excludeId)
                      || whs[0];
            return pick ? pick.id : '';
        }

        function autoWarehouses() {
            const mr = mrs.find(m => m.id === Number(mrSelect.value));
            const selectedTa = document.querySelector('.ta-check:checked');
            const ta = selectedTa ? tas.find(t => t.id === Number(selectedTa.value)) : null;

            if (mr && mr.items && mr.items.length > 0) {
                // Distribusi pengiriman material: Gudang asal pengirim adalah to_warehouse dari MR (biasanya Pusat), penerima adalah from_warehouse (Proyek)
                if (mr.to_warehouse_id && mr.from_warehouse_id && mr.to_warehouse_id !== mr.from_warehouse_id) {
                    fromSelect.value = mr.to_warehouse_id;
                    toSelect.value   = mr.from_warehouse_id;
                } else {
                    fromSelect.value = centralWarehouse(mr.from_warehouse_id);
                    toSelect.value   = mr.from_warehouse_id;
                }
            } else if (ta) {
                fromSelect.value = centralWarehouse(ta.from_warehouse_id);
                toSelect.value   = ta.from_warehouse_id;
            }
        }

        function renderMaterials(mr) {
            // remove previously auto-loaded material rows from MR
            document.querySelectorAll('tr[data-kind="material"][data-source="mr"]').forEach(r => r.remove());

            if (!mr || !mr.items || mr.items.length === 0) {
                updateBadge();
                return;
            }

            for (const it of mr.items) {
                const tr = document.createElement('tr');
                tr.dataset.kind = 'material';
                tr.dataset.source = 'mr';
                tr.dataset.sourceId = mr.id;
                tr.innerHTML = `
                    <td><span class="badge badge-gray"><i class="fas fa-box"></i> Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${it.material_id}">
                        <div class="fw-600">${it.name}</div>
                        <div class="text-muted" style="font-size:11.5px;">${it.code || ''} · Sisa persetujuan: <strong>${it.remaining} ${it.unit}</strong></div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${it.remaining}" min="0.01" max="${it.remaining}" step="0.01" required style="width:110px;">
                            <span class="text-muted" style="font-size:13px;">${it.unit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item ini dari surat jalan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>`;
                itemsBody.appendChild(tr);
                rowIndex++;
            }
            updateBadge();
        }

        function renderTool(ta) {
            const existing = document.querySelector(`tr[data-source="ta"][data-source-id="${ta.id}"]`);
            if (existing) {
                existing.querySelector('input.qty-input').max = ta.quantity;
                existing.querySelector('input.qty-input').value = Math.min(existing.querySelector('input.qty-input').value, ta.quantity);
                return;
            }

            const tr = document.createElement('tr');
            tr.dataset.kind = 'tool';
            tr.dataset.source = 'ta';
            tr.dataset.sourceId = ta.id;
            tr.innerHTML = `
                <td><span class="badge badge-purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fas fa-tools"></i> Alat</span></td>
                <td>
                    <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                    <input type="hidden" name="items[${rowIndex}][tool_id]" value="${ta.tool_id}">
                    <input type="hidden" name="items[${rowIndex}][tool_assignment_id]" value="${ta.id}">
                    <div class="fw-600">${ta.tool_name}</div>
                    <div class="text-muted" style="font-size:11.5px;">${ta.tool_code || ''} · ${ta.number} · Diajukan: ${ta.quantity} unit</div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                            value="${ta.quantity}" min="1" max="${ta.quantity}" step="1" required style="width:110px;">
                        <span class="text-muted" style="font-size:13px;">unit</span>
                    </div>
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeToolRow(${ta.id})" title="Hapus alat ini dari surat jalan">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;
            itemsBody.appendChild(tr);
            rowIndex++;
            updateBadge();
        }

        function removeToolRow(id) {
            const chk = document.querySelector(`.ta-check[value="${id}"]`);
            if (chk) chk.checked = false;
            document.querySelectorAll(`tr[data-source="ta"][data-source-id="${id}"]`).forEach(r => r.remove());
            syncToolCheckboxes();
            updateBadge();
            autoWarehouses();
        }

        function syncToolCheckboxes() {
            document.querySelectorAll('input[name="tool_assignment_ids[]"]').forEach(el => el.remove());
            document.querySelectorAll('.ta-check:checked').forEach(c => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'tool_assignment_ids[]';
                hidden.value = c.value;
                distForm.appendChild(hidden);
            });
        }

        function updateBadge() {
            const rows = itemsBody.querySelectorAll('tr[data-kind]');
            const count = document.getElementById('item-count-badge');
            const empty = rows.length === 0;
            emptyRow.style.display = empty ? '' : 'none';
            count.textContent = `${rows.length} item`;
            count.className = 'badge ' + (empty ? 'badge-gray' : 'badge-primary');
        }

        function toggleManualBox() {
            const isHidden = manualBox.style.display === 'none';
            manualBox.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                updateManualSelect();
            }
        }

        function updateManualSelect() {
            const type = manualType.value;
            const selectContainer = document.getElementById('manual-select-container');
            const customContainer = document.getElementById('manual-custom-container');

            if (type === 'custom') {
                if (selectContainer) selectContainer.style.display = 'none';
                if (customContainer) customContainer.style.display = 'block';
                manualQty.step = "0.01";
                manualQty.min = "0.01";
                manualHint.textContent = "Item bebas / custom tidak memotong stok inventori master.";
                return;
            }

            if (selectContainer) selectContainer.style.display = 'block';
            if (customContainer) customContainer.style.display = 'none';

            manualItemSelect.innerHTML = '<option value="">-- Pilih --</option>';
            if (type === 'material') {
                allMaterials.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = `${m.name} (${m.unit})`;
                    opt.dataset.unit = m.unit;
                    opt.dataset.name = m.name;
                    opt.dataset.code = m.code;
                    manualItemSelect.appendChild(opt);
                });
                manualQty.step = "0.01";
                manualQty.min = "0.01";
                manualHint.textContent = "Material akan dikirim langsung dan dicatat dalam Surat Jalan.";
            } else {
                allToolsDropdown.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    opt.dataset.unit = 'unit';
                    opt.dataset.name = t.name;
                    opt.dataset.code = t.code;
                    opt.dataset.max = t.available;
                    manualItemSelect.appendChild(opt);
                });
                manualQty.step = "1";
                manualQty.min = "1";
                manualHint.textContent = "Alat kerja akan dicatat dalam Surat Jalan pengiriman.";
            }
        }

        function addManualItem() {
            const type = manualType.value;
            const qty = parseFloat(manualQty.value);

            if (isNaN(qty) || qty <= 0) {
                alert("Masukkan jumlah (qty) yang valid.");
                return;
            }

            const tr = document.createElement('tr');
            tr.dataset.kind = type;
            tr.dataset.source = 'manual';

            if (type === 'custom') {
                const customNameInput = document.getElementById('manual-custom-name');
                const customUnitInput = document.getElementById('manual-custom-unit');
                const customName = customNameInput ? customNameInput.value.trim() : '';
                const customUnit = (customUnitInput && customUnitInput.value.trim()) ? customUnitInput.value.trim() : 'unit';

                if (!customName) {
                    alert("Nama barang/alat custom wajib diisi.");
                    if (customNameInput) customNameInput.focus();
                    return;
                }

                tr.innerHTML = `
                    <td><span class="badge badge-info" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-pen-nib"></i> Custom</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="custom">
                        <input type="hidden" name="items[${rowIndex}][custom_item_name]" value="${customName}">
                        <input type="hidden" name="items[${rowIndex}][custom_item_unit]" value="${customUnit}">
                        <div class="fw-600">${customName}</div>
                        <div class="text-muted" style="font-size:11.5px;">Item Custom / Bebas · Non-Master Stok</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:110px;">
                            <span class="text-muted" style="font-size:13px;">${customUnit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>`;

                itemsBody.appendChild(tr);
                rowIndex++;
                updateBadge();
                if (customNameInput) customNameInput.value = '';
                return;
            }

            const select = manualItemSelect;
            const opt = select.selectedOptions[0];

            if (!select.value) {
                alert("Pilih barang atau alat terlebih dahulu.");
                return;
            }

            if (type === 'material') {
                tr.innerHTML = `
                    <td><span class="badge badge-gray"><i class="fas fa-box"></i> Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${select.value}">
                        <div class="fw-600">${opt.dataset.name}</div>
                        <div class="text-muted" style="font-size:11.5px;">${opt.dataset.code || ''} · Ditambahkan Manual</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:110px;">
                            <span class="text-muted" style="font-size:13px;">${opt.dataset.unit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>`;
            } else {
                tr.innerHTML = `
                    <td><span class="badge badge-purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fas fa-tools"></i> Alat</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                        <input type="hidden" name="items[${rowIndex}][tool_id]" value="${select.value}">
                        <div class="fw-600">${opt.dataset.name}</div>
                        <div class="text-muted" style="font-size:11.5px;">${opt.dataset.code || ''} · Ditambahkan Manual</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="1" step="1" required style="width:110px;">
                            <span class="text-muted" style="font-size:13px;">unit</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus alat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>`;
            }

            itemsBody.appendChild(tr);
            rowIndex++;
            updateBadge();

            // reset inputs
            select.value = '';
            manualQty.value = '1';
        }

        // Listeners
        mrSelect.addEventListener('change', () => {
            const mr = mrs.find(m => m.id === Number(mrSelect.value));
            renderMaterials(mr);
            autoWarehouses();
        });

        document.querySelectorAll('.ta-check').forEach(chk => {
            chk.addEventListener('change', () => {
                syncToolCheckboxes();

                if (!chk.checked) {
                    document.querySelectorAll(`tr[data-source="ta"][data-source-id="${chk.value}"]`).forEach(r => r.remove());
                    updateBadge();
                    const checkedAny = document.querySelector('.ta-check:checked');
                    if (!checkedAny) autoWarehouses();
                    return;
                }
                const ta = tas.find(t => t.id === Number(chk.value));
                if (ta) renderTool(ta);
                autoWarehouses();
            });
        });

        // Form Submit handler
        distForm.addEventListener('submit', function(e) {
            const rows = itemsBody.querySelectorAll('tr[data-kind]');
            if (rows.length === 0) {
                e.preventDefault();
                alert("Harap masukkan minimal 1 barang atau alat ke dalam Surat Jalan.");
                return false;
            }

            if (fromSelect.value === toSelect.value && fromSelect.value !== '') {
                e.preventDefault();
                alert("Gudang Asal dan Gudang Tujuan tidak boleh sama.");
                return false;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Surat Jalan...';
        });

        // Auto initialize
        if (mrSelect.value) {
            const mr = mrs.find(m => m.id === Number(mrSelect.value));
            renderMaterials(mr);
        }
        autoWarehouses();
        updateBadge();
    </script>
    @endpush
</x-app-layout>