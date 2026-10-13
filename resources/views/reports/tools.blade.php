<x-app-layout>
    <x-slot name="title">Laporan Status & Kepemilikan Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Status & Kepemilikan Alat</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    {{-- Overview Metrics --}}
    <div class="grid grid-3" style="gap:16px;margin-bottom:20px;">
        <div class="card" style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Total Alat Terdaftar</div>
            <div style="font-size:26px;font-weight:700;color:#0f172a;margin-top:4px;">
                {{ $data->count() }} <span style="font-size:13px;font-weight:500;color:#64748b;">Jenis</span>
            </div>
        </div>
        <div class="card" style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Alat Tersedia (Siap Pakai)</div>
            <div style="font-size:26px;font-weight:700;color:#166534;margin-top:4px;">
                {{ $data->where('status', 'available')->count() }} <span style="font-size:13px;font-weight:500;color:#64748b;">Jenis</span>
            </div>
        </div>
        <div class="card" style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
            <div style="font-size:12px;font-weight:600;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.04em;">Alat Milik Pusat di Proyek</div>
            <div style="font-size:26px;font-weight:700;color:#1d4ed8;margin-top:4px;">
                {{ $centralToolsInProjects->sum('quantity') }} <span style="font-size:13px;font-weight:500;color:#64748b;">Unit ({{ $centralToolsInProjects->count() }} Item)</span>
            </div>
        </div>
    </div>

    {{-- Tabel Baru T14: Alat Milik Pusat yang Sedang Berada di Tiap Proyek --}}
    <div class="card mb-4" style="border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:6px;background:#eff6ff;display:flex;align-items:center;justify-content:center;color:#1d4ed8;flex-shrink:0;">
                    <i class="fas fa-building-circle-check" style="font-size:15px;"></i>
                </div>
                <div>
                    <span class="card-title" style="margin:0;font-size:15px;font-weight:700;color:#0f172a;">Alat Milik Pusat yang Sedang Berada di Tiap Proyek</span>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Rekapitulasi inventaris peralatan aset kantor pusat di lokasi gudang proyek</div>
                </div>
            </div>
            @if(isset($projectWarehouses) && $projectWarehouses->count() > 0)
            <form method="GET" action="{{ route('reports.tools') }}" style="margin:0;display:flex;align-items:center;gap:8px;">
                <label for="filter-wh" style="font-size:12px;font-weight:600;color:#475569;margin:0;">Filter Gudang:</label>
                <select name="warehouse_id" id="filter-wh" class="form-control" onchange="this.form.submit()" style="height:34px;font-size:12.5px;padding:4px 10px;width:auto;min-width:180px;">
                    <option value="">Semua Gudang Proyek</option>
                    @foreach($projectWarehouses as $pWh)
                    <option value="{{ $pWh->id }}" {{ (string)$warehouseId === (string)$pWh->id ? 'selected' : '' }}>
                        {{ $pWh->name }}
                    </option>
                    @endforeach
                </select>
                @if($warehouseId)
                <a href="{{ route('reports.tools') }}" class="btn btn-sm btn-light border" title="Reset Filter" style="height:34px;line-height:22px;">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </form>
            @endif
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="width:130px;">Kode Alat</th>
                        <th>Nama Alat & Merk</th>
                        <th style="text-align:center;width:110px;">Kuantitas (Qty)</th>
                        <th>Gudang Proyek</th>
                        <th style="width:140px;">Tgl Diterima</th>
                        <th>Kepemilikan</th>
                        <th>Kondisi Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($centralToolsInProjects as $item)
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:600;">{{ $item->tool_code }}</code></td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $item->tool_name }}</div>
                            <div style="font-size:11.5px;color:#64748b;">
                                {{ $item->brand }} · {{ $item->category_name }}
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="fw-700" style="font-size:15px;color:#0f172a;">{{ $item->quantity }}</span>
                            <span class="text-muted" style="font-size:11.5px;">unit</span>
                        </td>
                        <td>
                            <span class="fw-600" style="color:#334155;">{{ $item->warehouse_name }}</span>
                            @if(!empty($item->project_name) && $item->project_name !== '-')
                            <div style="font-size:11.5px;color:#64748b;">Proyek: {{ $item->project_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12.5px;color:#334155;display:flex;align-items:center;gap:5px;">
                                <i class="fas fa-calendar-day" style="font-size:11px;color:#64748b;"></i>
                                {{ \Carbon\Carbon::parse($item->received_date)->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;font-size:11px;padding:3px 8px;border-radius:4px;">
                                <i class="fas fa-building me-1" style="font-size:10px;"></i> Milik Pusat
                            </span>
                        </td>
                        <td>
                            <div style="font-size:12px;display:flex;gap:6px;flex-wrap:wrap;">
                                @if($item->stock_available > 0)
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">{{ $item->stock_available }} Tersedia</span>
                                @endif
                                @if($item->stock_borrowed > 0)
                                <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">{{ $item->stock_borrowed }} Dipinjam</span>
                                @endif
                                @if($item->stock_damaged > 0)
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">{{ $item->stock_damaged }} Rusak</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada alat milik pusat yang saat ini berada di gudang proyek.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daftar Status Seluruh Alat (Master) --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <i class="fas fa-toolbox" style="font-size:13px;"></i>
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Daftar Status Seluruh Alat</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:130px;">Kode</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Kondisi Fisik</th>
                        <th>Status Pemakaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    @php
                        $cond = $row->condition ?? $row['condition'] ?? 'good';
                        $stat = $row->status ?? $row['status'] ?? 'available';
                    @endphp
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{{ $row->code ?? $row['code'] }}</code></td>
                        <td class="fw-600" style="color:#0f172a;">{{ $row->name ?? $row['name'] }}</td>
                        <td>{{ $row->category_name ?? $row['category_name'] ?? '-' }}</td>
                        <td>
                            @if($cond === 'good')
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Baik</span>
                            @elseif($cond === 'damaged')
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">Rusak</span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;">Perbaikan</span>
                            @endif
                        </td>
                        <td>
                            @if($stat === 'available')
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Tersedia</span>
                            @elseif($stat === 'in_use')
                                <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">Dipinjam</span>
                            @else
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">Maintenance</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada data alat yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
