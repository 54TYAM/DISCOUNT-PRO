<x-app-layout title="Your Cart">

<x-page-header title="Your Cart" subtitle="Review your items before checkout.">
    @if (count($lines) > 0)
        <x-slot:actions>
            <form method="POST" action="{{ route('cart.clear') }}">
                @csrf
                <button class="text-sm text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-medium px-3 py-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">Clear cart</button>
            </form>
        </x-slot:actions>
    @endif
</x-page-header>

@if (empty($lines))
    <x-empty-state
        icon="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
        title="Your cart is empty"
        description="Add products to your cart and they'll show up here."
        ctaText="Start shopping"
        :ctaUrl="route('shop.index')" />
@else

@php
    // Group lines by store for a cleaner multi-store view
    $byStore = collect($lines)->groupBy(fn ($l) => (string) $l['product']->store_id);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Items grouped by store --}}
    <div class="lg:col-span-2 space-y-5">
    @foreach ($byStore as $storeId => $storeLines)
        @php
            $store = $storeMap->get((string) $storeId);
            $storeSub = collect($storeLines)->sum('line_total');
        @endphp
        <div class="card-glass overflow-hidden relative">
            <div class="glass-sheen"></div>
            {{-- Store header --}}
            <div class="relative px-4 py-3 bg-gradient-to-r from-brand-50/80 via-white/40 to-white/20 border-b border-white/40 flex items-center justify-between gap-3
                        dark:from-brand-500/15 dark:via-slate-800/30 dark:to-slate-900/30 dark:border-white/10">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 shadow-card flex items-center justify-center flex-shrink-0 ring-1 ring-slate-100 dark:ring-slate-700">
                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        @if ($store)
                            <a href="{{ route('shop.store', $store->slug) }}" class="text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-brand-600 dark:hover:text-brand-300 transition-colors truncate block">
                                {{ $store->name }}
                            </a>
                        @else
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">Store</p>
                        @endif
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ count($storeLines) }} {{ Str::plural('item', count($storeLines)) }}</p>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-900 dark:text-white flex-shrink-0">₹{{ number_format($storeSub, 0) }}</p>
            </div>

            <div class="relative divide-y divide-white/30 dark:divide-white/5">
            @foreach ($storeLines as $line)
                <div class="p-4 flex items-center gap-4">
                    <div class="w-16 h-16 bg-stone-100 dark:bg-slate-800 rounded-lg overflow-hidden flex-shrink-0">
                        @if ($line['product']->image_url)
                            <img src="{{ $line['product']->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('shop.product', (string) $line['product']->_id) }}"
                           class="font-medium text-slate-800 dark:text-slate-100 line-clamp-1 hover:text-brand-600 dark:hover:text-brand-300 text-sm">
                            {{ $line['product']->name }}
                        </a>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">₹{{ number_format($line['product']->price, 0) }}</p>
                    </div>

                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="product_id" value="{{ (string) $line['product']->_id }}">
                        <input type="number" name="qty" value="{{ $line['qty'] }}" min="0" max="99"
                               class="form-input w-16 text-center"
                               onchange="this.form.submit()">
                    </form>

                    <div class="text-right min-w-[70px]">
                        <p class="font-bold text-slate-900 dark:text-white text-sm">₹{{ number_format($line['line_total'], 0) }}</p>
                    </div>

                    <form method="POST" action="{{ route('cart.destroy', (string) $line['product']->_id) }}">
                        @csrf @method('DELETE')
                        <button class="p-2 text-slate-400 dark:text-slate-500 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors" title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @endforeach
            </div>
        </div>
    @endforeach
    </div>

    {{-- Summary (glass) --}}
    <div class="card-glass p-6 h-fit sticky top-24 relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="relative">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 pb-3 border-b border-white/40 dark:border-white/10">Order summary</h2>

            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">₹{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Shipping</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">Free</span>
                </div>
            </div>

            <div class="border-t border-white/40 dark:border-white/10 pt-4 mb-4">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-slate-800 dark:text-slate-100">Estimated total</span>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">₹{{ number_format($subtotal, 2) }}</span>
                </div>
            </div>

            {{-- Coupon hint --}}
            <a href="{{ route('coupons.index') }}"
               class="flex items-center gap-2 p-3 mb-3 bg-amber-50/70 dark:bg-amber-500/10 backdrop-blur border border-amber-200/70 dark:border-amber-500/30 rounded-lg text-amber-800 dark:text-amber-200 text-xs hover:bg-amber-100/80 dark:hover:bg-amber-500/15 transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="flex-1">Have a coupon? Browse <span class="font-semibold">all available codes</span> to save more.</span>
            </a>

            <a href="{{ route('checkout.show') }}" class="btn-primary w-full justify-center">
                Proceed to checkout →
            </a>
            <a href="{{ route('shop.index') }}" class="btn-secondary w-full justify-center mt-2">
                Continue shopping
            </a>
        </div>
    </div>
</div>

@endif

</x-app-layout>
