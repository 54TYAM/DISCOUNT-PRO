<x-admin-layout title="Edit Discount">

<div class="max-w-3xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.discounts.show', (string) $discount->_id) }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="page-title">Edit Discount</h1>
            <p class="text-sm text-slate-400 mt-0.5 font-mono tracking-wide">{{ $discount->code }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.discounts.update', (string) $discount->_id) }}">
        @csrf
        @method('PUT')
        @include('admin.discounts._form', ['discount' => $discount])
    </form>

</div>

</x-admin-layout>
