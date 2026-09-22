<x-app-layout>
    <x-slot name="title">Detail Peminjaman Alat #{{ $toolLoan->loan_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tool-assignments.index') }}">Peminjaman Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Detail Transaksi #{{ $toolLoan->loan_number }}</span>
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-4">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    @if($toolLoan->status === 'cancelled')
    <div class="alert alert-danger mb-4" style="display:flex;align-items:center;gap:10px;border:1px solid #fecaca;background:#fef2f2;">
        <i class="fas fa-ban" style="font-size:18px;color:#dc2626;"></i>
        <div>
            <strong style="color:#991b1b;">Peminjaman Ini Telah Dibatalkan</strong>
            <div class="text-muted" style="font-size:12.5px;margin-top:2px;">
                Oleh {{ $toolLoan->cancelledBy?->name ?? '-' }}
                pada {{ $toolLoan->cancelled_at?->format('d/m/Y H:i') ?? '-' }}.
            </div>
            @if($toolLoan->cancellation_reason)
            <div style="font-size:12.5px;margin-top:4px;color:#991b1b;">
                <strong>Alasan:</strong> {{ $toolLoan->cancellation_reason }}
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="grid" style="grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:20px;">
            {{-- Informasi Utama Transaksi --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-invoice text-primary"></i> 
                        <span class="card-title">Informasi Peminjaman</span>
                    </div>
                    <div>
                        <span class="badge" style="font-size:12px;background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0;">
                            {{ $toolLoan->loan_number }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $statusBadge = match ($toolLoan->status) {
                            'pending'   => '<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Menunggu Persetujuan</span>',
                            'returned'  => '<span class="badge badge-success"><i class="fas fa-check"></i> Dikembalikan</span>',
                            'overdue'   => '<span class="badge badge-danger"><i class="fas fa-clock"></i> Terlambat</span>',
                            'rejected'  => '<span class="badge badge-danger"><i class="fas fa-xmark"></i> Ditolak</span>',
                            'lost'      => '<span class="badge badge-danger"><i class="fas fa-eye-slash"></i> Hilang</span>',
                            'cancelled' => '<span class="badge badge-gray"><i class="fas fa-ban"></i> Dibatalkan</span>',
                            default     => '<span class="badge badge-purple"><i class="fas fa-hand-holding"></i> Dipinjam</span>',
                        };
                        $rows = [
                            ['Nama Peminjam', '<strong style="color:#0f172a;">' . e($toolLoan->borrower_name) . '</strong>'],
                            ['No. Kontak / HP', e($toolLoan->borrower_phone ?? '-')],
                            ['Lokasi / Site Proyek', e($toolLoan->location_name)],
                            ['Gudang Asal Alat', e($toolLoan->fromWarehouse?->name ?? '-')],
                            ['Diajukan Oleh', e($toolLoan->assignedBy?->name ?? '-')],
                            ['Tgl Pinjam', \Carbon\Carbon::parse($toolLoan->assigned_at)->format('d/m/Y')],
                            ['Batas Waktu Kembali', $toolLoan->expected_return_at ? \Carbon\Carbon::parse($toolLoan->expected_return_at)->format('d/m/Y') : '-'],
                            ['Catatan / Kebutuhan', $toolLoan->notes ?? '-'],
                            ['Status', $statusBadge],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                        <span class="text-muted fw-500" style="width:160px;flex-shrink:0;">{{ $label }}</span>
                        <span class="fw-600 text-end">{!! $value !!}</span>
                    </div>
                    @endforeach

                    @if($toolLoan->status === 'returned')
                        <h3 class="fw-700 mt-4 mb-2" style="font-size:15px;color:#0f172a;">Informasi Pengembalian</h3>
                        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                            <span class="text-muted fw-500" style="width:160px;flex-shrink:0;">Tgl Kembali</span>
                            <span class="fw-600 text-end">{{ \Carbon\Carbon::parse($toolLoan->returned_at)->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Daftar Rincian Alat Dalam Peminjaman Ini --}}
            <div class="card">
                <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-toolbox text-primary"></i>
                        <span class="card-title">Daftar Alat yang Dipinjam</span>
                    </div>
                    <span class="badge badge-info">{{ $toolLoan->items->count() }} Jenis Alat ({{ $toolLoan->items->sum('quantity') }} Unit)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Kategori</th>
                                <th style="text-align:center;">Jumlah Pinjam</th>
                                <th>Kode Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($toolLoan->items as $idx => $item)
                            <tr>
                                <td style="width:40px;color:#64748b;">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-600" style="color:#0f172a;">{{ $item->tool?->name ?? 'Alat tidak ditemukan' }}</div>
                                    <code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">{{ $item->tool?->code ?? '-' }}</code>
                                </td>
                                <td>{{ $item->tool?->category?->name ?? '-' }}</td>
                                <td style="text-align:center;">
                                    <span class="badge badge-purple" style="font-size:12px;font-weight:700;padding:4px 10px;">
                                        {{ $item->quantity }} Unit
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:11.5px;">{{ $item->assignment_number }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted p-3">Tidak ada daftar item alat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel Kanan: Aksi & Approval --}}
        <div>
            @if($toolLoan->status === 'pending')
                @can('approve tool assignments')
                <div class="card mb-4" style="border:2px solid #f59e0b;">
                    <div class="card-header" style="background:#fffbeb;">
                        <i class="fas fa-user-check text-warning"></i> <span class="card-title">Persetujuan Admin Pusat</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3" style="font-size:13px;">
                            Pengajuan peminjaman kelompok ini masih menunggu persetujuan Admin Pusat sebelum stok seluruh alat dikurangi.
                        </p>
                        <form method="POST" action="{{ route('tool-assignments.approve', $toolLoan->id) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-full" style="justify-content:center;" onclick="return confirm('Setujui pengajuan peminjaman #{{ $toolLoan->loan_number }} ini? Stok seluruh alat akan otomatis dikurangi.')">
                                <i class="fas fa-check-circle"></i> Setujui Seluruh Alat
                            </button>
                        </form>
                        <form method="POST" action="{{ route('tool-assignments.reject', $toolLoan->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <input type="text" name="rejection_reason" class="form-control" placeholder="misal: stok tidak mencukupi" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-full" style="justify-content:center;" onclick="return confirm('Tolak pengajuan peminjaman ini?')">
                                <i class="fas fa-xmark"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @elseif($toolLoan->status === 'rejected')
                <div class="card mb-4" style="border:1px solid #fecaca;">
                    <div class="card-header" style="background:#fef2f2;">
                        <i class="fas fa-xmark text-danger"></i> <span class="card-title">Pengajuan Ditolak</span>
                    </div>
                    <div class="card-body">
                        <p style="font-size:13.5px;">Alasan: <span class="fw-600">{{ $toolLoan->rejection_reason ?? '-' }}</span></p>
                    </div>
                </div>
            @elseif($toolLoan->approvedBy)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-user-check text-success"></i> <span class="card-title">Disetujui Oleh</span>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                            <span class="text-muted fw-500" style="width:130px;flex-shrink:0;">Disetujui</span>
                            <span class="fw-600 text-end">{{ $toolLoan->approvedBy?->name ?? '-' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                            <span class="text-muted fw-500" style="width:130px;flex-shrink:0;">Tgl Persetujuan</span>
                            <span class="fw-600 text-end">{{ $toolLoan->approved_at ? \Carbon\Carbon::parse($toolLoan->approved_at)->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(in_array($toolLoan->status, ['active', 'overdue']))
                @can('return tool assignments')
                <div class="card mb-4" style="border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:8px;">
                    <div class="card-header" style="background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-undo text-primary" style="font-size:14px;"></i> 
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;">Proses Pengembalian Alat</span>
                        </div>
                    </div>
                    <div class="card-body" style="padding:18px;">
                        <form method="POST" action="{{ route('tool-assignments.return', $toolLoan->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">Tanggal & Jam Kembali <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="returned_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}" style="height:38px;border-radius:6px;font-size:13px;" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">Kondisi Alat Saat Pengembalian <span class="text-danger">*</span></label>
                                <select name="condition" class="form-control" style="height:38px;border-radius:6px;font-size:13px;font-weight:600;" required>
                                    <option value="good" style="color:#047857;font-weight:600;">🟢 Baik (Siap Pakai / Ready)</option>
                                    <option value="under_maintenance" style="color:#b45309;font-weight:600;">🟡 Perlu Perbaikan (Under Maintenance)</option>
                                    <option value="damaged" style="color:#b91c1c;font-weight:600;">🔴 Rusak / Rusak Total (Damaged)</option>
                                </select>
                                <div class="text-muted" style="font-size:11px;margin-top:4px;line-height:1.4;">
                                    Stok terpinjam dari seluruh alat di peminjaman ini akan dikembalikan ke gudang sesuai kondisi yang dipilih.
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">Catatan / Rincian Pengembalian</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan kondisi alat atau kelengkapan..." style="border-radius:6px;font-size:13px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-full" style="justify-content:center;height:38px;font-weight:600;font-size:13px;" onclick="return confirm('Konfirmasi pengembalian seluruh alat dalam transaksi ini?')">
                                <i class="fas fa-check-circle me-1"></i> Konfirmasi Pengembalian Alat
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif

            {{-- Cancel Card: for pending or active assignments --}}
            @if(in_array($toolLoan->status, ['pending', 'active']))
                @can('cancel tool assignments')
                <div class="card" style="border:2px solid #f87171;border-radius:10px;">
                    <div class="card-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:14px 18px;">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-ban" style="color:#dc2626;font-size:14px;"></i>
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#991b1b;">Batalkan Peminjaman</span>
                        </div>
                    </div>
                    <div class="card-body" style="padding:18px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            @if($toolLoan->status === 'active')
                                Membatalkan peminjaman aktif ini akan <strong>mengembalikan stok seluruh alat</strong> ke gudang asal.
                            @else
                                Membatalkan pengajuan ini akan membatalkan peminjaman. Stok belum terpotong karena belum disetujui.
                            @endif
                            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                        </p>
                        <form method="POST" action="{{ route('tool-assignments.cancel', $toolLoan->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">
                                    Alasan Pembatalan <span class="text-danger">*</span>
                                </label>
                                <textarea name="cancellation_reason" class="form-control" rows="3" required
                                    placeholder="Contoh: Alat tidak jadi dipakai, salah input, dll..."
                                    style="border-radius:6px;font-size:13px;border-color:#fca5a5;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-full" style="justify-content:center;height:38px;font-weight:600;font-size:13px;"
                                onclick="return confirm('⚠️ PERHATIAN!\n\nApakah Anda yakin ingin MEMBATALKAN peminjaman #{{ $toolLoan->loan_number }} ini?\n{{ $toolLoan->status === 'active' ? '\nStok seluruh alat akan dikembalikan ke gudang asal.' : '' }}\nTindakan ini tidak dapat dibatalkan.\n\nLanjutkan?')">
                                <i class="fas fa-ban me-1"></i> Batalkan Peminjaman Ini
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif
        </div>

    </div>
</x-app-layout>
