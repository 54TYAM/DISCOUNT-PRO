<x-admin-layout title="Orders">

<x-page-header
    title="Orders"
    :subtitle="$counts['all'] . ' total · ' . $counts['placed'] . ' awaiting fulfillment'" />

{{-- Status tabs --}}
@php
    $tabs = ['all' => 'All', 'placed' => 'New', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'];
    $current = request('status', 'all');
@endphp
<div class="status-tabs">
    @foreach ($tabs as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key === 'all' ? null : $key, 'page' => null]) }}"
           class="{{ $current === $key ? 'status-tab-active' : 'status-tab' }}">
            {{ $label }}
            <span class="status-tab-count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order #" class="form-input pl-9">
    </div>
    <button type="submit" class="btn-primary">Search</button>
    @if (request()->hasAny(['search']))
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Clear</a>
    @endif
</form>

@if ($orders->isEmpty())
    <div class="text-center py-20 card">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-400 text-sm font-medium">No orders yet</p>
        <p class="text-slate-400 text-xs mt-1">Orders containing your products will appear here.</p>
    </div>
@else

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th class="text-center">Items</th>
                    <th>Coupon</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th class="text-right">Placed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($orders as $order)
                @php
                    $cust = $users->get((string) $order->user_id);
                    $statusClass = match($order->status) {
                        'placed'    => 'bg-brand-50 text-brand-700 ring-1 ring-brand-200',
                        'fulfilled' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                        'cancelled' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                        default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                    };
                    $dotColor = match($order->status) {
                        'placed'    => 'bg-brand-500 animate-pulse',
                        'fulfilled' => 'bg-emerald-500',
                        'cancelled' => 'bg-rose-500',
                        default     => 'bg-slate-400',
                    };
                @endphp
                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.orders.show', (string) $order->_id) }}'">
                    <td>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                            <span class="font-mono text-xs font-bold text-slate-700">{{ $order->order_number }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-brand flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                {{ strtoupper(substr($cust?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-slate-800 font-medium truncate">{{ $cust?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $cust?->email ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center text-slate-600 font-medium">{{ count($order->items ?? []) }}</td>
                    <td>
                        @if ($order->discount_code)
                            <span class="font-mono text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-2 py-0.5 rounded">{{ $order->discount_code }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="text-right font-bold text-slate-900">₹{{ number_format($order->total, 0) }}</td>
                    <td>
                        <span class="badge {{ $statusClass }} capitalize">{{ $order->status }}</span>
                    </td>
                    <td class="text-right text-xs text-slate-400 whitespace-nowrap">
                        {{ $order->placed_at?->diffForHumans() ?? '—' }}
                    </td>
                    <td class="text-right">
                        <svg class="w-4 h-4 row-chev inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $orders->links() }}</div>

@endif

</x-admin-layout>
