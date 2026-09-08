<x-app-layout>
    <x-slot name="title">Detail Pembayaran {{ $payment->payment_number }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Pembayaran #{{ $payment->payment_number }}</h2>
            <p style="font-size:13px;color:#64748b;">Dibuat oleh {{ $payment->creator->name ?? 'Sistem' }} pada {{ $payment->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @php
                $badgeClass = match($payment->status) {
                    'verified' => 'badge-success',
                    'pending'  => 'badge-warning',
                    'rejected' => 'badge-danger',
                    default    => 'badge-secondary'
                };
            @endphp
            <span class="badge {{ $badgeClass }}" style="font-size:14px;padding:6px 12px;">
                Status: {{ $payment->status_label }}
            </span>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns: 2fr 1fr;gap:20px;">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#0f172a;">Informasi Pembayaran</h3>
            <table class="table" style="font-size:14px;">
                <tr>
                    <td style="width:200px;color:#64748b;">Nomor PO</td>
                    <td>
                        @if($payment->purchaseOrder)
                            <a href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" style="color:#0284c7;font-weight:600;">
                                {{ $payment->purchaseOrder->po_number }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Supplier</td>
                    <td><strong>{{ $payment->purchaseOrder->supplier->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Jumlah Dibayar</td>
                    <td style="font-size:18px;font-weight:700;color:#16a34a;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Metode Pembayaran</td>
                    <td>{{ strtoupper($payment->payment_method ?? 'TRANSFER') }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Tanggal Pembayaran</td>
                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Rekening / Bank</td>
                    <td>{{ $payment->bank_account ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">No Referensi</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Catatan</td>
                    <td>{{ $payment->notes ?? '-' }}</td>
                </tr>
            </table>

            @if($payment->status === 'rejected')
                <div style="margin-top:16px;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#991b1b;">
                    <strong>Alasan Penolakan:</strong> {{ $payment->rejection_reason }}
                </div>
            @endif
        </div>

        <div class="card" style="height:fit-content;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#0f172a;">Aksi Verifikasi</h3>
            
            @if($payment->status === 'pending')
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <form method="POST" action="{{ route('payments.verify', $payment) }}">
                        @csrf
                        <button type="submit" class="btn btn-success" style="width:100%;">✓ Verifikasi Pembayaran</button>
                    </form>

                    <button type="button" onclick="document.getElementById('reject-form').style.display='block'" class="btn btn-danger" style="width:100%;">✕ Tolak Pembayaran</button>

                    <div id="reject-form" style="display:none;margin-top:12px;padding:12px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;">
                        <form method="POST" action="{{ route('payments.reject', $payment) }}">
                            @csrf
                            <label class="form-label required">Alasan Penolakan</label>
                            <textarea name="rejection_reason" class="form-input mb-2" rows="2" required></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="document.getElementById('reject-form').style.display='none'" class="btn btn-sm btn-secondary">Batal</button>
                                <button type="submit" class="btn btn-sm btn-danger">Kirim Penolakan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <p style="font-size:13px;color:#64748b;">
                    Pembayaran ini telah diproses dengan status: <strong>{{ $payment->status_label }}</strong>.
                </p>
                @if($payment->verifier)
                    <p style="font-size:12px;color:#94a3b8;margin-top:8px;">
                        Diverifikasi/Ditolak oleh: {{ $payment->verifier->name }} pada {{ $payment->verified_at ? $payment->verified_at->format('d/m/Y H:i') : '-' }}
                    </p>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
