<x-app-layout>
    <x-slot name="title">Catat Pengeluaran Material</x-slot>

    {{-- Page Header (Style persis seperti halaman Buat Material) --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="breadcrumb" style="margin-bottom: 4px;">
                <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <span>Catat Pengeluaran Baru</span>
            </div>
            <h2 class="fw-700" style="font-size: 20px; color: #0f172a; margin: 0;">Catat Pengeluaran Material (Bon Lapangan)</h2>
            <p class="text-muted" style="font-size: 12.5px; margin: 2px 0 0;">Keluarkan material dari gudang proyek berdasarkan surat permintaan (MR) yang disetujui Site Manager</p>
        </div>
        <a href="{{ route('material-usages.index') }}" class="btn btn-light border" style="font-size: 13px; color: #475569;">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-4" style="border-radius: 8px;">
        <i class="fas fa-triangle-exclamation text-danger" style="font-size: 16px;"></i>
        <div>
            <strong>Terdapat kesalahan pada input pengeluaran:</strong>
            <ul style="margin: 4px 0 0 16px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('material-usages.store') }}" id="usageForm" onsubmit="return validateBeforeSubmit(this)">
        @csrf
        <input type="hidden" name="input_mode" id="inputMode" value="mr">

        <div style="display: flex; flex-direction: column; gap: 18px; max-width: 1080px; margin-bottom: 30px;">

            {{-- ==================== SECTION 1: METODE & SURAT PERMINTAAN (MR) ==================== --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: none; border-radius: 8px;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="fw-700" style="font-size: 14px; color: #0f172a;">1. Metode & Surat Permintaan Material (MR)</div>
                        <div class="text-muted" style="font-size: 12px; margin-top: 1px;">Pilih metode kontrol pengeluaran material gudang proyek</div>
                    </div>
                    <span class="badge badge-primary" style="font-size: 11px;">
                        Gudang Aktif: {{ $selectedWarehouse?->name ?? '-' }}
                    </span>
                </div>
                <div style="padding: 18px;">
                    {{-- Mode Selector Cards --}}
                    <div class="grid grid-2 mb-3" style="gap: 14px;">
                        <!-- Mode MR -->
                        <div class="mode-select-box active" id="modeCardMR" onclick="switchMode('mr')">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-file-shield text-primary" style="font-size: 16px;"></i>
                                    <strong style="font-size: 13.5px; color: #0f172a;">Tarik dari Permintaan (MR) Disetujui</strong>
                                </div>
                                <span class="mode-radio"></span>
                            </div>
                            <div class="badge badge-success mb-1" style="font-size: 10px; padding: 2px 7px;">
                                <i class="fas fa-check-circle"></i> 100% Terkontrol Sesuai Approval Site Manager
                            </div>
                            <p class="text-muted mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                Material keluar dibatasi sesuai kuota MR yang telah disetujui. Sisa kuota dan status MR terupdate otomatis.
                            </p>
                        </div>

                        <!-- Mode Manual -->
                        <div class="mode-select-box" id="modeCardManual" onclick="switchMode('manual')">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pen-to-square text-warning" style="font-size: 16px;"></i>
                                    <strong style="font-size: 13.5px; color: #0f172a;">Input Bebas / Insidentil (Tanpa MR)</strong>
                                </div>
                                <span class="mode-radio"></span>
                            </div>
                            <div class="badge badge-warning mb-1" style="font-size: 10px; padding: 2px 7px;">
                                <i class="fas fa-triangle-exclamation"></i> Kebutuhan Darurat Lapangan
                            </div>
                            <p class="text-muted mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                Pengeluaran langsung tanpa surat MR. Digunakan khusus kebutuhan darurat lapangan.
                            </p>
                        </div>
                    </div>

                    {{-- Dropdown MR --}}
                    <div id="mrPickerWrapper">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                            Pilih Nomor Permintaan Material (MR) <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="material_request_id" id="mrSelect" class="form-control" style="height: 38px; border-radius: 6px; font-size: 13px; font-weight: 500;" onchange="loadMRDetails(this.value)">
                            <option value="">— Pilih Nomor MR yang Telah Disetujui Site Manager —</option>
                            @foreach($approvedMRs as $mr)
                            <option value="{{ $mr->id }}" {{ old('material_request_id') == $mr->id ? 'selected' : '' }}>
                                #{{ $mr->request_number }} — {{ $mr->items->count() }} Jenis Item | Pemohon: {{ $mr->requestedBy?->name ?? 'User' }} ({{ $mr->status === 'partially_fulfilled' ? 'Terkirim Sebagian' : 'Disetujui' }})
                            </option>
                            @endforeach
                        </select>

                        @if($approvedMRs->isEmpty())
                        <div class="alert alert-warning mt-2 mb-0" style="font-size: 12px; padding: 9px 14px; border-radius: 6px;">
                            <i class="fas fa-info-circle me-1"></i> Tidak ditemukan MR berstatus <strong>Disetujui / Terkirim Sebagian</strong> pada gudang ini. Silakan buat MR baru atau gunakan mode input manual bila darurat.
                        </div>
                        @endif

                        <!-- Summary Box MR Terpilih -->
                        <div id="mrDetailPanel" style="display: none; margin-top: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                            <div style="padding: 8px 14px; background: #eff6ff; border-bottom: 1px solid #dbeafe; display: flex; justify-content: space-between; align-items: center;">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-double text-success"></i>
                                    <strong id="panelMrNumber" class="text-primary" style="font-size: 13px;">#REQ-...</strong>
                                </div>
                                <span class="badge badge-success" id="panelMrStatus" style="font-size: 11px;">Disetujui</span>
                            </div>
                            <div class="grid grid-4" style="padding: 10px 14px; gap: 10px; font-size: 12px;">
                                <div>
                                    <span class="text-muted" style="font-size: 11px; display: block;">Pemohon:</span>
                                    <strong id="panelMrRequester" style="color: #0f172a;">-</strong>
                                </div>
                                <div>
                                    <span class="text-muted" style="font-size: 11px; display: block;">Penyetuju (Site Manager):</span>
                                    <strong id="panelMrApprover" style="color: #0f172a;">-</strong>
                                </div>
                                <div>
                                    <span class="text-muted" style="font-size: 11px; display: block;">Tanggal Pengajuan:</span>
                                    <span id="panelMrDate" style="font-weight: 600; color: #0f172a;">-</span>
                                </div>
                                <div>
                                    <span class="text-muted" style="font-size: 11px; display: block;">Total Item:</span>
                                    <span id="panelMrItemCount" class="badge badge-purple" style="font-size: 11px;">-</span>
                                </div>
                            </div>
                            <div id="panelMrNotesWrapper" style="display: none; padding: 8px 14px; background: #fefce8; border-top: 1px solid #fef08a; font-size: 11.5px; color: #854d0e;">
                                <strong>Catatan / Memo MR:</strong> <span id="panelMrNotes"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Warning Mode Manual --}}
                    <div id="manualModeAlert" style="display: none; padding: 10px 14px; background: #fefce8; border: 1px solid #fef08a; border-radius: 6px; font-size: 12px; color: #854d0e;">
                        <i class="fas fa-triangle-exclamation me-1 text-warning"></i>
                        <strong>Perhatian:</strong> Pengeluaran manual tidak terikat dengan nomor surat MR. Gunakan hanya untuk kebutuhan mendesak lapangan.
                    </div>
                </div>
            </div>

            {{-- ==================== SECTION 2: INFORMASI BUKTI BON PENGELUARAN ==================== --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: none; border-radius: 8px;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size: 14px; color: #0f172a;">2. Informasi Bukti Bon Pengeluaran</div>
                    <div class="text-muted" style="font-size: 12px; margin-top: 1px;">Gudang sumber, nama penerima, zona pekerjaan, dan tanggal pengeluaran</div>
                </div>
                <div style="padding: 18px;">
                    <div class="grid grid-4" style="gap: 16px; margin-bottom: 14px;">
                        {{-- Field 1: Gudang Sumber --}}
                        <div>
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                                Gudang Sumber <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="warehouse_id" id="warehouseSelect" class="form-control" style="height: 38px; border-radius: 6px; font-size: 13px;" onchange="onWarehouseChanged(this.value)">
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $selectedWarehouse?->id == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field 2: Nama Penerima --}}
                        <div>
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                                Penerima (Mandor / Tukang) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="recipient_name" id="recipientNameInput" class="form-control"
                                   placeholder="Contoh: Pak Supri (Mandor Besi)"
                                   value="{{ old('recipient_name') }}" required
                                   style="height: 38px; border-radius: 6px; font-size: 13px;">
                        </div>

                        {{-- Field 3: Bagian Pekerjaan / Zona --}}
                        <div>
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                                Pekerjaan / Zona <span class="text-muted fw-400">(Disarankan)</span>
                            </label>
                            <input type="text" name="job_section" id="jobSectionInput" class="form-control"
                                   placeholder="Contoh: Pengecoran Kolom Lt. 2"
                                   value="{{ old('job_section') }}"
                                   style="height: 38px; border-radius: 6px; font-size: 13px;">
                        </div>

                        {{-- Field 4: Tanggal Pengeluaran --}}
                        <div>
                            <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                                Tanggal Pengeluaran <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="date" name="usage_date" class="form-control"
                                   value="{{ old('usage_date', date('Y-m-d')) }}" required
                                   style="height: 38px; border-radius: 6px; font-size: 13px;">
                        </div>
                    </div>

                    {{-- Field 5: Catatan Tambahan --}}
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em;">
                            Catatan Tambahan
                        </label>
                        <textarea name="notes" id="notesInput" class="form-control" rows="2"
                            placeholder="Catatan bon, instruksi khusus, atau kondisi serah terima barang..."
                            style="border-radius: 6px; font-size: 13px; resize: vertical;">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ==================== SECTION 3: RINCIAN MATERIAL YANG DIKELUARKAN ==================== --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: none; border-radius: 8px; overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div class="fw-700" style="font-size: 14px; color: #0f172a;">3. Rincian Material yang Dikeluarkan</div>
                        <div class="text-muted" style="font-size: 12px; margin-top: 1px;">Kuantitas yang dikeluarkan dibatasi oleh persetujuan MR dan stok fisik gudang</div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span id="itemsLoadingSpinner" style="display: none; font-size: 12px; color: #2563eb; font-weight: 600;">
                            <i class="fas fa-spinner fa-spin me-1"></i> Memuat data MR...
                        </span>

                        <div id="mrQuickActions" style="display: none;" class="flex gap-2">
                            <button type="button" class="btn btn-sm btn-light border text-primary fw-600" onclick="fillAllRemainingQuota()" title="Otomatis mengisi kuantitas sesuai sisa kuota yang disetujui" style="height: 32px; font-size: 12px;">
                                <i class="fas fa-wand-magic-sparkles text-primary me-1"></i> Penuhi Sisa Kuota
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-muted fw-600" onclick="resetAllQuantities()" title="Kosongkan nilai input kuantitas" style="height: 32px; font-size: 12px;">
                                <i class="fas fa-rotate-left me-1"></i> Reset Qty
                            </button>
                        </div>

                        <button type="button" class="btn btn-sm btn-secondary" id="btnAddManualRow" onclick="addManualRow()" style="display: none; height: 32px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-plus me-1"></i> Tambah Baris Material
                        </button>
                    </div>
                </div>

                {{-- Search Toolbar --}}
                <div style="padding: 10px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="position: relative; max-width: 380px; width: 100%;">
                        <i class="fas fa-search" style="position: absolute; left: 10px; top: 10px; font-size: 12px; color: #94a3b8;"></i>
                        <input type="text" id="tableSearchInput" class="form-control" placeholder="Cari nama material atau kode barang..." oninput="filterTableRows(this.value)" style="height: 32px; padding-left: 30px; font-size: 12px; border-radius: 6px;">
                    </div>
                    <span class="text-muted" id="rowCountIndicator" style="font-size: 12px; font-weight: 600;">
                        0 item
                    </span>
                </div>

                {{-- Table Rincian Material --}}
                <div style="overflow-x: auto;">
                    <table style="width: 100%; font-size: 12.5px; border-collapse: collapse; min-width: 780px;" id="itemsTable">
                        <thead>
                            <tr style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 10px 16px; font-weight: 700; text-align: left;">Nama Material</th>
                                <th style="padding: 10px 12px; font-weight: 700; text-align: center; width: 130px;" class="col-mr-only">Persetujuan MR</th>
                                <th style="padding: 10px 12px; font-weight: 700; text-align: center; width: 120px;" class="col-mr-only">Sisa Kuota</th>
                                <th style="padding: 10px 12px; font-weight: 700; text-align: center; width: 120px;">Stok Gudang</th>
                                <th style="padding: 10px 14px; font-weight: 700; text-align: center; width: 160px;">Jumlah Keluar <span style="color:#ef4444;">*</span></th>
                                <th style="padding: 10px 14px; font-weight: 700; text-align: left;">Keterangan</th>
                                <th style="padding: 10px 12px; width: 44px; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Dynamic rows --}}
                        </tbody>
                    </table>

                    {{-- Empty State jika belum pilih MR --}}
                    <div id="mrEmptyState" style="padding: 36px 20px; text-align: center; color: #94a3b8;">
                        <i class="fas fa-file-circle-question" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px;"></i>
                        <div style="font-size: 13.5px; font-weight: 600; color: #475569;">Belum Ada Permintaan (MR) yang Dipilih</div>
                        <div style="font-size: 12px; margin-top: 2px;">Silakan pilih nomor MR pada bagian <strong>1. Metode & Surat Permintaan Material</strong> di atas.</div>
                    </div>
                </div>

                {{-- Summary Bar --}}
                <div style="padding: 10px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 16px; font-size: 12px;">
                        <div>Material Terisi: <strong id="sumItemCount" style="color: #0f172a;">0</strong> jenis</div>
                        <div>Total Kuantitas: <strong id="sumQuantityCount" style="color: #2563eb;">0</strong></div>
                    </div>
                    <div id="statusValidationBadge">
                        <span class="badge badge-success" style="font-size: 11px;">
                            <i class="fas fa-circle-check"></i> Siap Dikeluarkan
                        </span>
                    </div>
                </div>
            </div>

            {{-- ==================== ACTION BUTTONS ==================== --}}
            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0;">
                <button type="submit" class="btn btn-primary" id="submitBtn" style="height: 38px; padding: 0 22px; font-size: 13px; font-weight: 600; border-radius: 6px;">
                    <i class="fas fa-check-circle me-1"></i> Simpan & Potong Stok Gudang
                </button>
                <a href="{{ route('material-usages.index') }}" class="btn btn-light border" style="height: 38px; padding: 0 16px; font-size: 13px; font-weight: 500; border-radius: 6px; color: #64748b;">
                    Batal
                </a>
            </div>

        </div>
    </form>

    {{-- ==================== STYLES ==================== --}}
    <style>
        .mode-select-box {
            border: 1.5px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .mode-select-box:hover {
            border-color: #93c5fd;
            background: #fbfdff;
        }
        .mode-select-box.active {
            border-color: #2563eb;
            background: #f0f7ff;
        }
        .mode-radio {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: inline-block;
            transition: all 0.2s;
        }
        .mode-select-box.active .mode-radio {
            border-color: #2563eb;
            background: #2563eb;
            box-shadow: inset 0 0 0 3px #ffffff;
        }
        .is-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        .row-fulfilled {
            background: #f8fafc !important;
            opacity: 0.65;
        }
        .row-out-of-stock {
            background: #fffbeb !important;
        }
    </style>

    {{-- ==================== JAVASCRIPT ==================== --}}
    <script>
        const availableMaterials = @json($materialsData);
        const selectedWarehouseId = {{ $selectedWarehouse?->id ?? 'null' }};
        let currentMode = 'mr';
        let rowIndex = 0;
        let mrItemsCache = [];

        function switchMode(mode) {
            currentMode = mode;
            document.getElementById('inputMode').value = mode;

            const cardMR = document.getElementById('modeCardMR');
            const cardManual = document.getElementById('modeCardManual');
            const mrPicker = document.getElementById('mrPickerWrapper');
            const manualAlert = document.getElementById('manualModeAlert');
            const btnAddManual = document.getElementById('btnAddManualRow');
            const mrQuickActions = document.getElementById('mrQuickActions');
            const mrCols = document.querySelectorAll('.col-mr-only');
            const mrSelect = document.getElementById('mrSelect');
            const emptyState = document.getElementById('mrEmptyState');

            if (mode === 'mr') {
                cardMR.classList.add('active');
                cardManual.classList.remove('active');

                mrPicker.style.display = 'block';
                manualAlert.style.display = 'none';
                btnAddManual.style.display = 'none';
                mrCols.forEach(el => el.style.display = '');

                if (mrSelect.value) {
                    mrQuickActions.style.display = 'flex';
                    emptyState.style.display = 'none';
                    loadMRDetails(mrSelect.value);
                } else {
                    mrQuickActions.style.display = 'none';
                    document.getElementById('itemsBody').innerHTML = '';
                    emptyState.style.display = 'block';
                }
            } else {
                cardManual.classList.add('active');
                cardMR.classList.remove('active');

                mrPicker.style.display = 'none';
                manualAlert.style.display = 'block';
                btnAddManual.style.display = 'inline-flex';
                mrQuickActions.style.display = 'none';
                emptyState.style.display = 'none';
                mrCols.forEach(el => el.style.display = 'none');

                mrSelect.value = '';
                document.getElementById('mrDetailPanel').style.display = 'none';

                const tbody = document.getElementById('itemsBody');
                tbody.innerHTML = '';
                addManualRow();
            }
            updateTableSummary();
        }

        function onWarehouseChanged(warehouseId) {
            window.location.href = '{{ route('material-usages.create') }}?warehouse_id=' + warehouseId;
        }

        async function loadMRDetails(mrId) {
            const tbody = document.getElementById('itemsBody');
            const panel = document.getElementById('mrDetailPanel');
            const spinner = document.getElementById('itemsLoadingSpinner');
            const emptyState = document.getElementById('mrEmptyState');
            const quickActions = document.getElementById('mrQuickActions');

            if (!mrId) {
                panel.style.display = 'none';
                quickActions.style.display = 'none';
                tbody.innerHTML = '';
                emptyState.style.display = 'block';
                updateTableSummary();
                return;
            }

            emptyState.style.display = 'none';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch(`/material-requests/${mrId}/details?warehouse_id=${selectedWarehouseId}`);
                if (!response.ok) {
                    throw new Error('Gagal memuat rincian MR dari server');
                }
                const data = await response.json();
                spinner.style.display = 'none';
                quickActions.style.display = 'flex';

                // Fill MR executive panel
                document.getElementById('panelMrNumber').innerText = '#' + data.request_number;
                document.getElementById('panelMrRequester').innerText = data.requested_by;
                document.getElementById('panelMrApprover').innerText = data.approved_by;
                document.getElementById('panelMrDate').innerText = data.created_at;
                document.getElementById('panelMrItemCount').innerText = data.items.length + ' Item';

                const statusBadge = document.getElementById('panelMrStatus');
                if (data.status === 'partially_fulfilled') {
                    statusBadge.className = 'badge badge-info';
                    statusBadge.innerText = 'Terkirim Sebagian';
                } else {
                    statusBadge.className = 'badge badge-success';
                    statusBadge.innerText = 'Disetujui Site Manager';
                }

                if (data.notes) {
                    document.getElementById('panelMrNotes').innerText = data.notes;
                    document.getElementById('panelMrNotesWrapper').style.display = 'block';
                } else {
                    document.getElementById('panelMrNotesWrapper').style.display = 'none';
                }
                panel.style.display = 'block';

                // Auto-fill suggested notes if empty
                const notesInput = document.getElementById('notesInput');
                if (!notesInput.value && data.notes) {
                    notesInput.value = 'Berdasarkan MR #' + data.request_number + ': ' + data.notes;
                }

                // Render MR items into table
                tbody.innerHTML = '';
                mrItemsCache = data.items;

                data.items.forEach(item => {
                    const rowId = 'row-' + rowIndex;
                    const remaining = parseFloat(item.qty_remaining || 0);
                    const stock = parseFloat(item.stock || 0);
                    const defaultQty = remaining > 0 && stock > 0 ? Math.min(remaining, stock) : (remaining > 0 ? remaining : 0);
                    const isFullyFulfilled = remaining <= 0;
                    const isOutOfStock = stock <= 0;

                    const tr = document.createElement('tr');
                    tr.id = rowId;
                    tr.className = (isFullyFulfilled ? 'row-fulfilled' : (isOutOfStock ? 'row-out-of-stock' : ''));
                    tr.style.borderBottom = '1px solid #f1f5f9';

                    tr.innerHTML = `
                        <td style="padding: 10px 16px;">
                            <input type="hidden" name="items[${rowIndex}][material_id]" value="${item.material_id}">
                            <div class="fw-600 text-dark" style="font-size: 13px;">${item.name}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <code style="font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #475569;">${item.code}</code>
                                <span class="badge badge-gray" style="font-size: 10px;">${item.category}</span>
                            </div>
                            ${isFullyFulfilled ? '<span class="badge badge-gray mt-1" style="font-size: 10px;"><i class="fas fa-check"></i> Kuota Terpenuhi</span>' : ''}
                        </td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <div class="fw-700 text-dark" style="font-size: 12.5px;">${item.qty_approved} ${item.unit}</div>
                            <div class="text-muted" style="font-size: 11px;">Sudah: ${item.qty_fulfilled} ${item.unit}</div>
                        </td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <span class="badge ${remaining > 0 ? 'badge-success' : 'badge-gray'}" style="font-size: 11.5px; font-weight: 700; padding: 3px 8px;">
                                ${remaining} ${item.unit}
                            </span>
                        </td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <span class="badge ${stock > 0 ? 'badge-primary' : 'badge-danger'}" style="font-size: 11.5px; font-weight: 700; padding: 3px 8px;">
                                ${stock} ${item.unit}
                            </span>
                            ${isOutOfStock ? '<div class="text-danger" style="font-size: 10px; font-weight: 600; margin-top: 2px;">Stok 0</div>' : ''}
                        </td>
                        <td style="padding: 10px 14px;">
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <input type="number" 
                                       name="items[${rowIndex}][quantity]" 
                                       id="${rowId}-qty" 
                                       class="form-control qty-input text-center"
                                       step="0.01" 
                                       min="0"
                                       data-remaining="${remaining}"
                                       data-stock="${stock}"
                                       placeholder="0" 
                                       value="${isFullyFulfilled ? 0 : defaultQty}" 
                                       ${isFullyFulfilled ? 'readonly' : 'required'}
                                       oninput="validateMRQty('${rowId}')"
                                       style="font-weight: 700; font-size: 13.5px; height: 34px; border-radius: 6px;">
                                <span class="text-muted fw-600" style="font-size: 11.5px; min-width: 28px;">${item.unit}</span>
                            </div>
                            <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10.5px; margin-top: 2px; display: none;"></div>
                        </td>
                        <td style="padding: 10px 14px;">
                            <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Catatan..." value="${item.notes || ''}" style="font-size: 12px; height: 34px; border-radius: 6px;">
                        </td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <button type="button" class="btn btn-sm btn-light text-danger border" onclick="removeRow('${rowId}')" title="Hapus dari pengeluaran ini" style="padding: 4px 8px; border-radius: 4px;">
                                <i class="fas fa-trash-can" style="font-size: 12px;"></i>
                            </button>
                        </td>
                    `;

                    tbody.appendChild(tr);
                    rowIndex++;
                    validateMRQty(rowId);
                });

                updateTableSummary();

            } catch (err) {
                spinner.style.display = 'none';
                alert('Gagal memuat rincian MR: ' + err.message);
            }
        }

        function validateMRQty(rowId) {
            const input = document.getElementById(rowId + '-qty');
            if (!input) return;

            const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
            const stock = parseFloat(input.getAttribute('data-stock') || 0);
            const val = parseFloat(input.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (val > remaining) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-circle-exclamation me-1"></i> Max kuota MR: ${remaining}`;
            } else if (val > stock) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Max stok: ${stock}`;
            } else {
                input.classList.remove('is-invalid');
                warningEl.style.display = 'none';
                warningEl.innerText = '';
            }
            updateTableSummary();
        }

        function fillAllRemainingQuota() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
                    const stock = parseFloat(input.getAttribute('data-stock') || 0);
                    const autoQty = Math.min(remaining, stock);
                    input.value = autoQty > 0 ? autoQty : 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        function resetAllQuantities() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    input.value = 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        function addManualRow(preselectedId = null, qtyVal = '') {
            const tbody = document.getElementById('itemsBody');
            const rowId = 'row-' + rowIndex;

            let optionsHtml = '<option value="">— Pilih Material Tersedia —</option>';
            availableMaterials.forEach(m => {
                const selected = preselectedId && preselectedId == m.id ? 'selected' : '';
                optionsHtml += `<option value="${m.id}" data-stock="${m.stock}" data-unit="${m.unit}" ${selected}>${m.name} (${m.code}) — Stok: ${m.stock} ${m.unit}</option>`;
            });

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 10px 16px;">
                    <select name="items[${rowIndex}][material_id]" class="form-control material-select" required onchange="onManualMaterialChange(this, '${rowId}')" style="font-size: 12.5px; height: 34px; border-radius: 6px;">
                        ${optionsHtml}
                    </select>
                </td>
                <td style="padding: 10px 12px; text-align: center;">
                    <span class="badge badge-gray" id="${rowId}-stock" style="font-size: 11.5px; font-weight: 700; padding: 3px 8px;">-</span>
                </td>
                <td style="padding: 10px 14px;">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <input type="number" name="items[${rowIndex}][quantity]" id="${rowId}-qty" class="form-control qty-input text-center"
                               step="0.01" min="0.01" placeholder="0" value="${qtyVal}" required oninput="validateManualQty('${rowId}')"
                               style="font-weight: 700; font-size: 13.5px; height: 34px; border-radius: 6px;">
                        <span class="text-muted fw-600" id="${rowId}-unit" style="font-size: 11.5px; min-width: 28px;">-</span>
                    </div>
                    <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10.5px; margin-top: 2px; display: none;"></div>
                </td>
                <td style="padding: 10px 14px;">
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Keterangan..." style="font-size: 12px; height: 34px; border-radius: 6px;">
                </td>
                <td style="padding: 10px 12px; text-align: center;">
                    <button type="button" class="btn btn-sm btn-light text-danger border" onclick="removeRow('${rowId}')" title="Hapus Item" style="padding: 4px 8px; border-radius: 4px;">
                        <i class="fas fa-trash-can" style="font-size: 12px;"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;

            if (preselectedId) {
                const sel = tr.querySelector('.material-select');
                onManualMaterialChange(sel, rowId);
            }
            updateTableSummary();
        }

        function onManualMaterialChange(select, rowId) {
            const selectedOpt = select.options[select.selectedIndex];
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');

            if (selectedOpt && selectedOpt.value) {
                const stock = parseFloat(selectedOpt.getAttribute('data-stock') || 0);
                const unit = selectedOpt.getAttribute('data-unit') || '';

                stockBadge.className = 'badge badge-primary';
                stockBadge.innerText = stock + ' ' + unit;
                unitLabel.innerText = unit;
                qtyInput.max = stock;
            } else {
                stockBadge.className = 'badge badge-gray';
                stockBadge.innerText = '-';
                unitLabel.innerText = '-';
                qtyInput.removeAttribute('max');
            }
            validateManualQty(rowId);
        }

        function validateManualQty(rowId) {
            const qtyInput = document.getElementById(rowId + '-qty');
            if (!qtyInput) return;
            const max = parseFloat(qtyInput.max || 0);
            const val = parseFloat(qtyInput.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (max > 0 && val > max) {
                qtyInput.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Max stok: ${max}`;
            } else {
                qtyInput.classList.remove('is-invalid');
                warningEl.style.display = 'none';
            }
            updateTableSummary();
        }

        function removeRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
            }
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length === 0) {
                if (currentMode === 'manual') {
                    addManualRow();
                } else {
                    document.getElementById('mrEmptyState').style.display = 'block';
                }
            }
            updateTableSummary();
        }

        function filterTableRows(query) {
            const lower = query.toLowerCase().trim();
            const rows = document.querySelectorAll('#itemsBody tr');
            let visibleCount = 0;

            rows.forEach(tr => {
                const text = tr.innerText.toLowerCase();
                if (!lower || text.includes(lower)) {
                    tr.style.display = '';
                    visibleCount++;
                } else {
                    tr.style.display = 'none';
                }
            });

            document.getElementById('rowCountIndicator').innerText = visibleCount + ' item ditampilkan';
        }

        function updateTableSummary() {
            const rows = document.querySelectorAll('#itemsBody tr');
            let totalItems = 0;
            let totalQuantity = 0;
            let hasInvalid = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const q = parseFloat(qtyInput.value || 0);
                    if (q > 0) {
                        totalItems++;
                        totalQuantity += q;
                    }
                    if (qtyInput.classList.contains('is-invalid')) {
                        hasInvalid = true;
                    }
                }
            });

            document.getElementById('sumItemCount').innerText = totalItems;
            document.getElementById('sumQuantityCount').innerText = totalQuantity.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            document.getElementById('rowCountIndicator').innerText = rows.length + ' item terdaftar';

            const badge = document.getElementById('statusValidationBadge');
            if (hasInvalid) {
                badge.innerHTML = `<span class="badge badge-danger" style="font-size: 11px;"><i class="fas fa-circle-xmark"></i> Ada Jumlah Melebihi Batas</span>`;
            } else if (totalItems === 0) {
                badge.innerHTML = `<span class="badge badge-gray" style="font-size: 11px;"><i class="fas fa-minus-circle"></i> Belum Ada Kuantitas</span>`;
            } else {
                badge.innerHTML = `<span class="badge badge-success" style="font-size: 11px;"><i class="fas fa-circle-check"></i> Siap Dikeluarkan (${totalItems} item)</span>`;
            }
        }

        function validateBeforeSubmit(form) {
            if (currentMode === 'mr') {
                const mrSelect = document.getElementById('mrSelect');
                if (!mrSelect.value) {
                    alert('⚠️ Silakan pilih Nomor Permintaan (MR) yang telah disetujui terlebih dahulu.');
                    mrSelect.focus();
                    return false;
                }
            }

            const rows = document.querySelectorAll('#itemsBody tr');
            if (rows.length === 0) {
                alert('⚠️ Daftar material yang dikeluarkan tidak boleh kosong.');
                return false;
            }

            let hasValidQty = false;
            let hasError = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const qty = parseFloat(qtyInput.value || 0);
                    if (qty > 0) hasValidQty = true;

                    if (qtyInput.classList.contains('is-invalid')) {
                        hasError = true;
                    }
                }
            });

            if (!hasValidQty) {
                alert('⚠️ Silakan masukkan jumlah pengeluaran minimal 1 material dengan kuantitas lebih dari 0.');
                return false;
            }

            if (hasError) {
                alert('⚠️ Terdapat input kuantitas yang melebihi kuota persetujuan MR atau stok fisik gudang. Mohon perbaiki baris bertanda merah.');
                return false;
            }

            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            }

            return true;
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            switchMode('mr');
            const mrSelect = document.getElementById('mrSelect');
            if (mrSelect && mrSelect.value) {
                loadMRDetails(mrSelect.value);
            }
        });
    </script>
</x-app-layout>
