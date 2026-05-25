<x-app-layout title="{{ $store->name }}">

@php
    $bg = match($store->banner_color ?? 'brand') {
        'violet'  => 'from-violet-600 to-violet-800',
        'brand'   => 'from-brand-600 to-brand-800',
        'amber'   => 'from-amber-500 to-amber-700',
        'rose'    => 'from-rose-500 to-rose-700',
        'emerald' => 'from-emerald-600 to-emerald-800',
        'sky'     => 'from-sky-500 to-sky-700',
        default   => 'from-slate-600 to-slate-800',
    };
@endphp

<a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 mb-4">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    All stores
</a>

{{-- Hero banner --}}
<div class="rounded-2xl bg-gradient-to-br {{ $bg }} p-6 sm:p-10 mb-6 text-white relative overflow-hidden">
    <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-12 -left-12 w-60 h-60 rounded-full bg-white/5"></div>
    <div class="relative flex items-start gap-5 flex-wrap">
        <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
            @if ($store->logo_url)
                <img src="{{ $store->logo_url }}" alt="" class="w-full h-full rounded-2xl object-cover">
            @else
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-white/70 text-xs uppercase tracking-wider">{{ $store->category }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold mt-1">{{ $store->name }}</h1>
            @if ($store->description)
                <p class="text-white/80 mt-3 max-w-2xl">{{ $store->description }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Promotions banner row --}}
@if ($promotions->isNotEmpty())
<div class="mb-6">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Active campaigns</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($promotions as $promo)
        <div class="card p-4 border-l-4 border-amber-400">
            <p class="text-xs text-amber-700 font-medium uppercase tracking-wide">{{ \App\Models\Promotion::TYPE_LABELS[$promo->type] ?? '' }}</p>
            <p class="font-semibold text-slate-800 mt-1">{{ $promo->name }}</p>
            <p class="text-slate-500 text-sm mt-1 line-clamp-2">{{ $promo->description }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Active coupons row --}}
@if ($activeDiscounts->isNotEmpty())
<div class="mb-6">
    <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Available coupons</h2>
    <div class="flex flex-wrap gap-2">
        @foreach ($activeDiscounts as $d)
        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2"
             x-data="{ copied: false }">
            <span class="font-mono text-sm font-semibold text-emerald-800 tracking-wider">{{ $d->code }}</span>
            <span class="text-xs text-emerald-700">
                @if ($d->type === 'percentage') {{ $d->value }}% off
                @elseif ($d->type === 'fixed') ₹{{ number_format($d->value) }} off
                @elseif ($d->type === 'bogo') BOGO 50%
                @elseif ($d->type === 'free_shipping') Free shipping
                @else Tiered
                @endif
            </span>
            <button @click="navigator.clipboard.writeText('{{ $d->code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                    class="text-emerald-600 hover:text-emerald-800 transition-colors">
                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <svg x-show="copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Product grid --}}
<h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Products</h2>

@if ($products->isEmpty())
    <div class="text-center py-16 card">
        <p class="text-slate-400 text-sm">This store hasn't added any products yet.</p>
    </div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
@foreach ($products as $product)
    <div class="card-hover overflow-hidden flex flex-col">
        <a href="{{ route('shop.product', (string) $product->_id) }}" class="block">
            <div class="aspect-square bg-stone-100">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </a>
        <div class="p-4 flex flex-col flex-1">
            <a href="{{ route('shop.product', (string) $product->_id) }}"
               class="font-semibold text-slate-800 line-clamp-2 hover:text-brand-600 transition-colors">
                {{ $product->name }}
            </a>
            <p class="text-2xl font-bold text-slate-900 mt-2">₹{{ number_format($product->price, 0) }}</p>
            <form method="POST" action="{{ route('cart.add') }}" class="mt-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ (string) $product->_id }}">
                <button class="btn-primary w-full text-sm justify-center">Add to cart</button>
            </form>
        </div>
    </div>
@endforeach
</div>

<div class="mt-6">{{ $products->links() }}</div>

@endif

</x-app-layout>
