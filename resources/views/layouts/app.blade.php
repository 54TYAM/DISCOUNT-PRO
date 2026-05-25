<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Discover deals across every store. Browse coupons, save favourites, save more on every order.">
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ ($title ?? 'My Account') . ' — ' . config('app.name') }}</title>

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
</head>
<body class="antialiased bg-stone-50 min-h-screen">

{{-- subtle background wash that adds depth without distracting --}}
<div class="fixed inset-0 -z-20 section-canvas pointer-events-none"></div>

{{-- 3D parallax starfield (canvas) — sits between the wash and content --}}
<x-galaxy-bg />

{{-- ─── Top navigation ─────────────────────────────────────────────── --}}
<nav class="sticky top-0 z-30 bg-white/85 backdrop-blur-md backdrop-saturate-150 border-b border-slate-200/60
            dark:bg-slate-900/85 dark:border-slate-800/60 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">

            {{-- Logo --}}
            <a href="{{ route('shop.index') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-gradient-brand shadow-brand-glow group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 tracking-tight dark:text-white">DiscountPro</span>
            </a>

            {{-- Nav links --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('shop.index') }}"
                   class="{{ request()->routeIs('shop.*') ? 'topnav-link-active' : 'topnav-link' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z M3 7l9 6 9-6"/>
                    </svg>
                    Shop
                </a>
                <a href="{{ route('coupons.index') }}"
                   class="{{ request()->routeIs('coupons.*') ? 'topnav-link-active' : 'topnav-link' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Coupons
                </a>
                <a href="{{ route('wishlist.index') }}"
                   class="{{ request()->routeIs('wishlist.*') ? 'topnav-link-active' : 'topnav-link' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Wishlist
                </a>
                <a href="{{ route('cart.show') }}"
                   class="{{ request()->routeIs('cart.*') || request()->routeIs('checkout.*') ? 'topnav-link-active' : 'topnav-link' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Cart
                    @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                    @if ($cartCount > 0)
                        <span class="ml-0.5 text-[10px] font-bold bg-brand-600 text-white rounded-full min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center animate-pop">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('orders.index') }}"
                   class="{{ request()->routeIs('orders.*') ? 'topnav-link-active' : 'topnav-link' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Orders
                </a>
            </div>

            <div class="flex items-center gap-1">

                {{-- Notifications bell --}}
                <x-notifications-bell />

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-stone-100 transition-colors">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gradient-brand shadow-brand-glow">
                            <span class="text-white text-xs font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                        <span class="text-sm text-slate-700 font-medium hidden lg:block">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        <svg class="w-4 h-4 text-slate-400 hidden sm:block transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 mt-2 w-64 card-elevated py-1 z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('orders.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-stone-50 md:hidden transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            My orders
                        </a>
                        <a href="{{ route('coupons.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-stone-50 md:hidden transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Coupons
                        </a>
                        <a href="{{ route('wishlist.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-stone-50 md:hidden transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Wishlist
                        </a>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-stone-50 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profile settings
                        </a>

                        {{-- Dark-mode toggle --}}
                        <div class="border-t border-slate-100 dark:border-slate-800">
                            <x-theme-toggle variant="menu" />
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors dark:hover:bg-rose-500/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>{{-- end right cluster (bell + dropdown) --}}
        </div>
    </div>
</nav>

{{-- Universal flash toasts (success / error / warning / info) --}}
<x-flash-toasts />

{{-- Page content --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 animate-fade-in">
    {{ $slot }}
</main>

{{-- ─── Footer (dark) ────────────────────────────────────────────────── --}}
<footer class="bg-gradient-sidebar text-slate-300 mt-12 relative overflow-hidden">
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-fuchsia-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-10">
            {{-- Brand block --}}
            <div class="col-span-2 sm:col-span-1">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-gradient-brand shadow-brand-glow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold">DiscountPro</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Discover coupons across every store. Shop smarter, save more.
                </p>
            </div>

            {{-- Shop links --}}
            <div>
                <p class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-3">Shop</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white transition-colors">Browse all</a></li>
                    <li><a href="{{ route('coupons.index') }}" class="hover:text-white transition-colors">Coupons</a></li>
                    <li><a href="{{ route('wishlist.index') }}" class="hover:text-white transition-colors">Wishlist</a></li>
                    <li><a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">My orders</a></li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <p class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-3">Account</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('profile.edit') }}" class="hover:text-white transition-colors">Profile</a></li>
                    <li><a href="{{ route('notifications.index') }}" class="hover:text-white transition-colors">Notifications</a></li>
                    <li><a href="{{ route('cart.show') }}" class="hover:text-white transition-colors">Cart</a></li>
                </ul>
            </div>

            {{-- Trust badges --}}
            <div>
                <p class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-3">Why us</p>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Verified sellers</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-brand-400"></span> Real-time deals</li>
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Secure checkout</li>
                </ul>
            </div>
        </div>

        <div class="divider-gradient mb-6"></div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <p class="text-slate-500">© {{ date('Y') }} DiscountPro. Built as an MVC project.</p>
            <p class="text-slate-500 flex items-center gap-1">Crafted with
                <svg class="w-3 h-3 text-rose-400 inline animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                using Laravel + MongoDB</p>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
