<x-admin-layout title="{{ $discount->code }}">

@php
    $status = $discount->status;
    $statusClass = match($status) {
        'active'    => 'badge-active',
        'expired'   => 'badge-expired',
        'scheduled' => 'badge-scheduled',
        'exhausted' => 'badge-expired',
        default     => 'badge-paused',
    };
    $typeColor = match($discount->type) {
        'percentage'    => 'bg-brand-50 text-brand-700 border-brand-200',
        'fixed'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'bogo'          => 'bg-amber-50 text-amber-700 border-amber-200',
        'free_shipping' => 'bg-sky-50 text-sky-700 border-sky-200',
        'tiered'        => 'bg-violet-50 text-violet-700 border-violet-200',
        default         => 'bg-slate-50 text-slate-600 border-slate-200',
    };
    $maxBar = $last14Days->max('uses') ?: 1;
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between flex-wrap gap-4 mb-6">
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('admin.discounts.index') }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 font-mono tracking-widest truncate">{{ $discount->code }}</h1>
                <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                <span class="badge border {{ $typeColor }}">{{ ucfirst(str_replace('_', ' ', $discount->type)) }}</span>
            </div>
            <p class="text-slate-400 text-sm mt-0.5 truncate">{{ $discount->title }}</p>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex items-center gap-2 flex-shrink-0"
         x-data="{ active: {{ $discount->is_active ? 'true' : 'false' }}, deleting: false }">

        {{-- Toggle active --}}
        <button
            @click="
                fetch('{{ route('admin.discounts.toggle', (string) $discount->_id) }}', {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(d => active = d.is_active)
            "
            class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors"
            :class="active
                ? 'border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100'
                : 'border-slate-200 bg-white text-slate-500 hover:bg-stone-50'">
            <span class="w-2 h-2 rounded-full flex-shrink-0 transition-colors"
                  :class="active ? 'bg-brand-500' : 'bg-slate-300'"></span>
            <span x-text="active ? 'Active' : 'Paused'"></span>
        </button>

        <a href="{{ route('admin.discounts.edit', (string) $discount->_id) }}"
           class="btn-secondary text-sm px-3 py-1.5">Edit</a>

        {{-- Duplicate --}}
        <form method="POST" action="{{ route('admin.discounts.duplicate', (string) $discount->_id) }}">
            @csrf
            <button type="submit" class="btn-secondary text-sm px-3 py-1.5" title="Duplicate">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>
        </form>

        {{-- Delete --}}
        <button @click="deleting = true"
                class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>

        {{-- Delete modal --}}
        <div x-show="deleting" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div @click.outside="deleting = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-6 w-full max-w-sm border border-slate-100 dark:border-slate-800">
                <div class="w-12 h-12 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <h3 class="text-center font-semibold text-slate-800 mb-1">Delete {{ $discount->code }}?</h3>
                <p class="text-center text-sm text-slate-500 mb-5">This action cannot be undone. Usage history will remain.</p>
                <div class="flex gap-3">
                    <button @click="deleting = false" class="btn-secondary flex-1">Cancel</button>
                    <form method="POST"
                          action="{{ route('admin.discounts.destroy', (string) $discount->_id) }}"
                          class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger w-full">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Total uses</p>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($discount->used_count) }}</p>
        @if ($discount->max_uses)
            <p class="text-xs text-slate-400 mt-0.5">of {{ number_format($discount->max_uses) }} max</p>
        @else
            <p class="text-xs text-slate-400 mt-0.5">unlimited</p>
        @endif
    </div>

    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Revenue saved</p>
        <p class="text-2xl font-bold text-slate-900">₹{{ number_format($totalRevenueSaved) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">across all orders</p>
    </div>

    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Unique customers</p>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($uniqueUsers) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">distinct users</p>
    </div>

    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Avg. order value</p>
        <p class="text-2xl font-bold text-slate-900">₹{{ number_format($avgOrderValue) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">before discount</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- ── Mini chart ────────────────────────────────────────────────────── --}}
    <div class="card p-5 lg:col-span-2">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Usage — last 14 days</h3>
        <div class="flex items-end gap-1 h-24">
            @foreach ($last14Days as $day)
            @php $barPct = $maxBar > 0 ? ($day['uses'] / $maxBar * 100) : 0; @endphp
            <div class="flex-1 flex flex-col items-center gap-1 group relative">
                <div class="absolute -top-7 left-1/2 -translate-x-1/2 hidden group-hover:block
                            bg-slate-800 text-white text-xs px-2 py-1 rounded-lg whitespace-nowrap z-10">
                    {{ $day['uses'] }} use{{ $day['uses'] !== 1 ? 's' : '' }}
                    <br><span class="text-slate-400">{{ \Carbon\Carbon::parse($day['date'])->format('M j') }}</span>
                </div>
                <div class="w-full rounded-t-sm transition-all duration-300
                            {{ $day['uses'] > 0 ? 'bg-brand-500' : 'bg-slate-100' }}"
                     style="height: {{ max(4, $barPct) }}%"></div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-slate-400 mt-2">
            <span>{{ \Carbon\Carbon::parse($last14Days->first()['date'])->format('M j') }}</span>
            <span>{{ \Carbon\Carbon::parse($last14Days->last()['date'])->format('M j') }}</span>
        </div>
    </div>

    {{-- ── Discount details ──────────────────────────────────────────────── --}}
    <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700">Details</h3>

        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Value</p>
            <p class="text-lg font-semibold text-slate-800">
                @if ($discount->type === 'percentage')    {{ $discount->value }}%
                @elseif ($discount->type === 'fixed')     ₹{{ number_format($discount->value) }}
                @elseif ($discount->type === 'bogo')      Buy 1 Get 1 Free
                @elseif ($discount->type === 'free_shipping') Free Shipping
                @elseif ($discount->type === 'tiered')
                    Up to {{ collect($discount->tiered_rules ?? [])->max('discount_pct') }}% off
                @endif
            </p>
        </div>

        @if ($discount->min_order_value > 0)
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Min. order</p>
            <p class="text-sm font-medium text-slate-700">₹{{ number_format($discount->min_order_value) }}</p>
        </div>
        @endif

        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Applies to</p>
            <p class="text-sm font-medium text-slate-700">
                {{ $discount->applicable_to === 'all' ? 'All Products' : ucfirst($discount->applicable_to) }}
            </p>
            @if (!empty($discount->target_ids) && $discount->applicable_to !== 'all')
                <p class="text-xs text-slate-400 mt-0.5">{{ count($discount->target_ids) }} item(s) selected</p>
            @endif
        </div>

        @if ($discount->start_date || $discount->end_date)
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Schedule</p>
            @if ($discount->start_date)
                <p class="text-xs text-slate-600">Start: {{ $discount->start_date->format('M j, Y H:i') }}</p>
            @endif
            @if ($discount->end_date)
                <p class="text-xs text-slate-600">End: {{ $discount->end_date->format('M j, Y H:i') }}</p>
            @endif
        </div>
        @endif

        @if ($discount->description)
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Description</p>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $discount->description }}</p>
        </div>
        @endif

        {{-- Tiered rules --}}
        @if ($discount->type === 'tiered' && !empty($discount->tiered_rules))
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-2">Tier rules</p>
            <div class="space-y-1.5">
                @foreach ($discount->tiered_rules as $tier)
                <div class="flex justify-between text-sm bg-stone-50 rounded-lg px-3 py-1.5">
                    <span class="text-slate-500">≥ ₹{{ number_format($tier['min']) }}</span>
                    <span class="font-semibold text-violet-700">{{ $tier['discount_pct'] }}% off</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Usage log ─────────────────────────────────────────────────────────── --}}
<div class="card p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Usage Log</h3>
        <span class="text-xs text-slate-400">{{ $usages->total() }} records</span>
    </div>

    @if ($usages->isEmpty())
        <div class="text-center py-10">
            <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-slate-400 text-sm">No uses recorded yet.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Order</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Original</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Discount</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Final</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($usages as $usage)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs text-slate-500">
                            {{ substr((string) $usage->order_id, -8) }}
                        </td>
                        <td class="py-2.5 pr-4 text-slate-700">₹{{ number_format($usage->original_amount) }}</td>
                        <td class="py-2.5 pr-4 text-rose-600 font-medium">−₹{{ number_format($usage->discount_applied) }}</td>
                        <td class="py-2.5 pr-4 text-slate-900 font-semibold">₹{{ number_format($usage->final_amount) }}</td>
                        <td class="py-2.5 text-slate-400 text-xs">
                            {{ \Carbon\Carbon::parse($usage->used_at)->format('M j, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($usages->hasPages())
        <div class="mt-4 pt-4 border-t border-slate-100">
            {{ $usages->links() }}
        </div>
        @endif
    @endif
</div>

{{-- ── Related Promotions ────────────────────────────────────────────────── --}}
@if ($relatedPromotions->isNotEmpty())
<div class="card p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Linked Promotions</h3>
    <div class="space-y-2">
        @foreach ($relatedPromotions as $promo)
        @php
            $promoStatus = $promo->status;
            $promoStatusClass = match($promoStatus) {
                'active'    => 'badge-active',
                'expired'   => 'badge-expired',
                'scheduled' => 'badge-scheduled',
                default     => 'badge-paused',
            };
        @endphp
        <div class="flex items-center justify-between px-4 py-3 bg-stone-50 rounded-xl">
            <div>
                <p class="text-sm font-medium text-slate-700">{{ $promo->name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $promo->type_label ?? ucfirst(str_replace('_', ' ', $promo->type)) }}
                    &middot; {{ $promo->segment_label ?? ucfirst(str_replace('_', ' ', $promo->target_segment)) }}
                </p>
            </div>
            <span class="{{ $promoStatusClass }}">{{ ucfirst($promoStatus) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

</x-admin-layout>
