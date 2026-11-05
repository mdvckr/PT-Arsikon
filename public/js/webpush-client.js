/**
 * PT Arsikon Web Push Notification Client
 * Handles Browser PushManager Subscription and VAPID Authentication
 */

(function () {
    'use strict';

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    const WebPush = {
        getVapidPublicKey() {
            const el = document.querySelector('meta[name="vapid-public-key"]');
            return el ? el.getAttribute('content') : null;
        },

        getCsrfToken() {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '';
        },

        isSupported() {
            return ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
        },

        async checkStatus() {
            if (!this.isSupported()) return { supported: false, status: 'unsupported' };
            if (Notification.permission === 'denied') return { supported: true, status: 'denied' };

            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                return {
                    supported: true,
                    status: sub ? 'subscribed' : 'prompt',
                    subscription: sub
                };
            } catch (err) {
                return { supported: true, status: 'error', error: err };
            }
        },

        async subscribe() {
            if (!this.isSupported()) {
                if (window.notify) {
                    window.notify({
                        title: 'Tidak Didukung',
                        message: 'Browser Anda belum mendukung Web Push Notifications.',
                        type: 'warning'
                    });
                }
                return false;
            }

            const vapidKey = this.getVapidPublicKey();
            if (!vapidKey) {
                console.error('VAPID public key not found in meta tags.');
                return false;
            }

            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    if (window.notify) {
                        window.notify({
                            title: 'Izin Ditolak',
                            message: 'Izin notifikasi tidak diaktifkan oleh pengguna.',
                            type: 'warning'
                        });
                    }
                    return false;
                }

                const reg = await navigator.serviceWorker.ready;
                let sub = await reg.pushManager.getSubscription();

                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey)
                    });
                }

                const res = await fetch('/push-subscriptions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(sub)
                });

                if (!res.ok) throw new Error('Gagal menyimpan langganan Web Push ke server.');

                if (window.notify) {
                    window.notify({
                        title: 'Notifikasi OS Aktif',
                        message: 'Notifikasi sistem operasi PT Arsikon berhasil diaktifkan.',
                        type: 'success'
                    });
                }
                return true;
            } catch (err) {
                console.error('Web Push subscription error:', err);
                if (window.notify) {
                    window.notify({
                        title: 'Gagal Mengaktifkan',
                        message: 'Terjadi kesalahan saat mengaktifkan Web Push: ' + err.message,
                        type: 'error'
                    });
                }
                return false;
            }
        },

        async unsubscribe() {
            if (!this.isSupported()) return false;

            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();

                if (sub) {
                    await fetch('/push-subscriptions', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ endpoint: sub.endpoint })
                    });

                    await sub.unsubscribe();
                }

                if (window.notify) {
                    window.notify({
                        title: 'Notifikasi Dinonaktifkan',
                        message: 'Langganan notifikasi sistem operasi telah dimatikan.',
                        type: 'neutral'
                    });
                }
                return true;
            } catch (err) {
                console.error('Web Push unsubscribe error:', err);
                return false;
            }
        }
    };

    window.WebPush = WebPush;
})();

