<x-app-layout>
    <x-slot name="title">Catat Pembayaran PO Baru</x-slot>

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
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;">
                        Catat Pembayaran PO
                    </h2>
                    <p style="font-size:12.5px;color:#64748b;margin:2px 0 0 0;">
                        Formulir pencatatan bukti pelunasan atau cicilan tagihan Purchase Order kepada supplier.
                    </p>
                </div>
            </div>

            <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="font-size:12px;font-weight:600;padding:7px 14px;border-radius:6px;">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;">
        <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <i class="fas fa-circle-exclamation"></i> Terjadi Kesalahan Pengisian:
        </div>
        <ul style="margin:0;padding-left:18px;font-size:12px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="max-width: 820px; margin: 0 auto;">
        <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 2px 8px -2px rgba(0,0,0,0.05);overflow:hidden;">
            <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-credit-card text-primary" style="font-size:15px;"></i>
                <span style="font-size:14px;font-weight:700;color:#0f172a;">Formulir Pengajuan Pembayaran</span>
            </div>

            <div class="card-body" style="padding:22px 24px;">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf
                    
                    {{-- 1. Pilih PO --}}
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
                                @endphp
                                <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPO?->id) == $po->id) ? 'selected' : '' }}>
                                    {{ $po->po_number }} &mdash; {{ $po->supplier?->name }} (Sisa Boleh Dibayar: Rp {{ number_format($po->payable_remaining_amount, 0, ',', '.') }})
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

                    {{-- 2. Box Preview Status Finansial PO --}}
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
                                    <i class="fas fa-truck-field text-primary me-1"></i> Supplier: {{ $selectedPO->supplier?->name ?? '-' }}
                                </span>
                                <span class="text-muted ms-2" style="font-size:11px;">({{ $selectedPO->po_number }})</span>
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

                        @if(!empty($withheldData['items']))
                        <div style="margin-top:12px;background:#fff5f5;border:1px solid #fed7d7;border-radius:6px;padding:10px 12px;font-size:11.5px;">
                            <div style="font-weight:700;color:#991b1b;margin-bottom:4px;">
                                <i class="fas fa-circle-exclamation me-1"></i> Rincian Nilai Tertahan:
                            </div>
                            @foreach($withheldData['items'] as $wh)
                                <div style="display:flex;justify-content:space-between;padding:2px 0;color:#7f1d1d;">
                                    <span>{{ $wh['item_name'] }} ({{ number_format($wh['qty'], 2, ',', '.') }} {{ $wh['unit'] }}) &mdash; {{ $wh['reason'] }}</span>
                                    <strong style="margin-left:8px;">Rp {{ number_format($wh['value'], 0, ',', '.') }}</strong>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Rincian Nota Fisik Terkait PO --}}
                        @if($selectedPO->receipts && $selectedPO->receipts->count() > 0)
                        @php
                            $unpaidTotal = $selectedPO->receipts->where('payment_status', 'unpaid')->sum('total_amount');
                            $unpaidCount = $selectedPO->receipts->where('payment_status', 'unpaid')->count();
                        @endphp
                        <div style="margin-top:14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#1e293b;">
                                    <i class="fas fa-receipt text-primary me-1"></i> Nota Pembelian Harian Terkait PO ({{ $selectedPO->receipts->count() }} Nota)
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
                                            <i class="fas fa-external-link-alt"></i>
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
                                    onclick="document.getElementById('inputAmount').value = '{{ (int) $poPayableRemaining }}'" 
                                    class="btn btn-sm btn-light border" 
                                    style="font-size:11.5px;font-weight:600;padding:3px 9px;color:#2563eb;background:#ffffff;">
                                <i class="fas fa-check-double me-1"></i> Bayar Maksimal Nilai Sah (Rp {{ number_format($poPayableRemaining, 0, ',', '.') }})
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- 3. Nominal & Tanggal --}}
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

                    {{-- 4. Metode & Rekening Bank --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label class="form-label required" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Metode Pembayaran <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" class="form-control" required style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                                <option value="bank_transfer" {{ old('payment_method')=='bank_transfer' ? 'selected':'' }}>Bank Transfer</option>
                                <option value="cash" {{ old('payment_method')=='cash' ? 'selected':'' }}>Tunai / Cash</option>
                                <option value="cheque" {{ old('payment_method')=='cheque' ? 'selected':'' }}>Cek / Giro</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                                Rekening Bank / Catatan Bank
                            </label>
                            <input type="text" 
                                   name="bank_account" 
                                   value="{{ old('bank_account') }}" 
                                   placeholder="Contoh: BCA 1234567890 a/n PT Arsikon" 
                                   class="form-control"
                                   style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                        </div>
                    </div>

                    {{-- 5. No Referensi --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Nomor Referensi Transaksi / Bukti Transfer
                        </label>
                        <input type="text" 
                               name="reference_number" 
                               value="{{ old('reference_number') }}" 
                               placeholder="Contoh: TRX-202610-99238 atau No. Kwitansi" 
                               class="form-control"
                               style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                    </div>

                    {{-- 6. Catatan --}}
                    <div class="mb-4">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:6px;">
                            Catatan Tambahan
                        </label>
                        <textarea name="notes" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Keterangan peruntukan pembayaran, termin ke-berapa, atau memo lainnya..." 
                                  style="padding:10px 12px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Form Actions --}}
                    <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:16px;border-top:1px solid #f1f5f9;">
                        <a href="{{ route('payments.index') }}" class="btn btn-light border" style="font-size:12.5px;font-weight:600;padding:8px 18px;border-radius:8px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" style="font-size:12.5px;font-weight:700;padding:8px 22px;border-radius:8px;box-shadow:0 2px 6px rgba(37,99,235,0.25);display:inline-flex;align-items:center;gap:6px;">
                            <i class="fas fa-paper-plane"></i> Simpan & Ajukan Pembayaran
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
