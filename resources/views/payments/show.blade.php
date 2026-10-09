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
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;">
                            #{{ $payment->payment_number }}
                        </h2>
                        @if($payment->status === 'verified')
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <i class="fas fa-circle-check me-1"></i> Terverifikasi
                            </span>
                        @elseif($payment->status === 'pending')
                            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <i class="fas fa-clock me-1"></i> Menunggu Verifikasi
                            </span>
                        @elseif($payment->status === 'rejected')
                            <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11.5px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                <i class="fas fa-circle-xmark me-1"></i> Ditolak
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
                    <i class="fas fa-receipt me-1 text-primary"></i> Buka Purchase Order
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
        <i class="fas fa-check-circle" style="font-size:16px;"></i>
        <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger mb-3" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-triangle-exclamation" style="font-size:16px;"></i>
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
                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size:14px;"></i>
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
                            <i class="fas fa-money-bill-transfer"></i>
                        </div>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Nomor Pembayaran</span>
                        <span class="info-val" style="font-family:monospace;font-size:13px;color:#2563eb;">{{ $payment->payment_number }}</span>
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
                            <i class="fas fa-triangle-exclamation"></i> Alasan Penolakan:
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
                $poPaid = (float) $po->paid_amount;
                $poRemaining = (float) $po->remaining_amount;
                $paidPercent = $poTotal > 0 ? min(100, round(($poPaid / $poTotal) * 100)) : 0;
            @endphp
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-receipt text-primary" style="font-size:14px;"></i>
                        <span style="font-size:13.5px;font-weight:700;color:#0f172a;">Informasi Purchase Order Terkait</span>
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
                        <span class="info-label">No. Telepon / Kontak Supplier</span>
                        <span class="info-val">{{ $po->supplier?->phone ?? '-' }}</span>
                    </div>

                    {{-- Progress Pembayaran PO --}}
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e2e8f0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:12px;">
                            <span style="font-weight:700;color:#475569;">Progress Pembayaran Tagihan PO:</span>
                            <span style="font-weight:800;color:{{ $paidPercent >= 100 ? '#059669' : '#2563eb' }};">{{ $paidPercent }}% Lunas</span>
                        </div>
                        <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-bottom:12px;">
                            <div style="height:100%;width:{{ $paidPercent }}%;background:{{ $paidPercent >= 100 ? '#10b981' : '#3b82f6' }};border-radius:4px;transition:width 0.3s ease;"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;text-align:center;">
                            <div style="background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #e2e8f0;">
                                <div style="font-size:10.5px;color:#64748b;font-weight:600;">TOTAL NILAI PO</div>
                                <div style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;">
                                    Rp {{ number_format($poTotal, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#f0fdf4;padding:10px;border-radius:8px;border:1px solid #bbf7d0;">
                                <div style="font-size:10.5px;color:#166534;font-weight:600;">TOTAL DIBAYAR</div>
                                <div style="font-size:13px;font-weight:800;color:#15803d;margin-top:2px;">
                                    Rp {{ number_format($poPaid, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="background:#fef2f2;padding:10px;border-radius:8px;border:1px solid #fecaca;">
                                <div style="font-size:10.5px;color:#991b1b;font-weight:600;">SISA TAGIHAN</div>
                                <div style="font-size:13px;font-weight:800;color:#b91c1c;margin-top:2px;">
                                    Rp {{ number_format($poRemaining, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Right Panel: Verification Action / Status Info --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            
            {{-- Action Verifikasi Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-shield-halved text-primary" style="font-size:13px;"></i>
                    <span style="font-size:13px;font-weight:700;color:#0f172a;">Aksi Verifikasi Keuangan</span>
                </div>
                <div class="card-body" style="padding:16px;">
                    
                    @if($payment->status === 'pending')
                        <p class="text-muted mb-3" style="font-size:12px;line-height:1.4;">
                            Pembayaran ini telah diajukan dan menunggu verifikasi bagian keuangan sebelum saldo tagihan PO diperbarui secara resmi.
                        </p>

                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <form method="POST" action="{{ route('payments.verify', $payment) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-full" style="justify-content:center;padding:9px;font-size:12.5px;font-weight:700;border-radius:6px;" onclick="return confirm('Verifikasi pembayaran senilai Rp {{ number_format($payment->amount, 0, ',', '.') }} ini?')">
                                    <i class="fas fa-check-circle me-1"></i> Verifikasi Pembayaran
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('reject-form-wrap').style.display='block';this.style.display='none'" class="btn btn-outline-danger w-full" style="justify-content:center;padding:7px;font-size:12px;font-weight:600;border-radius:6px;">
                                <i class="fas fa-xmark me-1"></i> Tolak Pembayaran
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
                                <i class="fas fa-circle-check text-success"></i> Pembayaran Terverifikasi
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
                                <i class="fas fa-circle-xmark text-danger"></i> Pembayaran Ditolak
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
                    <i class="fas fa-clock-rotate-left text-muted" style="font-size:12px;"></i>
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
