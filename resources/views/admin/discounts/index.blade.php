<x-admin-layout title="Discounts">

{{-- ── Page header ────────────────────────────────────────────────────── --}}
<x-page-header
    title="Discount Codes"
    :subtitle="$counts['all'] . ' total discounts in your store'">
    <x-slot:actions>
        <a href="{{ route('admin.discounts.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Discount
        </a>
    </x-slot:actions>
</x-page-header>

{{-- ── Status tabs ─────────────────────────────────────────────────────── --}}
@php
    $tabs = ['all' => 'All', 'active' => 'Active', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'paused' => 'Paused'];
    $currentStatus = request('status', 'all');
@endphp
<div class="status-tabs">
    @foreach ($tabs as $key => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $key === 'all' ? null : $key, 'page' => null]) }}"
       class="{{ $currentStatus === $key ? 'status-tab-active' : 'status-tab' }}">
        {{ $label }}
        <span class="status-tab-count">{{ $counts[$key] }}</span>
    </a>
    @endforeach
</div>

{{-- ── Filter bar ──────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or title…"
               class="form-input pl-9">
    </div>

    <select name="type" class="form-input w-auto">
        <option value="">All types</option>
        @foreach (['percentage' => 'Percentage', 'fixed' => 'Fixed Amount', 'bogo' => 'BOGO', 'free_shipping' => 'Free Shipping', 'tiered' => 'Tiered'] as $v => $l)
            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>

    <select name="sort" class="form-input w-auto">
        <option value="created_at" {{ request('sort', 'created_at') === 'created_at' ? 'selected' : '' }}>Newest first</option>
        <option value="used_count" {{ request('sort') === 'used_count'   ? 'selected' : '' }}>Most used</option>
        <option value="end_date"   {{ request('sort') === 'end_date'     ? 'selected' : '' }}>Expiring soon</option>
    </select>

    <button type="submit" class="btn-primary">Apply</button>

    @if (request()->hasAny(['search', 'type', 'sort']))
        <a href="{{ route('admin.discounts.index') }}" class="btn-secondary">Clear</a>
    @endif
</form>

{{-- ── Discount cards grid ─────────────────────────────────────────────── --}}
@if ($discounts->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No discounts found</p>
        <a href="{{ route('admin.discounts.create') }}" class="btn-primary mt-4 inline-flex">Create your first discount</a>
    </div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 stagger-children">
@foreach ($discounts as $discount)

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
            'percentage'    => 'bg-brand-50 text-brand-700',
            'fixed'         => 'bg-emerald-50 text-emerald-700',
            'bogo'          => 'bg-amber-50 text-amber-700',
            'free_shipping' => 'bg-sky-50 text-sky-700',
            'tiered'        => 'bg-violet-50 text-violet-700',
            default         => 'bg-slate-100 text-slate-600',
        };
        $typeLabel = match($discount->type) {
            'percentage'    => '% Off',
            'fixed'         => 'Fixed',
            'bogo'          => 'BOGO',
            'free_shipping' => 'Ship',
            'tiered'        => 'Tiered',
            default         => ucfirst($discount->type),
        };
    @endphp

    <div class="card p-5 hover:shadow-card-hover hover:-translate-y-0.5 transition-all duration-200 flex flex-col gap-4 reveal"
         x-data="{ active: {{ $discount->is_active ? 'true' : 'false' }}, deleting: false }">

        {{-- Top row: badges + toggle --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                <span class="badge {{ $typeColor }}">{{ $typeLabel }}</span>
            </div>

            {{-- Active toggle --}}
            <button
                @click="
                    fetch('{{ route('admin.discounts.toggle', (string) $discount->_id) }}', {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                    }).then(r => r.json()).then(d => active = d.is_active)
                "
                :class="active ? 'bg-brand-600' : 'bg-slate-200'"
                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full cursor-pointer
                       transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2
                       focus:ring-brand-500 focus:ring-offset-2">
                <span :class="active ? 'translate-x-4' : 'translate-x-0.5'"
                      class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white shadow
                             ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>

        {{-- Discount value + info --}}
        <div>
            <p class="text-3xl font-bold text-slate-900">
                @if ($discount->type === 'percentage')    {{ $discount->value }}%
                @elseif ($discount->type === 'fixed')     ₹{{ number_format($discount->value) }}
                @elseif ($discount->type === 'bogo')      BOGO
                @elseif ($discount->type === 'free_shipping') Free Ship
                @elseif ($discount->type === 'tiered')
                    {{ collect($discount->tiered_rules ?? [])->max('discount_pct') }}% max
                @endif
            </p>
            <p class="text-slate-500 text-sm truncate mt-0.5">{{ $discount->title }}</p>
        </div>

        {{-- Code --}}
        <div class="font-mono text-sm font-semibold text-slate-700 bg-stone-100 rounded-lg px-3 py-2 tracking-wider">
            {{ $discount->code }}
        </div>

        {{-- Usage bar --}}
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>{{ number_format($discount->used_count) }} uses</span>
                @if ($discount->max_uses)
                    <span>of {{ number_format($discount->max_uses) }}</span>
                @else
                    <span>unlimited</span>
                @endif
            </div>
            @if ($discount->max_uses)
                @php $pct = min(100, $discount->usage_percent); @endphp
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="{{ $pct >= 90 ? 'bg-rose-400' : ($pct >= 60 ? 'bg-amber-400' : 'bg-brand-500') }}
                                h-full rounded-full transition-all duration-500"
                         style="width: {{ $pct }}%"></div>
                </div>
            @endif
        </div>

        {{-- Expiry --}}
        @if ($discount->end_date)
        <p class="text-xs text-slate-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $discount->end_date->lt(now()) ? 'Expired' : 'Expires' }} {{ $discount->end_date->diffForHumans() }}
        </p>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
            <a href="{{ route('admin.discounts.show', (string) $discount->_id) }}"
               class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">View</a>
            <a href="{{ route('admin.discounts.edit', (string) $discount->_id) }}"
               class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">Edit</a>

            {{-- Duplicate --}}
            <form method="POST" action="{{ route('admin.discounts.duplicate', (string) $discount->_id) }}">
                @csrf
                <button type="submit" title="Duplicate"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </form>

            {{-- Delete with confirm modal --}}
            <button @click="deleting = true" title="Delete"
                    class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
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
                    <p class="text-center text-sm text-slate-500 mb-5">
                        This cannot be undone. Usage history will remain.
                    </p>
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

@endforeach
</div>

{{-- Pagination --}}
<div class="mt-6">
    {{ $discounts->links() }}
</div>

@endif

</x-admin-layout>
