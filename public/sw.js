/**
 * PT Arsikon Service Worker
 * Handles PWA Caching, Web Push Notifications, and OS Click Redirection
 */

const CACHE_NAME = 'arsikon-pwa-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Web Push Event Handler
self.addEventListener('push', (event) => {
    let data = {
        title: 'PT Arsikon Notifikasi',
        body: 'Pembaruan operasional baru di sistem.',
        icon: '/assets/Logo-Dashboard.png',
        badge: '/assets/Logo-Dashboard.png',
        url: '/dashboard'
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            data = Object.assign({}, data, payload);
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/assets/Logo-Dashboard.png',
        badge: data.badge || '/assets/Logo-Dashboard.png',
        data: {
            url: data.url || '/dashboard'
        },
        vibrate: [100, 50, 100],
        actions: [
            { action: 'open', title: 'Buka Sistem' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification Click Event Handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

