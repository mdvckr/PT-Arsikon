<x-app-layout>
    <x-slot name="title">Notifikasi</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Semua Notifikasi</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Pusat pemberitahuan aktivitas sistem Anda</p>
        </div>
        <div>
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Tandai semua telah dibaca?')">
                    <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tipe</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notif)
                    @php $isUnread = is_null($notif->read_at); @endphp
                    <tr style="{{ $isUnread ? 'background:#eff6ff;' : '' }}">
                        <td class="text-muted" style="font-size:12.5px;">{{ $notif->created_at->diffForHumans() }}<br>{{ $notif->created_at->format('d/m H:i') }}</td>
                        <td>
                            @php
                                $typeMap = [
                                    'stock_alert' => ['badge-danger', 'triangle-exclamation', 'Stok Kritis'],
                                    'approval_needed' => ['badge-warning', 'clock', 'Butuh Approval'],
                                    'system_info' => ['badge-info', 'info-circle', 'Sistem'],
                                ];
                                [$cls, $icon, $label] = $typeMap[$notif->type] ?? ['badge-gray', 'bell', $notif->type];
                            @endphp
                            <span class="badge {{ $cls }}"><i class="fas fa-{{ $icon }}"></i> {{ $label }}</span>
                        </td>
                        <td class="{{ $isUnread ? 'fw-700' : '' }}" style="max-width:400px;font-size:13.5px;">
                            {{ $notif->message }}
                        </td>
                        <td>
                            @if($isUnread)
                                <span class="badge badge-warning" style="font-size:11px;">Belum Dibaca</span>
                            @else
                                <span class="badge badge-gray" style="font-size:11px;">Dibaca</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @if($notif->link)
                                <a href="{{ $notif->link }}" class="btn btn-sm btn-primary btn-icon" title="Buka Tautan"><i class="fas fa-external-link-alt"></i></a>
                                @endif
                                @if($isUnread)
                                <form method="POST" action="{{ route('notifications.markAsRead', $notif) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success btn-icon" title="Tandai Dibaca"><i class="fas fa-check"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted p-4">Tidak ada notifikasi baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
