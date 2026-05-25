@props([
    'title',
    'subtitle' => null,
    'actions'  => null,   // optional named slot for buttons on the right
])

{{-- ─────────────────────────────────────────────────────────────────────────
     Shared page header — replaces the repeated `<div class="page-header">…`
     pattern across 15+ views. Renders title (h1.page-title), optional
     subtitle (page-subtitle), and an optional right-side actions slot.

     Usage:
        <x-page-header title="My Wishlist" subtitle="5 items saved" />

        <x-page-header title="Coupons" subtitle="Find a code…">
            <x-slot:actions>
                <a class="btn-primary">…</a>
            </x-slot:actions>
        </x-page-header>
     ─────────────────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div class="min-w-0">
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($actions)
        <div class="flex items-center gap-2 flex-wrap flex-shrink-0">{{ $actions }}</div>
    @endif
</div>
