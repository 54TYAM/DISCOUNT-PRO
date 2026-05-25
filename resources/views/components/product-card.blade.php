@props([
    'product',
    'store'        => null,
    'showWishlist' => false,    // render a remove-from-wishlist button on the image
    'inWishlist'   => false,    // pre-filled state (for product detail page contexts)
])

{{-- ─────────────────────────────────────────────────────────────────────────
     Glassmorphic product card — consolidates the duplicated markup in
     shop/index.blade.php, wishlist/index.blade.php, and shop/product.blade.php
     (similar products grid).

     The image area stays opaque (image-zoom + stone bg) for proper image
     rendering; the lower content slab gets the translucent glass treatment
     so the page background shows through subtly.
     ─────────────────────────────────────────────────────────────────────── --}}
<div class="card-glass-hover overflow-hidden flex flex-col reveal group">

    {{-- Image + overlays --}}
    <a href="{{ route('shop.product', (string) $product->_id) }}" class="block">
        <div class="image-zoom aspect-square bg-stone-100 dark:bg-slate-800 relative">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     class="w-full h-full object-cover" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200 dark:from-slate-800 dark:to-slate-900">
                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Category badge --}}
            <span class="absolute top-3 left-3 bg-white/95 dark:bg-slate-900/85 backdrop-blur text-slate-700 dark:text-slate-200 text-[10px] font-semibold uppercase tracking-wider px-2 py-1 rounded-md ring-1 ring-white/40 dark:ring-white/10">
                {{ $product->category }}
            </span>

            {{-- Stock badge --}}
            @if (isset($product->stock) && $product->stock !== null && $product->stock < 5 && $product->stock > 0)
                <span class="absolute top-3 right-3 bg-amber-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md shadow-card">
                    Only {{ $product->stock }} left
                </span>
            @elseif (isset($product->stock) && $product->stock !== null && $product->stock <= 0)
                <span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md shadow-card">
                    Out of stock
                </span>
            @endif

            {{-- Wishlist remove (only when explicitly shown — i.e. on /wishlist) --}}
            @if ($showWishlist)
                <form method="POST" action="{{ route('wishlist.destroy', (string) $product->_id) }}" class="absolute top-3 right-3">
                    @csrf @method('DELETE')
                    <button class="w-9 h-9 bg-white/95 dark:bg-slate-900/85 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-rose-500 rounded-full flex items-center justify-center backdrop-blur transition-all shadow-card hover:scale-110"
                            title="Remove from wishlist" aria-label="Remove from wishlist">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </a>

    {{-- Body --}}
    <div class="p-4 flex flex-col flex-1">
        @if ($store)
            <a href="{{ route('shop.store', $store->slug) }}"
               class="text-xs text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-300 transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/>
                </svg>
                {{ $store->name }}
            </a>
        @endif

        <a href="{{ route('shop.product', (string) $product->_id) }}"
           class="font-semibold text-slate-800 dark:text-slate-100 mt-1 line-clamp-2 hover:text-brand-600 dark:hover:text-brand-300 transition-colors leading-snug">
            {{ $product->name }}
        </a>

        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-2 tracking-tight">
            ₹{{ number_format($product->price, 0) }}
        </p>

        <form method="POST" action="{{ route('cart.add') }}" class="mt-3">
            @csrf
            <input type="hidden" name="product_id" value="{{ (string) $product->_id }}">
            <button type="submit"
                    class="btn-primary w-full text-sm justify-center"
                    {{ (isset($product->stock) && $product->stock !== null && $product->stock <= 0) ? 'disabled' : '' }}>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Add to cart
            </button>
        </form>
    </div>
</div>
