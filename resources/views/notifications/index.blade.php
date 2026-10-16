<x-app-layout>
    <x-slot name="title">Pusat Pemberitahuan — Arsikon Warehouse</x-slot>

    @push('styles')
    <style>
        /* ========================================================
           ENTERPRISE NOTIFICATION CENTER (PT ARSIKON)
           Clean, Human-Crafted, Responsive Activity Feed
           ======================================================== */
        .notif-container {
            width: 100%;
        }

        /* Top Breadcrumb */
        .notif-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 16px;
        }

        .notif-breadcrumb a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
        }

        .notif-breadcrumb a:hover {
            color: #0f172a;
        }

        /* Page Header Banner */
        .notif-header-banner {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .notif-title-area h1 {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.4px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notif-title-area p {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        /* Controls Bar (Filter Tabs + Search) */
        .notif-controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .notif-tabs-segment {
            display: inline-flex;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 12px;
            gap: 4px;
        }

        .notif-tab-btn {
            border: none;
            background: transparent;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.15s ease;
        }

        .notif-tab-btn:hover {
            color: #0f172a;
        }

        .notif-tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            font-weight: 800;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .notif-tab-badge {
            font-size: 10.5px;
            font-weight: 800;
            padding: 1px 7px;
            border-radius: 9999px;
            background: #cbd5e1;
            color: #334155;
        }

        .notif-tab-btn.active .notif-tab-badge.badge-active-unread {
            background: #2563eb;
            color: #ffffff;
        }

        /* Search Input */
        .notif-search-wrap {
            position: relative;
            min-width: 240px;
            flex: 1;
            max-width: 320px;
        }

        .notif-search-wrap input {
            width: 100%;
            height: 38px;
            padding: 0 12px 0 36px;
            font-size: 12.5px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .notif-search-wrap input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .notif-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        /* Card List */
        .notif-list-feed {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notif-feed-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            position: relative;
        }

        .notif-feed-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .notif-feed-card.is-unread {
            background: #fcfdfe;
            border-color: #bfdbfe;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.05);
        }

        .notif-feed-card.is-unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 14px;
            bottom: 14px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: #2563eb;
        }

        .notif-squircle-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notif-body-area {
            flex: 1;
            min-width: 0;
        }

        .notif-meta-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 6px;
        }

        .notif-card-title {
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
        }

        .notif-card-desc {
            font-size: 13px;
            color: #334155;
            line-height: 1.5;
            margin-bottom: 12px;
            font-weight: 500;
            word-break: break-word;
        }

        .notif-actions-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-notif-action {
            height: 32px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-notif-primary {
            background: #2563eb;
            color: #ffffff;
            border: 1px solid #1d4ed8;
        }
        .btn-notif-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-notif-ghost {
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-notif-ghost:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .notif-empty-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 56px 24px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        @media (max-width: 640px) {
            .notif-header-banner {
                flex-direction: column;
                align-items: flex-start;
            }
            .notif-controls-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .notif-search-wrap {
                max-width: 100%;
            }
            .notif-feed-card {
                padding: 14px;
                gap: 12px;
            }
            .notif-squircle-icon {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }
        }
    </style>
    @endpush

    @php
        $unreadCount = $notifications->whereNull('read_at')->count();
        $readCount = $notifications->whereNotNull('read_at')->count();
        $totalCount = $notifications->total();
    @endphp

    <div class="notif-container">
        
        {{-- Breadcrumb --}}
        <div class="notif-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span style="color:#0f172a;font-weight:700;">Pusat Pemberitahuan</span>
        </div>

        {{-- Page Header Banner --}}
        <div class="notif-header-banner">
            <div class="notif-title-area">
                <h1>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base shrink-0 border border-blue-100">
                        <i class="fas fa-bell"></i>
                    </div>
                    <span>Pusat Pemberitahuan</span>
                </h1>
                <p>
                    Pantau aktivitas persetujuan, logistik material proyek, dan pembaruan sistem secara langsung.
                </p>
            </div>

            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-notif-action btn-notif-primary" style="height:36px;padding:0 16px;box-shadow:0 1px 2px rgba(37,99,235,0.2);" onclick="return confirm('Tandai semua notifikasi telah dibaca?')">
                    <i class="fas fa-check-double"></i>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
            @endif
        </div>

        {{-- Controls Bar (Segmented Tabs & Search) --}}
        <div class="notif-controls-bar">
            {{-- Tabs --}}
            <div class="notif-tabs-segment">
                <button type="button" class="notif-tab-btn active" data-filter="all" onclick="filterNotifs('all', this)">
                    <i class="fas fa-inbox"></i>
                    <span>Semua</span>
                    <span class="notif-tab-badge">{{ $totalCount }}</span>
                </button>
                <button type="button" class="notif-tab-btn" data-filter="unread" onclick="filterNotifs('unread', this)">
                    <i class="fas fa-envelope"></i>
                    <span>Belum Dibaca</span>
                    <span class="notif-tab-badge {{ $unreadCount > 0 ? 'badge-active-unread' : '' }}">{{ $unreadCount }}</span>
                </button>
                <button type="button" class="notif-tab-btn" data-filter="read" onclick="filterNotifs('read', this)">
                    <i class="fas fa-envelope-open"></i>
                    <span>Sudah Dibaca</span>
                    <span class="notif-tab-badge">{{ $readCount }}</span>
                </button>
            </div>

            {{-- Live Search --}}
            <div class="notif-search-wrap">
                <i class="fas fa-search notif-search-icon"></i>
                <input type="text" id="notifSearchInput" placeholder="Cari notifikasi atau nomor PO..." onkeyup="searchNotifs(this.value)">
            </div>
        </div>

        {{-- Notification List Feed --}}
        <div class="notif-list-feed" id="notifFeed">
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
                    $badgeStyle = 'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;';
                    $categoryLabel = 'Informasi';

                    if ($type === 'stock_alert' || str_contains(strtolower($title), 'stok')) {
                        $icon = 'triangle-exclamation';
                        $iconColor = '#dc2626';
                        $iconBg = '#fef2f2';
                        $badgeStyle = 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;';
                        $categoryLabel = 'Peringatan Stok';
                    } elseif ($type === 'approval_needed' || str_contains(strtolower($title), 'approval') || str_contains(strtolower($title), 'persetujuan')) {
                        $icon = 'hourglass-half';
                        $iconColor = '#d97706';
                        $iconBg = '#fffbeb';
                        $badgeStyle = 'background:#fffbeb;color:#92400e;border:1px solid #fde68a;';
                        $categoryLabel = 'Persetujuan';
                    } elseif (str_contains(strtolower($title), 'surat jalan') || str_contains(strtolower($title), 'pengiriman')) {
                        $icon = 'truck-fast';
                        $iconColor = '#2563eb';
                        $iconBg = '#eff6ff';
                        $badgeStyle = 'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;';
                        $categoryLabel = 'Surat Jalan';
                    } elseif (str_contains(strtolower($title), 'pengeluaran') || str_contains(strtolower($title), 'pemakaian')) {
                        $icon = 'file-invoice';
                        $iconColor = '#ea580c';
                        $iconBg = '#fff7ed';
                        $badgeStyle = 'background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;';
                        $categoryLabel = 'Pemakaian Material';
                    } elseif (str_contains(strtolower($title), 'penerimaan') || str_contains(strtolower($title), 'masuk') || str_contains(strtolower($title), 'gr')) {
                        $icon = 'dolly';
                        $iconColor = '#059669';
                        $iconBg = '#ecfdf5';
                        $badgeStyle = 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;';
                        $categoryLabel = 'Penerimaan Barang';
                    } elseif (str_contains(strtolower($title), 'peminjaman') || str_contains(strtolower($title), 'alat')) {
                        $icon = 'screwdriver-wrench';
                        $iconColor = '#7c3aed';
                        $iconBg = '#f5f3ff';
                        $badgeStyle = 'background:#f5f3ff;color:#5b21b6;border:1px solid #ddd6fe;';
                        $categoryLabel = 'Peminjaman Alat';
                    } elseif ($type === 'success' || str_contains(strtolower($title), 'selesai')) {
                        $icon = 'circle-check';
                        $iconColor = '#059669';
                        $iconBg = '#ecfdf5';
                        $badgeStyle = 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;';
                        $categoryLabel = 'Selesai';
                    }
                @endphp

                <div class="notif-feed-card {{ $isUnread ? 'is-unread' : 'is-read' }}" data-status="{{ $isUnread ? 'unread' : 'read' }}">
                    {{-- Left Squircle Icon --}}
                    <div class="notif-squircle-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};border:1px solid {{ $iconColor }}25;">
                        <i class="fas fa-{{ $icon }}"></i>
                    </div>

                    {{-- Content --}}
                    <div class="notif-body-area">
                        {{-- Meta Header: Category Badge + Timestamp + Unread Status --}}
                        <div class="notif-meta-header">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span class="badge" style="{{ $badgeStyle }}font-size:10.5px;padding:2px 8px;border-radius:6px;font-weight:700;">
                                    {{ $categoryLabel }}
                                </span>

                                @if($isUnread)
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:800;color:#2563eb;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#2563eb;box-shadow:0 0 6px #2563eb;"></span>
                                    Belum Dibaca
                                </span>
                                @else
                                <span style="font-size:11px;color:#94a3b8;font-weight:600;">
                                    <i class="fas fa-check text-slate-400 me-1"></i> Dibaca
                                </span>
                                @endif
                            </div>

                            <div style="font-size:11.5px;color:#64748b;display:flex;align-items:center;gap:6px;font-weight:500;">
                                <i class="far fa-clock" style="font-size:10.5px;color:#94a3b8;"></i>
                                <strong style="color:#0f172a;">{{ $notif->created_at->diffForHumans() }}</strong>
                                <span style="color:#cbd5e1;">&bull;</span>
                                <span>{{ $notif->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        {{-- Title: 100% BLACK TEXT --}}
                        <div class="notif-card-title">
                            {{ $title }}
                        </div>

                        {{-- Message: 100% BLACK/DARK SLATE TEXT --}}
                        <div class="notif-card-desc">
                            {{ $message }}
                        </div>

                        {{-- Action Buttons --}}
                        <div class="notif-actions-row">
                            @if($link)
                            <a href="{{ $link }}" class="btn-notif-action btn-notif-primary">
                                <span>Buka Berkas</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            @endif

                            @if($isUnread)
                            <form method="POST" action="{{ route('notifications.markAsRead', $notif->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-notif-action btn-notif-ghost" title="Tandai Sudah Dibaca">
                                    <i class="fas fa-check text-emerald-600"></i>
                                    <span>Tandai Dibaca</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="notif-empty-box">
                    <div style="width:56px;height:56px;border-radius:16px;background:#f1f5f9;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:14px;">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:6px;">Kotak Masuk Bersih</h3>
                    <p style="font-size:13px;color:#64748b;max-width:360px;margin:0 auto 20px;line-height:1.5;">
                        Tidak ada notifikasi baru saat ini. Semua pembaruan permohonan, persetujuan, dan pengiriman material akan langsung muncul di sini.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn-notif-action btn-notif-ghost" style="height:36px;padding:0 16px;">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Filter Empty State (Shown via JS when search / filter produces 0 results) --}}
        <div id="filterEmptyNotice" class="notif-empty-box" style="display:none;margin-top:14px;">
            <div style="width:50px;height:50px;border-radius:14px;background:#ecfdf5;color:#059669;display:inline-flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:12px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:4px;">Tidak Ada Notifikasi Sesuai Filter</h4>
            <p style="font-size:12.5px;color:#64748b;margin:0;">
                Semua notifikasi pada kategori atau kata kunci ini telah selesai atau tidak ditemukan.
            </p>
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
        <div class="mt-4 p-3 bg-white border border-slate-200 rounded-xl">
            {{ $notifications->links() }}
        </div>
        @endif

    </div>

    @push('scripts')
    <script>
        let currentFilterStatus = 'all';
        let currentSearchQuery = '';

        function filterNotifs(status, btn) {
            currentFilterStatus = status;
            document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applyFilters();
        }

        function searchNotifs(query) {
            currentSearchQuery = query.toLowerCase().trim();
            applyFilters();
        }

        function applyFilters() {
            const cards = document.querySelectorAll('.notif-feed-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardText = card.innerText.toLowerCase();

                const matchesStatus = (currentFilterStatus === 'all' || cardStatus === currentFilterStatus);
                const matchesSearch = (!currentSearchQuery || cardText.includes(currentSearchQuery));

                if (matchesStatus && matchesSearch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

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
