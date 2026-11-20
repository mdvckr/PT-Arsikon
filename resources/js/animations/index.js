import { gsap } from 'gsap';
import { Flip } from 'gsap/Flip';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Arsikon Enterprise Smooth Motion Engine (powered by GSAP v3)
 * Designed for snappy, executive, 60fps responsiveness across CWMS / ERP.
 */

// Detect user's preference for reduced motion
const prefersReducedMotion = () => {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};
const motionAllowed = () => !prefersReducedMotion();

/**
 * Format number into string matching Indonesian format or source format
 */
const formatNumberWithTemplate = (number, rawString) => {
    const isRupiah = rawString.includes('Rp');
    const hasPercent = rawString.includes('%');
    
    // Determine separator from source
    let separator = '.';
    if (rawString.includes(',') && !rawString.includes('.')) {
        separator = ',';
    }

    let rounded = Math.round(number);
    let formatted = rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, separator);

    if (isRupiah) {
        return `Rp ${formatted}`;
    }
    if (hasPercent) {
        return `${formatted}%`;
    }
    
    // Keep custom prefix/suffix if present
    const prefix = rawString.match(/^[^\d]+/)?.[0] || '';
    const suffix = rawString.match(/[^\d]+$/)?.[0] || '';

    if (prefix && !prefix.includes('Rp') && suffix && !suffix.includes('%')) {
        return `${prefix}${formatted}${suffix}`;
    }

    return formatted;
};

