<x-app-layout>
    <x-slot name="title">Audit Log</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;">Audit Trail Log</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Catatan aktivitas sistem yang tidak dapat diubah (immutable)</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Deskripsi atau modul...">
                </div>
                <div style="min-width:150px;">
                    <label class="form-label">Aksi</label>
                    <select name="action" class="form-control">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ strtoupper($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:130px;">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div style="min-width:130px;">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $actionColors = [
                            'LOGIN'      => 'badge-info',
                            'LOGOUT'     => 'badge-gray',
                            'CREATE'     => 'badge-success',
                            'UPDATE'     => 'badge-warning',
                            'DELETE'     => 'badge-danger',
                            'APPROVE'    => 'badge-success',
                            'REJECT'     => 'badge-danger',
                            'SHIP'       => 'badge-primary',
                            'RECEIVE'    => 'badge-info',
                            'ADJUSTMENT' => 'badge-purple',
                        ];
                    @endphp
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-muted" style="white-space:nowrap;">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span style="font-size:11px;">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <div class="fw-600">{{ $log->user?->name ?? 'System' }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $log->user?->email ?? '' }}</div>
                        </td>
                        <td>
                            @php $cls = $actionColors[strtoupper($log->event)] ?? 'badge-gray'; @endphp
                            <span class="badge {{ $cls }}">{{ strtoupper($log->event) }}</span>
                        </td>
                        <td class="text-muted">{{ class_basename($log->auditable_type ?? 'System') }}</td>
                        <td style="max-width:300px;font-size:13px;">{{ $log->event }} pada {{ class_basename($log->auditable_type ?? 'Aktivitas') }} #{{ $log->auditable_id ?? '' }}</td>
                        <td class="text-muted" style="font-size:12px;">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-shield-halved"></i>
                                <h3>Belum Ada Log</h3>
                                <p>Aktivitas sistem akan tercatat di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
