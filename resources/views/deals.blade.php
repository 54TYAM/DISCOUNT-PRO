<x-app-layout title="Browse Deals">

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<x-page-header
    title="Browse Deals"
    subtitle="Active coupons and promotions you can use right now." />

{{-- ── Active promotions ────────────────────────────────────────────────── --}}
@if ($promotions->isNotEmpty())
<div class="mb-8">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Active Campaigns</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
            $promoBannerColors = [
                'violet'  => 'from-violet-600 to-violet-800',
                'brand'   => 'from-brand-600 to-brand-800',
                'amber'   => 'from-amber-500 to-amber-700',
                'rose'    => 'from-rose-500 to-rose-700',
                'emerald' => 'from-emerald-600 to-emerald-800',
                'sky'     => 'from-sky-500 to-sky-700',
                'slate'   => 'from-slate-600 to-slate-800',
            ];
            $promoTypeIcons = [
                'flash_sale' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'seasonal'   => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
                'loyalty'    => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                'referral'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            ];
            $promoTypeLabels = \App\Models\Promotion::TYPE_LABELS;
        @endphp

        @foreach ($promotions as $promo)
        @php
            $grad = $promoBannerColors[$promo->banner_color ?? 'slate'] ?? 'from-slate-600 to-slate-800';
            $icon = $promoTypeIcons[$promo->type] ?? '';
        @endphp
        <div class="rounded-2xl bg-gradient-to-br {{ $grad }} p-5 relative overflow-hidden">
            <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-8 -left-4 w-36 h-36 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white/50 text-xs font-medium uppercase tracking-wide">
                            {{ $promoTypeLabels[$promo->type] ?? ucfirst($promo->type) }}
                        </p>
                        <p class="text-white font-bold mt-0.5 leading-snug">{{ $promo->name }}</p>
                    </div>
                </div>
                @if ($promo->description)
                    <p class="text-white/60 text-xs mt-2 leading-relaxed">{{ Str::limit($promo->description, 80) }}</p>
                @endif
                @if ($promo->end_at)
                    <p class="text-white/50 text-xs mt-2">Ends {{ $promo->end_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Filter bar ───────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search code or title…" class="form-input pl-9">
    </div>
    <select name="type" class="form-input w-auto">
        <option value="">All types</option>
        @foreach (['percentage' => 'Percentage', 'fixed' => 'Fixed Amount', 'bogo' => 'BOGO', 'free_shipping' => 'Free Shipping', 'tiered' => 'Tiered'] as $v => $l)
            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary">Search</button>
    @if (request()->hasAny(['search', 'type']))
        <a href="{{ route('deals') }}" class="btn-secondary">Clear</a>
    @endif
</form>

{{-- ── Coupon count ─────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">
        Available Coupons
        <span class="ml-2 text-brand-600">{{ $discounts->count() }}</span>
    </h2>
    <a href="{{ route('coupon.show') }}"
       class="text-xs text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
        Try a coupon →
    </a>
</div>

{{-- ── Discount grid ────────────────────────────────────────────────────── --}}
@if ($discounts->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <p class="text-slate-400 text-sm">No coupons match your filter.</p>
        @if (request()->hasAny(['search', 'type']))
            <a href="{{ route('deals') }}" class="btn-secondary mt-4 inline-flex">Clear filters</a>
        @endif
    </div>
@else

@php
$typeColors = [
    'percentage'    => 'text-brand-600',
    'fixed'         => 'text-emerald-600',
    'bogo'          => 'text-amber-600',
    'free_shipping' => 'text-sky-600',
    'tiered'        => 'text-violet-600',
];
$typeBadges = [
    'percentage'    => 'bg-brand-50 text-brand-700',
    'fixed'         => 'bg-emerald-50 text-emerald-700',
    'bogo'          => 'bg-amber-50 text-amber-700',
    'free_shipping' => 'bg-sky-50 text-sky-700',
    'tiered'        => 'bg-violet-50 text-violet-700',
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach ($discounts as $discount)
<div class="card p-5 hover:shadow-card-hover hover:-translate-y-0.5 transition-all duration-200"
     x-data="{ copied: false }">

    {{-- Top row --}}
    <div class="flex items-start justify-between mb-4">
        <span class="badge {{ $typeBadges[$discount->type] ?? 'bg-slate-100 text-slate-600' }}">
            {{ ['percentage' => '% Off', 'fixed' => 'Fixed', 'bogo' => 'BOGO', 'free_shipping' => 'Free Ship', 'tiered' => 'Tiered'][$discount->type] ?? ucfirst($discount->type) }}
        </span>
        <span class="text-xs text-slate-400">
            @if ($discount->end_date) Ends {{ $discount->end_date->diffForHumans() }}
            @else No expiry @endif
        </span>
    </div>

    {{-- Value --}}
    <div class="mb-4">
        <p class="text-3xl font-bold {{ $typeColors[$discount->type] ?? 'text-slate-900' }}">
            @if ($discount->type === 'percentage')    {{ $discount->value }}%
            @elseif ($discount->type === 'fixed')     ₹{{ number_format($discount->value) }} off
            @elseif ($discount->type === 'bogo')      Buy 1 Get 1
            @elseif ($discount->type === 'free_shipping') Free Shipping
            @elseif ($discount->type === 'tiered')
                Up to {{ collect($discount->tiered_rules ?? [])->max('discount_pct') }}% off
            @endif
        </p>
        <p class="text-sm text-slate-500 mt-0.5 truncate">{{ $discount->title }}</p>
    </div>

    {{-- Code copy --}}
    <div class="flex items-center gap-2 mb-4">
        <div class="flex-1 bg-stone-100 border border-dashed border-slate-300 rounded-lg px-3 py-2">
            <span class="font-mono text-sm font-semibold text-slate-700 tracking-wider">{{ $discount->code }}</span>
        </div>
        <button @click="navigator.clipboard.writeText('{{ $discount->code }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                :class="copied ? 'text-emerald-600 border-emerald-300 bg-emerald-50' : 'text-slate-500 border-slate-200 hover:text-brand-600 hover:border-brand-300'"
                class="flex-shrink-0 p-2 rounded-lg border transition-colors" title="Copy code">
            <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <svg x-show="copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
    </div>

    {{-- Meta --}}
    <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
        <span>@if ($discount->min_order_value > 0) Min. ₹{{ number_format($discount->min_order_value) }} @else No minimum @endif</span>
        <span>{{ number_format($discount->used_count) }} used</span>
    </div>

    {{-- Usage bar --}}
    @if ($discount->max_uses)
    @php $usagePct = min(100, $discount->usage_percent); @endphp
    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
        <div class="{{ $usagePct >= 90 ? 'bg-rose-400' : ($usagePct >= 60 ? 'bg-amber-400' : 'bg-brand-400') }}
                    h-full rounded-full transition-all duration-500"
             style="width: {{ $usagePct }}%"></div>
    </div>
    <p class="text-xs text-slate-400 mt-1 text-right">{{ number_format($discount->max_uses - $discount->used_count) }} remaining</p>
    @endif

    {{-- Try it CTA --}}
    <a href="{{ route('coupon.show') }}?code={{ $discount->code }}"
       class="mt-3 btn-secondary text-xs w-full justify-center">
        Try this coupon →
    </a>
</div>
@endforeach
</div>

@endif

</x-app-layout>
