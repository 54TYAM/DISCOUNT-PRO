<x-admin-layout title="Analytics">

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<x-page-header title="Analytics" subtitle="Discount usage and revenue insights">
    <x-slot:actions>
        {{-- Period selector (glass segmented control) --}}
        <div class="flex items-center bg-white/60 dark:bg-slate-800/60 backdrop-blur rounded-xl p-1 gap-0.5 ring-1 ring-white/50 dark:ring-white/10">
            @foreach ([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $label)
            <a href="{{ request()->fullUrlWithQuery(['days' => $d]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors
                      {{ $days === $d
                          ? 'bg-white text-slate-800 shadow-sm dark:bg-slate-700 dark:text-slate-100'
                          : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- CSV export --}}
        <a href="{{ route('admin.analytics.export', ['days' => $days]) }}"
           class="btn-secondary text-sm px-3 py-1.5 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
        </a>
    </x-slot:actions>
</x-page-header>

{{-- ── All-time stat cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
    <x-stat-card label="Total revenue saved" :numeric="(int) $totalSaved"      prefix="₹" sub="all time" color="emerald"
                 icon="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
    <x-stat-card label="Total uses"          :numeric="(int) $totalUses"                   sub="all time" color="brand"
                 icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
    <x-stat-card label="Unique customers"    :numeric="(int) $uniqueUsers"                 sub="used a coupon" color="violet"
                 icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
    <x-stat-card label="Avg. order value"    :numeric="(int) $avgOrderValue" prefix="₹" sub="before discount" color="amber"
                 icon="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
</div>

{{-- ── Period highlight bar (with vs-previous deltas) ──────────────────── --}}
@php
    $deltaBadge = function (?int $pct) {
        if ($pct === null) return '';
        $up = $pct >= 0;
        $bg = $up ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700';
        $arrow = $up ? '▲' : '▼';
        return '<span class="text-xs font-semibold ' . $bg . ' px-2 py-0.5 rounded-full ml-2">' . $arrow . ' ' . abs($pct) . '%</span>';
    };
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-brand-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-2xl font-bold text-slate-900">{{ number_format($periodUses) }} {!! $deltaBadge($periodDelta['uses_pct']) !!}</p>
            <p class="text-xs text-slate-400 mt-0.5">uses in last {{ $days }} days <span class="text-slate-300">· vs prior {{ $days }}d</span></p>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-2xl font-bold text-slate-900">₹{{ number_format($periodSaved) }} {!! $deltaBadge($periodDelta['saved_pct']) !!}</p>
            <p class="text-xs text-slate-400 mt-0.5">saved in last {{ $days }} days <span class="text-slate-300">· vs prior {{ $days }}d</span></p>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900">₹{{ number_format($periodAvgSaved) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">avg. saved per use</p>
        </div>
    </div>
</div>

{{-- ── Charts row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5 reveal">

    {{-- Daily usage + savings chart (2/3 width) --}}
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700">Daily Activity — last {{ $days }} days</h3>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-brand-500 inline-block rounded"></span> Uses
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-emerald-500 inline-block rounded"></span> Savings (₹)
                </span>
            </div>
        </div>
        <div class="relative h-56">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    {{-- Type doughnut (1/3 width) --}}
    <div class="card p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Usage by Type</h3>
        @if ($typeBreakdown->isEmpty())
            <div class="flex items-center justify-center h-48 text-slate-300 text-sm">No data</div>
        @else
        <div class="relative h-48 mb-4">
            <canvas id="typeChart"></canvas>
        </div>
        <div class="space-y-2">
            @php
            $typeColors = [
                'percentage'    => ['dot' => 'bg-brand-500',   'label' => '% Off'],
                'fixed'         => ['dot' => 'bg-emerald-500', 'label' => 'Fixed'],
                'bogo'          => ['dot' => 'bg-amber-500',   'label' => 'BOGO'],
                'free_shipping' => ['dot' => 'bg-sky-500',     'label' => 'Free Ship'],
                'tiered'        => ['dot' => 'bg-violet-500',  'label' => 'Tiered'],
                'unknown'       => ['dot' => 'bg-slate-400',   'label' => 'Unknown'],
            ];
            @endphp
            @foreach ($typeBreakdown as $type => $stats)
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $typeColors[$type]['dot'] ?? 'bg-slate-300' }} flex-shrink-0"></span>
                    <span class="text-slate-600">{{ $typeColors[$type]['label'] ?? ucfirst($type) }}</span>
                </span>
                <span class="font-semibold text-slate-800">{{ number_format($stats['uses']) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Top discounts table ──────────────────────────────────────────────── --}}
<div class="card p-5 mb-5 reveal">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Top Discounts by Usage</h3>
        <a href="{{ route('admin.discounts.index', ['sort' => 'used_count']) }}"
           class="text-xs text-brand-600 hover:text-brand-700 font-medium">View all →</a>
    </div>

    @if ($topDiscounts->isEmpty())
        <p class="text-sm text-slate-400 text-center py-8">No discount usage recorded yet.</p>
    @else
    @php
    $maxUses = $topDiscounts->max('used_count') ?: 1;
    $typeBadgeColors = [
        'percentage'    => 'bg-brand-50 text-brand-700',
        'fixed'         => 'bg-emerald-50 text-emerald-700',
        'bogo'          => 'bg-amber-50 text-amber-700',
        'free_shipping' => 'bg-sky-50 text-sky-700',
        'tiered'        => 'bg-violet-50 text-violet-700',
    ];
    $typeBadgeLabels = [
        'percentage' => '% Off', 'fixed' => 'Fixed', 'bogo' => 'BOGO',
        'free_shipping' => 'Free Ship', 'tiered' => 'Tiered',
    ];
    @endphp
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4 w-6">#</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Code</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4 hidden sm:table-cell">Type</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Uses</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4 hidden md:table-cell">Revenue saved</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 hidden lg:table-cell">Usage bar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach ($topDiscounts as $i => $disc)
                @php
                    $pct      = $maxUses > 0 ? min(100, ($disc->used_count / $maxUses) * 100) : 0;
                    $barColor = $i === 0 ? 'bg-brand-500' : ($i === 1 ? 'bg-brand-400' : ($i === 2 ? 'bg-brand-300' : 'bg-slate-200'));
                    $saved    = $topSavingsMap->get((string) $disc->_id, 0);
                @endphp
                <tr class="hover:bg-stone-50 transition-colors group">
                    <td class="py-3 pr-4 text-xs font-bold text-slate-300">{{ $i + 1 }}</td>
                    <td class="py-3 pr-4">
                        <a href="{{ route('admin.discounts.show', (string) $disc->_id) }}"
                           class="font-mono text-sm font-semibold text-slate-700 group-hover:text-brand-600 transition-colors">
                            {{ $disc->code }}
                        </a>
                        <p class="text-xs text-slate-400 truncate max-w-[140px]">{{ $disc->title }}</p>
                    </td>
                    <td class="py-3 pr-4 hidden sm:table-cell">
                        <span class="badge {{ $typeBadgeColors[$disc->type] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $typeBadgeLabels[$disc->type] ?? ucfirst($disc->type) }}
                        </span>
                    </td>
                    <td class="py-3 pr-4 font-semibold text-slate-800">{{ number_format($disc->used_count) }}</td>
                    <td class="py-3 pr-4 text-slate-600 hidden md:table-cell">₹{{ number_format($saved) }}</td>
                    <td class="py-3 hidden lg:table-cell">
                        <div class="w-32 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all duration-700"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── Recent activity ──────────────────────────────────────────────────── --}}
<div class="card p-5 reveal" style="transition-delay: 80ms">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Recent Activity</h3>
        <span class="text-xs text-slate-400">Last 20 uses</span>
    </div>

    @if ($recentUsages->isEmpty())
        <p class="text-sm text-slate-400 text-center py-8">No usage recorded yet.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Code</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4 hidden sm:table-cell">Type</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Original</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Saved</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Final</th>
                    <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach ($recentUsages as $usage)
                @php $disc = $recentDiscounts->get((string) $usage->discount_id); @endphp
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="py-2.5 pr-4">
                        @if ($disc)
                        <a href="{{ route('admin.discounts.show', (string) $disc->_id) }}"
                           class="font-mono text-xs font-semibold text-slate-700 hover:text-brand-600 transition-colors">
                            {{ $disc->code }}
                        </a>
                        @else
                        <span class="font-mono text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="py-2.5 pr-4 hidden sm:table-cell">
                        @if ($disc)
                        <span class="badge {{ $typeBadgeColors[$disc->type] ?? 'bg-slate-100 text-slate-600' }} text-[10px]">
                            {{ $typeBadgeLabels[$disc->type] ?? ucfirst($disc->type) }}
                        </span>
                        @endif
                    </td>
                    <td class="py-2.5 pr-4 text-slate-600">₹{{ number_format($usage->original_amount) }}</td>
                    <td class="py-2.5 pr-4 text-emerald-600 font-medium">−₹{{ number_format($usage->discount_applied) }}</td>
                    <td class="py-2.5 pr-4 text-slate-900 font-semibold">₹{{ number_format($usage->final_amount) }}</td>
                    <td class="py-2.5 text-xs text-slate-400 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($usage->used_at)->format('M j, Y · H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── Chart.js ─────────────────────────────────────────────────────────── --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── Shared defaults ────────────────────────────────────────────────────
    Chart.defaults.font.family   = "'Inter', sans-serif";
    Chart.defaults.font.size     = 11;
    Chart.defaults.color         = '#94a3b8'; // slate-400

    const labels = @json($dailyData->pluck('label'));
    const uses   = @json($dailyData->pluck('uses'));
    const saved  = @json($dailyData->pluck('saved'));

    // ── Daily activity chart ───────────────────────────────────────────────
    const dailyCtx = document.getElementById('dailyChart')?.getContext('2d');
    if (dailyCtx) {
        // Gradient fill for savings
        const gradientSaved = dailyCtx.createLinearGradient(0, 0, 0, 220);
        gradientSaved.addColorStop(0,   'rgba(16, 185, 129, 0.20)'); // emerald-500
        gradientSaved.addColorStop(1,   'rgba(16, 185, 129, 0.00)');

        const gradientUses = dailyCtx.createLinearGradient(0, 0, 0, 220);
        gradientUses.addColorStop(0,   'rgba(124, 58, 237, 0.15)');  // brand/violet
        gradientUses.addColorStop(1,   'rgba(124, 58, 237, 0.00)');

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Uses',
                        data: uses,
                        borderColor:     '#7c3aed',
                        backgroundColor: gradientUses,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#7c3aed',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'yUses',
                    },
                    {
                        label: 'Savings (₹)',
                        data: saved,
                        borderColor:     '#10b981',
                        backgroundColor: gradientSaved,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#10b981',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'ySaved',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor:      '#f1f5f9',
                        bodyColor:       '#cbd5e1',
                        padding:         10,
                        cornerRadius:    8,
                        callbacks: {
                            label: ctx => ctx.datasetIndex === 1
                                ? ` ₹${Number(ctx.raw).toLocaleString('en-IN')}`
                                : ` ${ctx.raw} use${ctx.raw !== 1 ? 's' : ''}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid:  { display: false },
                        ticks: { maxTicksLimit: 7 },
                        border: { display: false },
                    },
                    yUses: {
                        position: 'left',
                        grid:  { color: '#f1f5f9' },
                        border: { display: false, dash: [4, 4] },
                        ticks: { precision: 0 },
                        beginAtZero: true,
                    },
                    ySaved: {
                        position: 'right',
                        grid:  { display: false },
                        border: { display: false },
                        ticks: {
                            callback: v => '₹' + Number(v).toLocaleString('en-IN'),
                        },
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    // ── Type doughnut chart ────────────────────────────────────────────────
    const typeCtx = document.getElementById('typeChart')?.getContext('2d');
    @if (! $typeBreakdown->isEmpty())
    if (typeCtx) {
        // Labels + counts are pre-computed in AnalyticsController as clean arrays
        // to avoid complex PHP expressions inside the JSON serializer.
        const typeLabels = @json($typeChartLabels);
        const typeCounts = @json($typeChartCounts);
        const typeColors = ['#7c3aed','#10b981','#f59e0b','#0ea5e9','#8b5cf6','#94a3b8'];

        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeCounts,
                    backgroundColor: typeColors.slice(0, typeCounts.length),
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor:      '#f1f5f9',
                        bodyColor:       '#cbd5e1',
                        padding:         10,
                        cornerRadius:    8,
                        callbacks: {
                            label: ctx => ` ${ctx.raw} use${ctx.raw !== 1 ? 's' : ''}`,
                        },
                    },
                },
            },
        });
    }
    @endif

})();
</script>
@endpush

</x-admin-layout>
