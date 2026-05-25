<x-admin-layout title="Order {{ $order->order_number }}">

@php
    $statusClass = match($order->status) {
        'placed'    => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',
        'fulfilled' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
    };
    $myItemsTotal = collect($items)->sum('line_total');
@endphp

<div class="flex items-start justify-between flex-wrap gap-4 mb-6">
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('admin.orders.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 font-mono tracking-widest">{{ $order->order_number }}</h1>
                <span class="badge {{ $statusClass }} capitalize">{{ $order->status }}</span>
            </div>
            <p class="text-slate-400 text-sm mt-0.5">Placed {{ $order->placed_at?->diffForHumans() ?? '—' }}</p>
        </div>
    </div>

    {{-- Status update actions --}}
    @if ($order->status === 'placed')
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.orders.status', (string) $order->_id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="fulfilled">
                <button class="px-3 py-1.5 text-sm font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Mark as fulfilled
                </button>
            </form>
            <form method="POST" action="{{ route('admin.orders.status', (string) $order->_id) }}"
                  onsubmit="return confirm('Cancel this order?')">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button class="px-3 py-1.5 text-sm font-medium bg-white text-rose-600 border border-rose-200 rounded-lg hover:bg-rose-50 transition-colors
                               dark:bg-slate-800 dark:text-rose-400 dark:border-rose-500/30 dark:hover:bg-rose-500/10">
                    Cancel order
                </button>
            </form>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Items --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">
                Items ({{ count($items) }})
                @if (! auth()->user()->isAdmin())
                    <span class="text-xs font-normal text-slate-400">— filtered to your store</span>
                @endif
            </h2>

            <div class="space-y-3">
            @foreach ($items as $item)
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-stone-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800 line-clamp-1">{{ $item['product_name'] ?? 'Product' }}</p>
                        <p class="text-xs text-slate-400">Qty: {{ $item['qty'] ?? 1 }} × ₹{{ number_format($item['unit_price'] ?? 0, 0) }}</p>
                    </div>
                    <p class="font-semibold text-slate-900 text-sm">₹{{ number_format($item['line_total'] ?? 0, 0) }}</p>
                </div>
            @endforeach
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Timeline</h2>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-brand-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Order placed</p>
                        <p class="text-xs text-slate-400">{{ $order->placed_at?->format('M j, Y · H:i') ?? '—' }}</p>
                    </div>
                </div>
                @if ($order->status === 'fulfilled')
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Fulfilled</p>
                        <p class="text-xs text-slate-400">Marked complete</p>
                    </div>
                </div>
                @elseif ($order->status === 'cancelled')
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Cancelled</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Customer</h2>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($customer?->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-slate-800 truncate">{{ $customer?->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $customer?->email ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Totals</h2>
            <div class="space-y-2 text-sm">
                @if (! auth()->user()->isAdmin())
                <div class="flex justify-between">
                    <span class="text-slate-500">Your items subtotal</span>
                    <span class="font-medium text-slate-800">₹{{ number_format($myItemsTotal, 2) }}</span>
                </div>
                <div class="border-t border-slate-100 my-2"></div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500">Order subtotal</span>
                    <span class="font-medium text-slate-800">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-emerald-600">Coupon ({{ $order->discount_code }})</span>
                        <span class="font-medium text-emerald-600">−₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500">Shipping</span>
                    <span class="font-medium text-slate-800">Free</span>
                </div>
                <div class="border-t border-slate-100 pt-2 mt-2 flex justify-between">
                    <span class="font-semibold text-slate-800">Order total</span>
                    <span class="text-xl font-bold text-slate-900">₹{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
