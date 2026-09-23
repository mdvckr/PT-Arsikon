<x-app-layout>
    <x-slot name="title">Pinjamkan Alat (Input Jumlah & Stok)</x-slot>

    @push('styles')
    <style>
        .assignment-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }

        .stepper-btn {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-weight: 700;
            font-size: 13px;
            line-height: 1;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            padding: 0;
        }

        .stepper-btn:hover:not(:disabled) {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .stepper-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
            background: #f8fafc;
        }

        .qty-input-box {
            width: 40px;
            height: 26px;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            padding: 0 2px;
            color: #1e293b;
        }

        .qty-input-box:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .qty-input-box::-webkit-inner-spin-button,
        .qty-input-box::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .qty-input-box {
            -moz-appearance: textfield;
        }

        .selected-tool-row {
            background-color: #f0fdf4 !important;
        }

        .form-date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .sticky-sidebar .form-control {
            height: 35px;
            padding: 6px 10px;
            font-size: 12px;
            line-height: 1.4;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #0f172a;
        }

        .sticky-sidebar .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .sticky-sidebar textarea.form-control {
            height: auto !important;
            min-height: 60px;
            line-height: 1.4;
            padding: 8px 10px;
        }

        /* Responsive Breakpoints for Mobile */
        @media (max-width: 992px) {
            .assignment-layout {
                grid-template-columns: 1fr !important;
            }
            .sticky-sidebar {
                position: static !important;
            }
        }

        @media (max-width: 640px) {
            .form-date-grid {
                grid-template-columns: 1fr !important;
            }
            .tool-table-cell {
                padding: 6px 8px !important;
            }
            .tool-name-text {
                font-size: 12px !important;
            }
            .stepper-btn {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }
            .qty-input-box {
                width: 36px;
                height: 24px;
                font-size: 11.5px;
            }
            .mobile-hide-sn {
                display: none !important;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('tool-assignments.index') }}">Peminjaman Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Peminjaman per Jumlah Alat</span>
    </div>

    @if ($errors->has('quantities'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-triangle-exclamation"></i> {{ $errors->first('quantities') }}
    </div>
    @endif

    <form id="assignmentForm" method="POST" action="{{ route('tool-assignments.store') }}">
        @csrf
        <div class="assignment-layout mb-3">
            <!-- Left: Tools grouped by category -->
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-screwdriver-wrench text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Daftar Stok Alat Tersedia</span>
                    </div>
                    <div>
                        <span id="totalItemBadge" class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                            0 Unit Dipilih
                        </span>
                    </div>
                </div>

                <div class="card-body" style="padding:12px 14px;">
                    {{-- Search Box --}}
                    <div style="position:relative;margin-bottom:10px;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:11px;"></i>
                        <input type="text" id="toolSearch" class="form-control" placeholder="Cari nama alat, kode, atau kategori..." oninput="filterTools()" style="padding-left:30px;height:32px;border-radius:6px;font-size:12px;border-color:#cbd5e1;">
                    </div>

                    <div class="table-wrap" style="max-height:500px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;">
                        <table class="data-table mb-0" id="toolsTable">
                            <thead>
                                <tr style="background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:10.5px;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;">
                                    <th style="padding:7px 10px;">Nama Alat</th>
                                    <th style="text-align:center;width:105px;padding:7px 8px;">Stok</th>
                                    <th style="text-align:center;width:115px;padding:7px 8px;">Jumlah Pinjam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($toolCategories as $category)
                                @php $groupKey = 'cat-' . $category->id; @endphp
                                <tr class="cat-toggle" data-group="{{ $groupKey }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                    <td colspan="3" style="padding:6px 10px !important;">
                                        <div class="flex items-center justify-between" style="width:100%;">
                                            <div class="flex items-center" style="gap:6px;">
                                                <i class="fas fa-chevron-down cat-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                                <span class="fw-700" style="font-size:11.5px;text-transform:uppercase;letter-spacing:.02em;color:#1e293b;">{{ $category->name }}</span>
                                                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:600;">{{ $category->tools->count() }} alat</span>
                                            </div>
                                            <span class="text-muted" style="font-size:11px;font-weight:500;">{{ $category->tools->sum('stock_available') }} ready</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($category->tools as $tool)
                                <tr class="tool-row cat-rows {{ $groupKey }}" id="row-{{ $tool->id }}" style="transition:background-color .2s;border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 10px 6px 22px;" class="tool-table-cell">
                                        <div class="fw-600 tool-name-text" style="font-size:12.5px;color:#0f172a;">{{ $tool->name }}</div>
                                        <div style="display:flex;align-items:center;gap:4px;margin-top:1px;">
                                            @if($tool->code)
                                            <code style="font-size:10px;background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#475569;">{{ $tool->code }}</code>
                                            @endif
                                            @if($tool->serial_number)
                                            <span class="text-muted mobile-hide-sn" style="font-size:10.5px;">S/N: {{ $tool->serial_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:center;padding:6px 8px;" class="tool-table-cell">
                                        <span class="badge" style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:600;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                            <i class="fas fa-check" style="font-size:8.5px;"></i> {{ $tool->stock_available }} Ready
                                        </span>
                                    </td>
                                    <td style="text-align:center;padding:6px 8px;" class="tool-table-cell">
                                        <div style="display:inline-flex;align-items:center;justify-content:center;gap:3px;">
                                            <button type="button" class="stepper-btn" id="btn-minus-{{ $tool->id }}" onclick="stepQty({{ $tool->id }}, -1)" title="Kurangi" {{ old('quantities.'.$tool->id, 0) == 0 ? 'disabled' : '' }}>
                                                &minus;
                                            </button>
                                            <input type="number"
                                                   id="qty-{{ $tool->id }}"
                                                   name="quantities[{{ $tool->id }}]"
                                                   class="qty-input qty-input-box"
                                                   min="0" max="{{ $tool->stock_available }}"
                                                   value="{{ old('quantities.'.$tool->id, 0) }}"
                                                   oninput="validateAndCalculateTotal()"
                                                   data-tool-id="{{ $tool->id }}">
                                            <button type="button" class="stepper-btn" id="btn-plus-{{ $tool->id }}" onclick="stepQty({{ $tool->id }}, 1)" title="Tambah" {{ old('quantities.'.$tool->id, 0) >= $tool->stock_available ? 'disabled' : '' }}>
                                                &#43;
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                @endforelse

                                @if($uncategorizedTools->isNotEmpty())
                                <tr class="cat-toggle" data-group="cat-uncategorized" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                    <td colspan="3" style="padding:6px 10px !important;">
                                        <div class="flex items-center justify-between" style="width:100%;">
                                            <div class="flex items-center" style="gap:6px;">
                                                <i class="fas fa-chevron-down cat-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                                <span class="fw-700" style="font-size:11.5px;text-transform:uppercase;letter-spacing:.02em;color:#1e293b;">Lainnya</span>
                                                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:600;">{{ $uncategorizedTools->count() }} alat</span>
                                            </div>
                                            <span class="text-muted" style="font-size:11px;font-weight:500;">{{ $uncategorizedTools->sum('stock_available') }} ready</span>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($uncategorizedTools as $tool)
                                <tr class="tool-row cat-rows cat-uncategorized" id="row-{{ $tool->id }}" style="transition:background-color .2s;border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 10px 6px 22px;" class="tool-table-cell">
                                        <div class="fw-600 tool-name-text" style="font-size:12.5px;color:#0f172a;">{{ $tool->name }}</div>
                                        <div style="display:flex;align-items:center;gap:4px;margin-top:1px;">
                                            @if($tool->code)
                                            <code style="font-size:10px;background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#475569;">{{ $tool->code }}</code>
                                            @endif
                                            @if($tool->serial_number)
                                            <span class="text-muted mobile-hide-sn" style="font-size:10.5px;">S/N: {{ $tool->serial_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:center;padding:6px 8px;" class="tool-table-cell">
                                        <span class="badge" style="background:#ecfdf5;color:#047857;font-size:10.5px;font-weight:600;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                            <i class="fas fa-check" style="font-size:8.5px;"></i> {{ $tool->stock_available }} Ready
                                        </span>
                                    </td>
                                    <td style="text-align:center;padding:6px 8px;" class="tool-table-cell">
                                        <div style="display:inline-flex;align-items:center;justify-content:center;gap:3px;">
                                            <button type="button" class="stepper-btn" id="btn-minus-{{ $tool->id }}" onclick="stepQty({{ $tool->id }}, -1)" title="Kurangi" {{ old('quantities.'.$tool->id, 0) == 0 ? 'disabled' : '' }}>
                                                &minus;
                                            </button>
                                            <input type="number"
                                                   id="qty-{{ $tool->id }}"
                                                   name="quantities[{{ $tool->id }}]"
                                                   class="qty-input qty-input-box"
                                                   min="0" max="{{ $tool->stock_available }}"
                                                   value="{{ old('quantities.'.$tool->id, 0) }}"
                                                   oninput="validateAndCalculateTotal()"
                                                   data-tool-id="{{ $tool->id }}">
                                            <button type="button" class="stepper-btn" id="btn-plus-{{ $tool->id }}" onclick="stepQty({{ $tool->id }}, 1)" title="Tambah" {{ old('quantities.'.$tool->id, 0) >= $tool->stock_available ? 'disabled' : '' }}>
                                                &#43;
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endif

                                @if($toolCategories->isEmpty() && $uncategorizedTools->isEmpty())
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:28px 16px;color:#94a3b8;">
                                        <i class="fas fa-inbox" style="font-size:24px;margin-bottom:6px;display:block;color:#cbd5e1;"></i>
                                        <div style="font-weight:600;font-size:12.5px;color:#64748b;">Tidak ada stok alat tersedia</div>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Borrowing info form -->
            <div class="card sticky-sidebar" id="formCard" style="position:sticky;top:20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-clipboard-user text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Peminjaman</span>
                </div>
                <div class="card-body" style="padding:14px;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Nama Peminjam / Worker <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="borrower_name" class="form-control"
                               placeholder="Contoh: Pak Joko"
                               value="{{ old('borrower_name') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            No. Telepon / Kontak <span class="text-muted" style="font-weight:400;">(Opsional)</span>
                        </label>
                        <input type="text" name="borrower_phone" class="form-control"
                               placeholder="Contoh: 0812-3456-7890"
                               value="{{ old('borrower_phone') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Lokasi Pekerjaan / Site <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="location_name" class="form-control"
                               placeholder="Contoh: Gedung B Lt. 3"
                               value="{{ old('location_name') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Gudang Sumber Alat
                        </label>
                        <select name="warehouse_id" class="form-control" onchange="window.location.href = '{{ route('tool-assignments.create') }}?warehouse_id=' + this.value;" style="font-weight:500;">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $selectedWarehouseId) == $wh->id ? 'selected' : '' }}>{{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-date-grid">
                        <div>
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Tanggal Pinjam <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="assigned_at" class="form-control" value="{{ old('assigned_at', date('Y-m-d')) }}" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                                Estimasi Kembali
                            </label>
                            <input type="date" name="expected_return_at" class="form-control" value="{{ old('expected_return_at') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;margin-bottom:3px;">
                            Tujuan / Keperluan
                        </label>
                        <textarea name="purpose" class="form-control" rows="2" placeholder="Catatan keperluan pekerjaan...">{{ old('purpose') }}</textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button type="submit" id="btnSubmitAssignment" class="btn btn-primary w-full" style="justify-content:center;padding:9px;font-size:13px;font-weight:600;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Peminjaman
                        </button>
                        <a href="{{ route('tool-assignments.index') }}" class="btn btn-light border w-full text-center" style="justify-content:center;padding:7px;font-size:12px;font-weight:500;border-radius:6px;color:#64748b;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Floating Action Bar for Mobile View --}}
    <div id="mobileSummaryBar" style="display:none;position:fixed;bottom:14px;left:14px;right:14px;z-index:99;background:#0f172a;color:#ffffff;border-radius:10px;padding:10px 14px;box-shadow:0 8px 20px -3px rgba(0,0,0,0.3);align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:6px;">
            <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
            <span style="font-size:12px;font-weight:600;"><span id="mobileUnitCount">0</span> Unit Dipilih</span>
        </div>
        <button type="button" onclick="scrollToForm()" class="btn btn-sm btn-primary" style="padding:5px 12px;font-size:11.5px;font-weight:600;border-radius:5px;box-shadow:none;">
            Lanjut Isi Form &darr;
        </button>
    </div>

    @push('scripts')
    <script>
        function stepQty(toolId, delta) {
            const input = document.getElementById('qty-' + toolId);
            if (!input) return;
            const max = parseInt(input.getAttribute('max')) || 0;
            let val = (parseInt(input.value) || 0) + delta;
            if (val < 0) val = 0;
            if (val > max) val = max;
            input.value = val;
            validateAndCalculateTotal();
        }

        function validateAndCalculateTotal() {
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(function (input) {
                const max = parseInt(input.getAttribute('max')) || 0;
                let val = parseInt(input.value) || 0;
                if (val < 0) val = 0;
                if (val > max) val = max;
                input.value = val;
                total += val;

                const toolId = input.getAttribute('data-tool-id');
                const row = document.getElementById('row-' + toolId);
                const btnMinus = document.getElementById('btn-minus-' + toolId);
                const btnPlus = document.getElementById('btn-plus-' + toolId);

                // Visual row highlight
                if (row) {
                    if (val > 0) {
                        row.classList.add('selected-tool-row');
                    } else {
                        row.classList.remove('selected-tool-row');
                    }
                }

                // Stepper button disabled states
                if (btnMinus) btnMinus.disabled = (val <= 0);
                if (btnPlus) btnPlus.disabled = (val >= max);
            });

            // Update badge text
            const badge = document.getElementById('totalItemBadge');
            if (badge) {
                badge.innerText = total + ' Unit Dipilih';
                if (total > 0) {
                    badge.style.background = '#eff6ff';
                    badge.style.color = '#2563eb';
                    badge.style.borderColor = '#93c5fd';
                } else {
                    badge.style.background = '#f1f5f9';
                    badge.style.color = '#64748b';
                    badge.style.borderColor = '#cbd5e1';
                }
            }

            // Mobile summary bar
            const mobileBar = document.getElementById('mobileSummaryBar');
            const mobileCount = document.getElementById('mobileUnitCount');
            if (mobileBar && mobileCount) {
                mobileCount.innerText = total;
                if (window.innerWidth <= 992 && total > 0) {
                    mobileBar.style.display = 'flex';
                } else {
                    mobileBar.style.display = 'none';
                }
            }
        }

        function scrollToForm() {
            const formCard = document.getElementById('formCard');
            if (formCard) {
                formCard.scrollIntoView({ behavior: 'smooth' });
                const borrowerInput = formCard.querySelector('input[name="borrower_name"]');
                if (borrowerInput) setTimeout(() => borrowerInput.focus(), 300);
            }
        }

        function filterTools() {
            const keyword = document.getElementById('toolSearch').value.toLowerCase().trim();
            const toggleRows = document.querySelectorAll('.cat-toggle');
            const toolRows   = document.querySelectorAll('#toolsTable tbody tr.tool-row');

            if (!keyword) {
                toolRows.forEach(function(r)   { r.style.display = ''; });
                toggleRows.forEach(function(r) { r.style.display = ''; });
                return;
            }

            toggleRows.forEach(function(r) { r.style.display = 'none'; });

            toolRows.forEach(function (row) {
                const text = row.innerText.toLowerCase();
                if (text.indexOf(keyword) !== -1) {
                    row.style.display = '';
                    const group = Array.from(row.classList).find(function(c) {
                        return c.startsWith('cat-') && c !== 'cat-rows';
                    });
                    if (group) {
                        const header = document.querySelector('.cat-toggle[data-group="' + group + '"]');
                        if (header) header.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Form Submit Validation
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(function(input) {
                total += (parseInt(input.value) || 0);
            });

            if (total <= 0) {
                e.preventDefault();
                alert("Harap pilih minimal 1 unit alat yang akan dipinjam.");
                document.getElementById('toolSearch').focus();
                return false;
            }

            const btn = document.getElementById('btnSubmitAssignment');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengajukan...';
            }
        });

        (function () {
            function initAccordion() {
                document.querySelectorAll('.cat-toggle').forEach(function (row) {
                    const newRow = row.cloneNode(true);
                    row.parentNode.replaceChild(newRow, row);
                    const group = newRow.getAttribute('data-group');
                    newRow.addEventListener('click', function () {
                        const collapsed = newRow.classList.toggle('collapsed');
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            r.style.display = collapsed ? 'none' : '';
                        });
                        const chev = newRow.querySelector('.cat-chev');
                        if (chev) chev.style.transform = collapsed ? 'rotate(-90deg)' : '';
                    });
                });
            }

            window.addEventListener('resize', function() {
                validateAndCalculateTotal();
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () { initAccordion(); validateAndCalculateTotal(); });
            } else {
                initAccordion(); validateAndCalculateTotal();
            }
        })();
    </script>
    @endpush
</x-app-layout>
