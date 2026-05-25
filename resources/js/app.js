import './bootstrap';

import Alpine from 'alpinejs';
import focus   from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

import registerThemeStore from './theme';
import initStarfield      from './starfield';

window.Alpine = Alpine;

Alpine.plugin(focus);
Alpine.plugin(collapse);

// ── theme store (dark mode) ─────────────────────────────────────────────────
registerThemeStore(Alpine);

// ── auth store ──────────────────────────────────────────────────────────────
// Shared state between the auth-page left panel (in layouts/guest.blade.php)
// and the role selector inside the login/register form. The left panel reads
// `$store.auth.role` to display role-appropriate marketing copy.
Alpine.store('auth', {
    role: 'customer', // default until the user picks a tab
});

// ── countUp ─────────────────────────────────────────────────────────────────
// Usage: x-data="countUp(1234, '₹', '')" x-init="init()" x-text="display"
Alpine.data('countUp', (target, prefix = '', suffix = '') => ({
    display: prefix + '0' + suffix,

    init() {
        const num = Number(target);
        if (!num) {
            this.display = prefix + '0' + suffix;
            return;
        }

        const duration  = 1100;
        const startTime = performance.now();

        const fmt = (val) =>
            prefix + Math.round(val).toLocaleString('en-IN') + suffix;

        const step = (now) => {
            const p     = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
            this.display = fmt(num * eased);
            if (p < 1) requestAnimationFrame(step);
            else        this.display = fmt(num); // guarantee exact final value
        };

        requestAnimationFrame(step);
    },
}));

// ── toastTimer ──────────────────────────────────────────────────────────────
// Used by admin/app layouts to auto-dismiss toasts with a progress bar.
// Usage: x-data="toastTimer(4000)"
Alpine.data('toastTimer', (ms = 4000) => ({
    show: true,
    init() {
        setTimeout(() => (this.show = false), ms);
    },
}));

Alpine.start();

// ── Starfield ───────────────────────────────────────────────────────────────
// Mounts on whichever layout includes <x-galaxy-bg/>. No-op if the canvas isn't
// in the DOM. Safe to call once globally.
document.addEventListener('DOMContentLoaded', () => initStarfield('galaxy-bg'));

// ── Scroll-reveal ────────────────────────────────────────────────────────────
// Observes every .reveal element; adds .is-visible when it enters the viewport.
// Works for both server-rendered and Alpine-rendered (via MutationObserver) DOM.
(function setupReveal() {
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach(({ target, isIntersecting }) => {
                if (!isIntersecting) return;
                target.classList.add('is-visible');
                io.unobserve(target);
            });
        },
        { threshold: 0.06, rootMargin: '0px 0px -28px 0px' }
    );

    const observeNew = () =>
        document.querySelectorAll('.reveal:not(.is-visible)')
                .forEach((el) => io.observe(el));

    // Initial pass — runs after Alpine.start() so x-for content is rendered
    observeNew();

    // Catch any elements added later by Alpine or AJAX
    new MutationObserver(observeNew)
        .observe(document.body, { childList: true, subtree: true });
})();
