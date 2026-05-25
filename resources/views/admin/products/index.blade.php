<x-admin-layout title="Products">

<x-page-header
    title="Products"
    :subtitle="$counts['all'] . ' products in your store'">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add product
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Status tabs --}}
@php
    $tabs = ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'];
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

{{-- Filter bar --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products…" class="form-input pl-9">
    </div>

    <select name="category" class="form-input w-auto">
        <option value="">All categories</option>
        @foreach (\App\Models\Product::CATEGORIES as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-primary">Apply</button>
    @if (request()->hasAny(['search', 'category']))
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Clear</a>
    @endif
</form>

@if ($products->isEmpty())
    <x-empty-state
        icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
        title="No products yet"
        description="Add your first product to start selling. Customers will be able to find it in the shop."
        ctaText="Add your first product"
        :ctaUrl="route('admin.products.create')"
    />
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 stagger-children">
@foreach ($products as $product)
    @php
        $lowStock = isset($product->stock) && $product->stock !== null && $product->stock > 0 && $product->stock < 5;
        $outOfStock = isset($product->stock) && $product->stock !== null && $product->stock <= 0;
    @endphp
    <div class="card-hover overflow-hidden flex flex-col reveal group relative"
         x-data="{ active: {{ $product->is_active ? 'true' : 'false' }} }">

        {{-- Image with overlay actions --}}
        <a href="{{ route('admin.products.edit', (string) $product->_id) }}" class="block image-zoom aspect-square bg-stone-100 relative">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Stock ribbon --}}
            @if ($outOfStock)
                <span class="absolute top-3 left-3 bg-rose-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md shadow-card">Out of stock</span>
            @elseif ($lowStock)
                <span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md shadow-card animate-pulse">Low: {{ $product->stock }} left</span>
            @endif

            {{-- Hidden/inactive overlay --}}
            <div x-show="!active" x-cloak class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
                <span class="bg-white text-slate-700 text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg shadow-card">Hidden</span>
            </div>
        </a>

        {{-- Toggle pill (above image, top-right) --}}
        <button @click.stop="
                    fetch('{{ route('admin.products.toggle', (string) $product->_id) }}', {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                    }).then(r => r.json()).then(d => active = d.is_active)
                "
                :class="active ? 'bg-emerald-500/95' : 'bg-slate-600/95'"
                class="absolute top-3 right-3 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur shadow-card transition-colors z-10">
            <span x-text="active ? 'Active' : 'Hidden'"></span>
        </button>

        <div class="p-4 flex flex-col flex-1">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $product->category }}</p>
            <h3 class="font-semibold text-slate-800 mt-1 line-clamp-1 group-hover:text-brand-600 transition-colors">{{ $product->name }}</h3>
            <div class="flex items-end justify-between mt-2">
                <p class="text-2xl font-extrabold text-slate-900 tracking-tight">₹{{ number_format($product->price, 0) }}</p>
                <p class="text-xs text-slate-400 mb-1">{{ $product->stock ?? 0 }} in stock</p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                <a href="{{ route('admin.products.edit', (string) $product->_id) }}" class="btn-secondary text-xs px-3 py-1.5 flex-1 justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.products.destroy', (string) $product->_id) }}"
                      onsubmit="return confirm('Delete this product?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="mt-6">{{ $products->links() }}</div>

@endif

</x-admin-layout>
