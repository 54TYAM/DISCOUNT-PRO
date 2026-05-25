<x-app-layout title="{{ $product->name }}">

{{-- ── Breadcrumbs ─────────────────────────────────────────────────────── --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 mb-5" aria-label="Breadcrumb">
    <a href="{{ route('shop.index') }}" class="hover:text-brand-600 dark:hover:text-brand-300 transition-colors">Shop</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    @if ($store)
        <a href="{{ route('shop.store', $store->slug) }}" class="hover:text-brand-600 dark:hover:text-brand-300 transition-colors">{{ $store->name }}</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    @endif
    <span class="text-slate-600 dark:text-slate-300 truncate max-w-xs">{{ $product->name }}</span>
</nav>

{{-- ── Main grid: gallery + details ────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-10"
     x-data="{ activeImage: 0, zoom: false, zoomX: 50, zoomY: 50 }">

    {{-- ── Image gallery (3/5) ─────────────────────────────────────────── --}}
    <div class="lg:col-span-3 space-y-3">
        {{-- Main image with zoom-on-hover --}}
        <div class="relative aspect-square card overflow-hidden bg-stone-100 cursor-zoom-in group"
             @mousemove="
                 const r = $event.currentTarget.getBoundingClientRect();
                 zoomX = (($event.clientX - r.left) / r.width) * 100;
                 zoomY = (($event.clientY - r.top) / r.height) * 100;
             "
             @mouseenter="zoom = true"
             @mouseleave="zoom = false">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     class="w-full h-full object-cover transition-transform duration-300"
                     :style="zoom ? `transform: scale(1.7); transform-origin: ${zoomX}% ${zoomY}%;` : ''">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200">
                    <svg class="w-20 h-20 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Category badge --}}
            <span class="absolute top-4 left-4 bg-white/95 backdrop-blur text-slate-700 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md ring-1 ring-white/40 shadow-card">
                {{ $product->category }}
            </span>

            {{-- Zoom hint --}}
            <span class="absolute bottom-4 right-4 bg-slate-900/70 backdrop-blur text-white text-[10px] font-semibold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Hover to zoom
            </span>
        </div>

        {{-- Thumbnail strip (single image shown 4 times; would loop real images if multiple) --}}
        <div class="grid grid-cols-4 gap-2">
            @for ($i = 0; $i < 4; $i++)
                <button @click="activeImage = {{ $i }}"
                        class="aspect-square card overflow-hidden bg-stone-100 transition-all"
                        :class="activeImage === {{ $i }} ? 'ring-2 ring-brand-500 ring-offset-2' : 'hover:ring-1 hover:ring-brand-300 opacity-70 hover:opacity-100'">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                        </div>
                    @endif
                </button>
            @endfor
        </div>
    </div>

    {{-- ── Details (2/5) ───────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 flex flex-col">
        @if ($store)
            <a href="{{ route('shop.store', $store->slug) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/></svg>
                {{ $store->name }}
            </a>
        @endif

        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 tracking-tight leading-tight">{{ $product->name }}</h1>

        {{-- Rating summary --}}
        @if ($avgRating)
            <div class="flex items-center gap-1.5 mt-3">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.366 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.683-1.54 1.118L10 14.347l-3.366 2.446c-.785.565-1.84-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.65 8.155c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.95-.69l1.286-3.957z"/>
                    </svg>
                @endfor
                <a href="#reviews" class="text-sm text-slate-600 ml-1 hover:text-brand-600 transition-colors">
                    {{ $avgRating }} <span class="text-slate-400">({{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }})</span>
                </a>
            </div>
        @endif

        {{-- Price + stock --}}
        <div class="flex items-baseline gap-3 mt-4">
            <span class="text-4xl font-extrabold text-slate-900 tracking-tight">₹{{ number_format($product->price, 0) }}</span>
            @if ($product->stock <= 0)
                <span class="badge-expired text-sm py-1">Out of stock</span>
            @elseif ($product->stock < 5)
                <span class="badge bg-amber-50 text-amber-700 ring-1 ring-amber-200 text-sm py-1 animate-pulse">
                    Only {{ $product->stock }} left
                </span>
            @else
                <span class="badge-active">In stock</span>
            @endif
        </div>

        {{-- Trust badges row — glass tiles --}}
        <div class="grid grid-cols-3 gap-2 mt-5 text-center">
            <div class="p-2.5 rounded-xl border border-white/50 dark:border-white/10 backdrop-blur"
                 style="background: rgba(255,255,255,0.5);">
                <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-300 leading-tight">Free<br>shipping</p>
            </div>
            <div class="p-2.5 rounded-xl border border-white/50 dark:border-white/10 backdrop-blur"
                 style="background: rgba(255,255,255,0.5);">
                <svg class="w-5 h-5 text-brand-500 dark:text-brand-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-300 leading-tight">Verified<br>seller</p>
            </div>
            <div class="p-2.5 rounded-xl border border-white/50 dark:border-white/10 backdrop-blur"
                 style="background: rgba(255,255,255,0.5);">
                <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-300 leading-tight">Easy<br>returns</p>
            </div>
        </div>

        {{-- Add to cart form (the trigger we watch for sticky-bar visibility) --}}
        <div id="main-cta" class="mt-6 flex items-center gap-3">
            <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-3 flex-1">
                @csrf
                <input type="hidden" name="product_id" value="{{ (string) $product->_id }}">
                <input type="number" name="qty" value="1" min="1" max="{{ max(1, $product->stock) }}"
                       class="form-input w-20 text-center font-semibold">
                <button type="submit" class="btn-primary flex-1 justify-center text-base py-3"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Add to cart
                </button>
            </form>

            <form method="POST" action="{{ route('wishlist.toggle') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ (string) $product->_id }}">
                <button type="submit"
                        title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                        class="p-3.5 rounded-lg border transition-all hover:scale-105 {{ $inWishlist ? 'border-rose-300 bg-rose-50 text-rose-500' : 'border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-300 hover:bg-rose-50' }}">
                    <svg class="w-5 h-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        @if (! empty($product->tags))
        <div class="mt-5 flex flex-wrap gap-1.5">
            @foreach ((array) $product->tags as $tag)
                <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">#{{ $tag }}</span>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Tabbed section: Description / Specs / Reviews — glass tab bar ──── --}}
<div class="card-glass overflow-hidden mb-10 relative" x-data="{ tab: 'description' }">
    <div class="glass-sheen"></div>
    <div class="relative flex border-b border-white/40 dark:border-white/10 overflow-x-auto"
         style="background: rgba(255,255,255,0.25);">
        <button @click="tab = 'description'"
                :class="tab === 'description' ? 'text-brand-700 dark:text-brand-300 border-brand-600 dark:border-brand-400 bg-white/70 dark:bg-slate-800/60 backdrop-blur' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-200'"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 -mb-px transition-colors whitespace-nowrap">
            Description
        </button>
        <button @click="tab = 'specs'"
                :class="tab === 'specs' ? 'text-brand-700 dark:text-brand-300 border-brand-600 dark:border-brand-400 bg-white/70 dark:bg-slate-800/60 backdrop-blur' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-200'"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 -mb-px transition-colors whitespace-nowrap">
            Specifications
        </button>
        <button @click="tab = 'reviews'"
                :class="tab === 'reviews' ? 'text-brand-700 dark:text-brand-300 border-brand-600 dark:border-brand-400 bg-white/70 dark:bg-slate-800/60 backdrop-blur' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-200'"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 -mb-px transition-colors whitespace-nowrap flex items-center gap-2">
            Reviews
            <span class="text-xs bg-white/70 dark:bg-slate-800/70 backdrop-blur text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded-full ring-1 ring-slate-200/50 dark:ring-slate-700">{{ $reviews->count() }}</span>
        </button>
    </div>

    <div class="p-6">
        {{-- DESCRIPTION TAB --}}
        <div x-show="tab === 'description'" x-cloak id="description">
            @if ($product->description)
                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed whitespace-pre-line">{{ $product->description }}</div>
            @else
                <p class="text-sm text-slate-400 italic">The seller hasn't added a description yet.</p>
            @endif
        </div>

        {{-- SPECS TAB --}}
        <div x-show="tab === 'specs'" x-cloak>
            <dl class="divide-y divide-slate-100">
                <div class="grid grid-cols-3 py-3">
                    <dt class="text-sm text-slate-500">Category</dt>
                    <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $product->category }}</dd>
                </div>
                @if ($store)
                <div class="grid grid-cols-3 py-3">
                    <dt class="text-sm text-slate-500">Sold by</dt>
                    <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $store->name }}</dd>
                </div>
                @endif
                <div class="grid grid-cols-3 py-3">
                    <dt class="text-sm text-slate-500">Availability</dt>
                    <dd class="col-span-2 text-sm font-medium">
                        @if ($product->stock <= 0)
                            <span class="text-rose-600">Out of stock</span>
                        @elseif ($product->stock < 5)
                            <span class="text-amber-700">Only {{ $product->stock }} units left</span>
                        @else
                            <span class="text-emerald-700">In stock ({{ $product->stock }} units)</span>
                        @endif
                    </dd>
                </div>
                @if (! empty($product->tags))
                <div class="grid grid-cols-3 py-3">
                    <dt class="text-sm text-slate-500">Tags</dt>
                    <dd class="col-span-2 flex flex-wrap gap-1.5">
                        @foreach ((array) $product->tags as $tag)
                            <span class="badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">#{{ $tag }}</span>
                        @endforeach
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- REVIEWS TAB --}}
        <div x-show="tab === 'reviews'" x-cloak id="reviews">
            @if ($avgRating)
                <div class="flex items-center gap-6 pb-5 mb-5 border-b border-slate-100">
                    <div class="text-center">
                        <p class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ $avgRating }}</p>
                        <div class="flex justify-center items-center gap-0.5 mt-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.366 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.683-1.54 1.118L10 14.347l-3.366 2.446c-.785.565-1.84-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.65 8.155c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                            @endfor
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}</p>
                    </div>
                    <div class="flex-1 space-y-1">
                        @php $byRating = $reviews->groupBy('rating')->map->count(); @endphp
                        @for ($r = 5; $r >= 1; $r--)
                            @php $count = $byRating->get($r, 0); $pct = $reviews->count() > 0 ? ($count / $reviews->count() * 100) : 0; @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-500 w-3">{{ $r }}</span>
                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.366 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.683-1.54 1.118L10 14.347l-3.366 2.446c-.785.565-1.84-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.65 8.155c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-slate-400 w-6 text-right">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif

            {{-- Review form (only for buyers) --}}
            @if ($hasPurchased)
                <form method="POST" action="{{ route('reviews.store', (string) $product->_id) }}"
                      class="mb-5 pb-5 border-b border-slate-100"
                      x-data="{ rating: {{ $myReview?->rating ?? 5 }} }">
                    @csrf
                    <p class="text-sm font-semibold text-slate-700 mb-2">
                        {{ $myReview ? 'Update your review' : 'Leave a review' }}
                    </p>
                    <div class="flex items-center gap-1 mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}" class="hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.366 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.683-1.54 1.118L10 14.347l-3.366 2.446c-.785.565-1.84-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.65 8.155c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.95-.69l1.286-3.957z"/>
                                </svg>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" x-bind:value="rating">
                    </div>
                    <textarea name="comment" rows="3" maxlength="500" placeholder="Share your experience…"
                              class="form-input">{{ $myReview?->comment }}</textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="btn-primary text-sm">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
                    </div>
                </form>
            @elseif (! $reviews->contains('user_id', (string) auth()->user()->_id))
                <p class="text-xs text-slate-400 mb-4 italic">Purchase this product to leave a review.</p>
            @endif

            {{-- Existing reviews --}}
            @if ($reviews->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-sm text-slate-400">No reviews yet — be the first!</p>
                </div>
            @else
                <div class="space-y-5">
                @foreach ($reviews as $review)
                    <div class="flex gap-3">
                        <div class="w-10 h-10 bg-gradient-brand rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 shadow-card">
                            {{ strtoupper(substr($review->user_name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-slate-800">{{ $review->user_name ?? 'Anonymous' }}</span>
                                <span class="flex items-center gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.16c.969 0 1.371 1.24.588 1.81l-3.366 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.683-1.54 1.118L10 14.347l-3.366 2.446c-.785.565-1.84-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.65 8.155c-.783-.57-.38-1.81.588-1.81h4.161a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                    @endfor
                                </span>
                                <span class="text-xs text-slate-400 ml-auto">{{ $review->created_at?->diffForHumans() }}</span>
                            </div>
                            @if ($review->comment)
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $review->comment }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Similar products ────────────────────────────────────────────────── --}}
@if ($similar->isNotEmpty())
<div class="mb-8">
    <h2 class="text-lg font-bold text-slate-900 mb-4 tracking-tight">More from this store</h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($similar as $s)
        <a href="{{ route('shop.product', (string) $s->_id) }}" class="card-hover overflow-hidden block group">
            <div class="image-zoom aspect-square bg-stone-100">
                @if ($s->image_url)
                    <img src="{{ $s->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                    </div>
                @endif
            </div>
            <div class="p-3">
                <p class="font-semibold text-sm text-slate-800 line-clamp-1 group-hover:text-brand-600 transition-colors">{{ $s->name }}</p>
                <p class="text-lg font-bold text-slate-900 mt-1 tracking-tight">₹{{ number_format($s->price, 0) }}</p>
            </div>
        </a>
    @endforeach
    </div>
</div>
@endif

{{-- ── Sticky add-to-cart bar (slides up when main CTA scrolls out) ───── --}}
<div x-data="{ visible: false }"
     x-init="
         const target = document.getElementById('main-cta');
         if (target) {
             const io = new IntersectionObserver(([e]) => { visible = !e.isIntersecting; }, { threshold: 0 });
             io.observe(target);
         }
     "
     x-show="visible" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
     class="fixed bottom-0 inset-x-0 z-30 bg-white/85 dark:bg-slate-900/85 backdrop-blur-md backdrop-saturate-150 border-t border-slate-200/60 dark:border-slate-800/60 shadow-card-pop">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">
        <div class="w-12 h-12 rounded-lg bg-stone-100 dark:bg-slate-800 overflow-hidden flex-shrink-0 hidden sm:block">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-1">{{ $product->name }}</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">₹{{ number_format($product->price, 0) }}</p>
        </div>
        <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ (string) $product->_id }}">
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="btn-primary text-sm" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
                Add to cart
            </button>
        </form>
    </div>
</div>

</x-app-layout>
