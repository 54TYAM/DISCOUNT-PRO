<x-admin-layout title="My Store">

@php
    $bannerBg = match($store->banner_color ?? 'brand') {
        'violet'  => 'from-violet-600 to-violet-800',
        'brand'   => 'from-brand-600 to-brand-800',
        'amber'   => 'from-amber-500 to-amber-700',
        'rose'    => 'from-rose-500 to-rose-700',
        'emerald' => 'from-emerald-600 to-emerald-800',
        'sky'     => 'from-sky-500 to-sky-700',
        default   => 'from-slate-600 to-slate-800',
    };
@endphp

<x-page-header
    title="My Store"
    subtitle="Manage your storefront, products, and customer-facing details.">
    <x-slot:actions>
        <a href="{{ route('admin.store.edit') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit details
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Store banner --}}
<div class="rounded-2xl bg-gradient-to-br {{ $bannerBg }} p-6 sm:p-8 mb-6 text-white relative overflow-hidden">
    <div class="absolute -top-6 -right-6 w-40 h-40 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-8 -left-4 w-48 h-48 rounded-full bg-white/5"></div>
    <div class="relative flex items-start gap-4">
        <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            @if ($store->logo_url)
                <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-full h-full rounded-xl object-cover">
            @else
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @endif
        </div>
        <div class="min-w-0">
            <h2 class="text-2xl sm:text-3xl font-bold">{{ $store->name }}</h2>
            <p class="text-white/80 text-sm mt-1">{{ $store->category }}</p>
            @if ($store->description)
                <p class="text-white/70 text-sm mt-3 max-w-2xl">{{ $store->description }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Quick stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('admin.products.index') }}" class="stat-card hover:border-brand-200 transition-colors">
        <p class="text-2xl font-bold text-brand-600">{{ $stats['products'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Products</p>
        <p class="text-xs text-slate-400 mt-0.5">{{ $stats['active'] }} active</p>
    </a>
    <a href="{{ route('admin.discounts.index') }}" class="stat-card hover:border-emerald-200 transition-colors">
        <p class="text-2xl font-bold text-emerald-600">{{ $stats['discounts'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Coupons</p>
    </a>
    <a href="{{ route('admin.promotions.index') }}" class="stat-card hover:border-violet-200 transition-colors">
        <p class="text-2xl font-bold text-violet-600">{{ $stats['promotions'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Campaigns</p>
    </a>
    <a href="{{ route('shop.store', $store->slug) }}" target="_blank" class="stat-card hover:border-amber-200 transition-colors">
        <p class="text-2xl font-bold text-amber-600">View →</p>
        <p class="text-xs text-slate-500 mt-0.5">Public storefront</p>
        <p class="text-xs text-slate-400 mt-0.5">/{{ $store->slug }}</p>
    </a>
</div>

{{-- Store details --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 card p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Store information</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex">
                <dt class="w-32 text-slate-400">Slug</dt>
                <dd class="font-mono text-slate-700">{{ $store->slug }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-slate-400">Category</dt>
                <dd class="text-slate-700">{{ $store->category }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-slate-400">Status</dt>
                <dd>
                    @if ($store->is_active)
                        <span class="badge-active">Active</span>
                    @else
                        <span class="badge-paused">Inactive</span>
                    @endif
                </dd>
            </div>
            @if ($store->contact_email)
            <div class="flex">
                <dt class="w-32 text-slate-400">Email</dt>
                <dd class="text-slate-700">{{ $store->contact_email }}</dd>
            </div>
            @endif
            @if ($store->contact_phone)
            <div class="flex">
                <dt class="w-32 text-slate-400">Phone</dt>
                <dd class="text-slate-700">{{ $store->contact_phone }}</dd>
            </div>
            @endif
            @if ($store->address)
            <div class="flex">
                <dt class="w-32 text-slate-400">Address</dt>
                <dd class="text-slate-700">{{ $store->address }}</dd>
            </div>
            @endif
            <div class="flex">
                <dt class="w-32 text-slate-400">Registered</dt>
                <dd class="text-slate-700">{{ $store->created_at?->format('M j, Y') }}</dd>
            </div>
        </dl>
    </div>

    <div class="card p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Next steps</h3>
        <div class="space-y-3 text-sm">
            <a href="{{ route('admin.products.create') }}" class="block p-3 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                <p class="font-semibold text-slate-800">+ Add a product</p>
                <p class="text-slate-500 text-xs mt-0.5">Start filling your catalogue</p>
            </a>
            <a href="{{ route('admin.discounts.create') }}" class="block p-3 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                <p class="font-semibold text-slate-800">+ Create a coupon</p>
                <p class="text-slate-500 text-xs mt-0.5">Reward your customers</p>
            </a>
            <a href="{{ route('admin.promotions.create') }}" class="block p-3 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                <p class="font-semibold text-slate-800">+ Launch a campaign</p>
                <p class="text-slate-500 text-xs mt-0.5">Promote your offers</p>
            </a>
        </div>
    </div>
</div>

</x-admin-layout>
