<x-app-layout>
    <x-slot name="title">Detail Pembayaran #{{ $payment->payment_number }}</x-slot>

    @push('styles')
    <style>
        .payment-show-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .payment-show-layout {
                grid-template-columns: 1fr;
            }
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
    </style>
    @endpush

    {{-- Breadcrumb & Header --}}
    <div style="margin-bottom:18px;">
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;margin-bottom:6px;">
            <a href="{{ route('payments.index') }}" style="color:#2563eb;text-decoration:none;">Pembayaran</a>
            <span>/</span>
            <span>Detail #{{ $payment->payment_number }}</span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:12px;color:#475569;padding:6px 10px;border-radius:6px;">
                    <x-lucide-arrow-left />
                </a>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;">
                            #{{ $payment->payment_number }}
                        </h2>

                        @if($payment->isInstant())
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <x-lucide-zap class="text-warning me-1" /> Instan (Kas Proyek)
                            </span>
                        @else
                            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <x-lucide-clock class="text-blue-500 me-1" /> PO Proyek (Reguler)
                            </span>
                        @endif

                        @if($payment->status === 'verified')
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <x-lucide-circle-check class="me-1" /> Terverifikasi
                            </span>
                        @elseif($payment->status === 'pending')
                            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <x-lucide-clock class="me-1" /> Menunggu Verifikasi
                            </span>
                        @elseif($payment->status === 'rejected')
                            <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <x-lucide-circle-x class="me-1" /> Ditolak
                            </span>
                        @else
                            <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                {{ $payment->status_label }}
                            </span>
                        @endif
                    </div>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">
                        Dicatat oleh <strong>{{ $payment->creator?->name ?? 'Sistem' }}</strong> pada {{ $payment->created_at?->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:8px;">
                @if($payment->purchaseOrder)
                <a href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" class="btn btn-light border" style="font-size:12px;font-weight:600;padding:7px 12px;border-radius:6px;color:#334155;">
                    <x-lucide-receipt class="me-1 text-primary" /> Buka Purchase Order
                </a>
                @endif
                <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="font-size:12px;font-weight:600;padding:7px 12px;border-radius:6px;">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Feedback --}}
    @if(session('success'))
    <div class="alert alert-success mb-3" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <x-lucide-circle-check style="font-size:16px;" />
        <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger mb-3" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <x-lucide-triangle-alert style="font-size:16px;" />
        <span style="font-size:13px;font-weight:600;">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Main Layout --}}
    <div class="payment-show-layout">
        
        {{-- Left: Payment Details & Related PO --}}
        <div style="display:flex;flex-direction:column;gap:18px;">
            
            {{-- 1. Card Rincian Pembayaran --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <x-lucide-file-spreadsheet class="text-primary" style="font-size:14px;" />
                        <span style="font-size:13.5px;font-weight:700;color:#0f172a;">Rincian Transaksi Pembayaran</span>
                    </div>
                    <span class="text-muted" style="font-size:11.5px;">
                        Tgl Bayar: <strong>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</strong>
                    </span>
                </div>
                <div class="card-body" style="padding:16px 18px;">
                    
                    {{-- Highlight Nominal --}}
                    <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:0.4px;">
                                Total Nominal Dibayar
                            </div>
                            <div style="font-size:24px;font-weight:800;color:#15803d;margin-top:2px;">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div style="width:44px;height:44px;border-radius:10px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:20px;">
                            <x-lucide-arrow-left-right />
                        </div>
                    </div>

                    {{-- Saldo Mutasi Card jika Instant Payment --}}
                    @if($payment->isInstant() && $payment->balance_before !== null)
                    <div style="background:#f8fafc;border:1.5px solid #86efac;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:11.5px;font-weight:700;color:#065f46;">
                                <x-lucide-zap class="text-warning me-1" /> Mutasi Saldo Kas Proyek Admin PO
                            </span>
                            <span class="badge" style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;">
                                Kas Operasional
                            </span>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;text-align:center;">
                            <div style="background:#ffffff;padding:8px;border-radius:6px;border:1px solid #e2e8f0;">
                                <div style="font-size:10px;font-weight:700;color:#64748b;">SALDO SEBELUM</div>
                                <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-top:2px;">
                                    Rp {{ number_format($payment->balance_before, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#ffffff;padding:8px;border-radius:6px;border:1px solid #fee2e2;">
                                <div style="font-size:10px;font-weight:700;color:#b91c1c;">POTONGAN BAYAR</div>
                                <div style="font-size:12.5px;font-weight:800;color:#dc2626;margin-top:2px;">
                                    - Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#ffffff;padding:8px;border-radius:6px;border:1px solid #bbf7d0;">
                                <div style="font-size:10px;font-weight:700;color:#166534;">SISA SALDO KAS</div>
                                <div style="font-size:12.5px;font-weight:800;color:#15803d;margin-top:2px;">
                                    Rp {{ number_format($payment->balance_after, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Nomor Pembayaran</span>
                        <span class="info-val" style="font-family:monospace;font-size:13px;color:#2563eb;">{{ $payment->payment_number }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Jenis Pembayaran</span>
                        <span class="info-val">
                            @if($payment->isInstant())
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;font-weight:700;">
                                    <x-lucide-zap class="text-warning me-1" /> Pembayaran Instan (Kas Proyek)
                                </span>
                            @else
                                <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:11px;font-weight:700;">
                                    <x-lucide-clock class="text-blue-500 me-1" /> PO Proyek (Menunggu Verifikasi)
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Proyek Terkait</span>
                        <span class="info-val">
                            @php
                                $proj = $payment->project ?? $payment->purchaseOrder?->project;
                            @endphp
                            @if($proj)
                                <span style="color:#0f172a;font-weight:700;">
                                    <x-lucide-building-2 class="text-primary me-1" /> {{ $proj->name }}
                                </span>
                                <span class="text-muted ms-1">({{ $proj->code ?? 'PRJ' }})</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Tanggal Pelaksanaan Bayar</span>
                        <span class="info-val">{{ $payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Metode Pembayaran</span>
                        <span class="info-val">
                            @php
                                $m = strtolower($payment->payment_method ?? 'bank_transfer');
                                $mLabel = match($m) {
                                    'cash' => 'Tunai / Cash',
                                    'cheque' => 'Cek / Giro',
                                    default => 'Bank Transfer'
                                };
                            @endphp
                            <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:3px 8px;font-weight:700;">
                                {{ $mLabel }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Rekening Bank Tujuan / Sumber</span>
                        <span class="info-val">{{ $payment->bank_account ?: '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nomor Referensi Transaksi</span>
                        <span class="info-val" style="font-family:monospace;">{{ $payment->reference_number ?: '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Catatan Tambahan</span>
                        <span class="info-val" style="max-width:300px;font-weight:500;">{{ $payment->notes ?: '-' }}</span>
                    </div>

                    @if($payment->status === 'rejected')
                    <div style="margin-top:14px;padding:12px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;">
                        <div style="font-weight:700;font-size:12.5px;display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                            <x-lucide-triangle-alert /> Alasan Penolakan:
                        </div>
                        <div style="font-size:12px;line-height:1.4;">{{ $payment->rejection_reason }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 2. Card PO Terkait & Status Finansial Tagihan --}}
            @if($payment->purchaseOrder)
            @php
                $po = $payment->purchaseOrder;
                $poTotal = (float) $po->total_amount;
                $poNilaiSah = (float) $po->nilai_sah;
                $poPaid = (float) $po->verified_payment_amount;
                $poPending = (float) $po->pending_payment_amount;
                $poPayableRemaining = (float) $po->payable_remaining_amount;
                $withheldData = $po->withheld_summary;
                $poWithheld = (float) $withheldData['total_withheld'];
                $paidPercent = $poNilaiSah > 0 ? min(100, round(($poPaid / $poNilaiSah) * 100)) : 0;
            @endphp
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <x-lucide-receipt class="text-primary" style="font-size:14px;" />
                        <span style="font-size:13.5px;font-weight:700;color:#0f172a;">Informasi Purchase Order & Status Finansial</span>
                    </div>
                    <a href="{{ route('purchase-orders.show', $po) }}" style="font-size:11.5px;color:#2563eb;font-weight:700;text-decoration:none;">
                        Buka Detail PO &rarr;
                    </a>
                </div>
                <div class="card-body" style="padding:16px 18px;">
                    <div class="info-row">
                        <span class="info-label">Nomor Purchase Order</span>
                        <a href="{{ route('purchase-orders.show', $po) }}" class="info-val" style="color:#2563eb;text-decoration:none;font-weight:700;">
                            {{ $po->po_number }}
                        </a>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nama Supplier</span>
                        <span class="info-val">{{ $po->supplier?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Penanggung Risiko Pengiriman</span>
                        <span class="info-val">{{ $po->risk_bearer_label }}</span>
                    </div>

                    {{-- Progress Pembayaran PO --}}
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e2e8f0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:12px;">
                            <span style="font-weight:700;color:#475569;">Progress Pembayaran Terhadap Nilai Sah:</span>
                            <span style="font-weight:800;color:{{ $paidPercent >= 100 ? '#059669' : '#2563eb' }};">{{ $paidPercent }}% Lunas</span>
                        </div>
                        <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-bottom:12px;">
                            <div style="height:100%;width:{{ $paidPercent }}%;background:{{ $paidPercent >= 100 ? '#10b981' : '#3b82f6' }};border-radius:4px;transition:width 0.3s ease;"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:10px;text-align:center;">
                            <div style="background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #e2e8f0;">
                                <div style="font-size:10px;color:#64748b;font-weight:600;">TOTAL PO</div>
                                <div style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;">
                                    Rp {{ number_format($poTotal, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#f0fdf4;padding:10px;border-radius:8px;border:1px solid #bbf7d0;">
                                <div style="font-size:10px;color:#166534;font-weight:600;">SUDAH TERBAYAR</div>
                                <div style="font-size:13px;font-weight:800;color:#15803d;margin-top:2px;">
                                    Rp {{ number_format($poPaid, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#ecfdf5;padding:10px;border-radius:8px;border:1px solid #a7f3d0;">
                                <div style="font-size:10px;color:#065f46;font-weight:600;">SISA TAGIHAN</div>
                                <div style="font-size:13px;font-weight:800;color:#059669;margin-top:2px;">
                                    Rp {{ number_format($poPayableRemaining, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Right: Actions & Audit --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            
            {{-- Action Verifikasi Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px;display:flex;align-items:center;gap:8px;">
                    <x-lucide-shield class="text-primary" style="font-size:13px;" />
                    <span style="font-size:13px;font-weight:700;color:#0f172a;">Status & Verifikasi</span>
                </div>
                <div class="card-body" style="padding:16px;">
                    
                    @if($payment->isInstant())
                        {{-- Kasus Pembayaran Instan (Otomatis Terverifikasi via Kas Proyek) --}}
                        <div style="background:#ecfdf5;border:1.5px solid #86efac;border-radius:8px;padding:14px;color:#065f46;">
                            <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                <x-lucide-zap class="text-warning" /> Terverifikasi Instan Otomatis
                            </div>
                            <div style="font-size:12px;line-height:1.4;">
                                Pembayaran ini menggunakan <strong>Saldo Kas Operasional Proyek (Admin PO)</strong> sehingga langsung otomatis berstatus Terverifikasi saat diajukan, saldo kas proyek telah dipotong, dan nota harian terkait telah berstatus lunas.
                            </div>
                            @if($payment->creator)
                            <div style="font-size:11px;margin-top:8px;padding-top:8px;border-top:1px dashed #a7f3d0;color:#047857;">
                                Diproses oleh <strong>{{ $payment->creator->name }}</strong><br>
                                pada {{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '-' }}
                            </div>
                            @endif
                        </div>

                    @elseif($payment->status === 'pending')
                        {{-- Kasus PO Reguler Menunggu Verifikasi --}}
                        <p class="text-muted mb-3" style="font-size:12px;line-height:1.4;">
                            Pembayaran PO ini telah diajukan dan menunggu verifikasi bagian keuangan sebelum saldo tagihan PO diperbarui secara resmi.
                        </p>

                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <form method="POST" action="{{ route('payments.verify', $payment) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-full" style="justify-content:center;padding:9px;font-size:12.5px;font-weight:700;border-radius:6px;" onclick="return confirm('Verifikasi pembayaran senilai Rp {{ number_format($payment->amount, 0, ',', '.') }} ini?')">
                                    <x-lucide-circle-check class="me-1" /> Verifikasi Pembayaran
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('reject-form-wrap').style.display='block';this.style.display='none'" class="btn btn-outline-danger w-full" style="justify-content:center;padding:7px;font-size:12px;font-weight:600;border-radius:6px;">
                                <x-lucide-x class="me-1" /> Tolak Pembayaran
                            </button>

                            <div id="reject-form-wrap" style="display:none;margin-top:6px;padding:12px;border:1px solid #fca5a5;border-radius:8px;background:#fff5f5;">
                                <form method="POST" action="{{ route('payments.reject', $payment) }}">
                                    @csrf
                                    <label class="form-label required" style="font-size:11.5px;font-weight:700;color:#991b1b;margin-bottom:4px;">
                                        Alasan Penolakan
                                    </label>
                                    <textarea name="rejection_reason" class="form-control mb-2" rows="2" placeholder="Tuliskan alasan penolakan..." required style="font-size:12px;padding:6px 10px;border-radius:6px;border-color:#fca5a5;"></textarea>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('reject-form-wrap').style.display='none';document.querySelector('.btn-outline-danger').style.display='flex'" class="btn btn-sm btn-light border" style="font-size:11px;">
                                            Batal
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-danger" style="font-size:11px;font-weight:700;">
                                            Kirim Penolakan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @elseif($payment->status === 'verified')
                        <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:12px 14px;color:#065f46;">
                            <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                <x-lucide-circle-check class="text-success" /> Pembayaran Terverifikasi
                            </div>
                            <div style="font-size:11.5px;line-height:1.4;">
                                Saldo pembayaran telah disinkronkan ke PO terkait.
                            </div>
                            @if($payment->verifier)
                            <div style="font-size:11px;margin-top:6px;padding-top:6px;border-top:1px dashed #a7f3d0;color:#047857;">
                                Diverifikasi oleh <strong>{{ $payment->verifier->name }}</strong><br>
                                pada {{ $payment->verified_at ? $payment->verified_at->format('d/m/Y H:i') : '-' }}
                            </div>
                            @endif
                        </div>

                    @elseif($payment->status === 'rejected')
                        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;color:#991b1b;">
                            <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                <x-lucide-circle-x class="text-danger" /> Pembayaran Ditolak
                            </div>
                            <div style="font-size:11.5px;line-height:1.4;">
                                <strong>Alasan:</strong> {{ $payment->rejection_reason }}
                            </div>
                            @if($payment->verifier)
                            <div style="font-size:11px;margin-top:6px;padding-top:6px;border-top:1px dashed #fecaca;color:#7f1d1d;">
                                Ditolak oleh <strong>{{ $payment->verifier->name }}</strong><br>
                                pada {{ $payment->verified_at ? $payment->verified_at->format('d/m/Y H:i') : '-' }}
                            </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

            {{-- Audit Meta Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <x-lucide-history class="text-muted" style="font-size:12px;" />
                    <span style="font-size:12px;font-weight:700;color:#1e293b;">Riwayat Audit Sistem</span>
                </div>
                <div class="card-body" style="padding:12px 14px;font-size:11.5px;">
                    <div class="info-row" style="padding:4px 0;">
                        <span class="info-label">Dibuat Oleh</span>
                        <span class="info-val">{{ $payment->creator?->name ?? 'Sistem' }}</span>
                    </div>
                    <div class="info-row" style="padding:4px 0;">
                        <span class="info-label">Waktu Dibuat</span>
                        <span class="info-val">{{ $payment->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row" style="padding:4px 0;">
                        <span class="info-label">Terakhir Update</span>
                        <span class="info-val">{{ $payment->updated_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
