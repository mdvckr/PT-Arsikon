<x-app-layout>
    <x-slot name="title">Pengembalian Material (Returns)</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Pengembalian Material (Returns)</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola pengembalian sisa material atau barang rusak dari proyek ke Gudang Pusat</p>
        </div>
        <a href="{{ route('returns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajukan Pengembalian
        </a>
    </div>

    <!-- Filter Status & Pencarian -->
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('returns.index') }}" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari No. Pengembalian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Pengembalian..." class="form-control">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending (Menunggu)</option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Disetujui</option>
                        <option value="received" {{ request('status')=='received' ? 'selected':'' }}>Diterima (Selesai)</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Ditolak</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No Retur</th>
                        <th>Dari Gudang</th>
                        <th>Tujuan</th>
                        <th>Alasan</th>
                        <th>Tgl Pengembalian</th>
                        <th>Pemohon</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $reasonMap = [
                            'excess' => 'Kelebihan / Sisa',
                            'damaged' => 'Barang Rusak',
                            'wrong_item' => 'Salah Kirim',
                            'project_complete' => 'Proyek Selesai',
                            'other' => 'Lainnya',
                        ];
                        $statusMap = [
                            'pending'  => ['badge-warning', 'clock', 'Pending'],
                            'approved' => ['badge-primary', 'truck', 'Disetujui'],
                            'received' => ['badge-success', 'check-double', 'Diterima'],
                            'rejected' => ['badge-danger', 'times-circle', 'Ditolak'],
                        ];
                    @endphp
                    @forelse($returns as $r)
                        @php
                            [$cls, $icon, $label] = $statusMap[$r->status] ?? ['badge-gray', 'question', ucfirst($r->status)];
                            $reasonText = $reasonMap[$r->reason] ?? ucfirst($r->reason ?? 'Lainnya');
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('returns.show', $r) }}" class="text-primary fw-600">
                                    {{ $r->return_number }}
                                </a>
                            </td>
                            <td>{{ $r->fromWarehouse->name ?? '-' }}</td>
                            <td>{{ $r->toWarehouse->name ?? '-' }}</td>
                            <td><span class="badge badge-gray">{{ $reasonText }}</span></td>
                            <td>{{ $r->return_date ? $r->return_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $r->requester->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $cls }}">
                                    <i class="fas fa-{{ $icon }}"></i> {{ $label }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('returns.show', $r) }}" class="btn btn-sm btn-secondary btn-icon" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                Belum ada data pengembalian material.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
            <div style="padding: 12px; border-top:1px solid #f1f5f9;">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

