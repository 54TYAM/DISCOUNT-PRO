<x-admin-layout title="{{ $promo->name }}">

@php
    $status = $promo->status;
    $statusClass = match($status) {
        'active'    => 'badge-active',
        'expired'   => 'badge-expired',
        'scheduled' => 'badge-scheduled',
        default     => 'badge-paused',
    };
    $typeColors = [
        'flash_sale' => ['border' => 'border-t-amber-400',   'icon_bg' => 'bg-amber-100',   'icon_text' => 'text-amber-600',   'badge' => 'bg-amber-50 text-amber-700'],
        'seasonal'   => ['border' => 'border-t-emerald-400', 'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-700'],
        'loyalty'    => ['border' => 'border-t-violet-400',  'icon_bg' => 'bg-violet-100',  'icon_text' => 'text-violet-600',  'badge' => 'bg-violet-50 text-violet-700'],
        'referral'   => ['border' => 'border-t-sky-400',     'icon_bg' => 'bg-sky-100',     'icon_text' => 'text-sky-600',     'badge' => 'bg-sky-50 text-sky-700'],
    ];
    $typeIcons = [
        'flash_sale' => 'M13 10V3L4 14h7v7l9-11h-7z',
        'seasonal'   => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        'loyalty'    => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        'referral'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    ];
    $tc = $typeColors[$promo->type] ?? $typeColors['flash_sale'];

    $bannerBg = match($promo->banner_color ?? 'slate') {
        'violet'  => 'from-violet-600 to-violet-800',
        'brand'   => 'from-brand-600 to-brand-800',
        'amber'   => 'from-amber-500 to-amber-700',
        'rose'    => 'from-rose-500 to-rose-700',
        'emerald' => 'from-emerald-600 to-emerald-800',
        'sky'     => 'from-sky-500 to-sky-700',
        default   => 'from-slate-600 to-slate-800',
    };
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between flex-wrap gap-4 mb-6">
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('admin.promotions.index') }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 truncate">{{ $promo->name }}</h1>
                <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                <span class="badge {{ $tc['badge'] }}">
                    {{ \App\Models\Promotion::TYPE_LABELS[$promo->type] ?? ucfirst($promo->type) }}
                </span>
            </div>
            <p class="text-slate-400 text-sm mt-0.5">
                {{ \App\Models\Promotion::SEGMENT_LABELS[$promo->target_segment] ?? 'All Users' }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 flex-shrink-0"
         x-data="{ active: {{ $promo->is_active ? 'true' : 'false' }}, deleting: false }">

        {{-- Toggle --}}
        <button
            @click="
                fetch('{{ route('admin.promotions.toggle', (string) $promo->_id) }}', {
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

        <a href="{{ route('admin.promotions.edit', (string) $promo->_id) }}"
           class="btn-secondary text-sm px-3 py-1.5">Edit</a>

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
                <h3 class="text-center font-semibold text-slate-800 mb-1">Delete this campaign?</h3>
                <p class="text-center text-sm text-slate-500 mb-5">«{{ $promo->name }}» will be permanently removed.</p>
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

{{-- ── Banner preview ───────────────────────────────────────────────────── --}}
<div class="rounded-2xl bg-gradient-to-br {{ $bannerBg }} p-6 mb-6 relative overflow-hidden">
    {{-- Decorative blobs --}}
    <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/5"></div>
    <div class="absolute -bottom-12 -left-6 w-56 h-56 rounded-full bg-white/5"></div>

    <div class="relative flex items-center gap-4">
        <div class="{{ $tc['icon_bg'] }} w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7 {{ $tc['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $typeIcons[$promo->type] ?? '' }}"/>
            </svg>
        </div>
        <div class="text-white">
            <p class="text-xl font-bold">{{ $promo->name }}</p>
            <p class="text-white/70 text-sm mt-0.5">
                {{ \App\Models\Promotion::TYPE_LABELS[$promo->type] ?? '' }}
                &middot;
                {{ \App\Models\Promotion::SEGMENT_LABELS[$promo->target_segment] ?? '' }}
            </p>
            @if ($promo->description)
                <p class="text-white/60 text-xs mt-1.5">{{ $promo->description }}</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- ── Stat cards ────────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 grid grid-cols-2 gap-4 content-start">

        <div class="stat-card">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Impressions</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($promo->view_count) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">total views</p>
        </div>

        <div class="stat-card">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Linked discount</p>
            @if ($discount)
                <p class="text-lg font-bold text-slate-900 font-mono tracking-wide">{{ $discount->code }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $discount->used_count }} uses</p>
            @else
                <p class="text-sm text-slate-400 italic mt-1">None linked</p>
            @endif
        </div>

        <div class="stat-card">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Start</p>
            @if ($promo->start_at)
                <p class="text-sm font-semibold text-slate-800">{{ $promo->start_at->format('M j, Y') }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $promo->start_at->format('H:i') }}</p>
            @else
                <p class="text-sm text-slate-400 italic mt-1">Immediately</p>
            @endif
        </div>

        <div class="stat-card">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">End</p>
            @if ($promo->end_at)
                <p class="text-sm font-semibold text-slate-800">{{ $promo->end_at->format('M j, Y') }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $promo->end_at->diffForHumans() }}</p>
            @else
                <p class="text-sm text-slate-400 italic mt-1">No expiry</p>
            @endif
        </div>
    </div>

    {{-- ── Campaign details panel ─────────────────────────────────────────── --}}
    <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-700">Details</h3>

        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Type</p>
            <span class="badge {{ $tc['badge'] }}">
                {{ \App\Models\Promotion::TYPE_LABELS[$promo->type] ?? ucfirst($promo->type) }}
            </span>
        </div>

        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Audience</p>
            <p class="text-sm font-medium text-slate-700">
                {{ \App\Models\Promotion::SEGMENT_LABELS[$promo->target_segment] ?? ucfirst($promo->target_segment) }}
            </p>
        </div>

        @if ($promo->description)
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Description</p>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $promo->description }}</p>
        </div>
        @endif

        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Banner color</p>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full bg-gradient-to-br {{ $bannerBg }}"></div>
                <p class="text-sm text-slate-600 capitalize">{{ $promo->banner_color ?? 'slate' }}</p>
            </div>
        </div>

        @if ($promo->start_at || $promo->end_at)
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-2">Timeline</p>
            <div class="space-y-2">
                @if ($promo->start_at)
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></div>
                    <span class="text-slate-500">Starts</span>
                    <span class="text-slate-700 font-medium">{{ $promo->start_at->format('M j, Y H:i') }}</span>
                </div>
                @endif
                @if ($promo->end_at)
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 rounded-full {{ $promo->end_at->lt(now()) ? 'bg-slate-300' : 'bg-rose-400' }} flex-shrink-0"></div>
                    <span class="text-slate-500">{{ $promo->end_at->lt(now()) ? 'Ended' : 'Ends' }}</span>
                    <span class="text-slate-700 font-medium">{{ $promo->end_at->format('M j, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Linked discount card ─────────────────────────────────────────────── --}}
@if ($discount)
<div class="card p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Linked Discount Code</h3>
        <a href="{{ route('admin.discounts.show', (string) $discount->_id) }}"
           class="text-xs text-brand-600 hover:text-brand-700 font-medium">View full stats →</a>
    </div>
    <div class="flex items-center gap-4 p-4 bg-stone-50 rounded-xl">
        <div class="font-mono text-lg font-bold text-slate-800 tracking-widest flex-1">
            {{ $discount->code }}
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-400">{{ $discount->used_count }} uses</p>
            @php
                $discStatus = $discount->status;
                $discStatusClass = match($discStatus) {
                    'active'    => 'badge-active',
                    'expired'   => 'badge-expired',
                    'scheduled' => 'badge-scheduled',
                    default     => 'badge-paused',
                };
            @endphp
            <span class="{{ $discStatusClass }} mt-1">{{ ucfirst($discStatus) }}</span>
        </div>
    </div>
</div>
@endif

{{-- ── Sibling campaigns ────────────────────────────────────────────────── --}}
@if ($siblings->isNotEmpty())
<div class="card p-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Other campaigns using this discount</h3>
    <div class="space-y-2">
        @foreach ($siblings as $sibling)
        @php
            $sibStatus = $sibling->status;
            $sibStatusClass = match($sibStatus) {
                'active'    => 'badge-active',
                'expired'   => 'badge-expired',
                'scheduled' => 'badge-scheduled',
                default     => 'badge-paused',
            };
        @endphp
        <a href="{{ route('admin.promotions.show', (string) $sibling->_id) }}"
           class="flex items-center justify-between px-4 py-3 bg-stone-50 hover:bg-stone-100 rounded-xl transition-colors group">
            <div>
                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600 transition-colors">{{ $sibling->name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ \App\Models\Promotion::TYPE_LABELS[$sibling->type] ?? '' }}
                    &middot;
                    {{ \App\Models\Promotion::SEGMENT_LABELS[$sibling->target_segment] ?? '' }}
                </p>
            </div>
            <span class="{{ $sibStatusClass }}">{{ ucfirst($sibStatus) }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

</x-admin-layout>
