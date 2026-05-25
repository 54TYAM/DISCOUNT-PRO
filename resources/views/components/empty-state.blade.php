@props([
    'icon'        => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'title'       => 'Nothing here yet',
    'description' => null,
    'ctaText'     => null,
    'ctaUrl'      => null,
])

<div class="text-center py-16 sm:py-20 card">
    <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
    </svg>
    <p class="text-slate-500 text-sm font-semibold">{{ $title }}</p>
    @if ($description)
        <p class="text-slate-400 text-xs mt-1 max-w-xs mx-auto">{{ $description }}</p>
    @endif
    @if ($ctaText && $ctaUrl)
        <a href="{{ $ctaUrl }}" class="btn-primary mt-4 inline-flex">{{ $ctaText }}</a>
    @endif
</div>
