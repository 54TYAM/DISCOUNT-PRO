<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ $title ?? config('app.name', 'DiscountPro') }}</title>

    {{-- Dark-mode anti-flicker: must run BEFORE Vite assets so .dark is on <html> before paint --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored ? stored === 'dark'
                                   : window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (dark) document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            } catch (_) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head><body class="antialiased bg-stone-50">

{{-- 3D parallax starfield (canvas). Shows mostly through the aurora panel
     on the right since the left brand panel is fully opaque navy. --}}
<x-galaxy-bg />

{{-- Floating theme toggle (top-right of viewport on auth pages) --}}
<div class="fixed top-4 right-4 z-50">
    <x-theme-toggle variant="button" />
</div>

<div class="min-h-screen flex">

    {{-- ── Left brand panel (desktop only) ─────────────────────────────── --}}
    {{--
        Reads $store.auth.role to swap the hero / features per role.
        `heroData` is a getter that returns the right copy for the active role;
        the form on the right writes to the same store when a tab is clicked.
    --}}
    <div class="hidden lg:flex lg:w-[45%] bg-gradient-sidebar flex-col justify-between p-12 relative overflow-hidden"
         x-data="{
             get heroData() {
                 const role = this.$store.auth.role;
                 if (role === 'manager') {
                     return {
                         tagline: 'For store managers',
                         title:   'Sell smarter,',
                         titleAccent: 'grow faster.',
                         body:    'Run your store, launch coupon codes and campaigns, and watch real-time analytics — all from one dashboard.',
                         accent:  'from-emerald-300 via-teal-300 to-sky-300',
                         features: [
                             { icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', label: 'Real-time analytics for your store' },
                             { icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',  label: 'Launch coupon codes in seconds' },
                             { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',                    label: 'Fulfill orders with one click' },
                         ],
                     };
                 }
                 if (role === 'admin') {
                     return {
                         tagline: 'For super admins',
                         title:   'Total platform',
                         titleAccent: 'oversight.',
                         body:    'Review applications, manage every store and user, and keep the platform healthy with one secure key.',
                         accent:  'from-rose-300 via-fuchsia-300 to-pink-300',
                         features: [
                             { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', label: 'Approve new store-manager requests' },
                             { icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',                                                                            label: 'Oversee every store on the platform' },
                             { icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',                                                                                                                  label: 'Secret-key-protected sign-in' },
                         ],
                     };
                 }
                 // default: customer
                 return {
                     tagline: 'For shoppers',
                     title:   'Shop. Save.',
                     titleAccent: 'Smile.',
                     body:    'Browse coupons across every store, save your favourites, and pay less on every order.',
                     accent:  'from-brand-300 via-fuchsia-300 to-pink-300',
                     features: [
                         { icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',          label: 'Browse 100+ active coupon codes' },
                         { icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',           label: 'Save products to your wishlist' },
                         { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',         label: 'Track orders + savings in one place' },
                     ],
                 };
             }
         }">

        {{-- Decorative aurora --}}
        <div class="absolute -top-40 -left-40 w-[28rem] h-[28rem] bg-brand-600/30 rounded-full blur-3xl pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-fuchsia-600/20 rounded-full blur-3xl pointer-events-none animate-float-slow" style="animation-delay: -3s"></div>
        <div class="absolute top-1/2 left-1/4 w-72 h-72 bg-violet-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Dot pattern overlay --}}
        <div class="absolute inset-0 bg-pattern-dots bg-dots-md opacity-30 pointer-events-none"></div>

        {{-- Logo + role-aware hero --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-14">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-brand shadow-brand-glow">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-xl tracking-tight">DiscountPro</span>
            </div>

            {{-- Role tagline pill — animates in when role changes --}}
            <p class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/15 text-xs font-semibold text-brand-200 uppercase tracking-wider mb-4"
               x-text="heroData.tagline"
               :key="$store.auth.role + 'tag'"
               x-transition.opacity.duration.300ms></p>

            <h1 class="text-5xl font-extrabold text-white leading-[1.1] mb-5 tracking-tight min-h-[6rem]"
                :key="$store.auth.role + 'h1'"
                x-transition.opacity.duration.300ms>
                <span x-text="heroData.title"></span><br>
                <span class="bg-clip-text text-transparent bg-gradient-to-r" :class="heroData.accent" x-text="heroData.titleAccent"></span>
            </h1>
            <p class="text-slate-300 text-base leading-relaxed max-w-md"
               x-text="heroData.body"
               :key="$store.auth.role + 'body'"
               x-transition.opacity.duration.300ms></p>
        </div>

        {{-- Role-aware feature list --}}
        <div class="relative z-10 space-y-4">
            <template x-for="(f, i) in heroData.features" :key="$store.auth.role + i">
                <div class="flex items-center gap-3" x-transition.opacity.duration.300ms>
                    <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur ring-1 ring-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="f.icon"/>
                        </svg>
                    </div>
                    <span class="text-slate-300 text-sm" x-text="f.label"></span>
                </div>
            </template>

            <p class="text-slate-500 text-xs pt-4">© {{ date('Y') }} DiscountPro. MVC Project.</p>
        </div>
    </div>

    {{-- ── Right form panel ────────────────────────────────────────────── --}}
    <div class="relative w-full lg:w-[55%] flex items-center justify-center p-6 sm:p-10 bg-aurora overflow-hidden">

        {{-- Soft floating orbs for depth --}}
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-400/20 rounded-full blur-3xl pointer-events-none lg:hidden"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-fuchsia-400/20 rounded-full blur-3xl pointer-events-none lg:hidden"></div>

        <div class="w-full max-w-md relative z-10 animate-slide-up">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-brand shadow-brand-glow">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span class="text-slate-800 font-bold text-xl tracking-tight">DiscountPro</span>
            </div>

            {{-- Glass form card --}}
            <div class="card-glass p-6 sm:p-8 rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
</body>
</html>
