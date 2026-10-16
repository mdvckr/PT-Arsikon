/**
 * Liquid Glass (iOS 26 Style) Notification Center for PT-Arsikon
 * Powered by Alpine.js + Tailwind CSS + Laravel Reverb & Polling
 */

export function notificationCenter() {
    return {
        banners: [],
        queue: [],
        unreadCount: 0,
        panelOpen: false,
        panelItems: [],
        panelLoading: false,
        pollTimer: null,
        lastNotifId: null,

        init() {
            // Read initial stats from body dataset
            this.unreadCount = parseInt(document.body.dataset.notifCount || '0', 10);
            this.lastNotifId = document.body.dataset.notifLastId || null;

            // Expose globally so external triggers (flash session, custom JS) can push banners
            window.showGlassNotification = (data) => this.pushBanner(data);
            window.showNotificationToast = (data) => this.pushBanner(data);
            window.notificationCenter = this;

            // Connect to real-time Echo (Reverb)
            this.initEcho();

            // Background polling fallback (every 10s)
            this.startPolling();

            // Close panel on outside click or Esc
            window.addEventListener('click', (e) => {
                if (!e.target.closest('#glass-notification-bell') && !e.target.closest('#glass-notification-panel')) {
                    this.panelOpen = false;
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.panelOpen) this.panelOpen = false;
                    if (this.banners.length > 0) this.dismissBanner(this.banners[0].id);
                }
            });
        },

        initEcho() {
            const setupEcho = () => {
                if (!window.Echo) {
                    setTimeout(setupEcho, 500);
                    return;
                }

                const userId = document.body.dataset.userId;
                const isAdminPusat = document.body.dataset.isAdminPusat === '1';
                const isOwner = document.body.dataset.isOwner === '1';

                // Individual user notification channel
                if (userId) {
                    try {
                        window.Echo.private(`App.Models.User.${userId}`)
                            .notification((notification) => {
                                this.unreadCount++;
                                this.pushBanner({
                                    id: notification.id || 'notif-' + Date.now(),
                                    title: notification.title || 'Pemberitahuan Sistem',
                                    message: notification.message || '',
                                    type: notification.type || 'info',
                                    url: notification.url || document.body.dataset.notifUrl,
                                    sound_type: notification.sound_type || notification.type,
                                    created_at_human: 'Baru saja'
                                });
                            });
                    } catch (err) {
                        console.warn('[Echo] Notification channel error:', err);
                    }
                }

                // Handler for RequestSubmitted events
                const handleRequest = (data) => {
                    this.unreadCount++;
                    this.pushBanner({
                        id: data.id || 'req-' + Date.now(),
                        title: data.title || 'Pengajuan Baru',
                        message: data.message || '',
                        type: data.type || 'warning',
                        url: data.url || document.body.dataset.notifUrl,
                        sound_type: data.sound_type || 'approval',
                        created_at_human: 'Baru saja'
                    });
                };

                if (isAdminPusat) {
                    try {
                        window.Echo.private('admin-pusat')
                            .listen('.RequestSubmitted', handleRequest)
                            .listen('RequestSubmitted', handleRequest);
                    } catch (e) {}
                }

                if (isOwner) {
                    try {
                        window.Echo.private('owner-monitoring')
                            .listen('.RequestSubmitted', handleRequest)
                            .listen('RequestSubmitted', handleRequest);
                    } catch (e) {}
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupEcho);
            } else {
                setupEcho();
            }
        },

        startPolling() {
            const fetchUrl = document.body.dataset.notifFetchUrl || '/notifications/unread-count';
            const poll = () => {
                fetch(fetchUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    const count = res.count || 0;
                    const prevCount = this.unreadCount;
                    this.unreadCount = count;

                    if (res.latest && res.latest.id && res.latest.id !== this.lastNotifId && count > prevCount) {
                        this.lastNotifId = res.latest.id;
                        this.pushBanner(res.latest);
                    } else if (res.latest && !this.lastNotifId) {
                        this.lastNotifId = res.latest.id;
                    }
                })
                .catch(() => {});
            };

            setTimeout(poll, 1200);
            this.pollTimer = setInterval(poll, 10000);
        },

        pushBanner(item) {
            if (!item) return;

            const title = (item.title || 'Notifikasi').trim();
            const message = (item.message || '').trim();
            const dedupeKey = `${title}:::${message}`;

            // 1. Deduplication cooldown guard (4 seconds window)
            const now = Date.now();
            if (!this._recentPushes) this._recentPushes = new Map();
            if (this._recentPushes.has(dedupeKey)) {
                const lastTime = this._recentPushes.get(dedupeKey);
                if (now - lastTime < 4000) {
                    return; // Ignore duplicate within cooldown window
                }
            }
            this._recentPushes.set(dedupeKey, now);

            // Clean old deduplication entries (> 10s)
            for (const [k, t] of this._recentPushes.entries()) {
                if (now - t > 10000) this._recentPushes.delete(k);
            }

            // 2. Active banners & queue check: don't duplicate if already displayed or queued
            if (this.banners.some(b => b.title === title && b.message === message && !b.isClosing)) {
                return;
            }
            if (this.queue.some(b => b.title === title && b.message === message)) {
                return;
            }

            const soundType = item.sound_type || item.type || 'info';
            if (typeof window.playNotificationChime === 'function') {
                window.playNotificationChime(soundType);
            }

            // Haptic feedback (10ms subtle tick)
            if (typeof navigator !== 'undefined' && navigator.vibrate) {
                try { navigator.vibrate(10); } catch (e) {}
            }

            const rawType = (item.type || 'info').toLowerCase();
            let tint = 'info';
            if (rawType.includes('success') || rawType.includes('approved')) {
                tint = 'success';
            } else if (rawType.includes('approval') || rawType.includes('warning') || rawType.includes('stock')) {
                tint = 'warning';
            } else if (rawType.includes('reject') || rawType.includes('cancel') || rawType.includes('danger') || rawType.includes('error')) {
                tint = 'danger';
            }

            const duration = (item.url ? 8000 : 5500);

            const banner = {
                id: item.id || 'banner-' + Date.now() + '-' + Math.random().toString(36).substr(2, 4),
                title: title,
                message: message,
                type: tint,
                url: item.url || item.link || null,
                time: item.created_at_human || 'Baru saja',
                expanded: false,
                isClosing: false,
                touchStartX: 0,
                touchStartY: 0,
                currentDeltaY: 0,
                currentDeltaX: 0,
                timer: null,
                paused: false,
                duration: duration,
                hasActions: Boolean(item.url)
            };

            banner.timer = setTimeout(() => {
                this.dismissBanner(banner.id);
            }, duration);

            if (this.banners.length >= 3) {
                this.queue.push(banner);
            } else {
                this.banners.unshift(banner);
            }
        },

        pauseTimer(banner) {
            if (banner.timer) {
                clearTimeout(banner.timer);
                banner.timer = null;
                banner.paused = true;
            }
        },

        resumeTimer(banner) {
            if (banner.paused) {
                banner.paused = false;
                banner.timer = setTimeout(() => {
                    this.dismissBanner(banner.id);
                }, 3500);
            }
        },

        toggleExpand(id) {
            const b = this.banners.find(x => x.id === id);
            if (b) {
                b.expanded = !b.expanded;
                if (b.expanded) {
                    this.pauseTimer(b);
                } else {
                    this.resumeTimer(b);
                }
            }
        },

        dismissBanner(id) {
            const idx = this.banners.findIndex(x => x.id === id);
            if (idx === -1) return;

            const b = this.banners[idx];
            if (b.timer) clearTimeout(b.timer);
            b.isClosing = true;

            setTimeout(() => {
                this.banners = this.banners.filter(x => x.id !== id);
                if (this.queue.length > 0) {
                    const next = this.queue.shift();
                    this.banners.push(next);
                }
            }, 300);
        },

        openNotification(banner) {
            if (banner.id && typeof banner.id === 'string' && !banner.id.startsWith('banner-') && !banner.id.startsWith('req-')) {
                // Call mark as read asynchronously
                fetch(`/notifications/${banner.id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
            }

            this.dismissBanner(banner.id);
            if (banner.url && banner.url !== '#') {
                window.location.href = banner.url;
            }
        },

        // Touch gestures for swipe-up / swipe-away
        handleTouchStart(e, banner) {
            const touch = e.touches[0];
            banner.touchStartX = touch.clientX;
            banner.touchStartY = touch.clientY;
            banner.currentDeltaY = 0;
            banner.currentDeltaX = 0;
            this.pauseTimer(banner);
        },

        handleTouchMove(e, banner) {
            const touch = e.touches[0];
            banner.currentDeltaX = touch.clientX - banner.touchStartX;
            banner.currentDeltaY = touch.clientY - banner.touchStartY;
        },

        handleTouchEnd(e, banner) {
            // Swipe up to dismiss (deltaY < -40)
            if (banner.currentDeltaY < -40) {
                this.dismissBanner(banner.id);
                return;
            }

            // Swipe sideways to dismiss (abs(deltaX) > 80)
            if (Math.abs(banner.currentDeltaX) > 80) {
                this.dismissBanner(banner.id);
                return;
            }

            banner.currentDeltaX = 0;
            banner.currentDeltaY = 0;
            this.resumeTimer(banner);
        },

        // Dropdown Panel
        togglePanel() {
            this.panelOpen = !this.panelOpen;
            if (this.panelOpen) {
                this.fetchPanelItems();
            }
        },

        fetchPanelItems() {
            this.panelLoading = true;
            fetch('/notifications/unread', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                this.panelItems = res.unread || [];
                this.unreadCount = res.count ?? this.panelItems.length;
            })
            .catch(() => {
                this.panelItems = [];
            })
            .finally(() => {
                this.panelLoading = false;
            });
        },

        markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(() => {
                this.unreadCount = 0;
                this.panelItems = [];
                this.banners = [];
            })
            .catch(() => {});
        },

        markSingleAsRead(id, url) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(() => {
                this.panelItems = this.panelItems.filter(x => x.id !== id);
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                if (url && url !== '#') {
                    window.location.href = url;
                }
            })
            .catch(() => {
                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        }
    };
}
