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
                    $rows = [
                        ['Alat', $toolAssignment->tool?->name . ' (' . $toolAssignment->tool?->code . ')'],
                        ['Peminjam', $toolAssignment->assignedTo?->name ?? ($toolAssignment->notes ? explode('|', $toolAssignment->notes)[0] : '-')],
                        ['Lokasi / Gudang', $toolAssignment->fromWarehouse?->name ?? '-'],
                        ['Tgl Pinjam', \Carbon\Carbon::parse($toolAssignment->assigned_at)->format('d/m/Y')],
                        ['Batas Waktu', $toolAssignment->expected_return_at ? \Carbon\Carbon::parse($toolAssignment->expected_return_at)->format('d/m/Y') : '-'],
                        ['Catatan / Tujuan', $toolAssignment->notes ?? '-'],
                        ['Status', $toolAssignment->status === 'returned' ? '<span class="badge badge-success">Dikembalikan</span>' : '<span class="badge badge-warning">Dipinjam</span>'],
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

        @if($toolAssignment->status !== 'returned')
            @can('return tool assignments')
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-undo text-primary"></i> <span class="card-title">Proses Pengembalian</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tool-assignments.return', $toolAssignment) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="returned_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kondisi Alat Saat Kembali <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control" required>
                                <option value="good">Baik (Good)</option>
                                <option value="damaged">Rusak (Damaged)</option>
                                <option value="under_maintenance">Perlu Perbaikan</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Catatan Pengembalian</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-full" style="justify-content:center;" onclick="return confirm('Konfirmasi pengembalian alat?')">
                            <i class="fas fa-check-circle"></i> Konfirmasi Pengembalian
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        @endif

    </div>
</x-app-layout>
