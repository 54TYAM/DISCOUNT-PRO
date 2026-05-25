<x-admin-layout title="Promotions">

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<x-page-header
    title="Campaigns"
    :subtitle="$counts['all'] . ' promotion campaigns total'">
    <x-slot:actions>
        <a href="{{ route('admin.promotions.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Campaign
        </a>
    </x-slot:actions>
</x-page-header>

{{-- ── Status tabs ──────────────────────────────────────────────────────── --}}
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

{{-- ── Filter bar ───────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaign name…"
               class="form-input pl-9">
    </div>

    <select name="type" class="form-input w-auto">
        <option value="">All types</option>
        @foreach (\App\Models\Promotion::TYPE_LABELS as $v => $l)
            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-primary">Apply</button>

    @if (request()->hasAny(['search', 'type']))
        <a href="{{ route('admin.promotions.index') }}" class="btn-secondary">Clear</a>
    @endif
</form>

{{-- ── Campaign cards ───────────────────────────────────────────────────── --}}
@if ($promotions->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No campaigns found</p>
        <a href="{{ route('admin.promotions.create') }}" class="btn-primary mt-4 inline-flex">Create your first campaign</a>
    </div>
@else

@php
$typeColors = [
    'flash_sale' => ['card' => 'border-t-amber-400',   'badge' => 'bg-amber-50 text-amber-700',   'bar' => 'bg-amber-400'],
    'seasonal'   => ['card' => 'border-t-emerald-400', 'badge' => 'bg-emerald-50 text-emerald-700','bar' => 'bg-emerald-400'],
    'loyalty'    => ['card' => 'border-t-violet-400',  'badge' => 'bg-violet-50 text-violet-700',  'bar' => 'bg-violet-400'],
    'referral'   => ['card' => 'border-t-sky-400',     'badge' => 'bg-sky-50 text-sky-700',        'bar' => 'bg-sky-400'],
];
$typeIcons = [
    'flash_sale' => 'M13 10V3L4 14h7v7l9-11h-7z',
    'seasonal'   => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
    'loyalty'    => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    'referral'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 stagger-children">
@foreach ($promotions as $promo)

@php
    $status = $promo->status;
    $statusClass = match($status) {
        'active'    => 'badge-active',
        'expired'   => 'badge-expired',
        'scheduled' => 'badge-scheduled',
        default     => 'badge-paused',
    };
    $tc  = $typeColors[$promo->type] ?? $typeColors['flash_sale'];
    $discountModel = $promo->discount_id ? ($discounts->get($promo->discount_id) ?? $discounts->get((string)$promo->discount_id)) : null;
@endphp

<div class="card border-t-4 {{ $tc['card'] }} p-5 hover:shadow-card-hover hover:-translate-y-0.5 transition-all duration-200 flex flex-col gap-3 reveal"
     x-data="{ active: {{ $promo->is_active ? 'true' : 'false' }}, deleting: false }">

    {{-- Top row: badges + toggle --}}
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
            <span class="badge {{ $tc['badge'] }}">
                {{ \App\Models\Promotion::TYPE_LABELS[$promo->type] ?? ucfirst($promo->type) }}
            </span>
        </div>

        <button
            @click="
                fetch('{{ route('admin.promotions.toggle', (string) $promo->_id) }}', {
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

    {{-- Campaign name + icon --}}
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 {{ $tc['badge'] }}">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $typeIcons[$promo->type] ?? '' }}"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800 truncate">{{ $promo->name }}</p>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ \App\Models\Promotion::SEGMENT_LABELS[$promo->target_segment] ?? 'All Users' }}
            </p>
        </div>
    </div>

    {{-- Linked discount --}}
    @if ($discountModel)
    <div class="font-mono text-xs font-semibold text-slate-700 bg-stone-100 rounded-lg px-3 py-2 tracking-wider flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        {{ $discountModel->code }}
    </div>
    @else
    <div class="text-xs text-slate-400 italic px-1">No discount linked</div>
    @endif

    {{-- Schedule --}}
    <div class="text-xs text-slate-400 space-y-0.5">
        @if ($promo->start_at)
            <p class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Starts {{ $promo->start_at->format('M j, Y') }}
            </p>
        @endif
        @if ($promo->end_at)
            <p class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $promo->end_at->lt(now()) ? 'Expired' : 'Ends' }} {{ $promo->end_at->diffForHumans() }}
            </p>
        @endif
        @if (!$promo->start_at && !$promo->end_at)
            <p>No schedule set — runs indefinitely</p>
        @endif
    </div>

    {{-- View count --}}
    <div class="flex items-center gap-1.5 text-xs text-slate-400">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        {{ number_format($promo->view_count) }} impression{{ $promo->view_count !== 1 ? 's' : '' }}
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
        <a href="{{ route('admin.promotions.show', (string) $promo->_id) }}"
           class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">View</a>
        <a href="{{ route('admin.promotions.edit', (string) $promo->_id) }}"
           class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">Edit</a>

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
                <h3 class="text-center font-semibold text-slate-800 mb-1">Delete this campaign?</h3>
                <p class="text-center text-sm text-slate-500 mb-5">
                    «{{ $promo->name }}» will be permanently removed.
                </p>
                <div class="flex gap-3">
                    <button @click="deleting = false" class="btn-secondary flex-1">Cancel</button>
                    <form method="POST"
                          action="{{ route('admin.promotions.destroy', (string) $promo->_id) }}"
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

<div class="mt-6">
    {{ $promotions->links() }}
</div>

@endif

</x-admin-layout>
