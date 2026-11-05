/**
 * PT Arsikon SweetAlert2 Pastel Pill Toast Notification Engine
 * Matches exact 5-state pastel reference mockup with synchronized Web Audio Chimes
 */

(function () {
    'use strict';

    // Stroke Outline SVG Icons Matching User Design Mockup
    const ICONS = {
        neutral: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
        blue: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`,
        success: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/><polyline points="20 12 11 21 8 18"/></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>`
    };

    const TITLES = {
        neutral: 'Notifikasi Sistem',
        blue: 'Informasi Baru',
        info: 'Informasi Baru',
        success: 'Berhasil Disimpan',
        warning: 'Peringatan Operasional',
        error: 'Terjadi Kesalahan'
    };

    /**
     * Dispatch a pastel pill toast notification
     * @param {Object} options
     * @param {string} options.title - Notification title
     * @param {string} options.message - Notification descriptive text
     * @param {string} options.type - 'neutral' | 'blue' | 'info' | 'success' | 'warning' | 'error'
     * @param {string} [options.url] - Optional navigation target on toast click
     * @param {string} [options.sound_type] - 'info' | 'success' | 'warning' | 'error' | 'bell' | 'none'
     * @param {number} [options.duration=4500] - Duration in ms before auto-dismiss
     */
    function notify(options) {
        if (!options) return;

        let type = (options.type || 'info').toLowerCase();
        if (type === 'danger') type = 'error';
        if (type === 'primary') type = 'blue';

        const toastClass = 'toast-' + (['neutral', 'blue', 'info', 'success', 'warning', 'error'].includes(type) ? (type === 'info' ? 'blue' : type) : 'neutral');
        const iconSvg = ICONS[type] || ICONS.neutral;
        const titleText = options.title || TITLES[type] || 'Notifikasi';
        const messageText = options.message || '';

        const soundKey = options.sound_type || (type === 'blue' ? 'bell' : type);
        if (soundKey !== 'none' && typeof window.playNotificationChime === 'function') {
            try {
                window.playNotificationChime(soundKey);
            } catch (e) {
                console.warn('Notification chime error:', e);
            }
        }

        if (typeof Swal === 'undefined') {
            console.log(`[Notification ${type}]: ${titleText} - ${messageText}`);
            return;
        }

        const html = `
            <div class="swal2-pastel-icon">${iconSvg}</div>
            <div class="swal2-pastel-content">
                <div class="swal2-pastel-title">${escapeHtml(titleText)}</div>
                ${messageText ? `<div class="swal2-pastel-message">${escapeHtml(messageText)}</div>` : ''}
            </div>
        `;

        Swal.fire({
            toast: true,
            position: 'top',
            html: html,
            showConfirmButton: false,
            showCloseButton: true,
            timer: options.duration || 4500,
            timerProgressBar: false,
            customClass: {
                popup: `swal2-pastel-toast ${toastClass}`
            },
            didOpen: (toast) => {
                if (options.url) {
                    toast.style.cursor = 'pointer';
                    toast.addEventListener('click', (e) => {
                        // Don't navigate if user clicked the close button
                        if (e.target.closest('.swal2-close')) return;
                        window.location.href = options.url;
                    });
                }
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Expose global helper
    window.notify = notify;

    // Backward compatibility with legacy showNotificationToast & showGlassNotification
    window.showGlassNotification = function (data) {
        if (!data) return;
        notify({
            title: data.title || (data.type === 'success' ? 'Berhasil' : 'Informasi'),
            message: data.message || '',
            type: data.type || 'info',
            url: data.url || null,
            sound_type: data.sound_type || data.type || 'info'
        });
    };

    window.showNotificationToast = window.showGlassNotification;

    // Render session flash notifications if configured in blade
    document.addEventListener('DOMContentLoaded', function () {
        if (window.serverFlashNotifications && Array.isArray(window.serverFlashNotifications)) {
            window.serverFlashNotifications.forEach(function (f, idx) {
                setTimeout(function () {
                    notify(f);
                }, idx * 400);
            });
        }
    });

})();

