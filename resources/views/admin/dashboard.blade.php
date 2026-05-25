<x-admin-layout title="Dashboard">

<x-page-header
    title="Dashboard"
    subtitle="Overview of your discounts, campaigns, and recent activity.">
    @if (! auth()->user()->isAdmin())
        <x-slot:actions>
            <a href="{{ route('admin.products.create') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New product
            </a>
            <a href="{{ route('admin.discounts.create') }}" class="btn-primary text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New coupon
            </a>
        </x-slot:actions>
    @endif
</x-page-header>

{{-- ─── Stat cards ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
    <x-stat-card
        label="Active Discounts"
        :numeric="(int) $stats['active_discounts']"
        :sub="$stats['total_discounts'] . ' total'"
        color="brand"
        icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />

    <x-stat-card
        label="Revenue Saved"
        :numeric="(int) $stats['revenue_saved']"
        prefix="₹"
        :sub="$stats['total_usages'] . ' uses'"
        :trend="$trends['savings_pct'] ?? null"
        color="emerald"
        icon="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />

    <x-stat-card
        label="Active Campaigns"
        :numeric="(int) $stats['total_promotions']"
        sub="promotions live"
        color="violet"
        icon="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />

    <x-stat-card
        :label="auth()->user()->isAdmin() ? 'Registered Users' : 'Coupon uses'"
        :numeric="(int) (auth()->user()->isAdmin() ? $stats['total_users'] : $stats['total_usages'])"
        :sub="$stats['usages_today'] . ' uses today'"
        :trend="$trends['uses_pct'] ?? null"
        color="amber"
        icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
</div>

{{-- ─── Body grid ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 reveal">

    {{-- Top discounts by usage ────────────────────────────────────────── --}}
    <div class="lg:col-span-2 card-glass p-5 relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Top Discounts by Usage</h2>
                @if (Route::has('admin.discounts.index'))
                    <a href="{{ route('admin.discounts.index') }}" class="text-xs text-brand-600 dark:text-brand-300 hover:text-brand-700 dark:hover:text-brand-200 font-medium">View all →</a>
                @endif
            </div>

            <div class="space-y-3">
            @forelse ($topDiscounts as $discount)
                @php
                    $pct = $discount->max_uses > 0
                        ? min(100, round(($discount->used_count / $discount->max_uses) * 100))
                        : min(100, round($discount->used_count / 10));
                    $barColor = $pct >= 90 ? 'bg-rose-400' : ($pct >= 60 ? 'bg-amber-400' : 'bg-brand-500');
                @endphp
                <div class="group">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 backdrop-blur px-2 py-0.5 rounded ring-1 ring-slate-200/50 dark:ring-slate-700">
                                {{ $discount->code }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[140px]">{{ $discount->title }}</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200 tabular-nums">
                            {{ number_format($discount->used_count) }}
                            @if ($discount->max_uses)
                                <span class="text-slate-400 dark:text-slate-500">/ {{ number_format($discount->max_uses) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400 dark:text-slate-500 text-center py-6">No discount usage data yet.</p>
            @endforelse
            </div>
        </div>
    </div>

    {{-- Expiring soon ──────────────────────────────────────────────────── --}}
    <div class="card-glass p-5 relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="relative">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-5">Expiring Within 7 Days</h2>

            <div class="space-y-3">
            @forelse ($expiringDiscounts as $d)
                <div class="flex items-start justify-between gap-2 py-2 border-b border-slate-100/60 dark:border-slate-800 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $d->code }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $d->end_date->diffForHumans() }}</p>
                    </div>
                    <span class="badge-scheduled flex-shrink-0">
                        {{ $d->end_date->format('d M') }}
                    </span>
                </div>
            @empty
                <div class="text-center py-6">
                    <svg class="w-8 h-8 text-slate-200 dark:text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-slate-400 dark:text-slate-500">No discounts expiring soon</p>
                </div>
            @endforelse
            </div>
        </div>
    </div>

</div>

{{-- ─── Recent activity ──────────────────────────────────────────────── --}}
<div class="card-glass p-5 mt-5 reveal relative overflow-hidden" style="transition-delay: 120ms">
    <div class="glass-sheen"></div>
    <div class="relative">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recent Coupon Activity</h2>
            <span class="badge-active">Live</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100/60 dark:border-slate-800">
                        <th class="text-left pb-3 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">Order</th>
                        <th class="text-left pb-3 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">Code</th>
                        <th class="text-left pb-3 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">Customer</th>
                        <th class="text-right pb-3 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">Saved</th>
                        <th class="text-right pb-3 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/40 dark:divide-slate-800/60">
                @forelse ($recentUsages as $u)
                    <tr class="hover:bg-white/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $u['order_id'] }}</td>
                        <td class="py-3">
                            <span class="font-mono text-xs font-semibold bg-white/70 dark:bg-slate-800/70 backdrop-blur text-slate-700 dark:text-slate-200 px-2 py-0.5 rounded ring-1 ring-slate-200/50 dark:ring-slate-700">
                                {{ $u['discount_code'] }}
                            </span>
                        </td>
                        <td class="py-3 text-slate-600 dark:text-slate-300">{{ $u['user_name'] }}</td>
                        <td class="py-3 text-right font-medium text-emerald-600 dark:text-emerald-400">
                            ₹{{ number_format($u['discount_applied'], 2) }}
                        </td>
                        <td class="py-3 text-right text-slate-400 dark:text-slate-500 text-xs">
                            {{ $u['used_at'] ? $u['used_at']->diffForHumans() : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500 text-sm">No recent activity.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-admin-layout>
