<x-page-header
    title="Notifications"
    :subtitle="$notifications->total() . ' total · all marked read'" />

@if ($notifications->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No notifications yet</p>
        <p class="text-slate-400 text-xs mt-1">We'll let you know about important events here.</p>
    </div>
@else

<div class="card overflow-hidden">
    @foreach ($notifications as $n)
        @php
            $colorMap = [
                'brand'   => ['bg' => 'bg-brand-100',   'text' => 'text-brand-600'],
                'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
                'amber'   => ['bg' => 'bg-amber-100',   'text' => 'text-amber-600'],
                'rose'    => ['bg' => 'bg-rose-100',    'text' => 'text-rose-600'],
                'sky'     => ['bg' => 'bg-sky-100',     'text' => 'text-sky-600'],
            ];
            $c = $colorMap[$n->color ?? 'brand'] ?? $colorMap['brand'];
        @endphp
        <a href="{{ $n->link ?? '#' }}"
           class="flex gap-3 px-4 py-3 border-b border-slate-50 hover:bg-stone-50 transition-colors last:border-b-0">
            <div class="w-9 h-9 {{ $c['bg'] }} rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="{{ $n->icon ?? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' }}"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800">{{ $n->title }}</p>
                @if ($n->body)
                    <p class="text-sm text-slate-500 mt-0.5">{{ $n->body }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-1">{{ $n->created_at?->diffForHumans() ?? '—' }}</p>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-6">{{ $notifications->links() }}</div>

@endif
