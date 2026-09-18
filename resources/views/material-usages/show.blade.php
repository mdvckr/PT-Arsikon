<x-app-layout>
    <x-slot name="title">Detail Pemakaian Material #{{ $materialUsage->usage_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $materialUsage->usage_number }}</span>
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <i class="fas fa-circle-exclamation"></i>
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Pengeluaran Material #{{ $materialUsage->usage_number }}</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">
                Dikeluarkan pada {{ \Carbon\Carbon::parse($materialUsage->usage_date)->format('d F Y') }} oleh {{ $materialUsage->issuedBy?->name ?? '-' }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($materialUsage->status !== 'cancelled')
            <a href="{{ route('material-usages.print', $materialUsage) }}" target="_blank" class="btn btn-light border">
                <i class="fas fa-print"></i> Cetak Bon Pengeluaran
            </a>
            @endif
            <a href="{{ route('material-usages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-3 mb-4" style="grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left: Material Items Table -->
        <div>
            @if($materialUsage->status === 'cancelled')
            <div class="alert alert-danger mb-3" style="display:flex;align-items:center;gap:10px;border:1px solid #fecaca;background:#fef2f2;">
                <i class="fas fa-ban" style="font-size:18px;color:#dc2626;"></i>
                <div>
                    <strong style="color:#991b1b;">Transaksi Ini Telah Dibatalkan</strong>
                    <div class="text-muted" style="font-size:12.5px;margin-top:2px;">
                        Oleh {{ $materialUsage->cancelledBy?->name ?? '-' }}
                        pada {{ $materialUsage->cancelled_at?->format('d/m/Y H:i') ?? '-' }}.
                        Stok material telah dikembalikan ke gudang.
                    </div>
                    @if($materialUsage->cancellation_reason)
                    <div style="font-size:12.5px;margin-top:4px;color:#991b1b;">
                        <strong>Alasan:</strong> {{ $materialUsage->cancellation_reason }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($materialUsage->materialRequest)
            <div class="alert alert-info mb-3" style="display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #bbf7d0;">
                <i class="fas fa-file-circle-check" style="font-size:20px;color:#16a34a;"></i>
                <div>
                    <strong style="color:#15803d;">Pengeluaran Material Terkontrol (Sesuai Permintaan)</strong>
                    <div style="font-size:12.5px;color:#166534;margin-top:2px;">
                        Pengeluaran ini ditarik dari Permintaan Material 
                        <a href="{{ route('material-requests.show', $materialUsage->materialRequest) }}" class="fw-700 text-primary" style="text-decoration:underline;">
                            #{{ $materialUsage->materialRequest->request_number }}
                        </a> 
                        yang diajukan oleh <strong>{{ $materialUsage->materialRequest->requestedBy?->name ?? 'User' }}</strong> dan telah disetujui Site Manager.
                    </div>
                </div>
            </div>
            @endif

            <div class="card" style="{{ $materialUsage->status === 'cancelled' ? 'opacity:0.65;' : '' }}">
                <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-boxes-stacked text-primary"></i>
                        <span class="card-title">Rincian Material yang Dikeluarkan</span>
                    </div>
                    <span class="badge badge-purple">{{ $materialUsage->items->count() }} Jenis Item</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-wrap">
                        <table class="data-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;text-align:center;">#</th>
                                    <th>Nama Material</th>
                                    <th>Kategori</th>
                                    <th style="text-align:right;">Jumlah Keluar</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($materialUsage->items as $index => $item)
                                <tr>
                                    <td style="text-align:center;">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-600" style="color:#0f172a;">{{ $item->material?->name }}</div>
                                        <div class="text-muted" style="font-size:11px;">Kode: {{ $item->material?->code ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-gray">{{ $item->material?->category?->name ?? 'Umum' }}</span>
                                    </td>
                                    <td style="text-align:right;">
                                        <span class="fw-700" style="font-size:14px;color:#0f172a;">
                                            {{ format_quantity($item->quantity) }}
                                        </span>
                                        <span class="text-muted" style="font-size:12px;">{{ $item->material?->unit?->abbreviation ?? '' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:12.5px;">{{ $item->notes ?? '-' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Information Card + Cancel Card -->
        <div>
            <div class="card mb-3" style="height:fit-content;">
                <div class="card-header">
                    <i class="fas fa-circle-info text-primary"></i>
                    <span class="card-title">Informasi Bukti Pengeluaran</span>
                </div>
                <div class="card-body">
                    @php
                        $statusBadge = $materialUsage->status === 'cancelled'
                            ? '<span class="badge badge-danger"><i class="fas fa-ban"></i> Dibatalkan</span>'
                            : '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Selesai (Tercatat)</span>';

                        $mrVal = $materialUsage->materialRequest
                            ? '<a href="' . route('material-requests.show', $materialUsage->materialRequest) . '" class="fw-700 text-primary" style="text-decoration:underline;">#' . $materialUsage->materialRequest->request_number . '</a> <span class="badge badge-success" style="font-size:10px;margin-left:4px;">Terkontrol MR</span>'
                            : '<span class="badge badge-warning" style="font-size:10px;">Input Manual</span>';

                        $infoRows = [
                            ['No. Bukti', '<span class="fw-700 text-primary">' . $materialUsage->usage_number . '</span>'],
                            ['Status', $statusBadge],
                            ['Surat Permintaan (MR)', $mrVal],
                            ['Tanggal Pengeluaran', \Carbon\Carbon::parse($materialUsage->usage_date)->format('d/m/Y')],
                            ['Gudang Sumber', $materialUsage->warehouse?->name ?? '-'],
                            ['Proyek', $materialUsage->project?->name ?? ($materialUsage->warehouse?->is_central ? 'Gudang Pusat' : '-')],
                            ['Penerima (Mandor/Tukang)', '<strong style="color:#0f172a;">' . e($materialUsage->recipient_name) . '</strong>'],
                            ['Pekerjaan / Zona', e($materialUsage->job_section ?? '-')],
                            ['Petugas Gudang', e($materialUsage->issuedBy?->name ?? '-')],
                            ['Waktu Input', $materialUsage->created_at ? $materialUsage->created_at->format('d/m/Y H:i') : '-'],
                        ];
                    @endphp

                    @foreach($infoRows as [$label, $val])
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                        <span class="text-muted fw-500" style="width:135px;flex-shrink:0;">{{ $label }}</span>
                        <span class="text-end">{!! $val !!}</span>
                    </div>
                    @endforeach

                    @if($materialUsage->notes)
                    <div class="mt-3">
                        <label class="form-label text-muted" style="font-size:12px;">Catatan Tambahan:</label>
                        <div class="p-2 rounded bg-light border text-muted" style="font-size:12.5px;">
                            {{ $materialUsage->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cancel Card: only for completed usages --}}
            @if($materialUsage->status === 'completed')
                @can('cancel material usages')
                <div class="card" style="border:2px solid #f87171;border-radius:10px;">
                    <div class="card-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:14px 18px;">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-ban" style="color:#dc2626;font-size:14px;"></i>
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#991b1b;">Batalkan Transaksi Ini</span>
                        </div>
                    </div>
                    <div class="card-body" style="padding:18px;">
                        <p class="text-muted mb-3" style="font-size:12.5px;line-height:1.5;">
                            Membatalkan pengeluaran ini akan <strong>mengembalikan stok material</strong> ke gudang asal.
                            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                        </p>
                        <form method="POST" action="{{ route('material-usages.cancel', $materialUsage) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;">
                                    Alasan Pembatalan <span class="text-danger">*</span>
                                </label>
                                <textarea name="cancellation_reason" class="form-control" rows="3" required
                                    placeholder="Contoh: Salah input jumlah, material tidak jadi dipakai, dll..."
                                    style="border-radius:6px;font-size:13px;border-color:#fca5a5;"></textarea>
                                @error('cancellation_reason')
                                    <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-full" style="justify-content:center;height:38px;font-weight:600;font-size:13px;"
                                onclick="return confirm('⚠️ PERHATIAN!\n\nApakah Anda yakin ingin MEMBATALKAN pengeluaran material ini?\n\nStok material akan dikembalikan ke gudang asal.\nTindakan ini tidak dapat dibatalkan.\n\nLanjutkan?')">
                                <i class="fas fa-ban me-1"></i> Batalkan Pengeluaran Ini
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif
        </div>
    </div>
</x-app-layout>
