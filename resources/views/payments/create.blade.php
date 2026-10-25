<x-app-layout>
    <x-slot name="title">Catat Pembayaran PO & Kas Proyek</x-slot>

    @push('styles')
    <style>
        .payment-type-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
            position: relative;
        }

        .payment-type-card:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }

        .payment-type-card.active-waiting {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 2px 8px rgba(37,99,235,0.12);
        }

        .payment-type-card.active-instant {
            border-color: #059669;
            background: #ecfdf5;
            box-shadow: 0 2px 8px rgba(5,150,105,0.12);
        }
    </style>
    @endpush

    {{-- Breadcrumb & Header --}}
    <div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;margin-bottom:6px;">
            <a href="{{ route('payments.index') }}" style="color:#2563eb;text-decoration:none;">Pembayaran</a>
            <span>/</span>
            <span>Catat Pembayaran Baru</span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:12px;color:#475569;padding:6px 10px;border-radius:6px;">
                    <x-lucide-arrow-left />
                </a>
                <div>
                    <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;">
                        Catat Pembayaran PO
                    </h2>
                    <p style="font-size:12.5px;color:#64748b;margin:2px 0 0 0;">
                        Pilih antara PO Proyek reguler (menunggu verifikasi) atau Pembayaran Instan (potong saldo kas nota harian).
                    </p>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;">
                <button type="button" onclick="openTopupModal()" class="btn btn-sm btn-success" style="background:#059669;border-color:#059669;color:#ffffff;font-size:12px;font-weight:700;padding:7px 14px;border-radius:6px;">
                    <x-lucide-wallet class="me-1" /> + Top-Up Saldo Kas
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="font-size:12px;font-weight:600;padding:7px 14px;border-radius:6px;">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-3" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <x-lucide-circle-check style="font-size:16px;" />
        <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;">
        <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <x-lucide-circle-alert /> Terjadi Kesalahan Pengisian:
        </div>
        <ul style="margin:0;padding-left:18px;font-size:12px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="max-width: 860px; margin: 0 auto;">
        <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 8px -2px rgba(0,0,0,0.05);overflow:hidden;">
            <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <x-lucide-credit-card class="text-primary" style="font-size:15px;" />
                    <span style="font-size:14px;font-weight:700;color:#0f172a;">Formulir Pengajuan Pembayaran</span>
                </div>
                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:700;padding:3px 9px;">
                    2 Tipe: Reguler vs Instan
                </span>
            </div>

            <div class="card-body" style="padding:22px 24px;">
                <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
                    @csrf
                    
                    {{-- 1. PILIHAN JENIS PEMBAYARAN (RADIO CARD SELECTOR) --}}
                    <div class="mb-4">
                        <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:8px;">
                            Pilih Jenis Pembayaran <span class="text-danger">*</span>
                        </label>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            {{-- Option 1: PO Proyek Reguler (Menunggu Verifikasi) --}}
                            <label class="payment-type-card {{ old('payment_type', 'waiting') === 'waiting' ? 'active-waiting' : '' }}" id="cardWaiting" onclick="selectPaymentType('waiting')">
                                <div style="display:flex;align-items:flex-start;gap:10px;">
                                    <input type="radio" 
                                           name="payment_type" 
                                           id="typeWaiting" 
                                           value="waiting" 
                                           {{ old('payment_type', 'waiting') === 'waiting' ? 'checked' : '' }} 
                                           style="margin-top:3px;accent-color:#2563eb;">
                                    <div>
                                        <div style="font-weight:800;font-size:13.5px;color:#1e3a8a;display:flex;align-items:center;gap:6px;">
                                            <span>⏳ PO Proyek (Menunggu)</span>
                                        </div>
                                        <div style="font-size:11.5px;color:#475569;margin-top:3px;line-height:1.4;">
                                            Pengadaan vendor reguler pusat. Berstatus <strong>pending</strong> & harus menunggu persetujuan verifikasi bagian keuangan. Tidak memotong kas proyek.
                                        </div>
                                    </div>
                                </div>
                            </label>

                            {{-- Option 2: Pembayaran Instan (Saldo Kas Proyek / Nota Harian) --}}
                            <label class="payment-type-card {{ old('payment_type') === 'instant' ? 'active-instant' : '' }}" id="cardInstant" onclick="selectPaymentType('instant')">
                                <div style="display:flex;align-items:flex-start;gap:10px;">
                                    <input type="radio" 
                                           name="payment_type" 
                                           id="typeInstant" 
                                           value="instant" 
                                           {{ old('payment_type') === 'instant' ? 'checked' : '' }} 
                                           style="margin-top:3px;accent-color:#059669;">
                                    <div>
                                        <div style="font-weight:800;font-size:13.5px;color:#065f46;display:flex;align-items:center;gap:6px;">
                                            <span>⚡ Pembayaran Instan (Kas Proyek)</span>
                                            <span class="badge" style="background:#059669;color:#ffffff;font-size:9.5px;padding:1px 6px;border-radius:4px;">Auto Verified</span>
                                        </div>
                                        <div style="font-size:11.5px;color:#065f46;margin-top:3px;line-height:1.4;">
                                            Khusus saldo nota pembelian harian proyek (Admin PO). Langsung <strong>terverifikasi</strong>, memotong saldo kas proyek, & nota harian otomatis lunas.
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 2. Pilih PO --}}
                    <div class="mb-4">
                        <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Pilih Purchase Order (PO) <span class="text-danger">*</span>
                        </label>
                        <select name="purchase_order_id" 
                                id="poSelect" 
                                class="form-control" 
                                required 
                                onchange="if(this.value) window.location.href='{{ route('payments.create') }}?po_id=' + this.value"
                                style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                            <option value="">-- Pilih Purchase Order yang Memiliki Sisa Tagihan --</option>
                            @foreach($pos as $po)
                                @php
                                    $unpaidCount = $po->receipts ? $po->receipts->where('payment_status', 'unpaid')->count() : 0;
                                    $poProjectName = $po->project?->name;
                                @endphp
                                <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPO?->id) == $po->id) ? 'selected' : '' }}>
                                    {{ $po->po_number }} &mdash; {{ $po->supplier?->name }} (Sisa Boleh Dibayar: Rp {{ number_format($po->payable_remaining_amount, 0, ',', '.') }})
                                    @if($poProjectName)
                                        [{{ $poProjectName }}]
                                    @endif
                                    @if($unpaidCount > 0)
                                        &bull; [⚠️ {{ $unpaidCount }} Nota Belum Dibayar]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <span class="text-muted" style="font-size:11.5px;margin-top:4px;display:block;">
                            Menampilkan PO yang memiliki bukti Penerimaan Barang (GR) terkonfirmasi atau Nota Pembelian fisik dengan sisa tagihan.
                        </span>
                    </div>

                    {{-- 3. Box Preview Status Finansial PO --}}
                    @if($selectedPO)
                    @php
                        $poTotal = (float) $selectedPO->total_amount;
                        $poNilaiSah = (float) $selectedPO->nilai_sah;
                        $poVerified = (float) $selectedPO->verified_payment_amount;
                        $poPending = (float) $selectedPO->pending_payment_amount;
                        $poPayableRemaining = (float) $selectedPO->payable_remaining_amount;
                        $withheldData = $selectedPO->withheld_summary;
                        $poWithheld = (float) $withheldData['total_withheld'];
                        $pct = $poNilaiSah > 0 ? min(100, round(($poVerified / $poNilaiSah) * 100)) : ($poTotal > 0 ? min(100, round(($poVerified / $poTotal) * 100)) : 0);
                        $displayNilaiSah = $poNilaiSah > 0 ? $poNilaiSah : ($selectedPO->receipts ? min($poTotal ?: 999999999, (float)$selectedPO->receipts->sum('total_amount')) : 0);
                    @endphp
                    <div style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:16px;margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div>
                                <span style="font-size:12px;font-weight:700;color:#1e293b;">
                                    <x-lucide-truck class="text-primary me-1" /> Supplier: {{ $selectedPO->supplier?->name ?? '-' }}
                                </span>
                                <span class="text-muted ms-2" style="font-size:11px;">({{ $selectedPO->po_number }})</span>
                                @if($detectedProject)
                                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:700;margin-left:6px;">
                                        <x-lucide-building-2 class="me-1" /> {{ $detectedProject->name }}
                                    </span>
                                @endif
                            </div>
                            <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;">
                                Terbayar {{ $pct }}% dari Nilai Bukti Sah
                            </span>
                        </div>

                        <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-bottom:12px;">
                            <div style="height:100%;width:{{ $pct }}%;background:#2563eb;"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:10px;text-align:center;">
                            <div style="background:#ffffff;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                                <div style="font-size:10px;font-weight:700;color:#64748b;">TOTAL NILAI PO</div>
                                <div style="font-size:13.5px;font-weight:800;color:#0f172a;margin-top:2px;">
                                    Rp {{ number_format($poTotal, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#eff6ff;padding:10px;border-radius:6px;border:1px solid #bfdbfe;">
                                <div style="font-size:10px;font-weight:700;color:#1e40af;">NILAI SAH / BUKTI NOTA</div>
                                <div style="font-size:13.5px;font-weight:800;color:#1d4ed8;margin-top:2px;">
                                    Rp {{ number_format($displayNilaiSah, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#f0fdf4;padding:10px;border-radius:6px;border:1px solid #bbf7d0;">
                                <div style="font-size:10px;font-weight:700;color:#166534;">SUDAH DIBAYAR</div>
                                <div style="font-size:13.5px;font-weight:800;color:#15803d;margin-top:2px;">
                                    Rp {{ number_format($poVerified, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#ecfdf5;padding:10px;border-radius:6px;border:1px solid #a7f3d0;position:relative;">
                                <div style="font-size:10px;font-weight:700;color:#065f46;">SISA BISA DIBAYAR</div>
                                <div style="font-size:13.5px;font-weight:800;color:#059669;margin-top:2px;">
                                    Rp {{ number_format($poPayableRemaining, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#fef2f2;padding:10px;border-radius:6px;border:1px solid #fecaca;position:relative;">
                                <div style="font-size:10px;font-weight:700;color:#991b1b;">NILAI TERTAHAN</div>
                                <div style="font-size:13.5px;font-weight:800;color:#b91c1c;margin-top:2px;">
                                    Rp {{ number_format($poWithheld, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Rincian Nota Fisik Terkait PO --}}
                        @if($selectedPO->receipts && $selectedPO->receipts->count() > 0)
                        @php
                            $unpaidTotal = $selectedPO->receipts->where('payment_status', 'unpaid')->sum('total_amount');
                            $unpaidCount = $selectedPO->receipts->where('payment_status', 'unpaid')->count();
                        @endphp
                        <div style="margin-top:14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#1e293b;">
                                    <x-lucide-receipt class="text-primary me-1" /> Nota Pembelian Harian Terkait PO ({{ $selectedPO->receipts->count() }} Nota)
                                </span>
                                @if($unpaidCount > 0)
                                    <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700;font-size:11px;">
                                        ⚠️ {{ $unpaidCount }} Nota Belum Dibayar (Rp {{ number_format($unpaidTotal, 0, ',', '.') }})
                                    </span>
                                @else
                                    <span class="badge" style="background:#ecfdf5;color:#047857;font-weight:700;font-size:11px;">
                                        ✅ Semua Nota Fisik Lunas
                                    </span>
                                @endif
                            </div>

                            <div style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;padding-right:4px;">
                                @foreach($selectedPO->receipts as $rc)
                                <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;">
                                    <div>
                                        <strong>{{ $rc->receipt_number ? 'Nota #'.$rc->receipt_number : 'Nota ID #'.$rc->id }}</strong>
                                        <span class="text-muted ms-2">{{ $rc->receipt_date ? $rc->receipt_date->format('d/m/Y') : '-' }}</span>
                                        <span class="text-muted ms-2">({{ $rc->items->count() }} item)</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <strong style="color:#0f172a;">Rp {{ number_format($rc->total_amount, 0, ',', '.') }}</strong>
                                        <span class="badge badge-{{ $rc->status_color }}" style="font-size:10.5px;">{{ $rc->status_label }}</span>
                                        <a href="{{ route('purchase-receipts.show', $rc) }}" target="_blank" class="text-primary text-xs" title="Lihat Detail Nota">
                                            <x-lucide-external-link />
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($unpaidCount > 0)
                            <div style="margin-top:10px;padding-top:8px;border-top:1px dashed #cbd5e1;">
                                <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:#1e293b;cursor:pointer;font-weight:600;">
                                    <input type="checkbox" name="mark_receipts_as_paid" value="1" checked style="width:16px;height:16px;accent-color:#2563eb;">
                                    <span>Tandai otomatis {{ $unpaidCount }} nota fisik di atas sebagai <strong>Sudah Dibayar</strong> saat pengajuan pembayaran ini</span>
                                </label>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                            <button type="button" 
                                    onclick="setPaymentAmount('{{ (int) $poPayableRemaining }}')" 
                                    class="btn btn-sm btn-light border" 
                                    style="font-size:11.5px;font-weight:600;padding:4px 10px;color:#2563eb;background:#ffffff;">
                                <x-lucide-check-check class="me-1" /> Bayar Maksimal Nilai Sah (Rp {{ number_format($poPayableRemaining, 0, ',', '.') }})
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- 4. PROYEK & KOTAK SALDO KAS KECIL (INTERAKTIF & LIVE) --}}
                    <div class="mb-4" id="projectBalanceSection">
                        <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Proyek Terkait (Pemilik Kas Operasional) <span class="text-danger">*</span>
                        </label>

                        <div style="display:flex;gap:10px;align-items:center;">
                            <div style="flex:1;">
                                <select name="project_id" 
                                        id="projectSelect" 
                                        class="form-control" 
                                        required 
                                        onchange="updateBalanceCalculations()" 
                                        style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                                    <option value="">-- Pilih Proyek Terkait --</option>
                                    @foreach($projects as $prj)
                                        @php
                                            $isSel = (old('project_id', $detectedProject?->id) == $prj->id);
                                        @endphp
                                        <option value="{{ $prj->id }}" 
                                                data-balance="{{ (float)$prj->operational_balance }}" 
                                                data-name="{{ $prj->name }}"
                                                {{ $isSel ? 'selected' : '' }}>
                                            {{ $prj->name }} &mdash; Saldo Kas Tersedia: Rp {{ number_format($prj->operational_balance, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" onclick="openTopupModal()" class="btn btn-sm btn-light border" style="height:40px;padding:0 12px;font-size:12px;font-weight:600;color:#065f46;background:#f0fdf4;border-color:#86efac;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;">
                                <x-lucide-circle-plus /> Top-Up Saldo
                            </button>
                        </div>

                        {{-- Card Live Kalkulasi Saldo Kas Proyek (Tampil khusus saat mode Instant dipilih) --}}
                        <div id="instantBalanceBox" style="margin-top:12px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:14px 16px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="badge" style="background:#059669;color:#ffffff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;">
                                        ⚡ Kas Operasional Admin PO
                                    </span>
                                    <span style="font-size:12px;font-weight:700;color:#065f46;" id="instantProjectNameDisplay">
                                        {{ $detectedProject?->name ?? 'Pilih Proyek' }}
                                    </span>
                                </div>
                                <span id="balanceStatusBadge" class="badge" style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;">
                                    Siap Dihitung
                                </span>
                            </div>

                            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;text-align:center;">
                                <div style="background:#ffffff;padding:8px 10px;border-radius:6px;border:1px solid #bbf7d0;">
                                    <div style="font-size:10px;font-weight:700;color:#166534;">SALDO KAS TERSEDIA</div>
                                    <div style="font-size:13.5px;font-weight:800;color:#047857;margin-top:2px;" id="dispCurrentBalance">
                                        Rp 0
                                    </div>
                                </div>
                                <div style="background:#ffffff;padding:8px 10px;border-radius:6px;border:1px solid #bbf7d0;">
                                    <div style="font-size:10px;font-weight:700;color:#1e3a8a;">NOMINAL PEMBAYARAN</div>
                                    <div style="font-size:13.5px;font-weight:800;color:#1d4ed8;margin-top:2px;" id="dispPayAmount">
                                        Rp 0
                                    </div>
                                </div>
                                <div style="background:#ffffff;padding:8px 10px;border-radius:6px;border:1px solid #bbf7d0;" id="remainingBalanceBox">
                                    <div style="font-size:10px;font-weight:700;color:#065f46;">SISA SALDO SESUDAH</div>
                                    <div style="font-size:13.5px;font-weight:800;color:#059669;margin-top:2px;" id="dispRemainingBalance">
                                        Rp 0
                                    </div>
                                </div>
                            </div>

                            {{-- Alert jika saldo kurang --}}
                            <div id="insufficientBalanceAlert" style="display:none;margin-top:10px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:8px 12px;color:#991b1b;font-size:11.5px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <x-lucide-triangle-alert style="font-size:14px;" />
                                    <span>Saldo kas proyek tidak mencukupi! Silakan top-up saldo terlebih dahulu atau alihkan ke opsi <strong>PO Proyek (Menunggu)</strong>.</span>
                                </div>
                                <button type="button" onclick="openTopupModal()" class="btn btn-sm btn-danger" style="font-size:11px;font-weight:700;padding:3px 8px;">
                                    + Top-Up Sekarang
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Nominal & Tanggal --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Nominal Pembayaran (Rp) <span class="text-danger">*</span>
                            </label>
                            <div style="position:relative;">
                                <div style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:#64748b;font-size:13px;">
                                    Rp
                                </div>
                                <input type="number" 
                                       name="amount" 
                                       id="inputAmount"
                                       value="{{ old('amount', $selectedPO ? (int)$selectedPO->payable_remaining_amount : '') }}" 
                                       class="form-control" 
                                       min="1" 
                                       max="{{ $selectedPO ? $selectedPO->payable_remaining_amount : '' }}"
                                       step="any" 
                                       placeholder="0"
                                       required
                                       oninput="updateBalanceCalculations()"
                                       style="height:40px;padding-left:38px;font-size:14px;font-weight:700;color:#047857;border-radius:8px;border-color:#cbd5e1;">
                            </div>
                            <span class="text-muted" style="font-size:11px;margin-top:3px;display:block;">
                                Maksimal sesuai sisa nilai sah yang dapat dibayar.
                            </span>
                        </div>

                        <div>
                            <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Tanggal Pembayaran <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="payment_date" 
                                   value="{{ old('payment_date', date('Y-m-d')) }}" 
                                   class="form-control" 
                                   required
                                   style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                        </div>
                    </div>

                    {{-- 6. Metode & Rekening Bank --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Metode Pembayaran <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" id="paymentMethodSelect" class="form-control" required style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                                <option value="cash" {{ old('payment_method')=='cash' ? 'selected':'' }}>Tunai / Cash (Kas Operasional Proyek)</option>
                                <option value="bank_transfer" {{ old('payment_method', 'bank_transfer')=='bank_transfer' ? 'selected':'' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method')=='cheque' ? 'selected':'' }}>Cek / Giro</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Rekening Bank / Catatan Kas Sumber
                            </label>
                            <input type="text" 
                                   name="bank_account" 
                                   id="inputBankAccount"
                                   value="{{ old('bank_account') }}" 
                                   placeholder="Contoh: Kas Kecil Proyek / BCA 1234567890" 
                                   class="form-control"
                                   style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                        </div>
                    </div>

                    {{-- 7. No Referensi Bukti --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Nomor Referensi Transaksi / Bukti Pembayaran
                        </label>
                        <input type="text" 
                               name="reference_number" 
                               value="{{ old('reference_number') }}" 
                               placeholder="Contoh: NOTA-HARIAN-001 / Kwitansi / No Ref Transfer" 
                               class="form-control"
                               style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                    </div>

                    {{-- 8. Catatan Tambahan --}}
                    <div class="mb-4">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Catatan Tambahan
                        </label>
                        <textarea name="notes" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Keterangan peruntukan pembayaran nota harian, termin ke-berapa, atau memo lainnya..." 
                                  style="padding:10px 12px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Form Actions --}}
                    <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:16px;border-top:1px solid #f1f5f9;">
                        <a href="{{ route('payments.index') }}" class="btn btn-light border" style="font-size:12.5px;font-weight:600;padding:8px 18px;border-radius:8px;">
                            Batal
                        </a>
                        <button type="submit" id="btnSubmitPayment" class="btn btn-primary" style="font-size:12.5px;font-weight:700;padding:8px 22px;border-radius:8px;box-shadow:0 2px 6px rgba(37,99,235,0.25);display:inline-flex;align-items:center;gap:6px;">
                            <x-lucide-send /> <span id="btnSubmitText">Simpan & Ajukan Pembayaran</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TOP-UP SALDO CEPAT DI HALAMAN INI --}}
    <div id="modalTopupBalance" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);width:100%;max-width:500px;overflow:hidden;">
            <div style="background:#064e3b;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <x-lucide-wallet class="text-emerald-200" style="font-size:18px;" />
                    <h3 style="font-size:15px;font-weight:800;margin:0;">Top-Up Saldo Kas Proyek</h3>
                </div>
                <button type="button" onclick="closeTopupModal()" style="background:none;border:none;color:#d1fae5;cursor:pointer;font-size:18px;">
                    <x-lucide-x />
                </button>
            </div>
            <form method="POST" action="{{ route('payments.topupBalance') }}">
                @csrf
                <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                    <div>
                        <label class="form-label required" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:5px;display:block;">
                            Proyek Tujuan <span class="text-danger">*</span>
                        </label>
                        <select name="project_id" id="modalTopupProjectId" required class="form-control" style="height:40px;font-size:13px;border-radius:8px;">
                            @foreach($projects as $prj)
                                <option value="{{ $prj->id }}" {{ (old('project_id', $detectedProject?->id) == $prj->id) ? 'selected' : '' }}>
                                    {{ $prj->name }} (Saldo: Rp {{ number_format($prj->operational_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label required" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:5px;display:block;">
                            Nominal Pengisian Saldo (Rp) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="amount" min="1000" step="any" required placeholder="0" class="form-control" style="height:40px;font-size:14px;font-weight:700;border-radius:8px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:5px;display:block;">
                            No. Referensi / Bukti Transfer
                        </label>
                        <input type="text" name="reference_number" placeholder="Contoh: TRF-PUSAT-0192" class="form-control" style="height:38px;font-size:12.5px;border-radius:8px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:5px;display:block;">
                            Catatan Tambahan
                        </label>
                        <textarea name="notes" rows="2" placeholder="Catatan pengisian saldo..." class="form-control" style="font-size:12.5px;border-radius:8px;"></textarea>
                    </div>
                </div>
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="closeTopupModal()" class="btn btn-light border" style="font-size:12px;font-weight:600;">Batal</button>
                    <button type="submit" class="btn btn-success" style="font-size:12px;font-weight:700;background:#059669;border-color:#059669;">Simpan Top-Up Saldo</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function selectPaymentType(type) {
            const cardWaiting = document.getElementById('cardWaiting');
            const cardInstant = document.getElementById('cardInstant');
            const radioWaiting = document.getElementById('typeWaiting');
            const radioInstant = document.getElementById('typeInstant');
            const btnSubmitText = document.getElementById('btnSubmitText');
            const instantBox = document.getElementById('instantBalanceBox');
            const methodSelect = document.getElementById('paymentMethodSelect');
            const bankAccountInput = document.getElementById('inputBankAccount');

            if (type === 'instant') {
                radioInstant.checked = true;
                cardInstant.classList.add('active-instant');
                cardWaiting.classList.remove('active-waiting');
                btnSubmitText.textContent = '⚡ Proses Pembayaran Instan (Potong Kas Proyek)';
                instantBox.style.display = 'block';

                if (methodSelect && !methodSelect.value) {
                    methodSelect.value = 'cash';
                }
                if (bankAccountInput && !bankAccountInput.value) {
                    bankAccountInput.value = 'Kas Kecil Proyek / Petty Cash';
                }
            } else {
                radioWaiting.checked = true;
                cardWaiting.classList.add('active-waiting');
                cardInstant.classList.remove('active-instant');
                btnSubmitText.textContent = '⏳ Simpan & Ajukan Pembayaran (Menunggu Approval)';
                instantBox.style.display = 'none';
            }

            updateBalanceCalculations();
        }

        function setPaymentAmount(amount) {
            const input = document.getElementById('inputAmount');
            if (input) {
                input.value = amount;
                updateBalanceCalculations();
            }
        }

        function updateBalanceCalculations() {
            const projectSelect = document.getElementById('projectSelect');
            const amountInput = document.getElementById('inputAmount');
            const typeInstant = document.getElementById('typeInstant');
            const dispCurrent = document.getElementById('dispCurrentBalance');
            const dispPay = document.getElementById('dispPayAmount');
            const dispRemaining = document.getElementById('dispRemainingBalance');
            const statusBadge = document.getElementById('balanceStatusBadge');
            const alertBox = document.getElementById('insufficientBalanceAlert');
            const btnSubmit = document.getElementById('btnSubmitPayment');
            const nameDisplay = document.getElementById('instantProjectNameDisplay');

            if (!projectSelect) return;

            const selectedOpt = projectSelect.options[projectSelect.selectedIndex];
            const currentBalance = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-balance') || 0) : 0;
            const projectName = selectedOpt ? selectedOpt.getAttribute('data-name') : 'Pilih Proyek';
            const payAmount = parseFloat(amountInput ? amountInput.value : 0) || 0;
            const remainingBalance = currentBalance - payAmount;
            const isInstant = typeInstant && typeInstant.checked;

            if (nameDisplay) {
                nameDisplay.textContent = projectName || 'Pilih Proyek';
            }

            if (dispCurrent) {
                dispCurrent.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentBalance);
            }
            if (dispPay) {
                dispPay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(payAmount);
            }
            if (dispRemaining) {
                dispRemaining.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(remainingBalance);
                dispRemaining.style.color = (remainingBalance < 0) ? '#dc2626' : '#059669';
            }

            if (isInstant) {
                if (payAmount > 0 && currentBalance < payAmount) {
                    // Saldo kurang
                    if (statusBadge) {
                        statusBadge.textContent = '❌ Saldo Kurang';
                        statusBadge.style.background = '#fee2e2';
                        statusBadge.style.color = '#991b1b';
                    }
                    if (alertBox) alertBox.style.display = 'flex';
                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                        btnSubmit.style.opacity = '0.6';
                        btnSubmit.title = 'Saldo kas operasional proyek tidak mencukupi untuk melakukan pembayaran instan!';
                    }
                } else {
                    // Saldo cukup
                    if (statusBadge) {
                        statusBadge.textContent = '✅ Saldo Cukup';
                        statusBadge.style.background = '#dcfce7';
                        statusBadge.style.color = '#15803d';
                    }
                    if (alertBox) alertBox.style.display = 'none';
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.style.opacity = '1';
                        btnSubmit.removeAttribute('title');
                    }
                }
            } else {
                // Mode waiting (tidak tergantung saldo kas proyek)
                if (alertBox) alertBox.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = '1';
                    btnSubmit.removeAttribute('title');
                }
            }
        }

        function openTopupModal() {
            const projectSelect = document.getElementById('projectSelect');
            const modalSelect = document.getElementById('modalTopupProjectId');
            if (projectSelect && modalSelect && projectSelect.value) {
                modalSelect.value = projectSelect.value;
            }
            document.getElementById('modalTopupBalance').style.display = 'flex';
        }

        function closeTopupModal() {
            document.getElementById('modalTopupBalance').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const initialType = "{{ old('payment_type', 'waiting') }}";
            selectPaymentType(initialType);
        });
    </script>
    @endpush
</x-app-layout>
