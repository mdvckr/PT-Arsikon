<x-app-layout>
    <x-slot name="title">Laporan & Audit Terpadu</x-slot>

    <div x-data="{
        showActionModal: false,
        activeFindingId: null,
        activeFindingCode: '',
        activeFindingTitle: '',
        showVerifyModal: false,
        activePlanId: null,
        activePlanAction: '',
        openActionModal(id, code, title) {
            this.activeFindingId = id;
            this.activeFindingCode = code;
            this.activeFindingTitle = title;
            this.showActionModal = true;
        },
        openVerifyModal(planId, actionText) {
            this.activePlanId = planId;
            this.activePlanAction = actionText;
            this.showVerifyModal = true;
        }
    }">

        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-700" style="font-size:22px;color:#0f172a;">Laporan & Audit Terpadu</h2>
            <p class="text-muted" style="font-size:13.5px;margin-top:2px;">
                Pusat rekapitulasi data logistik gudang, deviasi pengadaan PO, serta pelacakan temuan audit internal & corrective action plans.
            </p>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:13.5px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:13.5px;">
                <ul class="mb-0" style="padding-left:18px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        @if(isset($auditSummary))
        <div class="grid grid-4 mb-4" style="gap:16px;">
            <div class="card" style="padding:16px 20px;border-left:4px solid #2563eb;">
                <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Temuan Terbuka (Active)</div>
                <div class="fw-700" style="font-size:24px;color:#0f172a;margin-top:4px;">
                    {{ $auditSummary['open_count'] }} <span style="font-size:13px;font-weight:normal;color:#64748b;">Kasus</span>
                </div>
                <div style="font-size:11.5px;color:#2563eb;margin-top:4px;">Status: Open & In Progress</div>
            </div>

            <div class="card" style="padding:16px 20px;border-left:4px solid #dc2626;">
                <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Lewat Batas Waktu (Overdue)</div>
                <div class="fw-700" style="font-size:24px;color:#dc2626;margin-top:4px;">
                    {{ $auditSummary['overdue_count'] }} <span style="font-size:13px;font-weight:normal;color:#dc2626;">Kasus</span>
                </div>
                <div style="font-size:11.5px;color:#ef4444;margin-top:4px;">Perlu eskalasi segera</div>
            </div>

            <div class="card" style="padding:16px 20px;border-left:4px solid #d97706;">
                <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Eksposur Finansial Terbuka</div>
                <div class="fw-700" style="font-size:22px;color:#b45309;margin-top:4px;">
                    Rp {{ number_format($auditSummary['total_exposure'], 0, ',', '.') }}
                </div>
                <div style="font-size:11.5px;color:#64748b;margin-top:4px;">Akumulasi nilai deviasi</div>
            </div>

            <div class="card" style="padding:16px 20px;border-left:4px solid #16a34a;">
                <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Selesai Bulan Ini (Closed)</div>
                <div class="fw-700" style="font-size:24px;color:#16a34a;margin-top:4px;">
                    {{ $auditSummary['closed_this_month'] }} <span style="font-size:13px;font-weight:normal;color:#64748b;">Kasus</span>
                </div>
                <div style="font-size:11.5px;color:#15803d;margin-top:4px;">Terverifikasi & ditutup</div>
            </div>
        </div>
        @endif

        <!-- 5 Modul Laporan Section -->
        <h3 class="fw-700 mb-3" style="font-size:16px;color:#1e293b;display:flex;align-items:center;gap:8px;">
            <x-lucide-file-text style="font-size:16px;color:#2563eb;" /> Modul Cetak & Filter Laporan Terpadu
        </h3>

        <div class="grid grid-3 mb-5" style="gap:18px;">

            {{-- 1. Laporan Stok Material --}}
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;color:#2563eb;flex-shrink:0;">
                            <x-lucide-boxes style="font-size:16px;" />
                        </div>
                        <div>
                            <div class="card-title" style="font-size:15px;margin:0;">1. Laporan Stok Material</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Kuantitas & valuasi inventori</div>
                        </div>
                    </div>
                    <div class="card-body" style="padding:20px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Posisi stok terkini material per gudang, batas minimum buffer stok, serta estimasi nilai total inventori.
                        </p>
                        <form method="GET" action="{{ route('reports.stock') }}">
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;">Filter Gudang</label>
                                <select name="warehouse_id" class="form-control" style="font-size:12.5px;">
                                    <option value="all">Semua Gudang</option>
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" style="font-size:12px;">Filter Kategori</label>
                                <select name="category_id" class="form-control" style="font-size:12.5px;">
                                    <option value="all">Semua Kategori</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:8px 14px;font-size:13px;">
                                Lihat Laporan Stok
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. Laporan Mutasi Stok --}}
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;color:#16a34a;flex-shrink:0;">
                            <x-lucide-arrow-left-right style="font-size:16px;" />
                        </div>
                        <div>
                            <div class="card-title" style="font-size:15px;margin:0;">2. Laporan Mutasi Stok</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Arus barang masuk & keluar</div>
                        </div>
                    </div>
                    <div class="card-body" style="padding:20px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Riwayat perpindahan material: penerimaan vendor, distribusi proyek, dan penyesuaian opname.
                        </p>
                        <form method="GET" action="{{ route('reports.mutation') }}">
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;">Filter Gudang</label>
                                <select name="warehouse_id" class="form-control" style="font-size:12.5px;">
                                    <option value="all">Semua Gudang</option>
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-2 mb-4" style="gap:10px;">
                                <div>
                                    <label class="form-label" style="font-size:12px;">Dari Tgl</label>
                                    <input type="date" name="date_from" class="form-control" style="font-size:12px;" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:12px;">Sampai Tgl</label>
                                    <input type="date" name="date_to" class="form-control" style="font-size:12px;" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:8px 14px;font-size:13px;">
                                Lihat Laporan Mutasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 3. Laporan Diskrepansi Opname --}}
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#dc2626;flex-shrink:0;">
                            <x-lucide-clipboard-check style="font-size:16px;" />
                        </div>
                        <div>
                            <div class="card-title" style="font-size:15px;margin:0;">3. Diskrepansi Opname</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Audit stok fisik vs sistem</div>
                        </div>
                    </div>
                    <div class="card-body" style="padding:20px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Daftar perbedaan kuantitas hasil stock opname lapangan dengan pencatatan pembukuan sistem WMS.
                        </p>
                        <form method="GET" action="{{ route('reports.discrepancy') }}">
                            <div class="mb-4">
                                <label class="form-label" style="font-size:12px;">Filter Gudang</label>
                                <select name="warehouse_id" class="form-control" style="font-size:12.5px;">
                                    <option value="all">Semua Gudang</option>
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-top:54px;">
                                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:8px 14px;font-size:13px;">
                                    Lihat Diskrepansi Opname
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 4. Laporan Status Alat --}}
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#fefce8;display:flex;align-items:center;justify-content:center;color:#ca8a04;flex-shrink:0;">
                            <x-lucide-wrench style="font-size:16px;" />
                        </div>
                        <div>
                            <div class="card-title" style="font-size:15px;margin:0;">4. Status & Lokasi Alat</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Kondisi & inventori peralatan kerja</div>
                        </div>
                    </div>
                    <div class="card-body" style="padding:20px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Status pemakaian alat: unit siap pakai (tersedia), dipinjam proyek, perbaikan, maupun rusak.
                        </p>
                        <form method="GET" action="{{ route('reports.tools') }}">
                            <div class="mb-4">
                                <label class="form-label" style="font-size:12px;">Filter Lokasi / Gudang</label>
                                <select name="warehouse_id" class="form-control" style="font-size:12.5px;">
                                    <option value="all">Semua Lokasi</option>
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-top:54px;">
                                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:8px 14px;font-size:13px;">
                                    Lihat Status Peralatan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 5. Laporan Audit Pengadaan & Keuangan PO (FITUR BARU) --}}
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;border:1.5px solid #bfdbfe;background:linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                <div>
                    <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid #dbeafe;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;color:#1d4ed8;flex-shrink:0;">
                            <x-lucide-receipt style="font-size:16px;" />
                        </div>
                        <div>
                            <div class="card-title" style="font-size:15px;margin:0;color:#1e3a8a;">5. Audit Pengadaan & PO</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Deviasi kedatangan vendor & nilai PO</div>
                        </div>
                    </div>
                    <div class="card-body" style="padding:20px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Monitoring kepatuhan vendor pengadaan: perbandingan tanggal order vs estimasi tiba, keterlambatan kirim, dan status realisasi barang.
                        </p>
                        <form method="GET" action="{{ route('reports.procurement-audit') }}">
                            <div class="grid grid-2 mb-4" style="gap:10px;">
                                <div>
                                    <label class="form-label" style="font-size:12px;">Dari Tgl PO</label>
                                    <input type="date" name="start_date" class="form-control" style="font-size:12px;" value="{{ date('Y-m-01') }}">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:12px;">Sampai Tgl PO</label>
                                    <input type="date" name="end_date" class="form-control" style="font-size:12px;" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div style="margin-top:54px;">
                                <button type="submit" class="btn btn-primary w-full" style="background:#1d4ed8;justify-content:center;padding:8px 14px;font-size:13px;">
                                    Audit Pengadaan PO
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Audit Findings & Action Plans Section -->
        <div class="card mb-4">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f8fafc;display:flex;align-items:center;justify-content:center;color:#0f172a;flex-shrink:0;">
                        <x-lucide-shield-alert style="font-size:18px;color:#dc2626;" />
                    </div>
                    <div>
                        <h4 class="card-title" style="margin:0;font-size:16px;">Tiket Temuan Audit & Rencana Tindak Lanjut (Action Plans)</h4>
                        <div class="text-muted" style="font-size:12px;margin-top:2px;">
                            Daftar temuan audit operasional gudang dan pengadaan yang memerlukan tindak lanjut perbaikan dari PIC.
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Temuan</th>
                            <th>Judul & Sumber</th>
                            <th>PIC & Lokasi</th>
                            <th style="text-align:right;">Eksposur Finansial</th>
                            <th>Batas Waktu</th>
                            <th style="text-align:center;">Tingkat Risiko</th>
                            <th style="text-align:center;">Status</th>
                            <th>Tindak Lanjut PIC</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentFindings as $finding)
                        <tr>
                            <td>
                                <span class="fw-700" style="color:#0f172a;">{{ $finding->finding_code }}</span>
                                <div class="text-muted" style="font-size:11px;">{{ $finding->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#1e293b;max-width:260px;">{{ $finding->title }}</div>
                                <span class="badge" style="font-size:10.5px;background:#f1f5f9;color:#475569;margin-top:4px;">
                                    {{ strtoupper(str_replace('_', ' ', $finding->source_type)) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-600">{{ $finding->pic->name ?? '-' }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $finding->warehouse->name ?? 'Gudang Pusat' }}</div>
                            </td>
                            <td style="text-align:right;font-weight:700;color:#dc2626;">
                                Rp {{ number_format($finding->financial_impact, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="fw-600" style="color:{{ $finding->is_overdue ? '#dc2626' : '#334155' }};">
                                    {{ $finding->due_date ? $finding->due_date->format('d/m/Y') : '-' }}
                                </div>
                                @if($finding->is_overdue)
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:10px;padding:2px 6px;">Overdue</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $riskBadge = match($finding->risk_level) {
                                        'Critical' => 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;',
                                        'High'     => 'background:#ffedd5;color:#9a3412;border:1px solid #fed7aa;',
                                        'Medium'   => 'background:#fef9c3;color:#854d0e;border:1px solid #fef08a;',
                                        default    => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                                    };
                                @endphp
                                <span class="badge" style="{{ $riskBadge }}">
                                    {{ $finding->risk_level }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $statusBadge = match($finding->status) {
                                        'Closed'       => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;',
                                        'Under Review' => 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;',
                                        'In Progress'  => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                                        default        => 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;',
                                    };
                                @endphp
                                <span class="badge" style="{{ $statusBadge }}">
                                    {{ $finding->status }}
                                </span>
                            </td>
                            <td>
                                @php $latestPlan = $finding->actionPlans->last(); @endphp
                                @if($latestPlan)
                                    <div style="font-size:12px;color:#334155;max-width:200px;line-height:1.4;">
                                        {{ Str::limit($latestPlan->corrective_action, 50) }}
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                                        <span class="badge" style="font-size:10px;background:{{ $latestPlan->verification_status === 'Approved' ? '#dcfce7' : ($latestPlan->verification_status === 'Rejected' ? '#fee2e2' : '#fef3c7') }};color:{{ $latestPlan->verification_status === 'Approved' ? '#166534' : ($latestPlan->verification_status === 'Rejected' ? '#991b1b' : '#92400e') }};">
                                            {{ $latestPlan->verification_status }}
                                        </span>
                                        @if($latestPlan->evidence_file)
                                            <a href="{{ asset('storage/' . $latestPlan->evidence_file) }}" target="_blank" class="text-primary" style="font-size:11px;text-decoration:underline;">
                                                Bukti
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size:11.5px;">Belum ada tindak lanjut</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    @if($finding->status !== 'Closed')
                                        <button type="button" 
                                            class="btn btn-xs btn-primary"
                                            @click="openActionModal({{ $finding->id }}, '{{ $finding->finding_code }}', '{{ addslashes($finding->title) }}')">
                                            Tindak Lanjut
                                        </button>
                                    @endif

                                    @if($latestPlan && $latestPlan->verification_status === 'Pending' && auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
                                        <button type="button"
                                            class="btn btn-xs btn-outline-success"
                                            @click="openVerifyModal({{ $latestPlan->id }}, '{{ addslashes($latestPlan->corrective_action) }}')">
                                            Verifikasi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:36px;color:#94a3b8;">
                                <x-lucide-check-circle style="font-size:28px;color:#16a34a;margin-bottom:6px;display:inline-block;" />
                                <div>Tidak ada temuan audit terbuka saat ini. Operasional gudang & pengadaan berjalan tertib.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($recentFindings, 'hasPages') && $recentFindings->hasPages())
                <div style="padding:16px 24px;border-top:1px solid #f1f5f9;">
                    {{ $recentFindings->links() }}
                </div>
            @endif
        </div>

        <!-- Modal 1: Submit Action Plan (PIC) -->
        <div x-show="showActionModal" 
            style="position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;"
            x-transition.opacity
            x-cloak>
            <div class="card" style="width:100%;max-width:550px;background:#ffffff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;"
                @click.outside="showActionModal = false">
                <div class="card-header" style="background:#f8fafc;padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h4 class="card-title" style="margin:0;font-size:15px;color:#0f172a;">Form Tindak Lanjut Perbaikan (Action Plan)</h4>
                        <div class="text-muted" style="font-size:12px;margin-top:2px;" x-text="'No. Temuan: #' + activeFindingCode"></div>
                    </div>
                    <button type="button" @click="showActionModal = false" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer;">&times;</button>
                </div>
                <form :action="'{{ url('/reports/findings') }}/' + activeFindingId + '/action-plan'" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body" style="padding:20px;">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Judul Temuan</label>
                            <div class="p-2.5 rounded bg-light" style="font-size:13px;color:#334155;background:#f1f5f9;border:1px solid #e2e8f0;" x-text="activeFindingTitle"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Rencana / Tindakan Koreksi (Corrective Action) <span class="text-danger">*</span></label>
                            <textarea name="corrective_action" rows="4" class="form-control" placeholder="Jelaskan investigasi penyebab masalah dan tindakan perbaikan nyata yang dilakukan di lapangan..." required minlength="10"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Bukti Pendukung / Berita Acara (BAST / Foto / Invoice)</label>
                            <input type="file" name="evidence_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xlsx,.doc,.docx">
                            <div class="text-muted" style="font-size:11.5px;margin-top:4px;">Format didukung: PDF, JPG, PNG, XLSX, DOCX (Maks 5MB)</div>
                        </div>
                    </div>
                    <div class="card-footer" style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" class="btn btn-secondary" @click="showActionModal = false">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim Bukti Perbaikan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Verify Action Plan (Auditor / Admin) -->
        <div x-show="showVerifyModal" 
            style="position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;"
            x-transition.opacity
            x-cloak>
            <div class="card" style="width:100%;max-width:550px;background:#ffffff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;"
                @click.outside="showVerifyModal = false">
                <div class="card-header" style="background:#f8fafc;padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h4 class="card-title" style="margin:0;font-size:15px;color:#0f172a;">Verifikasi Tindak Lanjut Audit</h4>
                        <div class="text-muted" style="font-size:12px;margin-top:2px;">Keputusan evaluasi perbaikan temuan</div>
                    </div>
                    <button type="button" @click="showVerifyModal = false" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer;">&times;</button>
                </div>
                <form :action="'{{ url('/reports/action-plans') }}/' + activePlanId + '/verify'" method="POST">
                    @csrf
                    <div class="card-body" style="padding:20px;">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Tindakan Yang Telah Dijalankan PIC</label>
                            <div class="p-2.5 rounded bg-light" style="font-size:13px;color:#334155;background:#f1f5f9;border:1px solid #e2e8f0;" x-text="activePlanAction"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Keputusan Verifikasi <span class="text-danger">*</span></label>
                            <div style="display:flex;gap:20px;margin-top:6px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13.5px;">
                                    <input type="radio" name="verification_status" value="Approved" checked>
                                    <span style="font-weight:600;color:#16a34a;">Setujui & Tutup Tiket (Approved & Closed)</span>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13.5px;">
                                    <input type="radio" name="verification_status" value="Rejected">
                                    <span style="font-weight:600;color:#dc2626;">Tolak & Minta Revisi (Rejected)</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight:600;font-size:12.5px;">Catatan Verifikator</label>
                            <textarea name="verifier_notes" rows="3" class="form-control" placeholder="Tulis catatan evaluasi atau alasan penolakan jika diperlukan..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer" style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" class="btn btn-secondary" @click="showVerifyModal = false">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Hasil Verifikasi</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
