<x-app-layout title="Dashboard">

    {{-- ── Welcome hero card ─────────────────────────────────────────────── --}}
    @php
        $mySavings = \App\Models\DiscountUsage::where('user_id', (string) auth()->user()->_id)->sum('discount_applied');
        $myUsages  = \App\Models\DiscountUsage::where('user_id', (string) auth()->user()->_id)->count();
        $first     = explode(' ', auth()->user()->name)[0];
    @endphp

    <div class="rounded-2xl bg-gradient-hero text-white p-6 sm:p-8 mb-6 relative overflow-hidden shadow-card-pop">
        <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 animate-float-slow"></div>
        <div class="absolute -bottom-16 -left-12 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="absolute inset-0 bg-pattern-dots bg-dots-md opacity-25 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-end justify-between gap-5">
            <div>
                <p class="text-white/70 text-xs font-semibold uppercase tracking-widest mb-2">Welcome back</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Hey, {{ $first }} <span class="inline-block animate-float-slow">👋</span>
                </h1>
                <p class="text-white/80 mt-2 max-w-xl">Browse the latest deals, copy coupon codes, and save on every order.</p>
                <div class="flex items-center gap-2 mt-4 flex-wrap">
                    <a href="{{ route('shop.index') }}" class="bg-white text-brand-700 hover:bg-stone-50 font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                        Browse shop →
                    </a>
                    <a href="{{ route('coupons.index') }}" class="bg-white/15 hover:bg-white/25 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors backdrop-blur">
                        See all coupons
                    </a>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-6 text-right">
                <div>
                    <p class="text-3xl font-extrabold">₹{{ number_format($mySavings, 0) }}</p>
                    <p class="text-white/60 text-xs uppercase tracking-wider mt-0.5">Saved so far</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold">{{ $myUsages }}</p>
                    <p class="text-white/60 text-xs uppercase tracking-wider mt-0.5">Coupons used</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Quick stats (mobile) ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-3 page-section sm:hidden">
        <div class="stat-card text-center">
            <p class="text-xl font-bold text-emerald-600">₹{{ number_format($mySavings, 0) }}</p>
            <p class="text-[10px] uppercase tracking-wider text-slate-500 mt-0.5">Saved</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xl font-bold text-brand-600">{{ $myUsages }}</p>
            <p class="text-[10px] uppercase tracking-wider text-slate-500 mt-0.5">Used</p>
        </div>
        <div class="stat-card text-center">
            <p class="text-xl font-bold text-amber-600">{{ App\Models\Discount::active()->count() }}</p>
            <p class="text-[10px] uppercase tracking-wider text-slate-500 mt-0.5">Active</p>
        </div>
    </div>

    {{-- ── Available coupons ─────────────────────────────────────────────── --}}
    <div class="flex items-end justify-between mb-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight">Featured Coupons</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Top deals across the platform — copy and use at checkout.</p>
        </div>
        <a href="{{ route('coupons.index') }}" class="text-xs text-brand-600 dark:text-brand-300 hover:text-brand-700 dark:hover:text-brand-200 font-semibold">See all →</a>
    </div>

    @php
        $discounts = App\Models\Discount::active()->limit(6)->get();
        // Pre-load stores so the coupon-card can show "Shop at X" without N+1 queries
        $dashStores = App\Models\Store::whereIn('_id', $discounts->pluck('store_id')->filter()->unique()->values()->all())
                        ->get()->keyBy(fn ($s) => (string) $s->_id);
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children">
    @forelse ($discounts as $discount)
        <x-coupon-card
            :coupon="$discount"
            :store="$dashStores->get((string) $discount->store_id)" />
    @empty
        <div class="col-span-full">
            <x-empty-state
                icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                title="No active coupons right now"
                description="Check back later — new deals drop all the time."
                ctaText="Browse the shop"
                :ctaUrl="route('shop.index')"
            />
        </div>
    @endforelse
    </div>

</x-app-layout>
