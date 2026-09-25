<x-app-layout>
    <x-slot name="title">Pusat Notifikasi</x-slot>

    @push('styles')
    <style>
        /* Breadcrumb styling */
        .notif-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
        }
        .notif-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }
        .notif-breadcrumb a:hover {
            color: #2563eb;
        }

        /* Page Header */
        .notif-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }

        /* Tab Filter Bar */
        .notif-filter-tabs {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .notif-filter-btn {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 6px;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            outline: none;
        }

        .notif-filter-btn:hover {
            color: #0f172a;
            background: rgba(255,255,255,0.6);
        }

        .notif-filter-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            font-weight: 700;
        }

        .notif-filter-btn .counter-pill {
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Notification Card Feed */
        .notif-feed-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notif-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .notif-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }

        .notif-card.unread {
            background: #f8faff;
            border-color: #bfdbfe;
            border-left: 4px solid #2563eb;
        }

        .notif-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-meta-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 4px;
        }

        .notif-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
        }

        .notif-message {
            font-size: 12.5px;
            color: #475569;
            line-height: 1.45;
            margin-bottom: 10px;
            word-break: break-word;
        }

        .notif-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .notif-actions .btn {
            height: 32px;
            padding: 0 12px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 6px;
            box-sizing: border-box;
        }

        /* Unread pulse dot */
        .unread-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2563eb;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.25);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.75; }
        }

        /* Empty state */
        .notif-empty-state {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 48px 20px;
            text-align: center;
        }

        /* Mobile specific responsive styling */
        @media (max-width: 640px) {
            .notif-page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .notif-page-header .header-left h2 {
                font-size: 17px !important;
            }

            .notif-page-header .header-action-form,
            .notif-page-header .header-action-form .btn {
                width: 100%;
            }

            .notif-page-header .header-action-form .btn {
                height: 36px !important;
                font-size: 12.5px !important;
                justify-content: center;
            }

            .notif-card {
                padding: 12px;
                gap: 10px;
                border-radius: 8px;
            }

            .notif-icon-box {
                width: 34px;
                height: 34px;
                font-size: 14px;
                border-radius: 8px;
            }

            .notif-title {
                font-size: 13px;
            }

            .notif-message {
                font-size: 12px;
                margin-bottom: 8px;
            }

            .notif-actions {
                width: 100%;
            }

            .notif-actions .btn {
                flex: 1;
                justify-content: center;
                font-size: 11.5px !important;
                height: 34px !important;
                padding: 0 10px !important;
            }

            .notif-actions form {
                flex: 1;
                display: flex;
            }

            .notif-actions form .btn {
                width: 100%;
            }
        }
    </style>
    @endpush

    @php
        $unreadCount = $notifications->whereNull('read_at')->count();
        $readCount = $notifications->whereNotNull('read_at')->count();
        $totalCount = $notifications->total();
    @endphp

    {{-- Breadcrumb --}}
    <div class="notif-breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
        <i class="fas fa-chevron-right" style="font-size:9px;"></i>
        <span style="color:#0f172a;font-weight:600;">Notifikasi</span>
    </div>

    {{-- Page Header --}}
    <div class="notif-page-header">
        <div class="header-left">
            <h2 class="fw-700" style="font-size:18px;color:#0f172a;margin:0;display:flex;align-items:center;gap:7px;">
                <i class="fas fa-bell text-primary" style="font-size:16px;"></i>
                Pusat Pemberitahuan
            </h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;margin-bottom:0;">
                Semua notifikasi aktivitas pengiriman, pemakaian material, dan peminjaman alat
            </p>
        </div>

        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.markAllAsRead') }}" class="header-action-form" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-primary" style="height:34px;padding:0 14px;font-size:12px;font-weight:600;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.2);" onclick="return confirm('Tandai semua notifikasi telah dibaca?')">
                <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    {{-- Filter Tabs (Semua / Belum Dibaca / Sudah Dibaca) --}}
    <div class="notif-filter-tabs">
        <button type="button" class="notif-filter-btn active" data-filter="all" onclick="filterNotifs('all', this)">
            <i class="fas fa-inbox"></i>
            <span>Semua</span>
            <span class="counter-pill" style="background:#e2e8f0;color:#334155;">{{ $totalCount }}</span>
        </button>
        <button type="button" class="notif-filter-btn" data-filter="unread" onclick="filterNotifs('unread', this)">
            <i class="fas fa-envelope"></i>
            <span>Belum Dibaca</span>
            <span class="counter-pill" style="background:{{ $unreadCount > 0 ? '#2563eb' : '#e2e8f0' }};color:{{ $unreadCount > 0 ? '#ffffff' : '#64748b' }};">
                {{ $unreadCount }}
            </span>
        </button>
        <button type="button" class="notif-filter-btn" data-filter="read" onclick="filterNotifs('read', this)">
            <i class="fas fa-envelope-open"></i>
            <span>Sudah Dibaca</span>
            <span class="counter-pill" style="background:#e2e8f0;color:#64748b;">{{ $readCount }}</span>
        </button>
    </div>

    {{-- Notification List Feed --}}
    <div class="notif-feed-container" id="notifFeed">
        @forelse($notifications as $notif)
            @php
                $isUnread = is_null($notif->read_at);
                $data = $notif->data ?? [];
                $type = $data['type'] ?? $notif->type ?? 'info';
                $title = $data['title'] ?? $notif->title ?? 'Pemberitahuan Sistem';
                $message = $data['message'] ?? $notif->message ?? '-';
                $link = $data['url'] ?? $data['link'] ?? $notif->url ?? $notif->link ?? null;

                // Color & Icon Resolution
                $icon = 'bell';
                $iconColor = '#2563eb';
                $iconBg = '#eff6ff';
                $badgeCls = 'badge-info';
                $categoryLabel = 'Informasi';

                if ($type === 'stock_alert' || str_contains(strtolower($title), 'stok')) {
                    $icon = 'triangle-exclamation';
                    $iconColor = '#dc2626';
                    $iconBg = '#fef2f2';
                    $badgeCls = 'badge-danger';
                    $categoryLabel = 'Stok Kritis';
                } elseif ($type === 'approval_needed' || str_contains(strtolower($title), 'approval')) {
                    $icon = 'clock';
                    $iconColor = '#d97706';
                    $iconBg = '#fffbeb';
                    $badgeCls = 'badge-warning';
                    $categoryLabel = 'Persetujuan';
                } elseif (str_contains(strtolower($title), 'surat jalan') || str_contains(strtolower($title), 'pengiriman')) {
                    $icon = 'truck-fast';
                    $iconColor = '#2563eb';
                    $iconBg = '#eff6ff';
                    $badgeCls = 'badge-primary';
                    $categoryLabel = 'Surat Jalan';
                } elseif (str_contains(strtolower($title), 'pengeluaran') || str_contains(strtolower($title), 'pemakaian')) {
                    $icon = 'file-invoice';
                    $iconColor = '#ea580c';
                    $iconBg = '#fff7ed';
                    $badgeCls = 'badge-warning';
                    $categoryLabel = 'Pemakaian Material';
                } elseif (str_contains(strtolower($title), 'penerimaan') || str_contains(strtolower($title), 'masuk')) {
                    $icon = 'dolly';
                    $iconColor = '#059669';
                    $iconBg = '#ecfdf5';
                    $badgeCls = 'badge-success';
                    $categoryLabel = 'Penerimaan';
                } elseif (str_contains(strtolower($title), 'peminjaman') || str_contains(strtolower($title), 'alat')) {
                    $icon = 'screwdriver-wrench';
                    $iconColor = '#7c3aed';
                    $iconBg = '#f5f3ff';
                    $badgeCls = 'badge-secondary';
                    $categoryLabel = 'Peminjaman Alat';
                } elseif ($type === 'success' || str_contains(strtolower($title), 'selesai')) {
                    $icon = 'circle-check';
                    $iconColor = '#059669';
                    $iconBg = '#ecfdf5';
                    $badgeCls = 'badge-success';
                    $categoryLabel = 'Sukses';
                }
            @endphp

            <div class="notif-card {{ $isUnread ? 'unread' : 'read' }}" data-status="{{ $isUnread ? 'unread' : 'read' }}">
                {{-- Icon Box --}}
                <div class="notif-icon-box" style="background:{{ $iconBg }};color:{{ $iconColor }};">
                    <i class="fas fa-{{ $icon }}"></i>
                </div>

                {{-- Content --}}
                <div class="notif-content">
                    {{-- Meta Header: Category Badge + Timestamp + Unread Status --}}
                    <div class="notif-meta-top">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span class="badge {{ $badgeCls }}" style="font-size:10px;padding:2px 7px;border-radius:4px;">
                                {{ $categoryLabel }}
                            </span>

                            @if($isUnread)
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;color:#2563eb;">
                                <span class="unread-dot"></span> Belum Dibaca
                            </span>
                            @else
                            <span class="text-muted" style="font-size:10.5px;">
                                <i class="fas fa-check text-muted" style="font-size:9px;"></i> Telah Dibaca
                            </span>
                            @endif
                        </div>

                        <div class="text-muted" style="font-size:11px;display:flex;align-items:center;gap:4px;">
                            <i class="far fa-clock" style="font-size:10px;"></i>
                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                            <span style="opacity:0.6;">• {{ $notif->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="notif-title">
                        {{ $title }}
                    </div>

                    {{-- Message --}}
                    <div class="notif-message">
                        {{ $message }}
                    </div>

                    {{-- Actions --}}
                    <div class="notif-actions">
                        @if($link)
                        <a href="{{ $link }}" class="btn btn-primary" title="Buka Detail">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                            <span>Buka Halaman</span>
                        </a>
                        @endif

                        @if($isUnread)
                        <form method="POST" action="{{ route('notifications.markAsRead', $notif->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-light border" style="background:#ffffff;color:#475569;" title="Tandai Dibaca">
                                <i class="fas fa-check text-success"></i>
                                <span>Tandai Dibaca</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="notif-empty-state">
                <div style="width:54px;height:54px;border-radius:50%;background:#f1f5f9;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:12px;">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Belum Ada Notifikasi</div>
                <p class="text-muted" style="font-size:12.5px;max-width:320px;margin:0 auto 16px;">
                    Kotak masuk Anda masih bersih. Setiap ada pembaruan aktivitas logistik dan lapangan, informasinya akan tampil di sini.
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary" style="height:32px;padding:0 14px;border-radius:6px;font-size:12px;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        @endforelse
    </div>

    {{-- Filter Empty Notice (Hidden by default, shown via JS if all items in tab are hidden) --}}
    <div id="filterEmptyNotice" class="notif-empty-state" style="display:none;margin-top:10px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:10px;">
            <i class="fas fa-check-circle text-success"></i>
        </div>
        <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:2px;">Tidak Ada Notifikasi dalam Kategori Ini</div>
        <p class="text-muted" style="font-size:12px;margin:0;">
            Semua notifikasi pada tab ini sudah beres atau tidak ditemukan.
        </p>
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="mt-3 card" style="border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;background:#ffffff;">
        {{ $notifications->links() }}
    </div>
    @endif

    @push('scripts')
    <script>
        function filterNotifs(status, btn) {
            // Update active button styling
            document.querySelectorAll('.notif-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('.notif-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                if (status === 'all' || cardStatus === status) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Toggle empty notice
            const notice = document.getElementById('filterEmptyNotice');
            if (notice) {
                if (cards.length > 0 && visibleCount === 0) {
                    notice.style.display = 'block';
                } else {
                    notice.style.display = 'none';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
