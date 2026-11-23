<x-app-layout>
    <x-slot name="title">Pusat Pemberitahuan — Arsikon Warehouse</x-slot>

    @push('styles')
    <style>
        :root {
            --arsikon-amber: #ea580c;
            --arsikon-amber-dark: #c2410c;
            --arsikon-amber-soft: #fff7ed;
            --arsikon-slate-dark: #0f172a;
            --arsikon-slate-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.88);
            --glass-border: rgba(226, 232, 240, 0.85);
            --glass-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
        }

        .notif-container {
            width: 100%;
        }

        /* Top Breadcrumb */
        .notif-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 14px;
            font-weight: 500;
        }

        .notif-breadcrumb a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .notif-breadcrumb a:hover {
            color: #0f172a;
        }

        /* Header Banner */
        .notif-header-banner {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .notif-header-title {
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notif-header-desc {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        /* Top KPI Metric Cards */
        .notif-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }

        .notif-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .notif-kpi-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -2px rgba(15, 23, 42, 0.06);
        }

        .notif-kpi-card.is-active-filter {
            border-color: var(--arsikon-amber);
            background: #fffcf9;
            box-shadow: 0 0 0 1px var(--arsikon-amber), 0 4px 14px -2px rgba(234, 88, 12, 0.12);
        }

        .notif-kpi-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .notif-kpi-number {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .notif-kpi-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
        }

        /* Controls Bar */
        .notif-controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .notif-tabs-segment {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            gap: 4px;
            border: 1px solid #e2e8f0;
        }

        .notif-tab-btn {
            border: none;
            background: transparent;
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .notif-tab-btn:hover {
            color: #0f172a;
        }

        .notif-tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .notif-tab-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #475569;
        }

        .notif-tab-btn.active .notif-tab-badge.badge-active-unread {
            background: var(--arsikon-amber);
            color: #ffffff;
        }

        /* Search Box */
        .notif-search-wrap {
            position: relative;
            width: 100%;
            max-width: 320px;
        }

        .notif-search-wrap input {
            width: 100%;
            height: 38px;
            padding: 0 14px 0 36px;
            font-size: 12.5px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: all 0.15s ease;
        }

        .notif-search-wrap input:focus {
            border-color: var(--arsikon-amber);
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12);
        }

        .notif-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: #94a3b8;
            pointer-events: none;
        }

        /* Notification Feed Cards */
        .notif-feed-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 12px;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.15s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
        }

        .notif-feed-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px -3px rgba(15, 23, 42, 0.06);
        }

        .notif-feed-card.is-unread {
            background: #ffffff;
            border-left: 4px solid var(--arsikon-amber);
            box-shadow: 0 3px 12px -2px rgba(234, 88, 12, 0.06);
        }

        .notif-feed-card.is-read {
            background: #fbfcfd;
            border-color: #e2e8f0;
            opacity: 0.92;
        }

        .notif-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .notif-card-title {
            font-size: 15.5px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.2px;
            line-height: 1.35;
            margin-bottom: 6px;
        }

        .notif-card-desc {
            font-size: 13px;
            color: #475569;
            line-height: 1.55;
            margin-bottom: 14px;
        }

        .notif-actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
        }

        /* Buttons */
        .btn-notif-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 16px;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            background: #0f172a;
            border: 1px solid #0f172a;
            border-radius: 9px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        .btn-notif-primary:hover {
            background: var(--arsikon-amber);
            border-color: var(--arsikon-amber);
            color: #ffffff;
        }

        .btn-notif-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-notif-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .notif-empty-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 48px 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(234, 88, 12, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(234, 88, 12, 0); }
        }

        .pulse-indicator {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--arsikon-amber);
            display: inline-block;
            animation: pulseDot 2s infinite;
        }

        @media (max-width: 768px) {
            .notif-kpi-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
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
        }
    </style>
    @endpush

    @php
        $unreadCount = $notifications->whereNull('read_at')->count();
        $readCount = $notifications->whereNotNull('read_at')->count();
        $totalCount = $notifications->total();
    @endphp

    <div class="notif-container" data-animate="panel">
        
        {{-- Breadcrumb --}}
        <div class="notif-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span style="color:#cbd5e1;">/</span>
            <span style="color:#0f172a;font-weight:600;">Pusat Pemberitahuan</span>
        </div>

        {{-- Page Header Banner --}}
        <div class="notif-header-banner">
            <div>
                <h1 class="notif-header-title">
                    <span>Pusat Pemberitahuan & Aktivitas</span>
                    @if($unreadCount > 0)
                        <span style="font-size:11.5px;font-weight:700;padding:2px 10px;border-radius:9999px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;">
                            {{ $unreadCount }} Perlu Tindakan
                        </span>
                    @endif
                </h1>
                <p class="notif-header-desc">
                    Pantau aktivitas persetujuan, logistik material proyek, dan pembaruan sistem secara langsung.
                </p>
            </div>

            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}" style="margin:0;"
                  data-confirm="Tandai semua notifikasi belum dibaca sebagai telah dibaca?"
                  data-confirm-title="Tandai Semua Dibaca"
                  data-confirm-btn="Ya, Tandai Semua">
                @csrf
                <button type="submit" class="btn-notif-secondary" style="height:36px;padding:0 16px;font-weight:600;">
                    Tandai Semua Dibaca
                </button>
            </form>
            @endif
        </div>
        {{-- Controls Bar (Tabs + Search) --}}
        <div class="notif-controls-bar">
            {{-- Segmented Tabs --}}
            <div class="notif-tabs-segment">
                <button type="button" class="notif-tab-btn active" data-filter="all" onclick="filterNotifs('all', this)">
                    <span>Semua</span>
                    <span class="notif-tab-badge">{{ $totalCount }}</span>
                </button>
                <button type="button" class="notif-tab-btn" data-filter="unread" onclick="filterNotifs('unread', this)">
                    <span>Belum Dibaca</span>
                    <span class="notif-tab-badge {{ $unreadCount > 0 ? 'badge-active-unread' : '' }}">{{ $unreadCount }}</span>
                </button>
                <button type="button" class="notif-tab-btn" data-filter="read" onclick="filterNotifs('read', this)">
                    <span>Sudah Dibaca</span>
                    <span class="notif-tab-badge">{{ $readCount }}</span>
                </button>
            </div>

            {{-- Live Search --}}
            <div class="notif-search-wrap">
                <x-lucide-search class="notif-search-icon" />
                <input type="text" id="notifSearchInput" placeholder="Cari notifikasi atau nomor PO..." onkeyup="searchNotifs(this.value)">
            </div>
        </div>

        {{-- Notification List Feed --}}
        <div id="notifFeed">
            @forelse($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $data = $notif->data ?? [];
                    $type = $data['type'] ?? $notif->type ?? 'info';
                    $title = $data['title'] ?? $notif->title ?? 'Pemberitahuan Sistem';
                    $message = $data['message'] ?? $notif->message ?? '-';
                    $link = $data['url'] ?? $data['link'] ?? $notif->url ?? $notif->link ?? null;

                    // Clean, subtle category styling (no screaming rainbow colors)
                    $categoryLabel = 'Informasi';
                    $badgeStyle = 'background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;';

                    if ($type === 'stock_alert' || str_contains(strtolower($title), 'stok')) {
                        $categoryLabel = 'Peringatan Stok';
                        $badgeStyle = 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;';
                    } elseif ($type === 'approval_needed' || str_contains(strtolower($title), 'approval') || str_contains(strtolower($title), 'persetujuan')) {
                        $categoryLabel = 'Persetujuan';
                        $badgeStyle = 'background:#fffbeb;color:#92400e;border:1px solid #fde68a;';
                    } elseif (str_contains(strtolower($title), 'pengadaan') || str_contains(strtolower($title), 'mr') || str_contains(strtolower($title), 'po')) {
                        $categoryLabel = 'Pengadaan Material';
                        $badgeStyle = 'background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;';
                    } elseif (str_contains(strtolower($title), 'surat jalan') || str_contains(strtolower($title), 'pengiriman')) {
                        $categoryLabel = 'Surat Jalan';
                        $badgeStyle = 'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;';
                    } elseif (str_contains(strtolower($title), 'pengeluaran') || str_contains(strtolower($title), 'pemakaian')) {
                        $categoryLabel = 'Pemakaian Material';
                        $badgeStyle = 'background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;';
                    } elseif (str_contains(strtolower($title), 'penerimaan') || str_contains(strtolower($title), 'masuk') || str_contains(strtolower($title), 'gr')) {
                        $categoryLabel = 'Penerimaan Barang';
                        $badgeStyle = 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;';
                    } elseif (str_contains(strtolower($title), 'peminjaman') || str_contains(strtolower($title), 'alat')) {
                        $categoryLabel = 'Peminjaman Alat';
                        $badgeStyle = 'background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;';
                    } elseif ($type === 'success' || str_contains(strtolower($title), 'selesai')) {
                        $categoryLabel = 'Selesai';
                        $badgeStyle = 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;';
                    }
                @endphp

                <div class="notif-feed-card {{ $isUnread ? 'is-unread' : 'is-read' }}" data-status="{{ $isUnread ? 'unread' : 'read' }}">
                    {{-- Meta Header: Category Badge + Status + Timestamp --}}
                    <div class="notif-card-meta">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="{{ $badgeStyle }}font-size:11px;font-weight:700;padding:2.5px 9px;border-radius:6px;letter-spacing:0.2px;">
                                {{ $categoryLabel }}
                            </span>

                            @if($isUnread)
                            <span style="font-size:11.5px;font-weight:700;color:var(--arsikon-amber);display:inline-flex;align-items:center;gap:6px;">
                                <span class="pulse-indicator"></span>
                                Belum Dibaca
                            </span>
                            @else
                            <span style="font-size:11px;font-weight:500;color:#94a3b8;">
                                Sudah Ditinjau
                            </span>
                            @endif
                        </div>

                        <div style="font-size:12px;color:#64748b;font-weight:500;">
                            <strong style="color:#1e293b;">{{ $notif->created_at->diffForHumans() }}</strong>
                            <span style="color:#cbd5e1;margin:0 5px;">&bull;</span>
                            <span>{{ $notif->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="notif-card-title">
                        {{ $title }}
                    </div>

                    {{-- Description --}}
                    <div class="notif-card-desc">
                        {{ $message }}
                    </div>

                    {{-- Action Row --}}
                    @if($link || $isUnread)
                    <div class="notif-actions-row">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            @if($link)
                            <a href="{{ $link }}" class="btn-notif-primary">
                                Buka Berkas
                            </a>
                            @endif

                            @if($isUnread)
                            <form method="POST" action="{{ route('notifications.markAsRead', $notif->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-notif-secondary" title="Tandai Sudah Dibaca">
                                    Tandai Dibaca
                                </button>
                            </form>
                            @endif
                        </div>

                        <div style="font-size:11.5px;color:#94a3b8;font-weight:500;">
                            Pusat Notifikasi PT-Arsikon
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="notif-empty-box">
                    <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:6px;">Semua Pemberitahuan Telah Ditinjau</h3>
                    <p style="font-size:13px;color:#64748b;max-width:380px;margin:0 auto 20px;line-height:1.5;">
                        Tidak ada notifikasi baru saat ini. Semua pembaruan aktivitas permohonan, persetujuan, dan logistik akan langsung muncul di sini.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn-notif-secondary" style="height:36px;padding:0 18px;">
                        Kembali ke Dashboard
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Filter Empty State (Shown via JS when search / filter produces 0 results) --}}
        <div id="filterEmptyNotice" class="notif-empty-box" style="display:none;margin-top:14px;">
            <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Tidak Ada Notifikasi Sesuai Filter</h4>
            <p style="font-size:13px;color:#64748b;margin:0;">
                Semua notifikasi pada kategori atau kata kunci pencarian ini tidak ditemukan.
            </p>
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
        <div style="margin-top:16px;padding:12px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
            {{ $notifications->links() }}
        </div>
        @endif

    </div>

    @push('scripts')
    <script>
        let currentFilterStatus = 'all';
        let currentSearchQuery = '';

        function setFilterTab(status) {
            const btn = document.querySelector(`.notif-tab-btn[data-filter="${status}"]`);
            if (btn) {
                filterNotifs(status, btn);
            }
        }

        function filterNotifs(status, btn) {
            currentFilterStatus = status;
            document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Highlight corresponding KPI card
            document.querySelectorAll('.notif-kpi-card').forEach(c => c.classList.remove('is-active-filter'));
            const kpiTarget = document.getElementById(`kpi-${status}`);
            if (kpiTarget) {
                kpiTarget.classList.add('is-active-filter');
            }

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
                    card.style.display = 'block';
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
