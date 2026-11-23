import { gsap } from 'gsap';
import Lenis from 'lenis';

export class ArsikonScroll {
    constructor({ lerp = 0.1, smoothWheel = true, smoothTouch = false } = {}) {
        this.lenis = null;
        this.options = { lerp, smoothWheel, smoothTouch };
        this.stopCallbacks = [];
        this.startCallbacks = [];

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        this.init();
    }

    init() {
        if (this.lenis) return;

        this.lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            gestureDirection: 'vertical',
            smooth: this.options.smoothWheel,
            smoothTouch: this.options.smoothTouch,
            touchMultiplier: this.options.smoothTouch ? 3.5 : 1,
            lerp: this.options.lerp,
            wheelMultiplier: 0.9,
        });

        this.lenis.on('scroll', () => {
            if (typeof ScrollTrigger !== 'undefined') {
                ScrollTrigger.update();
            }
        });

        gsap.ticker.add(this.raf);
        gsap.ticker.lagSmoothing(0);

        window.arsikonScroll = this;
        window.lenis = this.lenis;
    }

    raf = (time) => {
        if (this.lenis) {
            this.lenis.raf(time * 1000);
        }
    };

    stop() {
        if (this.lenis) {
            this.lenis.stop();
        }
        this.stopCallbacks.forEach((cb) => cb());
    }

    start() {
        if (this.lenis) {
            this.lenis.start();
        }
        this.startCallbacks.forEach((cb) => cb());
    }

    onStop(cb) {
        this.stopCallbacks.push(cb);
    }

    onStart(cb) {
        this.startCallbacks.push(cb);
    }

    scrollTo(target, options = {}) {
        if (this.lenis) {
            this.lenis.scrollTo(target, options);
        }
    }

    destroy() {
        if (this.lenis) {
            this.lenis.destroy();
            this.lenis = null;
        }
        gsap.ticker.remove(this.raf);
        window.arsikonScroll = undefined;
        window.lenis = undefined;
    }
}

export const arsikonScroll = new ArsikonScroll();
