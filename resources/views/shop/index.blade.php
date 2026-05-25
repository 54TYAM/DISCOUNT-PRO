<x-app-layout title="Shop">

{{-- ── Hero banner ──────────────────────────────────────────────────────── --}}
@if ($heroCoupon)
<div class="rounded-2xl bg-gradient-to-br from-brand-600 via-violet-600 to-brand-800 text-white p-6 sm:p-8 mb-6 relative overflow-hidden">
    <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-16 -left-12 w-64 h-64 rounded-full bg-white/5"></div>
    <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="min-w-0">
            <p class="text-white/70 text-xs font-semibold uppercase tracking-wider mb-2">Featured deal</p>
            <h1 class="text-3xl sm:text-4xl font-bold">
                @if ($heroCoupon->type === 'percentage')
                    {{ (int) $heroCoupon->value }}% off at {{ $heroStore?->name ?? 'partner stores' }}
                @else
                    Up to {{ (int) collect($heroCoupon->tiered_rules ?? [])->max('discount_pct') }}% off — tiered rewards
                @endif
            </h1>
            <p class="text-white/80 mt-1.5 line-clamp-1">{{ $heroCoupon->title }}</p>
            <div class="flex items-center gap-2 mt-4">
                <code class="font-mono text-sm font-bold bg-white/15 px-3 py-1.5 rounded-lg tracking-widest">{{ $heroCoupon->code }}</code>
                @if ($heroStore)
                    <a href="{{ route('shop.store', $heroStore->slug) }}" class="text-sm bg-white text-brand-700 hover:bg-stone-50 font-semibold px-4 py-2 rounded-lg transition-colors">
                        Shop now →
                    </a>
                @endif
                <a href="{{ route('coupons.index') }}" class="text-sm text-white/80 hover:text-white px-3 py-2 transition-colors">
                    See all coupons
                </a>
            </div>
        </div>
        <div class="hidden sm:flex w-32 h-32 rounded-2xl bg-white/15 items-center justify-center flex-shrink-0">
            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
    </div>
</div>
@endif

<x-page-header
    title="Discover deals across every store"
    subtitle="Browse products, add to cart, and apply coupon codes at checkout.">
    <x-slot:actions>
        <a href="{{ route('coupons.index') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            See all coupons
        </a>
    </x-slot:actions>
</x-page-header>

{{-- ── Categories rail — now using the new glassmorphic pill component ── --}}
<div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 -mx-1 px-1">
    <a href="{{ route('shop.index') }}" class="{{ ! request('category') ? 'glass-pill-active' : 'glass-pill' }} flex-shrink-0">
        All
    </a>
    @foreach ($categories as $cat)
        <a href="{{ route('shop.index', ['category' => $cat]) }}"
           class="{{ request('category') === $cat ? 'glass-pill-active' : 'glass-pill' }} flex-shrink-0">
            {{ $cat }}
        </a>
    @endforeach
</div>

{{-- Featured stores --}}
@if ($featuredStores->isNotEmpty())
<div class="mb-8">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Featured stores</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach ($featuredStores as $store)
            @php
                $bg = match($store->banner_color ?? 'brand') {
                    'violet'  => 'from-violet-500 to-violet-700',
                    'brand'   => 'from-brand-500 to-brand-700',
                    'amber'   => 'from-amber-500 to-amber-600',
                    'rose'    => 'from-rose-500 to-rose-600',
                    'emerald' => 'from-emerald-500 to-emerald-700',
                    'sky'     => 'from-sky-500 to-sky-700',
                    default   => 'from-slate-500 to-slate-700',
                };
            @endphp
            <a href="{{ route('shop.store', $store->slug) }}"
               class="rounded-xl bg-gradient-to-br {{ $bg }} text-white p-4 hover:shadow-card-hover transition-all relative overflow-hidden">
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/10"></div>
                <p class="font-semibold text-sm relative">{{ $store->name }}</p>
                <p class="text-white/70 text-xs mt-0.5 relative">{{ $store->category }}</p>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Filter bar --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products…" class="form-input pl-9">
    </div>

    <select name="category" class="form-input w-auto">
        <option value="">All categories</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-primary">Search</button>
</form>

@if ($products->isEmpty())
    <x-empty-state
        icon="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
        title="No products found"
        description="Try clearing your filters or browsing a different category."
        ctaText="View all products"
        :ctaUrl="route('shop.index')"
    />
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 stagger-children">
    @foreach ($products as $product)
        <x-product-card
            :product="$product"
            :store="$stores->get((string) $product->store_id)" />
    @endforeach
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endif

</x-app-layout>
