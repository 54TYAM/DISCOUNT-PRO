<x-app-layout title="My Orders">

@php
    $totalSpent = $orders->sum('total');
    $totalSaved = $orders->sum('discount_amount');
@endphp

<x-page-header title="My Orders" subtitle="Your complete purchase history." />

@if ($orders->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No orders yet. Time to find some deals!</p>
        <div class="flex items-center justify-center gap-2 mt-4">
            <a href="{{ route('shop.index') }}" class="btn-primary">Browse the shop</a>
            <a href="{{ route('coupons.index') }}" class="btn-secondary">View coupons</a>
        </div>
    </div>
@else

{{-- Quick stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 page-section">
    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Total spent</p>
        <p class="text-2xl font-bold text-slate-900">₹{{ number_format($totalSpent, 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">across {{ $orders->total() }} order{{ $orders->total() === 1 ? '' : 's' }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Saved with coupons</p>
        <p class="text-2xl font-bold text-emerald-600">₹{{ number_format($totalSaved, 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">on this page</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">Last order</p>
        <p class="text-2xl font-bold text-slate-900">{{ $orders->first()?->placed_at?->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) ?? '—' }}</p>
        <p class="text-xs text-slate-400 mt-0.5">ago</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Order #</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Items</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Coupon</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Saved</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Total</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-slate-400 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @foreach ($orders as $order)
                @php
                    $statusClass = match($order->status) {
                        'placed'    => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',
                        'fulfilled' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                        'cancelled' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                        default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                    };
                @endphp
                <tr class="hover:bg-stone-50/50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $order->order_number }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ count($order->items ?? []) }} items</td>
                    <td class="px-4 py-3">
                        @if ($order->discount_code)
                            <span class="font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">
                                {{ $order->discount_code }}
                            </span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if (($order->discount_amount ?? 0) > 0)
                            <span class="font-semibold text-emerald-600">−₹{{ number_format($order->discount_amount, 0) }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-900">₹{{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $statusClass }} capitalize">{{ $order->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-slate-400 whitespace-nowrap">
                        {{ $order->placed_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('orders.show', (string) $order->_id) }}"
                           class="text-xs text-brand-600 hover:text-brand-700 font-medium">View →</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $orders->links() }}</div>

@endif

</x-app-layout>
