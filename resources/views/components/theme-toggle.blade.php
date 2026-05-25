@props([
    'variant' => 'menu', // 'menu' (full-width row in a dropdown) or 'button' (icon-only)
])

@if ($variant === 'button')
    {{-- Standalone icon button — used as a floating toggle on guest auth pages.
         Wrapped in an Alpine x-data scope so Alpine actually processes the
         x-show / x-cloak / @click directives (these are only honoured inside
         an x-data block — without one, the button would be inert). --}}
    <button
        x-data
        type="button"
        @click="$store.theme.toggle()"
        :aria-label="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
        class="relative w-10 h-10 rounded-xl flex items-center justify-center
               bg-white/80 backdrop-blur shadow-card border border-slate-200/60
               hover:bg-white hover:shadow-card-hover transition-all
               dark:bg-slate-900/80 dark:border-slate-800/60 dark:hover:bg-slate-800">
        <span class="relative w-5 h-5 block">
            {{-- Sun (shown when dark, click → light) --}}
            <svg x-show="$store.theme.dark" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 rotate-90 scale-50"
                 x-transition:enter-end="opacity-100 rotate-0 scale-100"
                 class="absolute inset-0 w-5 h-5 text-amber-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{-- Moon (shown when light, click → dark) --}}
            <svg x-show="!$store.theme.dark" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -rotate-90 scale-50"
                 x-transition:enter-end="opacity-100 rotate-0 scale-100"
                 class="absolute inset-0 w-5 h-5 text-slate-600"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </span>
    </button>
@else
    {{-- Menu-row variant — drop into any dropdown above the divider --}}
    <button
        type="button"
        @click="$store.theme.toggle()"
        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-600 hover:bg-stone-50 transition-colors
               dark:text-slate-300 dark:hover:bg-slate-800">
        <span class="relative w-4 h-4 inline-block flex-shrink-0">
            <svg x-show="$store.theme.dark" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 rotate-90 scale-50"
                 x-transition:enter-end="opacity-100 rotate-0 scale-100"
                 class="absolute inset-0 w-4 h-4 text-amber-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg x-show="!$store.theme.dark" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -rotate-90 scale-50"
                 x-transition:enter-end="opacity-100 rotate-0 scale-100"
                 class="absolute inset-0 w-4 h-4 text-slate-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </span>
        <span x-text="$store.theme.dark ? 'Light mode' : 'Dark mode'"></span>
        <span class="ml-auto text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500"
              x-text="$store.theme.dark ? 'On' : 'Off'"></span>
    </button>
@endif
