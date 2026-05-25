@props([
    'label',                    // small label under the value
    'value',                    // pre-formatted display string (e.g. "₹12,400")
    'numeric'    => null,       // raw integer for animated count-up. If set, overrides static value display.
    'prefix'     => '',         // count-up prefix (e.g. '₹')
    'suffix'     => '',         // count-up suffix (e.g. '%')
    'icon'       => null,       // SVG path d-attribute
    'color'      => 'brand',    // brand|emerald|amber|rose|sky|violet
    'sub'        => null,       // small caption beneath the label
    'trend'      => null,       // signed percentage (e.g. 12, -3.4). Renders a chip.
    'trendLabel' => 'vs last 7 days',
])

@php
    // Map color tokens → palette classes. Centralising this means every
    // stat card across the project shares one consistent palette mapping.
    $palette = match ($color) {
        'emerald' => ['text' => 'text-emerald-600 dark:text-emerald-300', 'bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'ring' => 'ring-emerald-100/60 dark:ring-emerald-500/20', 'glow' => 'rgba(16,185,129,0.10)'],
        'amber'   => ['text' => 'text-amber-600 dark:text-amber-300',     'bg' => 'bg-amber-100 dark:bg-amber-500/20',     'ring' => 'ring-amber-100/60 dark:ring-amber-500/20',     'glow' => 'rgba(245,158,11,0.10)'],
        'rose'    => ['text' => 'text-rose-600 dark:text-rose-300',       'bg' => 'bg-rose-100 dark:bg-rose-500/20',       'ring' => 'ring-rose-100/60 dark:ring-rose-500/20',       'glow' => 'rgba(244,63,94,0.10)'],
        'sky'     => ['text' => 'text-sky-600 dark:text-sky-300',         'bg' => 'bg-sky-100 dark:bg-sky-500/20',         'ring' => 'ring-sky-100/60 dark:ring-sky-500/20',         'glow' => 'rgba(14,165,233,0.10)'],
        'violet'  => ['text' => 'text-violet-600 dark:text-violet-300',   'bg' => 'bg-violet-100 dark:bg-violet-500/20',   'ring' => 'ring-violet-100/60 dark:ring-violet-500/20',   'glow' => 'rgba(139,92,246,0.10)'],
        default   => ['text' => 'text-brand-600 dark:text-brand-300',     'bg' => 'bg-brand-100 dark:bg-brand-500/20',     'ring' => 'ring-brand-100/60 dark:ring-brand-500/20',     'glow' => 'rgba(124,58,237,0.10)'],
    };

    $hasTrend = $trend !== null;
    $trendUp  = $hasTrend && $trend >= 0;
@endphp

{{-- Glassmorphic stat card with subtle radial sheen on hover --}}
<div {{ $attributes->merge(['class' => 'card-glass-hover group p-5 relative overflow-hidden reveal']) }}>

    {{-- Hover sheen — radial gradient in the brand colour --}}
    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
         style="background: radial-gradient(at top right, {{ $palette['glow'] }}, transparent 65%);"></div>

    {{-- Top edge highlight for glass depth --}}
    <div class="glass-sheen"></div>

    <div class="relative">
        <div class="flex items-start justify-between mb-4">
            @if ($icon)
                <div class="{{ $palette['bg'] }} w-11 h-11 rounded-xl flex items-center justify-center ring-4 {{ $palette['ring'] }} group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 {{ $palette['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
                    </svg>
                </div>
            @endif

            @if ($hasTrend)
                @php
                    $trendBg = $trendUp
                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30'
                        : 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30';
                    $trendIcon = $trendUp
                        ? 'M5 10l7-7m0 0l7 7m-7-7v18'
                        : 'M19 14l-7 7m0 0l-7-7m7 7V3';
                @endphp
                <span class="text-xs font-bold {{ $trendBg }} ring-1 px-2 py-1 rounded-full flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $trendIcon }}"/>
                    </svg>
                    {{ abs($trend) }}%
                </span>
            @endif
        </div>

        @if ($numeric !== null)
            <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mb-1 counter-value tracking-tight"
               x-data="countUp({{ (int) $numeric }}, '{{ $prefix }}', '{{ $suffix }}')"
               x-init="init()"
               x-text="display">{{ $prefix }}{{ number_format((int) $numeric) }}{{ $suffix }}</p>
        @else
            <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mb-1 tracking-tight">{{ $value }}</p>
        @endif

        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</p>

        @if ($sub)
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ $sub }}
                @if ($hasTrend)
                    <span class="text-slate-300 dark:text-slate-600">· {{ $trendLabel }}</span>
                @endif
            </p>
        @endif
    </div>
</div>
