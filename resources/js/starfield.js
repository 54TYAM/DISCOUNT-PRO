/**
 * 3D parallax starfield rendered to a single <canvas>.
 *
 * Mount via the <x-galaxy-bg> Blade component, which drops a canvas with
 * id="galaxy-bg" behind the rest of the page (z-index: -10, pointer-events: none).
 *
 * Each star has (x, y, z) with z ∈ (0, 1]. Every frame we decrement z; the star
 * is projected to the screen with perspective. Closer stars (smaller z) appear
 * larger and brighter. When z drops below 0, the star is respawned at the back.
 *
 * Performance:
 *   - Pure 2D canvas, no shaders, GPU-composited via fixed positioning
 *   - Pauses when document.hidden (Page Visibility API)
 *   - Honors prefers-reduced-motion (renders one static frame, no rAF loop)
 *   - Star count halved on viewports < 768px to save battery
 *   - Reads `<html class="dark">` to adapt palette + opacity per theme
 */
export default function initStarfield(canvasId = 'galaxy-bg') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    const ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) return null;

    const dpr = window.devicePixelRatio || 1;
    let width = 0;
    let height = 0;
    let cx = 0;
    let cy = 0;

    // Cursor parallax — gentle offset (max ±8px at edges)
    const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
    const PARALLAX_MAX = 8;

    // ── Theme-aware palettes ───────────────────────────────────────────────
    // Each star stores a paletteIdx (0..3); the actual color is resolved per
    // frame from PALETTES[theme][paletteIdx]. This means a single toggle of
    // dark mode recolors every star live — no re-spawn needed.
    const PALETTES = {
        // Light mode: deeper violet/fuchsia tones so stars actually show on cream
        light: [
            { r: 109, g:  40, b: 217 }, // brand-700 — deep violet (50%)
            { r: 168, g:  85, b: 247 }, // purple-500 — vivid lavender (28%)
            { r: 217, g:  70, b: 239 }, // fuchsia-500 — pink-magenta (17%)
            { r:  14, g: 165, b: 233 }, // sky-500 — cool blue accent (5%)
        ],
        // Dark mode: bright white + tinted accents — classic starfield
        dark: [
            { r: 255, g: 255, b: 255 }, // white (60%)
            { r: 196, g: 181, b: 253 }, // brand-300 (25%)
            { r: 240, g: 171, b: 252 }, // fuchsia-300 (10%)
            { r: 125, g: 211, b: 252 }, // sky-300 (5%)
        ],
    };
    // Weights are shared across both palettes (same distribution)
    const WEIGHTS = [60, 25, 10, 5]; // dark-mode tuning; works for light too
    const TOTAL_WEIGHT = WEIGHTS.reduce((s, w) => s + w, 0);
    const pickPaletteIndex = () => {
        let r = Math.random() * TOTAL_WEIGHT;
        for (let i = 0; i < WEIGHTS.length; i++) {
            if (r < WEIGHTS[i]) return i;
            r -= WEIGHTS[i];
        }
        return 0;
    };

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMobile = () => window.innerWidth < 768;
    const isDark = () => document.documentElement.classList.contains('dark');

    let stars = [];
    let speed = 0.0025;

    // ── Eased values that interpolate smoothly during a theme toggle ──────
    // Light mode now actually shows the stars (0.55) rather than subliminally (0.18).
    let opacityScale = isDark() ? 0.95 : 0.55;
    const targetOpacity = () => (isDark() ? 0.95 : 0.55);

    /** Re-init stars whenever the canvas resizes. */
    const setupStars = () => {
        const count = isMobile() ? 140 : 240;
        stars = new Array(count).fill(null).map(() => makeStar(true));
    };

    /** Create a new star. initial=true randomises z everywhere; false puts it at the back. */
    const makeStar = (initial = false) => ({
        x: Math.random() * 2 - 1,
        y: Math.random() * 2 - 1,
        z: initial ? Math.random() : 1,
        paletteIdx: pickPaletteIndex(),
    });

    /** Project (x, y, z) → screen coords with perspective. */
    const project = (s) => {
        const k = 0.5 / s.z;
        return {
            sx: cx + s.x * width  * k + (mouse.x * PARALLAX_MAX * (1 - s.z)),
            sy: cy + s.y * height * k + (mouse.y * PARALLAX_MAX * (1 - s.z)),
        };
    };

    const resize = () => {
        width  = window.innerWidth;
        height = window.innerHeight;
        cx = width  / 2;
        cy = height / 2;
        canvas.width  = width  * dpr;
        canvas.height = height * dpr;
        canvas.style.width  = width  + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        setupStars();
    };

    /** Single frame render. Called each rAF tick (or once if reduced-motion). */
    const render = () => {
        ctx.clearRect(0, 0, width, height);

        // Smoothly interpolate opacity toward the theme's target
        opacityScale += (targetOpacity() - opacityScale) * 0.05;

        // Ease mouse position toward target for buttery parallax
        mouse.x += (mouse.tx - mouse.x) * 0.05;
        mouse.y += (mouse.ty - mouse.y) * 0.05;

        // Pick current palette once per frame (cheap)
        const palette = isDark() ? PALETTES.dark : PALETTES.light;

        for (let i = 0; i < stars.length; i++) {
            const s = stars[i];
            const depth = 1 - s.z;
            const size  = depth * 2.0 + 0.4;                 // slightly bigger
            const alpha = (depth * 0.85 + 0.18) * opacityScale;
            const { sx, sy } = project(s);

            if (sx < -20 || sx > width + 20 || sy < -20 || sy > height + 20) {
                stars[i] = makeStar(false);
                continue;
            }

            const { r, g, b } = palette[s.paletteIdx];
            ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
            ctx.beginPath();
            ctx.arc(sx, sy, size, 0, Math.PI * 2);
            ctx.fill();
        }

        // Advance — skip if reduced motion
        if (!reducedMotion) {
            for (let i = 0; i < stars.length; i++) {
                stars[i].z -= speed;
                if (stars[i].z <= 0.01) stars[i] = makeStar(false);
            }
        }
    };

    let rafId = null;
    const loop = () => {
        render();
        rafId = requestAnimationFrame(loop);
    };

    const start = () => {
        if (rafId !== null) return;
        loop();
    };
    const stop = () => {
        if (rafId === null) return;
        cancelAnimationFrame(rafId);
        rafId = null;
    };

    // ── Wiring ─────────────────────────────────────────────────────────────
    resize();

    if (reducedMotion) {
        render();
    } else {
        start();
    }

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stop() : start();
    });

    window.addEventListener('mousemove', (e) => {
        mouse.tx = (e.clientX / width  - 0.5) * 2;
        mouse.ty = (e.clientY / height - 0.5) * 2;
    });

    let resizePending = false;
    window.addEventListener('resize', () => {
        if (resizePending) return;
        resizePending = true;
        requestAnimationFrame(() => {
            resize();
            resizePending = false;
        });
    });

    return { start, stop };
}