export const ArsikonAnimate = {
    gsap,

    /**
     * Animate overall page entrance (headers, KPI cards, content panels, tables) across all views
     */
    pageEntrance(scope = document) {
        if (prefersReducedMotion()) return;

        const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

        // 1. Executive Top Headers & Breadcrumbs (all views)
        const headerElements = scope.querySelectorAll(
            '.erp-header-panel, .page-header, .breadcrumb, .profile-hero-card, .daily-filter-card, .daily-page-header, main > header'
        );
        if (headerElements.length > 0) {
            tl.fromTo(
                headerElements,
                { opacity: 0, y: -10 },
                { opacity: 1, y: 0, duration: 0.35, stagger: 0.05, clearProps: 'transform,opacity' },
                0
            );
        }

        // 2. KPI / Stat Cards Grid (Dashboard, Inventory, Daily Log, Logistics, Procurement, etc.)
        const statCards = scope.querySelectorAll(
            '.kpi-erp-card, .stat-card, .daily-kpi-card, .dash-kpi-grid > *, .daily-kpi-grid > *, .grid-4 > .stat-card, .grid-3 > .stat-card, .grid-2 > .stat-card, section.grid > div.p-5, section.grid > div.p-4'
        );
        if (statCards.length > 0) {
            tl.fromTo(
                statCards,
                { opacity: 0, y: 16, scale: 0.98 },
                { opacity: 1, y: 0, scale: 1, duration: 0.32, stagger: 0.04, clearProps: 'transform,scale,opacity' },
                0.05
            );
        }

        // 3. Primary Content Cards & Panels across all views
        const contentPanels = scope.querySelectorAll(
            '.card:not(.kpi-erp-card), .glass-erp-card:not(.kpi-erp-card), .clean-profile-card, .workspace-card, .daily-section-card, .login-card, .inv-card, [data-animate="panel"]'
        );
        if (contentPanels.length > 0) {
            tl.fromTo(
                contentPanels,
                { opacity: 0, y: 12 },
                { opacity: 1, y: 0, duration: 0.35, stagger: 0.06, clearProps: 'transform,opacity' },
                0.12
            );
        }

        // 4. Data Tables Rows (Fast, subtle entry for initial rows across all index views)
        const tableRows = scope.querySelectorAll(
            'table.data-table tbody tr:not(.empty-row), table.daily-table tbody tr:not(.empty-row), table.item-table tbody tr:not(.empty-row)'
        );
        if (tableRows.length > 0) {
            const firstBatchRows = Array.from(tableRows).slice(0, 15);
            tl.fromTo(
                firstBatchRows,
                { opacity: 0, x: -6 },
                { opacity: 1, x: 0, duration: 0.22, stagger: 0.02, clearProps: 'transform,opacity' },
                0.18
            );
        }

        // 4b. Opt-in hooks: [data-animate="header|kpi|panel|rows|reveal"]
        if (!prefersReducedMotion()) {
            const hooked = scope.querySelectorAll('[data-animate]');
            if (hooked.length > 0) {
                const byType = {};
                hooked.forEach((el) => {
                    const t = (el.getAttribute('data-animate') || '').trim().split(/\s+/);
                    t.forEach((type) => {
                        if (!type) return;
                        (byType[type] = byType[type] || []).push(el);
                    });
                });
                if (byType['header']) {
                    tl.fromTo(byType['header'], { opacity: 0, y: -10 }, { opacity: 1, y: 0, duration: 0.35, stagger: 0.05, clearProps: 'transform,opacity' }, 0);
                }
                if (byType['kpi']) {
                    tl.fromTo(byType['kpi'], { opacity: 0, y: 16, scale: 0.98 }, { opacity: 1, y: 0, scale: 1, duration: 0.32, stagger: 0.04, clearProps: 'transform,scale,opacity' }, 0.05);
                }
                if (byType['rows']) {
                    tl.fromTo(byType['rows'].slice(0, 15), { opacity: 0, x: -6 }, { opacity: 1, x: 0, duration: 0.22, stagger: 0.02, clearProps: 'transform,opacity' }, 0.18);
                }
                if (byType['reveal']) {
                    byType['reveal'].forEach((el) => {
                        gsap.fromTo(el, { opacity: 0, y: 22 }, {
                            opacity: 1, y: 0, duration: 0.45, ease: 'power2.out',
                            scrollTrigger: { trigger: el, start: 'top 85%', once: true },
                            clearProps: 'transform,opacity',
                        });
                    });
                }
            }
        }

        // 5. Trigger KPI Counter Animations
        this.animateCounters(scope);

        // 6. Setup interactive micro-interactions
        this.setupMicroInteractions(scope);
    },

    /**
     * Capture Flip layout state for items under a root (pair with x-show toggles via Alpine)
     */
    flipGetState(root) {
        if (prefersReducedMotion() || !root) return null;
        try {
            const items = root.querySelectorAll('[data-flip]');
            if (items.length === 0) return null;
            return Flip.getState(items);
        } catch (e) {
            return null;
        }
    },

    /**
     * Animate items to their new layout positions after Alpine applies x-show changes
     */
    flipFrom(state) {
        if (!state) return;
        try {
            Flip.from(state, {
                duration: 0.28,
                ease: 'power2.out',
                overwrite: true,
                nested: true,
                absolute: false,
            });
        } catch (e) {
            // layout animation is decorative; interaction must never break
        }
    },

    /**
     * Animate numeric counters in KPI cards (from 0 to target value)
     */
    animateCounters(scope = document) {
        if (prefersReducedMotion()) return;

        const counterElements = scope.querySelectorAll(
            '.kpi-primary-val, .stat-info .value, .daily-kpi-val, [data-counter], [data-gsap-counter], section.grid .tabular-nums'
        );

        counterElements.forEach((el) => {
            // Avoid re-animating already processed counters
            if (el.dataset.counterAnimated === 'true') return;

            const originalText = el.innerText.trim();
            // Extract numeric digits
            const cleanDigits = originalText.replace(/[^\d]/g, '');
            if (!cleanDigits || isNaN(cleanDigits)) return;

            const targetVal = parseInt(cleanDigits, 10);
            // Skip trivial zeroes or negative values
            if (targetVal <= 0) return;

            el.dataset.counterAnimated = 'true';
            el.dataset.originalText = originalText;

            const counterObj = { val: 0 };
            const duration = Math.min(1.2, Math.max(0.5, 0.35 + (Math.log10(targetVal + 1) * 0.15)));

            gsap.to(counterObj, {
                val: targetVal,
                duration: duration,
                ease: 'power2.out',
                onUpdate: () => {
                    el.innerText = formatNumberWithTemplate(counterObj.val, originalText);
                },
                onComplete: () => {
                    el.innerText = originalText;
                }
            });
        });
    },

    /**
     * Smooth spring pop opening for Modals
     */
    openModal(modalBackdropEl) {
        if (!modalBackdropEl) return;
        modalBackdropEl.classList.add('open');

        if (prefersReducedMotion()) {
            modalBackdropEl.style.opacity = '1';
            return;
        }

        const modalBox = modalBackdropEl.querySelector('.modal-box, .clean-modal-card, [class*="modal-card"]');

        gsap.fromTo(
            modalBackdropEl,
            { opacity: 0 },
            { opacity: 1, duration: 0.22, ease: 'power2.out' }
        );

        if (modalBox) {
            gsap.fromTo(
                modalBox,
                { opacity: 0, scale: 0.94, y: 12 },
                { opacity: 1, scale: 1, y: 0, duration: 0.28, ease: 'back.out(1.25)', clearProps: 'transform' }
            );
        }
    },

    /**
     * Smooth closing for Modals
     */
    closeModal(modalBackdropEl, callback = null) {
        if (!modalBackdropEl) return;

        if (prefersReducedMotion()) {
            modalBackdropEl.classList.remove('open');
            if (callback) callback();
            return;
        }

        const modalBox = modalBackdropEl.querySelector('.modal-box, .clean-modal-card, [class*="modal-card"]');
        const tl = gsap.timeline({
            onComplete: () => {
                modalBackdropEl.classList.remove('open');
                gsap.set([modalBackdropEl, modalBox], { clearProps: 'all' });
                if (callback) callback();
            }
        });

        if (modalBox) {
            tl.to(modalBox, { opacity: 0, scale: 0.96, y: 8, duration: 0.18, ease: 'power2.in' }, 0);
        }
        tl.to(modalBackdropEl, { opacity: 0, duration: 0.18, ease: 'power2.in' }, 0);
    },

    /**
     * Micro-interactions for buttons, cards hover elevation & alerts across all views
     */
    setupMicroInteractions(scope = document) {
        if (prefersReducedMotion()) return;

        // Button press feedback
        const interactiveButtons = scope.querySelectorAll('.btn, .btn-clean-amber, .btn-clean-navy, .btn-sm, .btn-login-interactive');
        interactiveButtons.forEach((btn) => {
            if (btn.dataset.motionBound) return;
            btn.dataset.motionBound = 'true';

            btn.addEventListener('mousedown', () => {
                gsap.to(btn, { scale: 0.97, duration: 0.12, ease: 'power1.out' });
            });
            btn.addEventListener('mouseup', () => {
                gsap.to(btn, { scale: 1, duration: 0.18, ease: 'back.out(2)' });
            });
            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { scale: 1, duration: 0.18, ease: 'power1.out' });
            });
        });

        // Hover elevation for KPI cards across all views
        const kpiCards = scope.querySelectorAll('.kpi-erp-card, .stat-card, .daily-kpi-card');
        kpiCards.forEach((card) => {
            if (card.dataset.elevationBound) return;
            card.dataset.elevationBound = 'true';

            card.addEventListener('mouseenter', () => {
                gsap.to(card, { y: -3, duration: 0.22, ease: 'power1.out' });
            });
            card.addEventListener('mouseleave', () => {
                gsap.to(card, { y: 0, duration: 0.22, ease: 'power1.out' });
            });
        });
    },

    /**
     * Smooth dismissal for alert banners
     */
    dismissAlert(alertEl) {
        if (!alertEl) return;
        if (prefersReducedMotion()) {
            alertEl.remove();
            return;
        }

        gsap.to(alertEl, {
            opacity: 0,
            y: -8,
            height: 0,
            marginBottom: 0,
            paddingTop: 0,
            paddingBottom: 0,
            duration: 0.28,
            ease: 'power2.inOut',
            onComplete: () => alertEl.remove()
        });
    }
};

// Global bootstrap on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    ArsikonAnimate.pageEntrance(document);

    // Watch for tab clicks (Alpine tabs, filter pills, etc.) to smoothly cascade dynamic content
    document.addEventListener('click', (e) => {
        try {
            const target = e.target instanceof Element ? e.target : null;
            if (!target) return;
            const tabTrigger = target.closest('[role="tab"], .btn-tab-filter, button[x-on\\:click*="activeTab"]');
            if (tabTrigger) {
                setTimeout(() => {
                    ArsikonAnimate.animateCounters(document);
                }, 80);
            }
        } catch (err) {
            // decorative watcher; never break page behaviour
        }
    });
});

// Support dynamic view reload / Livewire / Alpine custom events
window.addEventListener('content-changed', (e) => {
    const scope = e.detail?.target ? document.querySelector(e.detail.target) : document;
    ArsikonAnimate.pageEntrance(scope || document);
});
