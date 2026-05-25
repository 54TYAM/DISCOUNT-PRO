<x-app-layout title="Order #{{ $order->order_number }}">

<a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 mb-4">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    All orders
</a>

{{-- Success header --}}
<div class="card overflow-hidden mb-6">
    <div class="bg-gradient-emerald px-5 sm:px-6 py-5 flex items-center gap-3 text-white relative overflow-hidden">
        <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/10"></div>
        <div class="absolute inset-0 bg-pattern-dots bg-dots-sm opacity-20 pointer-events-none"></div>
        <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center flex-shrink-0 relative ring-1 ring-white/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1 relative">
            <p class="font-bold text-lg">Order placed successfully!</p>
            <p class="text-emerald-100 text-sm">Order #{{ $order->order_number }} · {{ $order->placed_at?->format('M j, Y g:i A') }}</p>
        </div>
    </div>
    <div class="px-5 sm:px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-0 sm:divide-x sm:divide-slate-100">
        <div class="sm:pr-5">
            <p class="text-xs text-slate-400 mb-0.5">Subtotal</p>
            <p class="text-lg font-semibold text-slate-700">₹{{ number_format($order->subtotal, 2) }}</p>
        </div>
        <div class="sm:px-5">
            <p class="text-xs text-slate-400 mb-0.5">Discount</p>
            <p class="text-lg font-semibold text-emerald-600">
                @if ($order->discount_amount > 0)
                    −₹{{ number_format($order->discount_amount, 2) }}
                @else
                    —
                @endif
            </p>
        </div>
        <div class="sm:pl-5">
            <p class="text-xs text-slate-400 mb-0.5">Total paid</p>
            <p class="text-lg font-bold text-slate-900">₹{{ number_format($order->total, 2) }}</p>
        </div>
    </div>
    @if ($order->discount_code)
    <div class="px-5 sm:px-6 pb-4 text-xs text-slate-400">
        Coupon applied: <span class="font-mono text-slate-700 font-semibold">{{ $order->discount_code }}</span>
    </div>
    @endif
</div>

{{-- Items --}}
<div class="card p-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Items ordered</h2>

    <div class="space-y-3">
    @foreach (($order->items ?? []) as $item)
        @php $store = $stores->get((string) ($item['store_id'] ?? '')); @endphp
        <div class="flex items-center gap-4 py-2">
            <div class="flex-1 min-w-0">
                <p class="font-medium text-slate-800">{{ $item['product_name'] ?? 'Item' }}</p>
                @if ($store)
                    <p class="text-xs text-slate-400">{{ $store->name }}</p>
                @endif
                <p class="text-xs text-slate-500 mt-0.5">{{ $item['qty'] }} × ₹{{ number_format($item['unit_price'], 0) }}</p>
            </div>
            <p class="font-semibold text-slate-900">₹{{ number_format($item['line_total'], 2) }}</p>
        </div>
    @endforeach
    </div>
</div>

{{-- Timeline --}}
<div class="card p-5 mt-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Order timeline</h2>
    @php
        $steps = [
            ['key' => 'placed',    'label' => 'Order placed', 'icon' => 'M5 13l4 4L19 7'],
            ['key' => 'fulfilled', 'label' => 'Fulfilled',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2'],
        ];
        $statusOrder = ['placed' => 1, 'fulfilled' => 2, 'cancelled' => 0];
        $now = $statusOrder[$order->status] ?? 1;
    @endphp
    <div class="flex items-stretch gap-0">
    @foreach ($steps as $i => $step)
        @php $done = $now >= ($i + 1); @endphp
        <div class="flex-1 flex flex-col items-center text-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $done ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-slate-300' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                </svg>
            </div>
            <p class="text-xs font-medium mt-2 {{ $done ? 'text-slate-700' : 'text-slate-400' }}">{{ $step['label'] }}</p>
        </div>
        @if (! $loop->last)
            <div class="flex-1 self-center h-0.5 {{ $now >= $i + 2 ? 'bg-emerald-200' : 'bg-stone-100' }} mt-[-20px]"></div>
        @endif
    @endforeach
    </div>
    @if ($order->status === 'cancelled')
        <p class="text-xs text-rose-600 text-center mt-4">This order was cancelled.</p>
    @endif
</div>

<div class="mt-5 flex items-center gap-3 flex-wrap">
    @if ($order->status !== 'cancelled')
        <form method="POST" action="{{ route('orders.reorder', (string) $order->_id) }}">
            @csrf
            <button class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Order again
            </button>
        </form>
    @endif
    <a href="{{ route('shop.index') }}" class="btn-secondary">Continue shopping</a>
    <a href="{{ route('orders.index') }}" class="btn-secondary">All orders</a>
</div>

</x-app-layout>
