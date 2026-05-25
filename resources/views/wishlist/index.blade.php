<x-app-layout title="My Wishlist">

<x-page-header
    title="My Wishlist"
    :subtitle="$items->count() . ' ' . Str::plural('item', $items->count()) . ' saved for later.'" />

@if ($items->isEmpty())
    <x-empty-state
        icon="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
        title="Your wishlist is empty"
        description="Tap the heart icon on any product to save it here for later."
        ctaText="Browse the shop"
        :ctaUrl="route('shop.index')"
    />
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 stagger-children">
    @foreach ($items as $product)
        <x-product-card
            :product="$product"
            :store="$stores->get((string) $product->store_id)"
            show-wishlist />
    @endforeach
    </div>
@endif

</x-app-layout>
