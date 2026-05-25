/**
 * Dark-mode controller.
 *
 * Responsibilities:
 *   1. Provide an Alpine store (`$store.theme`) that components can read/write.
 *   2. Sync the store ↔ <html class="dark"> ↔ localStorage('theme').
 *   3. Listen for system preference changes when the user hasn't explicitly chosen.
 *
 * IMPORTANT: the initial dark/light decision is made by the inline anti-flicker
 * script in each layout's <head>. This file runs after Alpine starts and just
 * registers the reactive store + helpers. By the time we get here, the correct
 * class is already on <html> — we just sync the store to match it.
 */
export default function registerThemeStore(Alpine) {
    const STORAGE_KEY = 'theme'; // 'dark' | 'light' | null (= follow system)

    const systemPrefersDark = () =>
        window.matchMedia('(prefers-color-scheme: dark)').matches;

    const resolveStored = () => {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch {
            return null;
        }
    };

    const applyClass = (isDark) => {
        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    };

    Alpine.store('theme', {
        // initial value mirrors what the anti-flicker script already applied
        dark: document.documentElement.classList.contains('dark'),

        init() {
            // If user hasn't explicitly picked, follow system preference live
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            mq.addEventListener('change', (e) => {
                if (resolveStored() === null) {
                    this.dark = e.matches;
                    applyClass(e.matches);
                }
            });
        },

        toggle() {
            this.dark = !this.dark;

            // Add a brief transition class so every element fades color smoothly.
            // We remove it after the transition so it doesn't slow normal interactions.
            document.documentElement.classList.add('theme-transition');
            applyClass(this.dark);
            window.setTimeout(() => {
                document.documentElement.classList.remove('theme-transition');
            }, 320);

            try {
                localStorage.setItem(STORAGE_KEY, this.dark ? 'dark' : 'light');
            } catch {
                // localStorage may be blocked (private mode) — silently ignore
            }
        },

        /** Reset to "follow system" mode (clears the explicit choice). */
        followSystem() {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch { /* ignore */ }
            this.dark = systemPrefersDark();
            applyClass(this.dark);
        },
    });

    // Call init explicitly — Alpine.store doesn't auto-init like Alpine.data does
    Alpine.store('theme').init();
}
