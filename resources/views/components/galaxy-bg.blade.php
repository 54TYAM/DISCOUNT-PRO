{{--
    3D parallax starfield. Drop this once per page (typically in the layout
    immediately after <body>). The canvas is fixed and pointer-events: none,
    so it never interferes with interaction.

    The companion JS lives in resources/js/starfield.js and is bootstrapped
    on DOMContentLoaded by app.js. It pauses when the tab is hidden and
    honors prefers-reduced-motion (renders a single static frame).
--}}
<canvas
    id="galaxy-bg"
    aria-hidden="true"
    class="fixed inset-0 -z-10 pointer-events-none w-screen h-screen"
></canvas>
