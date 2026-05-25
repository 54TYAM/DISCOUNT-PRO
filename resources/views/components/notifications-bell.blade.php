{{-- Bell icon with live unread count and a dropdown of recent notifications.
     Polls /notifications/bell every 60 seconds. --}}

<div x-data="notificationsBell()" x-init="load(); setInterval(load, 60000)"
     @click.outside="open = false" class="relative">

    <button @click="toggle()"
            class="relative p-2 rounded-lg text-slate-500 hover:bg-stone-100 transition-colors
                   dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            aria-label="Notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unread > 0" x-cloak
              x-text="unread > 9 ? '9+' : unread"
              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold bg-rose-500 text-white rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900"></span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-slate-100 shadow-card-hover z-50 overflow-hidden
                dark:bg-slate-900 dark:border-slate-800">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between dark:border-slate-800">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifications</p>
            <button x-show="unread > 0" @click="markAllRead()" class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">Mark all read</button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-if="recent.length === 0">
                <div class="text-center py-10">
                    <svg class="w-10 h-10 text-slate-200 dark:text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="text-xs text-slate-400 dark:text-slate-500">You're all caught up!</p>
                </div>
            </template>

            <template x-for="n in recent" :key="n.id">
                <a :href="n.link || '#'"
                   class="flex gap-3 px-4 py-3 border-b border-slate-50 hover:bg-stone-50 transition-colors
                          dark:border-slate-800 dark:hover:bg-slate-800"
                   :class="!n.read && 'bg-brand-50/30 dark:bg-brand-500/10'">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                         :class="{
                             'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300':   n.color === 'brand' || !n.color,
                             'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300': n.color === 'emerald',
                             'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300':   n.color === 'amber',
                             'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300':     n.color === 'rose',
                             'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300':       n.color === 'sky',
                         }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  :d="n.icon || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 line-clamp-1 dark:text-slate-100" x-text="n.title"></p>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-0.5 dark:text-slate-400" x-text="n.body"></p>
                        <p class="text-[11px] text-slate-400 mt-1 dark:text-slate-500" x-text="n.ago"></p>
                    </div>
                    <span x-show="!n.read" class="w-2 h-2 bg-brand-500 rounded-full flex-shrink-0 mt-2"></span>
                </a>
            </template>
        </div>

        <a href="{{ route('notifications.index') }}"
           class="block text-center px-4 py-2.5 text-xs text-brand-600 hover:bg-stone-50 border-t border-slate-100 font-medium
                  dark:text-brand-400 dark:hover:bg-slate-800 dark:border-slate-800">
            See all notifications
        </a>
    </div>
</div>

@once
    @push('scripts')
    <script>
        function notificationsBell() {
            return {
                open: false,
                unread: 0,
                recent: [],
                async load() {
                    try {
                        const r = await fetch('{{ route('notifications.bell') }}', { headers: { 'Accept': 'application/json' } });
                        const d = await r.json();
                        this.unread = d.unread_count;
                        this.recent = d.recent;
                    } catch (_) {}
                },
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.load();
                },
                async markAllRead() {
                    await fetch('{{ route('notifications.markAllRead') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    });
                    this.unread = 0;
                    this.recent = this.recent.map(n => ({ ...n, read: true }));
                },
            };
        }
    </script>
    @endpush
@endonce
