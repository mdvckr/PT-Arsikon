<x-app-layout>
    <x-slot name="title">Detail Inventori: {{ $inventory->material?->name }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="breadcrumb" style="margin-bottom:8px;">
        <a href="{{ route('inventory.index') }}">Inventori</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $inventory->material?->name }}</span>
    </div>

    {{-- Action Bar --}}
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">{{ $inventory->material?->name }}</h2>
                <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;color:#475569;background:#f1f5f9;padding:2px 7px;border-radius:4px;border:1px solid #e2e8f0;">
                    {{ $inventory->material?->sku ?? '-' }}
                </span>
            </div>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">
                Lokasi: {{ $inventory->warehouse?->name ?? 'Gudang Utama' }}
            </p>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:320px 1fr;gap:20px;align-items:start;">
        {{-- Info Card --}}
        <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                <div class="fw-700" style="font-size:14px;color:#0f172a;">Info Inventori</div>
                <div class="text-muted" style="font-size:11.5px;margin-top:1px;">Detail stok fisik di gudang ini</div>
            </div>
            <div class="card-body" style="padding:18px;">
                @php
                    $rows = [
                        ['Nama Material', $inventory->material?->name],
                        ['Kode SKU', '<span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:11.5px;color:#334155;background:#f1f5f9;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0;">'.($inventory->material?->sku ?? '-').'</span>'],
                        ['Kategori', $inventory->material?->category?->name ?? '-'],
                        ['Satuan', ($inventory->material?->unit?->name ?? '-').' ('.($inventory->material?->unit?->abbreviation ?? '').')'],
                        ['Gudang', $inventory->warehouse?->name ?? '-'],
                        ['Stok Minimum', number_format($inventory->min_stock, 0, ',', '.')],
                    ];
                @endphp
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    @foreach($rows as [$lbl, $val])
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:8px 0;color:#64748b;font-weight:500;width:45%;vertical-align:top;font-size:12px;">{{ $lbl }}</td>
                        <td style="padding:8px 0;color:#1e293b;vertical-align:top;">{!! $val !!}</td>
                    </tr>
                    @endforeach
                </table>

                <div style="margin-top:20px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;text-align:center;">
                    <div class="text-muted" style="font-size:11.5px;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">Stok Tersedia</div>
                    <div style="font-size:32px;font-weight:700;color:#0f172a;margin-top:2px;">
                        {{ number_format($inventory->quantity, 0, ',', '.') }}
                    </div>
                    <div class="text-muted" style="font-size:12px;">{{ $inventory->material?->unit?->abbreviation }}</div>
                    <div style="margin-top:8px;">
                        @if($inventory->quantity <= 0)
                            <span style="color:#dc2626;font-weight:600;font-size:12px;">Stok Habis</span>
                        @elseif($inventory->quantity <= $inventory->min_stock)
                            <span style="color:#b45309;font-weight:600;font-size:12px;">Stok Rendah</span>
                        @else
                            <span style="color:#16a34a;font-weight:600;font-size:12px;">Stok Normal</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Mutation History Card --}}
        <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                <div class="fw-700" style="font-size:14px;color:#0f172a;">Riwayat Mutasi (30 Terakhir)</div>
                <div class="text-muted" style="font-size:11.5px;margin-top:1px;">Log pergerakan barang masuk dan keluar untuk inventori ini</div>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tanggal</th>
                            <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tipe</th>
                            <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Jumlah</th>
                            <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Referensi</th>
                            <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory->stockMutations as $mut)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:9px 16px;color:#64748b;font-size:12px;white-space:nowrap;">
                                {{ $mut->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding:9px 16px;font-size:12px;">
                                @if(in_array($mut->type, ['in', 'receipt', 'opname_adjust_up']))
                                    <span style="color:#16a34a;font-weight:600;">Masuk</span>
                                @else
                                    <span style="color:#dc2626;font-weight:600;">Keluar</span>
                                @endif
                            </td>
                            <td style="padding:9px 16px;text-align:right;font-weight:600;font-size:13px;color:#0f172a;">
                                {{ number_format(abs($mut->quantity), 0, ',', '.') }}
                                <span class="text-muted" style="font-size:11px;font-weight:normal;">{{ $inventory->material?->unit?->abbreviation }}</span>
                            </td>
                            <td class="text-muted" style="padding:9px 16px;font-size:12px;">
                                {{ $mut->reference_type ?? '-' }} {{ $mut->reference_id ?? '' }}
                            </td>
                            <td class="text-muted" style="padding:9px 16px;font-size:12px;">
                                {{ $mut->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;">
                                Belum ada riwayat mutasi stok untuk item ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
