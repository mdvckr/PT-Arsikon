<x-app-layout>
    <x-slot name="title">Detail Pengembalian {{ $return->return_number }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Pengembalian #{{ $return->return_number }}</h2>
            <p style="font-size:13px;color:#64748b;">Diajukan oleh {{ $return->requester->name ?? 'Sistem' }} pada {{ $return->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @php
                $badgeClass = match($return->status) {
                    'received' => 'badge-success',
                    'approved' => 'badge-primary',
                    'pending'  => 'badge-warning',
                    'rejected' => 'badge-danger',
                    default    => 'badge-secondary'
                };
            @endphp
            <span class="badge {{ $badgeClass }}" style="font-size:14px;padding:6px 12px;">
                Status: {{ ucfirst($return->status) }}
            </span>
            <a href="{{ route('returns.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#0f172a;">Rincian Pengembalian</h3>
            <table class="table" style="font-size:14px;margin-bottom:20px;">
                <tr>
                    <td style="width:200px;color:#64748b;">Dari Gudang Proyek</td>
                    <td><strong>{{ $return->fromWarehouse->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Tujuan (Gudang Pusat)</td>
                    <td><strong>{{ $return->toWarehouse->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Alasan Pengembalian</td>
                    <td><span class="badge badge-secondary">{{ ucfirst($return->reason ?? '-') }}</span></td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Tanggal Pengembalian</td>
                    <td>{{ $return->return_date ? $return->return_date->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="color:#64748b;">Catatan</td>
                    <td>{{ $return->notes ?? '-' }}</td>
                </tr>
            </table>

            <h4 style="font-size:15px;font-weight:600;color:#0f172a;margin-bottom:12px;">Item Material</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Qty Dikembalikan</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($return->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->material->name ?? '-' }}</strong>
                                @if($item->material?->type)
                                    <span style="font-size:12px;color:#64748b;">[{{ $item->material->type }}]</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }} {{ $item->material->unit->abbreviation ?? '' }}</td>
                            <td>
                                @php
                                    $condBadge = match($item->condition) {
                                        'good' => 'badge-success',
                                        'damaged' => 'badge-warning',
                                        default => 'badge-danger'
                                    };
                                @endphp
                                <span class="badge {{ $condBadge }}">{{ ucfirst($item->condition) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card" style="height:fit-content;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#0f172a;">Aksi Status</h3>

            @if($return->status === 'pending')
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <form method="POST" action="{{ route('returns.approve', $return) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width:100%;">✓ Setujui Pengembalian</button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-form').style.display='block'" class="btn btn-danger" style="width:100%;">✕ Tolak Pengembalian</button>

                    <div id="reject-form" style="display:none;margin-top:12px;padding:12px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;">
                        <form method="POST" action="{{ route('returns.reject', $return) }}">
                            @csrf
                            <label class="form-label required">Alasan Penolakan</label>
                            <textarea name="rejection_reason" class="form-input mb-2" rows="2" required></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="document.getElementById('reject-form').style.display='none'" class="btn btn-sm btn-secondary">Batal</button>
                                <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($return->status === 'approved')
                <form method="POST" action="{{ route('returns.receive', $return) }}">
                    @csrf
                    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Material sudah tiba di Gudang Pusat? Klik tombol di bawah untuk memasukkan kembali stok ke Gudang Pusat.</p>
                    <button type="submit" class="btn btn-success" style="width:100%;">📦 Terima Barang & Update Stok</button>
                </form>
            @else
                <p style="font-size:13px;color:#64748b;">
                    Status saat ini: <strong>{{ ucfirst($return->status) }}</strong>.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
