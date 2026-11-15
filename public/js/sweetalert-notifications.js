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
            backdrop: false,
            heightAuto: false,
            html: html,
            showConfirmButton: false,
            showCloseButton: true,
            timer: options.duration || 4500,
            timerProgressBar: false,
            customClass: {
                container: 'swal2-toast-clean-container',
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

    /**
     * Global SweetAlert2 Confirmation Dialog for PT Arsikon
     * Replaces native browser window.confirm
     * @param {Object} options
     * @param {string} [options.title='Konfirmasi Tindakan']
     * @param {string} [options.text='Apakah Anda yakin ingin melanjutkan?']
     * @param {string} [options.icon='warning']
     * @param {string} [options.confirmButtonText='Ya, Lanjutkan']
     * @param {string} [options.cancelButtonText='Batal']
     * @param {boolean} [options.isDanger=false]
     * @returns {Promise<boolean>}
     */
    function confirmAction(options) {
        if (!options) options = {};
        const title = options.title || 'Konfirmasi Tindakan';
        const text = options.text || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        const isDanger = options.isDanger || /hapus|delete|batal|keluar|logout/i.test(text + ' ' + title);
        const icon = options.icon || (isDanger ? 'warning' : 'question');
        const confirmBtnText = options.confirmButtonText || (isDanger ? 'Ya, Lanjutkan' : 'Ya, Setuju');
        const cancelBtnText = options.cancelButtonText || 'Batal';

        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm(text));
        }

        return Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: confirmBtnText,
            cancelButtonText: cancelBtnText,
            reverseButtons: true,
            focusCancel: isDanger,
            customClass: {
                popup: 'swal2-arsikon-modal',
                confirmButton: isDanger ? 'swal2-arsikon-btn-danger' : 'swal2-arsikon-btn-primary',
                cancelButton: 'swal2-arsikon-btn-cancel'
            },
            buttonsStyling: false
        }).then(function (result) {
            return !!result.isConfirmed;
        });
    }

    window.confirmAction = confirmAction;

    // Override native window.alert to eliminate '127.0.0.1:8000 says'
    window.alert = function (message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Pemberitahuan Sistem',
                text: String(message || ''),
                icon: 'info',
                confirmButtonText: 'Mengerti',
                customClass: {
                    popup: 'swal2-arsikon-modal',
                    confirmButton: 'swal2-arsikon-btn-primary'
                },
                buttonsStyling: false
            });
        } else {
            console.warn('[Pemberitahuan]:', message);
        }
    };

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

    // Global Delegated Handler for forms with data-confirm
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.dataset) return;

        if (form.dataset.confirm && !form.dataset.confirmed) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const confirmMsg = form.dataset.confirm;
            const confirmTitle = form.dataset.confirmTitle;
            const confirmBtn = form.dataset.confirmBtn;
            const isDanger = form.dataset.confirmDanger === 'true' || 
                /hapus|delete|batal|keluar|logout/i.test(confirmMsg + ' ' + (confirmTitle || ''));

            confirmAction({
                title: confirmTitle || (isDanger ? 'Konfirmasi Tindakan' : 'Konfirmasi'),
                text: confirmMsg,
                confirmButtonText: confirmBtn || (isDanger ? 'Ya, Lanjutkan' : 'Ya, Setuju'),
                cancelButtonText: form.dataset.confirmCancel || 'Batal',
                icon: form.dataset.confirmIcon || (isDanger ? 'warning' : 'question'),
                isDanger: isDanger
            }).then(function (confirmed) {
                if (confirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    }, true);

    // Global Delegated Handler for buttons/links with data-confirm
    document.addEventListener('click', function (e) {
        const target = e.target.closest('[data-confirm]');
        if (!target) return;

        // If target is inside a form without data-confirm on form itself
        const form = target.closest('form');
        if (form && !form.dataset.confirm) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const confirmMsg = target.dataset.confirm;
            const confirmTitle = target.dataset.confirmTitle;
            const confirmBtn = target.dataset.confirmBtn;
            const isDanger = target.dataset.confirmDanger === 'true' || 
                /hapus|delete|batal|keluar|logout/i.test(confirmMsg + ' ' + (confirmTitle || ''));

            confirmAction({
                title: confirmTitle || (isDanger ? 'Konfirmasi Tindakan' : 'Konfirmasi'),
                text: confirmMsg,
                confirmButtonText: confirmBtn || (isDanger ? 'Ya, Lanjutkan' : 'Ya, Setuju'),
                cancelButtonText: target.dataset.confirmCancel || 'Batal',
                icon: target.dataset.confirmIcon || (isDanger ? 'warning' : 'question'),
                isDanger: isDanger
            }).then(function (confirmed) {
                if (confirmed) {
                    if (target.name) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = target.name;
                        hidden.value = target.value;
                        form.appendChild(hidden);
                    }
                    form.submit();
                }
            });
            return;
        }

        // If it is a standalone link
        if (target.tagName === 'A' && target.href) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const href = target.href;
            confirmAction({
                title: target.dataset.confirmTitle || 'Konfirmasi',
                text: target.dataset.confirm,
                confirmButtonText: target.dataset.confirmBtn || 'Ya, Lanjutkan',
                isDanger: target.dataset.confirmDanger === 'true'
            }).then(function (confirmed) {
                if (confirmed) {
                    window.location.href = href;
                }
            });
        }
    }, true);

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

