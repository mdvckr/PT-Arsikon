<x-app-layout>
    <x-slot name="title">Buat Permintaan Pengadaan</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('procurement.index') }}">Permintaan Pengadaan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>

    <form method="POST" action="{{ route('procurement.store') }}">
        @csrf

        {{-- PO Document Card --}}
        <div class="po-wrapper">

            {{-- PO Header --}}
            <div class="po-header">
                <div class="po-header-left">
                    <div class="po-company-logo">
                        <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo" style="height:48px;object-fit:contain;">
                    </div>
                    <div class="po-company-info">
                        <div class="po-company-name">PT. ARSIKON CIPTA KARYA</div>
                        <div class="po-company-detail">Formulir Permintaan Pengadaan Material</div>
                    </div>
                </div>
            </div>

            {{-- PO Meta Info --}}
            <div class="po-meta-grid">
                <div class="po-meta-row">
                    <div class="po-meta-item">
                        <label class="po-meta-label">Dibutuhkan Sebelum</label>
                        <input type="date" name="needed_by" class="po-meta-input" value="{{ old('needed_by') }}">
                    </div>
                    <div class="po-meta-item">
                        <label class="po-meta-label">Dibuat Oleh</label>
                        <div class="po-meta-value">{{ auth()->user()->name }}</div>
                    </div>
                    <div class="po-meta-item">
                        <label class="po-meta-label">Tanggal</label>
                        <div class="po-meta-value" id="currentDate"></div>
                    </div>
                    <div class="po-meta-item">
                        <label class="po-meta-label">No. PR</label>
                        <div class="po-meta-value po-badge-auto">Otomatis</div>
                    </div>
                </div>
            </div>

            {{-- Justification --}}
            <div class="po-justification">
                <label class="po-meta-label">Justifikasi / Alasan Pengadaan</label>
                <textarea name="justification" class="po-textarea" rows="2" placeholder="Jelaskan alasan dan tujuan pengadaan ini...">{{ old('justification') }}</textarea>
            </div>

            {{-- Items Table --}}
            <div class="po-table-section">
                <div class="po-table-header">
                    <div class="po-table-title">Daftar Item yang Dibutuhkan</div>
                    <button type="button" class="po-add-btn" onclick="addRow()">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>

                <div class="po-table-wrap">
                    <table class="po-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th class="col-no">NO</th>
                                <th class="col-material">JENIS MATERIAL</th>
                                <th class="col-qty">JUMLAH</th>
                                <th class="col-price">HARGA SATUAN (Rp)</th>
                                <th class="col-notes">KETERANGAN</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr id="row-0" class="po-row">
                                <td class="col-no"><span class="row-num">1</span></td>
                                <td class="col-material">
                                    <select name="items[0][material_id]" class="po-select" required>
                                        <option value="">— Pilih Material —</option>
                                        @foreach($materials as $m)
                                        <option value="{{ $m->id }}">{{ $m->code }} – {{ $m->name }} ({{ $m->unit?->abbreviation }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="col-qty">
                                    <input type="number" name="items[0][quantity]" class="po-input" min="0.01" step="0.01" value="1" required>
                                </td>
                                <td class="col-price">
                                    <input type="number" name="items[0][estimated_price]" class="po-input" min="0" step="100" value="0" placeholder="0">
                                </td>
                                <td class="col-notes">
                                    <input type="text" name="items[0][notes]" class="po-input" placeholder="Opsional">
                                </td>
                                <td class="col-action">
                                    <button type="button" class="po-del-btn" onclick="removeRow(0)" disabled title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="po-total-row">
                                <td colspan="3" style="text-align:right;font-weight:600;color:#1e40af;">
                                    <i class="fas fa-calculator"></i> Est. Total:
                                </td>
                                <td colspan="3">
                                    <span id="grandTotal" class="po-grand-total">Rp 0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="po-footer">
                <div class="po-signature-area">
                    <div class="po-sig-box">
                        <div class="po-sig-label">Mengetahui</div>
                        <div class="po-sig-line"></div>
                        <div class="po-sig-name">Manajer / Direktur</div>
                    </div>
                    <div class="po-sig-box">
                        <div class="po-sig-label">Pemohon</div>
                        <div class="po-sig-line"></div>
                        <div class="po-sig-name">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                <div class="po-action-buttons">
                    <a href="{{ route('procurement.index') }}" class="po-btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="po-btn-submit">
                        <i class="fas fa-paper-plane"></i> Ajukan PR
                    </button>
                </div>
            </div>

        </div>{{-- end po-wrapper --}}
    </form>

    @push('scripts')
    <script>
        // Set current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });

        let rowCount = 1;
        const materials = {!! json_encode($materials->map(fn($m) => ['id'=>$m->id,'code'=>$m->code,'name'=>$m->name,'abbr'=>$m->unit?->abbreviation])) !!};

        function buildSelect(idx) {
            let opts = "<option value=''>— Pilih Material —</option>";
            materials.forEach(m => {
                opts += `<option value="${m.id}">${m.code} – ${m.name} (${m.abbr ?? ''})</option>`;
            });
            return `<select name="items[${idx}][material_id]" class="po-select" required>${opts}</select>`;
        }

        function updateRowNumbers() {
            document.querySelectorAll('#itemsBody tr.po-row .row-num').forEach((el, i) => {
                el.textContent = i + 1;
            });
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('#itemsBody tr.po-row').forEach(row => {
                const qty = parseFloat(row.querySelector('input[name*="quantity"]')?.value || 0);
                const price = parseFloat(row.querySelector('input[name*="estimated_price"]')?.value || 0);
                total += qty * price;
            });
            document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        function addRow() {
            const idx = rowCount++;
            const tr = document.createElement('tr');
            tr.id = 'row-' + idx;
            tr.className = 'po-row po-row-new';
            tr.innerHTML = `
                <td class="col-no"><span class="row-num">${document.querySelectorAll('#itemsBody tr.po-row').length + 1}</span></td>
                <td class="col-material">${buildSelect(idx)}</td>
                <td class="col-qty"><input type="number" name="items[${idx}][quantity]" class="po-input" min="0.01" step="0.01" value="1" required oninput="updateTotal()"></td>
                <td class="col-price"><input type="number" name="items[${idx}][estimated_price]" class="po-input" min="0" step="100" value="0" placeholder="0" oninput="updateTotal()"></td>
                <td class="col-notes"><input type="text" name="items[${idx}][notes]" class="po-input" placeholder="Opsional"></td>
                <td class="col-action"><button type="button" class="po-del-btn" onclick="removeRow(${idx})" title="Hapus"><i class="fas fa-trash-alt"></i></button></td>`;
            document.getElementById('itemsBody').appendChild(tr);
            requestAnimationFrame(() => tr.classList.remove('po-row-new'));
            updateRowNumbers();
            updateTotal();
        }

        function removeRow(idx) {
            const r = document.getElementById('row-' + idx);
            if (r) {
                r.style.animation = 'rowFadeOut 0.25s ease forwards';
                setTimeout(() => { r.remove(); updateRowNumbers(); updateTotal(); }, 250);
            }
        }

        // Attach live total update to initial row
        document.querySelectorAll('#itemsBody input[type="number"]').forEach(el => {
            el.addEventListener('input', updateTotal);
        });

        // Enable/disable delete buttons (always keep at least 1 row)
        document.getElementById('itemsBody').addEventListener('click', () => {
            const rows = document.querySelectorAll('#itemsBody tr.po-row');
            rows.forEach(r => {
                const btn = r.querySelector('.po-del-btn');
                if (btn) btn.disabled = rows.length <= 1;
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        /* ===== PO Wrapper ===== */
        .po-wrapper {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
            max-width: 1100px;
            margin: 0 auto 32px auto;
        }

        /* ===== PO Header ===== */
        .po-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 32px;
            gap: 16px;
        }
        .po-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .po-company-logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .po-company-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .po-company-detail {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ===== PO Meta ===== */
        .po-meta-grid {
            padding: 20px 32px;
            background: #fafafa;
            border-bottom: 1px solid #e2e8f0;
        }
        .po-meta-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .po-meta-item { display: flex; flex-direction: column; gap: 6px; }
        .po-meta-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .po-meta-input {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s;
        }
        .po-meta-input:focus {
            border-color: #334155;
        }
        .po-meta-value {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .po-badge-auto {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            width: fit-content;
        }

        /* ===== Justification ===== */
        .po-justification {
            padding: 16px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .po-textarea {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            resize: vertical;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s;
        }
        .po-textarea:focus {
            border-color: #334155;
        }

        /* ===== Table Section ===== */
        .po-table-section { padding: 0; }
        .po-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 32px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .po-table-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .po-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .po-add-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .po-table-wrap { overflow-x: auto; }
        .po-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .po-table thead tr {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .po-table thead th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            white-space: nowrap;
        }
        .col-no { width: 50px; text-align: center !important; }
        .col-material { width: 35%; }
        .col-qty { width: 120px; }
        .col-price { width: 160px; }
        .col-notes { width: auto; }
        .col-action { width: 44px; text-align: center !important; }

        .po-row {
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.1s;
        }
        .po-row:hover { background: #f8fafc; }
        .po-row td { padding: 9px 14px; vertical-align: middle; }
        .row-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .po-select, .po-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: #fff;
            outline: none;
            transition: border-color 0.2s;
        }
        .po-select:focus, .po-input:focus {
            border-color: #334155;
        }

        .po-del-btn {
            background: none;
            border: 1px solid #e2e8f0;
            color: #94a3b8;
            border-radius: 6px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            font-size: 11px;
            margin: auto;
        }
        .po-del-btn:hover:not(:disabled) {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fca5a5;
        }
        .po-del-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .po-total-row {
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0;
        }
        .po-total-row td { padding: 12px 14px; font-size: 13.5px; }
        .po-grand-total {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        /* ===== Footer ===== */
        .po-footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            background: #fafafa;
            gap: 24px;
            flex-wrap: wrap;
        }
        .po-signature-area {
            display: flex;
            gap: 40px;
        }
        .po-sig-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .po-sig-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .po-sig-line {
            width: 120px;
            height: 1px;
            background: #cbd5e1;
            margin: 28px 0 4px 0;
        }
        .po-sig-name {
            font-size: 12px;
            color: #334155;
            font-weight: 600;
        }

        .po-action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .po-btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            background: #fff;
            text-decoration: none;
            transition: all 0.2s;
        }
        .po-btn-cancel:hover {
            border-color: #94a3b8;
            color: #1e293b;
            background: #f8fafc;
        }
        .po-btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 22px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .po-btn-submit:hover {
            background: #1e293b;
        }

        /* ===== Animations ===== */
        @keyframes rowFadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes rowFadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(20px); }
        }

        @media (max-width: 768px) {
            .po-header { flex-direction: column; align-items: flex-start; padding: 20px; }
            .po-meta-row { grid-template-columns: 1fr 1fr; }
            .po-footer { flex-direction: column; align-items: stretch; }
            .po-signature-area { display: none; }
            .po-action-buttons { justify-content: flex-end; }
            .po-meta-grid, .po-justification, .po-table-header { padding-left: 20px; padding-right: 20px; }
        }
    </style>
    @endpush
</x-app-layout>
