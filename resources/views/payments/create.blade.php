<x-app-layout>
    <x-slot name="title">Catat Pembayaran PO</x-slot>

    <div class="mb-4">
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Catat Pembayaran PO</h2>
        <p style="font-size:13px;color:#64748b;">Pilih PO dan masukkan rincian pembayaran</p>
    </div>

    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div style="display:grid;gap:16px;">
                <div>
                    <label class="form-label required">Pilih Purchase Order (PO)</label>
                    <select name="purchase_order_id" class="form-select" required onchange="window.location.href='{{ route('payments.create') }}?po_id=' + this.value">
                        <option value="">-- Pilih PO --</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPO?->id) == $po->id) ? 'selected' : '' }}>
                                {{ $po->po_number }} - {{ $po->supplier->name }} (Sisa: Rp {{ number_format($po->remaining_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror
                </div>

                @if($selectedPO)
                    <div style="background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;font-size:13px;">
                        <div class="flex justify-between mb-1"><span>Total PO:</span> <strong>Rp {{ number_format($selectedPO->total_amount, 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between mb-1"><span>Sudah Dibayar:</span> <strong style="color:#16a34a;">Rp {{ number_format($selectedPO->paid_amount, 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between"><span>Sisa Tagihan:</span> <strong style="color:#dc2626;">Rp {{ number_format($selectedPO->remaining_amount, 0, ',', '.') }}</strong></div>
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label class="form-label required">Jumlah Pembayaran (Rp)</label>
                        <input type="number" name="amount" value="{{ old('amount', $selectedPO?->remaining_amount) }}" class="form-input" min="1" step="any" required>
                        @error('amount') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="form-label required">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-input" required>
                        @error('payment_date') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label class="form-label required">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="bank_transfer" {{ old('payment_method')=='bank_transfer' ? 'selected':'' }}>Bank Transfer</option>
                            <option value="cash" {{ old('payment_method')=='cash' ? 'selected':'' }}>Tunai / Cash</option>
                            <option value="cheque" {{ old('payment_method')=='cheque' ? 'selected':'' }}>Cek / Giro</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Rekening Bank / Catatan Bank</label>
                        <input type="text" name="bank_account" value="{{ old('bank_account') }}" placeholder="Contoh: BCA 1234567890" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Nomor Referensi / Transaksi</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Contoh: TRX-9923812" class="form-input">
                </div>

                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 mt-2">
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan Pembayaran</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
