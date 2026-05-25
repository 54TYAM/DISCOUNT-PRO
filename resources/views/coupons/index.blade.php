<x-app-layout title="Coupons">

<x-page-header title="Coupons" subtitle="Find a code, copy it, then paste it at checkout." />

{{-- ── Customer savings stats — uses the shared glass stat-card component ── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 page-section stagger-children">
    <x-stat-card
        label="Total saved"
        :numeric="(int) $totalSavings"
        prefix="₹"
        color="emerald"
        icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
        sub="across all your orders" />

    <x-stat-card
        label="Coupons redeemed"
        :numeric="$couponsUsed"
        color="brand"
        icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
        :sub="'on ' . $totalOrders . ' order' . ($totalOrders === 1 ? '' : 's')" />

    <x-stat-card
        label="Available now"
        :numeric="$coupons->count()"
        color="amber"
        icon="M13 10V3L4 14h7v7l9-11h-7z"
        sub="active coupons" />
</div>

{{-- ── Filter bar ───────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or title…" class="form-input pl-9">
    </div>

    <select name="type" class="form-input w-auto">
        <option value="">All types</option>
        @foreach (['percentage' => 'Percentage', 'fixed' => 'Fixed Amount', 'bogo' => 'BOGO', 'free_shipping' => 'Free Shipping', 'tiered' => 'Tiered'] as $v => $l)
            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-primary">Filter</button>

    @if (request()->hasAny(['search', 'type']))
        <a href="{{ route('coupons.index') }}" class="btn-secondary">Clear</a>
    @endif
</form>

{{-- ── Available coupons grid ──────────────────────────────────────────── --}}
<h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Available coupons</h2>

@if ($coupons->isEmpty())
    <div class="text-center py-16 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No coupons match your filters</p>
        <a href="{{ route('coupons.index') }}" class="btn-secondary mt-4 inline-flex">Clear filters</a>
    </div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children">
@foreach ($coupons as $coupon)
    <x-coupon-card :coupon="$coupon" :store="$stores->get((string) $coupon->store_id)" />
@endforeach
</div>

@endif

{{-- ── Your usage history ──────────────────────────────────────────────── --}}
@if ($recentUses->isNotEmpty())
<div class="mt-10">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Your recent redemptions</h2>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Code</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Title</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Original</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Saved</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @foreach ($recentUses as $u)
                    @php $d = $usedDiscounts->get((string) $u->discount_id); @endphp
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-4 py-2.5">
                            <span class="font-mono text-xs font-semibold bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                                {{ $d?->code ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $d?->title ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-500">₹{{ number_format($u->original_amount, 0) }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-emerald-600">
                            −₹{{ number_format($u->discount_applied, 0) }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs text-slate-400">
                            {{ $u->used_at?->diffForHumans() ?? '—' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

</x-app-layout>
