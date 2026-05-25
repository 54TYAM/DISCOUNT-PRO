@props([
    'coupon',           // App\Models\Discount instance
    'store'  => null,   // optional App\Models\Store
    'showShopCta' => true,
])

@php
    // Map discount type → gradient header + label
    $typeMeta = match ($coupon->type) {
        'percentage'    => ['bg' => 'from-brand-500 to-brand-700',    'label' => 'Percentage off'],
        'fixed'         => ['bg' => 'from-emerald-500 to-emerald-700','label' => 'Fixed amount'],
        'bogo'          => ['bg' => 'from-amber-500 to-amber-700',    'label' => 'BOGO'],
        'free_shipping' => ['bg' => 'from-sky-500 to-sky-700',        'label' => 'Free shipping'],
        'tiered'        => ['bg' => 'from-violet-500 to-violet-700',  'label' => 'Tiered discount'],
        default         => ['bg' => 'from-slate-500 to-slate-700',    'label' => ucfirst($coupon->type)],
    };
@endphp

{{-- ─────────────────────────────────────────────────────────────────────────
     Glassmorphic coupon card — consolidates the three near-identical
     implementations in dashboard.blade.php, coupons/index.blade.php, and
     historically checkout.blade.php. The bold gradient header sits above a
     translucent glass body so the starfield/aurora behind the page shows
     through subtly.
     ─────────────────────────────────────────────────────────────────────── --}}
<div class="card-glass-hover overflow-hidden flex flex-col reveal group" x-data="{ copied: false }">

    {{-- Gradient header --}}
    <div class="bg-gradient-to-br {{ $typeMeta['bg'] }} text-white p-5 relative overflow-hidden">
        <div class="absolute -top-8 -right-8 w-28 h-28 rounded-full bg-white/12 group-hover:scale-110 transition-transform duration-500"></div>
        <div class="absolute -bottom-10 -left-6 w-36 h-36 rounded-full bg-white/5"></div>
        <div class="absolute inset-0 bg-pattern-dots bg-dots-sm opacity-20 pointer-events-none"></div>

        <p class="text-white/70 text-[10px] font-bold uppercase tracking-widest relative">{{ $typeMeta['label'] }}</p>
        <p class="text-3xl font-extrabold mt-2 relative tracking-tight">
            @if ($coupon->type === 'percentage')        {{ (int) $coupon->value }}% off
            @elseif ($coupon->type === 'fixed')         ₹{{ number_format($coupon->value, 0) }} off
            @elseif ($coupon->type === 'bogo')          BOGO
            @elseif ($coupon->type === 'free_shipping') Free ship
            @elseif ($coupon->type === 'tiered')        Up to {{ (int) collect($coupon->tiered_rules ?? [])->max('discount_pct') }}%
            @endif
        </p>
        <p class="text-white/90 text-sm mt-1 line-clamp-1 relative">{{ $coupon->title }}</p>
    </div>

    {{-- Body --}}
    <div class="p-4 flex flex-col flex-1">
        @if ($store)
            <a href="{{ route('shop.store', $store->slug) }}"
               class="text-xs text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-300 transition-colors inline-flex items-center gap-1 mb-2">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ $store->name }}
            </a>
        @endif

        {{-- Code box (dashed dark-aware glass tray) --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="flex-1 bg-white/60 dark:bg-slate-800/60 backdrop-blur border border-dashed border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-center">
                <span class="font-mono text-sm font-bold text-slate-700 dark:text-slate-200 tracking-widest">{{ $coupon->code }}</span>
            </div>
            <button @click="navigator.clipboard.writeText('{{ $coupon->code }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    :class="copied ? 'text-emerald-600 border-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 dark:border-emerald-500/40'
                                   : 'text-slate-500 border-slate-200 hover:text-brand-600 hover:border-brand-300 dark:text-slate-400 dark:border-slate-600 dark:hover:text-brand-300 dark:hover:border-brand-400/40'"
                    class="flex-shrink-0 p-2 rounded-lg border transition-colors"
                    :title="copied ? 'Copied!' : 'Copy code'"
                    :aria-label="copied ? 'Copied to clipboard' : 'Copy coupon code'">
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <svg x-show="copied" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </div>

        {{-- Meta --}}
        <div class="text-xs text-slate-400 dark:text-slate-500 space-y-1 mb-3">
            @if ($coupon->min_order_value > 0)
                <p class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    Min. order ₹{{ number_format($coupon->min_order_value, 0) }}
                </p>
            @endif
            @if ($coupon->end_date)
                <p class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Expires {{ $coupon->end_date->diffForHumans() }}
                </p>
            @endif
            @if (isset($coupon->used_count))
                <p class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ number_format($coupon->used_count) }} used
                </p>
            @endif
        </div>

        @if ($showShopCta && $store)
            <a href="{{ route('shop.store', $store->slug) }}" class="btn-secondary w-full text-xs justify-center mt-auto">
                Shop at {{ \Illuminate\Support\Str::limit($store->name, 18) }} →
            </a>
        @endif
    </div>
</div>
