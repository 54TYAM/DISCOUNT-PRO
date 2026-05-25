<x-admin-layout title="All Stores">

<x-page-header
    title="All Stores"
    :subtitle="$stores->total() . ' stores on the platform'" />

<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by store name…" class="form-input pl-9">
    </div>
    <select name="category" class="form-input w-auto">
        <option value="">All categories</option>
        @foreach (\App\Models\Store::CATEGORIES as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-primary">Filter</button>
    @if (request()->hasAny(['search', 'category']))
        <a href="{{ route('admin.platform.stores') }}" class="btn-secondary">Clear</a>
    @endif
</form>

@if ($stores->isEmpty())
    <div class="text-center py-20 card">
        <p class="text-slate-400 text-sm">No stores match those filters.</p>
    </div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach ($stores as $store)
    @php
        $owner   = $owners->get((string) $store->owner_id);
        $pCount  = $productCounts->get((string) $store->_id, 0);
        $cCount  = $couponCounts->get((string) $store->_id, 0);
        $bg = match($store->banner_color ?? 'brand') {
            'violet'  => 'from-violet-500 to-violet-700',
            'brand'   => 'from-brand-500 to-brand-700',
            'amber'   => 'from-amber-500 to-amber-600',
            'rose'    => 'from-rose-500 to-rose-600',
            'emerald' => 'from-emerald-500 to-emerald-700',
            'sky'     => 'from-sky-500 to-sky-700',
            default   => 'from-slate-500 to-slate-700',
        };
    @endphp
    <div class="card-hover overflow-hidden">
        <div class="bg-gradient-to-br {{ $bg }} text-white p-4 relative overflow-hidden">
            <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/10"></div>
            <div class="flex items-start justify-between gap-2 relative">
                <div class="min-w-0">
                    <p class="font-bold truncate">{{ $store->name }}</p>
                    <p class="text-white/70 text-xs mt-0.5">{{ $store->category }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $store->is_active ? 'bg-white/20 text-white' : 'bg-rose-100/90 text-rose-700' }}">
                    {{ $store->is_active ? 'Active' : 'Suspended' }}
                </span>
            </div>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 gap-2 mb-3 text-center">
                <div class="bg-stone-50 rounded-lg py-2">
                    <p class="text-lg font-bold text-slate-800">{{ $pCount }}</p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Products</p>
                </div>
                <div class="bg-stone-50 rounded-lg py-2">
                    <p class="text-lg font-bold text-slate-800">{{ $cCount }}</p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Coupons</p>
                </div>
            </div>
            @if ($owner)
                <p class="text-xs text-slate-500 truncate">Owner: {{ $owner->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $owner->email }}</p>
            @endif
            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('shop.store', $store->slug) }}" target="_blank"
                   class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">View public</a>

                <form method="POST" action="{{ route('admin.platform.stores.feature', (string) $store->_id) }}">
                    @csrf @method('PATCH')
                    <button title="{{ $store->is_featured ? 'Remove from featured' : 'Mark as featured' }}"
                            class="p-2 rounded-lg border transition-colors {{ $store->is_featured ? 'border-amber-300 bg-amber-50 text-amber-500' : 'border-slate-200 text-slate-400 hover:text-amber-500 hover:border-amber-300 hover:bg-amber-50' }}">
                        <svg class="w-4 h-4" fill="{{ $store->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.platform.stores.toggle', (string) $store->_id) }}"
                      onsubmit="return confirm('{{ $store->is_active ? 'Suspend' : 'Reactivate' }} this store?')">
                    @csrf @method('PATCH')
                    <button class="text-xs px-3 py-1.5 rounded-lg border {{ $store->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} transition-colors">
                        {{ $store->is_active ? 'Suspend' : 'Reactivate' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="mt-6">{{ $stores->links() }}</div>

@endif

</x-admin-layout>
