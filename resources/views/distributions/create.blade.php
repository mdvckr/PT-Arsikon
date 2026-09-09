<x-app-layout>
    <x-slot name="title">Buat Surat Jalan / Pengiriman Barang & Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi & Surat Jalan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Surat Jalan Baru</span>
    </div>

    <div id="ajax-alert" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:6px;font-size:14px;"></div>

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
                'id'              => $mr->id,
                'number'          => $mr->request_number,
                'from_warehouse_id' => $mr->from_warehouse_id,
                'to_warehouse_id' => $mr->to_warehouse_id,
                'from_warehouse'  => $mr->fromWarehouse?->name ?? '',
                'to_warehouse'    => $mr->toWarehouse?->name ?? '',
                'items'           => $its,
            ];
        }
        $tas = [];
        foreach ($toolAssignments as $ta) {
            $tas[] = [
                'id'              => $ta->id,
                'number'          => $ta->assignment_number,
                'tool_id'         => $ta->tool_id,
                'tool_name'       => $ta->tool?->name ?? '',
                'tool_code'       => $ta->tool?->code ?? '',
                'quantity'        => (int) $ta->quantity,
                'from_warehouse_id' => $ta->from_warehouse_id,
                'from_warehouse'  => $ta->fromWarehouse?->name ?? '',
                'status'          => $ta->status,
                'notes'           => $ta->notes ?? '',
            ];
        }
        $whs = [];
        foreach ($warehouses as $w) {
            $whs[] = ['id' => $w->id, 'name' => $w->name, 'is_central' => (bool) $w->is_central];
        }
    @endphp

    <form id="distribution-form" method="POST" action="{{ route('distributions.store') }}">
        @csrf

        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Pilih Sumber --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice text-primary"></i> <span class="card-title">Pilih Sumber Permintaan</span>
                    </div>
                    <div class="card-body">
                        <div class="text-muted mb-3" style="font-size:13px;">
                            Barang & alat di bawah akan terisi otomatis dari permintaan yang sudah ada — tidak perlu memilih satu per satu.
                            Bisa gabungkan <strong>1 permintaan material</strong> dengan <strong>beberapa peminjaman alat</strong> dalam satu Surat Jalan.
                        </div>

                        <label class="form-label">Permintaan Material <span class="text-muted" style="font-weight:400;">(opsional — yang sudah disetujui)</span></label>
                        <select id="mr-select" class="form-control">
                            <option value="">-- Pilih Permintaan Material --</option>
                            @foreach($mrs as $mr)
                            <option value="{{ $mr['id'] }}"
                                    data-from="{{ $mr['from_warehouse_id'] }}"
                                    data-to="{{ $mr['to_warehouse_id'] }}">
                                {{ $mr['number'] }} — {{ $mr['from_warehouse'] }} → {{ $mr['to_warehouse'] }}
                            </option>
                            @endforeach
                        </select>

                        <label class="form-label mt-4">Peminjaman Alat <span class="text-muted" style="font-weight:400;">(opsional — pending / telah disetujui)</span></label>
                        @if(count($tas) > 0)
                        <div class="chip-list" id="ta-list" style="display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($tas as $ta)
                            <label class="chip" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-size:13px;">
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
                                        ({{ $ta['number'] }}) · jml {{ $ta['quantity'] }} · {{ $ta['from_warehouse'] }}
                                        · @if($ta['status'] === 'pending')<span style="color:#b45309;">{{ 'menunggu persetujuan' }}</span>@else<span style="color:#7c3aed;">{{ 'disetujui' }}</span>@endif
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <div class="text-muted" style="font-size:13px;">Belum ada peminjaman alat yang bisa dipilih.</div>
                        @endif
                    </div>
                </div>

                {{-- Preview Item --}}
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <div>
                            <i class="fas fa-boxes-stacked text-primary"></i> <span class="card-title">Barang / Alat yang Dikirim</span>
                        </div>
                        <span id="item-count-badge" class="badge badge-gray">0 item</span>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:8%;">Tipe</th>
                                    <th style="width:47%;">Barang / Alat</th>
                                    <th style="width:35%;">Jumlah (Qty) Maks {{ '' }}</th>
                                    <th style="width:10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="empty-row">
                                    <td colspan="4" class="text-center text-muted p-4">
                                        Pilih permintaan material dan/atau peminjaman alat di atas untuk mengisi otomatis.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tanggal & Catatan --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-truck text-primary"></i> <span class="card-title">Informasi Surat Jalan</span>
                    </div>
                    <div class="card-body">
                        <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                            </div>
                            <div>
                                <label class="form-label">Nama Supir / Kurir</label>
                                <input type="text" name="driver_name" value="{{ old('driver_name') }}" placeholder="Pak Supir" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">No. Kendaraan (Plat)</label>
                                <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="B 1234 CD" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">Catatan Pengiriman</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan tambahan..." class="form-control">
                            </div>
                        </div>
                        <div class="text-muted" style="font-size:12.5px;margin-top:8px;">
                            Gudang asal & tujuan di bawah otomatis mengikuti sumber, tetapi masih bisa diedit manual.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gudang & Submit --}}
            <div class="card" style="position:sticky;top:20px;">
                <div class="card-header">
                    <i class="fas fa-warehouse text-primary"></i> <span class="card-title">Gudang</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" id="from-warehouse" class="form-control" required>
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" id="to-warehouse" class="form-control" required>
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" id="btn-submit" class="btn btn-primary w-full" style="justify-content:center;">
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

        const itemsBody  = document.getElementById('itemsBody');
        const emptyRow   = document.getElementById('empty-row');
        const mrSelect   = document.getElementById('mr-select');
        const fromSelect = document.getElementById('from-warehouse');
        const toSelect   = document.getElementById('to-warehouse');

        let rowIndex = 0;
        let materialsRendered = false;

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
                // Distribusi: pusat -> proyek ; MR bertolak belakang (from=proyek, to=pusat)
                fromSelect.value = mr.to_warehouse_id || centralWarehouse(null);
                toSelect.value   = mr.from_warehouse_id || fromSelect.value;
            } else if (ta) {
                fromSelect.value = centralWarehouse(ta.from_warehouse_id);
                toSelect.value   = ta.from_warehouse_id;
            }
        }

        function renderMaterials(mr) {
            // remove material rows
            document.querySelectorAll('tr[data-kind="material"]').forEach(r => r.remove());
            materialsRendered = false;

            if (!mr || !mr.items || mr.items.length === 0) {
                materialsRendered = true;
                updateBadge();
                return;
            }

            for (const it of mr.items) {
                const tr = document.createElement('tr');
                tr.dataset.kind = 'material';
                tr.dataset.sourceId = mr.id;
                tr.innerHTML = `
                    <td><span class="badge badge-gray"><i class="fas fa-box"></i> Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${it.material_id}">
                        <div class="fw-600">${it.name}</div>
                        <div class="text-muted" style="font-size:11.5px;">${it.code || ''} · Sisa persetujuan ${it.remaining} ${it.unit}</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${it.remaining}" min="0.01" max="${it.remaining}" step="0.01" required style="width:110px;">
                            <span class="text-muted" style="font-size:13px;">${it.unit}</span>
                        </div>
                    </td>
                    <td></td>`;
                rowIndex++;
            }
            materialsRendered = true;
            updateBadge();
        }

        function renderTool(ta) {
            const existing = document.querySelector(`tr[data-source-id="${ta.id}"]`);
            if (existing) {
                existing.querySelector('input.qty-input').max = ta.quantity;
                existing.querySelector('input.qty-input').value = Math.min(existing.querySelector('input.qty-input').value, ta.quantity);
                return;
            }

            const tr = document.createElement('tr');
            tr.dataset.kind = 'tool';
            tr.dataset.sourceId = ta.id;
            tr.innerHTML = `
                <td><span class="badge badge-purple"><i class="fas fa-hand-holding"></i> Alat</span></td>
                <td>
                    <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                    <input type="hidden" name="items[${rowIndex}][tool_id]" value="${ta.tool_id}">
                    <input type="hidden" name="items[${rowIndex}][tool_assignment_id]" value="${ta.id}">
                    <div class="fw-600">${ta.tool_name}</div>
                    <div class="text-muted" style="font-size:11.5px;">${ta.tool_code || ''} · ${ta.number} · tersedia ${ta.quantity} unit</div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                            value="${ta.quantity}" min="0.01" max="${ta.quantity}" step="1" required style="width:110px;">
                        <span class="text-muted" style="font-size:13px;">unit</span>
                    </div>
                </td>
                <td style="text-align:right;">
                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeToolRow(${ta.id}, this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;
            itemsBody.appendChild(tr);
            rowIndex++;
            updateBadge();
        }

        function removeToolRow(id, btn) {
            const chk = document.querySelector(`.ta-check[value="${id}"]`);
            if (chk) chk.checked = false;
            document.querySelectorAll(`tr[data-kind="tool"][data-source-id="${id}"]`)
                .forEach(r => r.remove());
            updateBadge();
            autoWarehouses();
        }

        function updateBadge() {
            const rows = itemsBody.querySelectorAll('tr[data-kind]');
            const count = document.getElementById('item-count-badge');
            const empty = rows.length === 0;
            emptyRow.style.display = empty ? '' : 'none';
            count.textContent = `${rows.length} item`;
            count.className = 'badge ' + (empty ? 'badge-gray' : 'badge-primary');
        }

        mrSelect.addEventListener('change', () => {
            const mr = mrs.find(m => m.id === Number(mrSelect.value));
            renderMaterials(mr);
            autoWarehouses();
        });

        document.querySelectorAll('.ta-check').forEach(chk => {
            chk.addEventListener('change', () => {
                if (!chk.checked) {
                    const checkedAny = document.querySelector('.ta-check:checked');
                    if (!checkedAny) autoWarehouses();
                    return;
                }
                const ta = tas.find(t => t.id === Number(chk.value));
                if (ta) renderTool(ta);
                autoWarehouses();
            });
        });

        autoWarehouses();
    </script>
    @endpush
</x-app-layout>