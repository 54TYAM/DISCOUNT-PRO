<x-admin-layout title="New Campaign">

<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.promotions.index') }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="page-title">New Campaign</h1>
            <p class="page-subtitle">Set up a targeted promotion for your customers</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.promotions.store') }}">
        @csrf
        @include('admin.promotions._form', ['promo' => null])
    </form>

</div>

</x-admin-layout>
