<x-app-layout>
    <x-slot name="title">Detail Peminjaman Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tool-assignments.index') }}">Peminjaman Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Detail Transaksi</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt text-primary"></i> <span class="card-title">Informasi Peminjaman</span>
            </div>
            <div class="card-body">
                @php
                    $statusBadge = match ($toolAssignment->status) {
                        'pending'   => '<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Menunggu Persetujuan</span>',
                        'returned'  => '<span class="badge badge-success"><i class="fas fa-check"></i> Dikembalikan</span>',
                        'overdue'   => '<span class="badge badge-danger"><i class="fas fa-clock"></i> Terlambat</span>',
                        'rejected'  => '<span class="badge badge-danger"><i class="fas fa-xmark"></i> Ditolak</span>',
                        'lost'      => '<span class="badge badge-danger"><i class="fas fa-eye-slash"></i> Hilang</span>',
                        default     => '<span class="badge badge-purple"><i class="fas fa-hand-holding"></i> Dipinjam</span>',
                    };
                    $rows = [
                        ['Alat', $toolAssignment->tool?->name . ' (' . $toolAssignment->tool?->code . ')'],
                        ['Peminjam', $toolAssignment->assignedTo?->name ?? ($toolAssignment->notes ? explode('|', $toolAssignment->notes)[0] : '-')],
                        ['Lokasi / Gudang', $toolAssignment->fromWarehouse?->name ?? '-'],
                        ['Tgl Pinjam', \Carbon\Carbon::parse($toolAssignment->assigned_at)->format('d/m/Y')],
                        ['Batas Waktu', $toolAssignment->expected_return_at ? \Carbon\Carbon::parse($toolAssignment->expected_return_at)->format('d/m/Y') : '-'],
                        ['Catatan / Tujuan', $toolAssignment->notes ?? '-'],
                        ['Status', $statusBadge],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted fw-500" style="width:140px;flex-shrink:0;">{{ $label }}</span>
                    <span class="fw-600 text-end">{!! $value !!}</span>
                </div>
                @endforeach

                @if($toolAssignment->status === 'returned')
                    <h3 class="fw-700 mt-4 mb-2" style="font-size:15px;color:#0f172a;">Informasi Pengembalian</h3>
                    @php
                        $retRows = [
                            ['Tgl Kembali', \Carbon\Carbon::parse($toolAssignment->returned_at)->format('d/m/Y H:i')],
                            ['Catatan', $toolAssignment->notes ?? '-'],
                        ];
                    @endphp
                    @foreach($retRows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                        <span class="text-muted fw-500" style="width:140px;flex-shrink:0;">{{ $label }}</span>
                        <span class="fw-600 text-end">{{ $value }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        @if($toolAssignment->status === 'pending')
            @can('approve tool assignments')
            <div class="card" style="border:2px solid #f59e0b;">
                <div class="card-header" style="background:#fffbeb;">
                    <i class="fas fa-user-check text-warning"></i> <span class="card-title">Persetujuan Admin Pusat</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Pengajuan ini masih menunggu persetujuan Admin Pusat sebelum stok alat dikurangi.
                    </p>
                    <form method="POST" action="{{ route('tool-assignments.approve', $toolAssignment) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-full" style="justify-content:center;" onclick="return confirm('Setujui pengajuan ini? Stok alat akan dikurangi.')">
                            <i class="fas fa-check-circle"></i> Setujui & Kurangi Stok
                        </button>
                    </form>
                    <form method="POST" action="{{ route('tool-assignments.reject', $toolAssignment) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <input type="text" name="rejection_reason" class="form-control" placeholder="misal: stok tidak mencukupi" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-full" style="justify-content:center;" onclick="return confirm('Tolak pengajuan ini?')">
                            <i class="fas fa-xmark"></i> Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        @elseif($toolAssignment->status === 'rejected')
            <div class="card" style="border:1px solid #fecaca;">
                <div class="card-header" style="background:#fef2f2;">
                    <i class="fas fa-xmark text-danger"></i> <span class="card-title">Pengajuan Ditolak</span>
                </div>
                <div class="card-body">
                    <p style="font-size:13.5px;">Alasan: <span class="fw-600">{{ $toolAssignment->rejection_reason ?? '-' }}</span></p>
                </div>
            </div>
        @elseif($toolAssignment->approvedBy)
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-check text-success"></i> <span class="card-title">Disetujui Oleh</span>
                </div>
                <div class="card-body">
                    @php
                        $appRows = [
                            ['Disetujui', $toolAssignment->approvedBy?->name ?? '-'],
                            ['Tgl Persetujuan', $toolAssignment->approved_at ? \Carbon\Carbon::parse($toolAssignment->approved_at)->format('d/m/Y H:i') : '-'],
                        ];
                    @endphp
                    @foreach($appRows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                        <span class="text-muted fw-500" style="width:140px;flex-shrink:0;">{{ $label }}</span>
                        <span class="fw-600 text-end">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(in_array($toolAssignment->status, ['active', 'overdue']))
            @can('return tool assignments')
            <div class="card" style="border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:8px;">
                <div class="card-header" style="background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-undo text-primary" style="font-size:14px;"></i> 
                        <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;">Proses Pengembalian Alat</span>
                    </div>
                </div>
                <div class="card-body" style="padding:18px;">
                    <form method="POST" action="{{ route('tool-assignments.return', $toolAssignment) }}">
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
                                Jika memilih <strong>Rusak</strong> / <strong>Perbaikan</strong>, stok terpinjam akan dipindahkan ke kategori stok rusak/maintenance tanpa mengurangi stok total.
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">Catatan / Rincian Kerusakan</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan kondisi alat, kelengkapan, atau kronologi jika ada kerusakan..." style="border-radius:6px;font-size:13px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-full" style="justify-content:center;height:38px;font-weight:600;font-size:13px;" onclick="return confirm('Konfirmasi pengembalian alat?')">
                            <i class="fas fa-check-circle me-1"></i> Konfirmasi Pengembalian Alat
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        @endif

    </div>
</x-app-layout>
