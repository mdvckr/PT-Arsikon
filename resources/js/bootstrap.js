/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Konfigurasi Laravel Echo & Laravel Reverb (WebSocket) untuk real-time broadcasting.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED === 'true' && Boolean(import.meta.env.VITE_REVERB_APP_KEY);

if (typeof window !== 'undefined' && reverbEnabled) {
    try {
        const isSecure = (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https';
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: isSecure,
            enabledTransports: isSecure ? ['wss'] : ['ws'],
            unavailableTimeout: 3000,
        });

        // Cegah infinite reconnect loop jika server Reverb lokal tidak dijalankan
        if (window.Echo?.connector?.pusher?.connection) {
            window.Echo.connector.pusher.connection.bind('unavailable', () => {
                console.info('[Reverb] Server WebSocket lokal tidak aktif. Retry dihentikan; sistem beralih ke polling background.');
                window.Echo.connector.pusher.disconnect();
            });
            window.Echo.connector.pusher.connection.bind('failed', () => {
                window.Echo.connector.pusher.disconnect();
            });
        }
    } catch (error) {
        console.warn('[Reverb] Inisialisasi WebSocket Echo dilewati:', error);
    }
}
