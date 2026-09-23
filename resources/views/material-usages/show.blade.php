<x-app-layout>
    <x-slot name="title">Detail Pemakaian Material #{{ $materialUsage->usage_number }}</x-slot>

    @push('styles')
    <style>
        .material-usage-show-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 16px;
            align-items: start;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
            width: 120px;
            flex-shrink: 0;
            font-size: 11.5px;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
            font-size: 12px;
        }

        @media (max-width: 992px) {
            .material-usage-show-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
            .info-label {
                width: 100%;
                font-size: 11px;
            }
            .info-val {
                text-align: left;
                width: 100%;
            }
            .page-actions-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Detail #{{ $materialUsage->usage_number }}</span>
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-exclamation"></i>
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
    @endif

    {{-- Top Action & Header Bar --}}
    <div class="flex items-center justify-between mb-3 page-actions-header">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('material-usages.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h2 class="fw-700" style="font-size:15px;color:#0f172a;margin:0;display:inline-flex;align-items:center;gap:6px;">
                    #{{ $materialUsage->usage_number }}
                </h2>
            </div>
            @if($materialUsage->status === 'cancelled')
                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                    <i class="fas fa-ban me-1"></i> Dibatalkan
                </span>
            @else
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                    <i class="fas fa-check-circle me-1"></i> Selesai
                </span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:6px;">
            @if($materialUsage->status !== 'cancelled')
            <a href="{{ route('material-usages.print', $materialUsage) }}" target="_blank" class="btn btn-primary btn-sm" style="font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                <i class="fas fa-print"></i> Cetak Bon
            </a>
            @endif
        </div>
    </div>

    @if($materialUsage->status === 'cancelled')
    <div class="alert alert-danger mb-3" style="display:flex;align-items:center;gap:10px;border:1px solid #fecaca;background:#fef2f2;border-radius:8px;padding:10px 14px;">
        <i class="fas fa-ban" style="font-size:16px;color:#dc2626;"></i>
        <div>
            <strong style="color:#991b1b;font-size:12.5px;">Transaksi Ini Telah Dibatalkan</strong>
            <div class="text-muted" style="font-size:11px;margin-top:1px;">
                Oleh {{ $materialUsage->cancelledBy?->name ?? '-' }} pada {{ $materialUsage->cancelled_at?->format('d/m/Y H:i') ?? '-' }}. Stok material telah dikembalikan ke gudang asal.
            </div>
            @if($materialUsage->cancellation_reason)
            <div style="font-size:11.5px;margin-top:2px;color:#991b1b;">
                <strong>Alasan:</strong> {{ $materialUsage->cancellation_reason }}
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($materialUsage->materialRequest)
    <div class="alert alert-info mb-3" style="display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;">
        <i class="fas fa-file-circle-check text-primary" style="font-size:14px;"></i>
        <div style="font-size:12px;color:#334155;">
            Pengeluaran ini terikat dengan Permintaan Material 
            <a href="{{ route('material-requests.show', $materialUsage->materialRequest) }}" class="fw-700 text-primary" style="text-decoration:underline;">
                #{{ $materialUsage->materialRequest->request_number }}
            </a> 
            diajukan oleh <strong>{{ $materialUsage->materialRequest->requestedBy?->name ?? 'User' }}</strong>.
        </div>
    </div>
    @endif

    <div class="material-usage-show-layout mb-3">
        <!-- Left: Material Items Table -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;{{ $materialUsage->status === 'cancelled' ? 'opacity:0.75;' : '' }}">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Rincian Material yang Dikeluarkan</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                        {{ $materialUsage->items->count() }} Jenis Item
                    </span>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mb-0">
                        <thead>
                            <tr style="background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:10.5px;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;">
                                <th style="width:36px;padding:7px 10px;text-align:center;">#</th>
                                <th style="padding:7px 12px;">Nama Material</th>
                                <th style="padding:7px 10px;">Kategori</th>
                                <th style="text-align:right;width:120px;padding:7px 10px;">Jumlah Keluar</th>
                                <th style="padding:7px 12px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialUsage->items as $index => $item)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px 10px;text-align:center;color:#94a3b8;font-size:11.5px;">{{ $index + 1 }}</td>
                                <td style="padding:8px 12px;">
                                    @if($item->isCustom())
                                        <div class="fw-600" style="color:#0f172a;font-size:12.5px;">
                                            {{ $item->displayName() }} 
                                            <span class="badge" style="font-size:9.5px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;margin-left:3px;padding:1px 4px;">Custom</span>
                                        </div>
                                        <div class="text-muted" style="font-size:10.5px;">Non-Master</div>
                                    @else
                                        <div class="fw-600" style="color:#0f172a;font-size:12.5px;">{{ $item->displayName() }}</div>
                                        @if($item->material?->code)
                                        <code style="font-size:10.5px;background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#475569;margin-top:1px;display:inline-block;">{{ $item->material->code }}</code>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding:8px 10px;font-size:12px;color:#475569;">
                                    <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:10.5px;padding:2px 6px;border-radius:4px;">
                                        {{ $item->isCustom() ? 'Custom' : ($item->material?->category?->name ?? 'Umum') }}
                                    </span>
                                </td>
                                <td style="padding:8px 10px;text-align:right;">
                                    <span class="fw-700" style="font-size:13px;color:#0f172a;">
                                        {{ format_quantity($item->quantity) }}
                                    </span>
                                    <span class="text-muted" style="font-size:11px;margin-left:2px;">{{ $item->displayUnit() }}</span>
                                </td>
                                <td style="padding:8px 12px;font-size:11.5px;color:#64748b;">
                                    {{ $item->notes ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Information Card + Cancel Card -->
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-circle-info text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Bon Pengeluaran</span>
                </div>
                <div class="card-body" style="padding:12px 14px;">
                    @php
                        $statusBadge = $materialUsage->status === 'cancelled'
                            ? '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:1px 6px;border-radius:12px;font-weight:600;"><i class="fas fa-ban me-1"></i> Dibatalkan</span>'
                            : '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:1px 6px;border-radius:12px;font-weight:600;"><i class="fas fa-check-circle me-1"></i> Selesai</span>';

                        $mrVal = $materialUsage->materialRequest
                            ? '<a href="' . route('material-requests.show', $materialUsage->materialRequest) . '" class="fw-700 text-primary" style="text-decoration:underline;">#' . $materialUsage->materialRequest->request_number . '</a>'
                            : '<span class="badge" style="background:#f1f5f9;color:#64748b;font-size:10.5px;padding:1px 6px;border-radius:4px;">Manual</span>';

                        $infoRows = [
                            ['No. Bon', '<strong style="color:#0f172a;">' . $materialUsage->usage_number . '</strong>'],
                            ['Status', $statusBadge],
                            ['Surat MR', $mrVal],
                            ['Tanggal', \Carbon\Carbon::parse($materialUsage->usage_date)->format('d/m/Y')],
                            ['Gudang', $materialUsage->warehouse?->name ?? '-'],
                            ['Penerima', '<strong style="color:#0f172a;">' . e($materialUsage->recipient_name) . '</strong>'],
                            ['Pekerjaan', e($materialUsage->job_section ?? '-')],
                            ['Petugas', e($materialUsage->issuedBy?->name ?? '-')],
                            ['Waktu Input', $materialUsage->created_at ? $materialUsage->created_at->format('d/m/Y H:i') : '-'],
                        ];
                    @endphp

                    @foreach($infoRows as [$label, $val])
                    <div class="info-row">
                        <span class="info-label">{{ $label }}</span>
                        <span class="info-val">{!! $val !!}</span>
                    </div>
                    @endforeach

                    @if($materialUsage->notes)
                    <div style="margin-top:10px;padding-top:8px;border-top:1px dashed #cbd5e1;">
                        <span class="text-muted" style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;">Catatan:</span>
                        <div style="font-size:11.5px;color:#475569;background:#f8fafc;padding:6px 8px;border-radius:4px;border:1px solid #e2e8f0;">
                            {{ $materialUsage->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cancel Card: only for completed usages --}}
            @if($materialUsage->status === 'completed')
                @can('cancel material usages')
                <div class="card" style="border:1px solid #fecaca;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(220,38,38,0.05);overflow:hidden;">
                    <div class="card-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-ban" style="color:#dc2626;font-size:12px;"></i>
                        <span class="card-title" style="font-size:12px;font-weight:700;color:#991b1b;">Batalkan Pengeluaran Ini</span>
                    </div>
                    <div class="card-body" style="padding:12px 14px;">
                        <p class="text-muted mb-2" style="font-size:11px;line-height:1.4;">
                            Membatalkan pengeluaran ini akan mengembalikan stok material ke gudang asal.
                        </p>
                        <form method="POST" action="{{ route('material-usages.cancel', $materialUsage) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control" rows="2" required
                                    placeholder="Alasan pembatalan..."
                                    style="border-radius:5px;font-size:11.5px;border-color:#fca5a5;"></textarea>
                                @error('cancellation_reason')
                                    <div class="text-danger" style="font-size:11px;margin-top:2px;">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-full" style="justify-content:center;padding:6px;font-size:12px;font-weight:600;border-radius:6px;"
                                onclick="return confirm('Batalkan pengeluaran material ini? Stok akan dikembalikan ke gudang asal.')">
                                <i class="fas fa-ban me-1"></i> Batalkan Pengeluaran
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif
        </div>
    </div>
</x-app-layout>
