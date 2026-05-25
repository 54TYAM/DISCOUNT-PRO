<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ ($title ?? 'Dashboard') . ' — ' . config('app.name') }}</title>

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
<body class="antialiased bg-stone-50" x-data="{ sidebarOpen: false }">

{{-- 3D parallax starfield (canvas). Sits behind everything; the opaque sidebar
     naturally covers it on the left so stars only show in the main content area. --}}
<x-galaxy-bg />

{{-- ─── Mobile sidebar backdrop ─────────────────────────────────────── --}}
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"
     style="display:none"></div>

{{-- ─── Sidebar ─────────────────────────────────────────────────────── --}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-gradient-sidebar flex flex-col
           transform transition-transform duration-200 ease-in-out
           lg:translate-x-0 shadow-2xl">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-gradient-brand shadow-brand-glow flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <span class="text-white font-bold text-base block leading-tight">DiscountPro</span>
            <span class="text-brand-300/80 text-[10px] leading-none uppercase tracking-wider font-semibold">
                {{ auth()->user()->role_label }}
            </span>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">

        @php
            $hasStore = auth()->user()->hasStore() || auth()->user()->isAdmin();
            $navItems = [];

            if ($hasStore) {
                $navItems[] = ['route' => 'admin.dashboard',         'label' => 'Dashboard',  'icon' => 'M3 7a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm10 0a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2V7zM3 17a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2h-4a2 2 0 01-2-2v-2z'];

                // Admin-only: review pending manager applications
                if (auth()->user()->isAdmin()) {
                    $navItems[] = ['route' => 'admin.approvals.index', 'label' => 'Approvals', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'];
                }

                // Managers only: their own store. Admins skip — they don't own a personal store.
                if (! auth()->user()->isAdmin()) {
                    $navItems[] = ['route' => 'admin.store.show',    'label' => 'My Store',   'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'];
                }

                $navItems[] = ['route' => 'admin.orders.index',      'label' => 'Orders',     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'];
                $navItems[] = ['route' => 'admin.products.index',    'label' => 'Products',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'];
                $navItems[] = ['route' => 'admin.discounts.index',   'label' => 'Coupons',    'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'];
                $navItems[] = ['route' => 'admin.promotions.index',  'label' => 'Campaigns',  'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'];
                $navItems[] = ['route' => 'admin.analytics',         'label' => 'Analytics',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'];

                // Admin-only platform pages
                if (auth()->user()->isAdmin()) {
                    $navItems[] = ['_divider' => 'Platform'];
                    $navItems[] = ['route' => 'admin.platform.stores', 'label' => 'All Stores', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'];
                    $navItems[] = ['route' => 'admin.platform.users',  'label' => 'All Users',  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'];
                    $navItems[] = ['route' => 'admin.platform.orders', 'label' => 'All Orders', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'];
                }
            } else {
                $navItems[] = ['route' => 'admin.store.create',      'label' => 'Register store', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'];
            }
        @endphp

        @foreach ($navItems as $item)
            @if (isset($item['_divider']))
                <p class="px-3 pt-5 pb-2 text-[10px] font-semibold text-brand-300/60 uppercase tracking-widest">
                    {{ $item['_divider'] }}
                </p>
            @else
                @php $active = request()->routeIs($item['route']); @endphp
                @if (Route::has($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="{{ $active ? 'sidebar-link-active' : 'sidebar-link' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @else
                    <span class="sidebar-link opacity-40 cursor-not-allowed" title="Coming soon">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span>{{ $item['label'] }}</span>
                        <span class="ml-auto text-[10px] text-slate-400 bg-white/5 px-1.5 py-0.5 rounded">Soon</span>
                    </span>
                @endif
            @endif
        @endforeach

    </nav>

    {{-- User + Logout --}}
    <div class="border-t border-white/5 px-4 py-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-full bg-gradient-brand flex items-center justify-center text-white text-sm font-semibold shadow-brand-glow flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-slate-400 text-xs truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="sidebar-link w-full text-rose-300 hover:text-rose-200 hover:bg-rose-900/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- ─── Main wrapper ────────────────────────────────────────────────── --}}
<div class="lg:pl-64 flex flex-col min-h-screen">

    {{-- Topbar --}}
    <header class="sticky top-0 z-10 bg-white/85 backdrop-blur-md backdrop-saturate-150 border-b border-slate-200/60
                   dark:bg-slate-900/85 dark:border-slate-800/60 transition-colors duration-300">
        <div class="flex items-center justify-between px-4 sm:px-6 h-16">
            {{-- Mobile menu toggle --}}
            <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-stone-100 dark:text-slate-400 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title --}}
            <h1 class="text-slate-900 font-semibold text-base tracking-tight dark:text-slate-100">{{ $title ?? 'Dashboard' }}</h1>

            {{-- Right actions --}}
            <div class="flex items-center gap-2" x-data="{ open: false }">
                {{-- Notifications bell --}}
                <x-notifications-bell />

                {{-- Avatar dropdown --}}
                <div class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-stone-100 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-brand flex items-center justify-center shadow-brand-glow">
                            <span class="text-white text-xs font-semibold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                         class="absolute right-0 mt-2 w-56 card-elevated py-1 z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-slate-800 text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-slate-400 text-xs truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-stone-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile settings
                        </a>

                        {{-- Dark-mode toggle --}}
                        <div class="border-t border-slate-100 dark:border-slate-800 mt-1">
                            <x-theme-toggle variant="menu" />
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 dark:border-slate-800">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors dark:hover:bg-rose-500/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Universal flash toasts (success / error / warning / info) --}}
    <x-flash-toasts />

    {{-- Page content --}}
    <main class="flex-1 p-4 sm:p-6 lg:p-8 animate-fade-in">
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    <footer class="text-center text-slate-400 text-xs py-4 border-t border-slate-100">
        © {{ date('Y') }} DiscountPro — MVC Project
    </footer>
</div>

@stack('scripts')
</body>
</html>
