{{-- Universal flash toasts. Drop into any layout once.
     Supports session('success'), session('error'), session('info'), session('warning'). --}}

@php
    $toasts = [
        'success' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',         'colors' => ['border-emerald-200', 'bg-emerald-100', 'text-emerald-600', 'text-emerald-700', 'bg-emerald-400']],
        'error'   => ['icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'colors' => ['border-rose-200',    'bg-rose-100',    'text-rose-600',    'text-rose-700',    'bg-rose-400']],
        'warning' => ['icon' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',                     'colors' => ['border-amber-200',   'bg-amber-100',   'text-amber-600',   'text-amber-700',   'bg-amber-400']],
        'info'    => ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',  'colors' => ['border-sky-200',     'bg-sky-100',     'text-sky-600',     'text-sky-700',     'bg-sky-400']],
    ];
@endphp

<div class="fixed bottom-5 right-5 z-50 space-y-2 max-w-sm w-full px-4 sm:px-0">
@foreach ($toasts as $type => $config)
    @if (session($type))
        @php [$borderC, $iconBg, $iconColor, $textColor, $barColor] = $config['colors']; @endphp
        <div x-data="toastTimer(4500)" x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="bg-white dark:bg-slate-900 border {{ $borderC }} dark:border-slate-700 rounded-xl shadow-card-hover overflow-hidden"
             role="alert">
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-8 h-8 {{ $iconBg }} rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $config['icon'] }}"/>
                    </svg>
                </div>
                <span class="text-sm font-medium {{ $textColor }} dark:text-slate-200 flex-1">{{ session($type) }}</span>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors flex-shrink-0" aria-label="Dismiss">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="h-0.5 bg-stone-100 dark:bg-slate-800">
                <div class="h-full {{ $barColor }} toast-progress" style="--toast-duration: 4.5s"></div>
            </div>
        </div>
    @endif
@endforeach
</div>
