<x-admin-layout title="Platform Orders">

<x-page-header
    title="All Orders"
    subtitle="Platform-wide order activity across every store" />

{{-- Platform stats — glass --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 page-section stagger-children">
    <x-stat-card label="Total revenue"  :numeric="(int) $stats['total_revenue']" prefix="₹" color="brand"
                 icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
    <x-stat-card label="Total savings"  :numeric="(int) $stats['total_savings']" prefix="₹" color="emerald"
                 icon="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
    <x-stat-card label="Orders"         :numeric="(int) $stats['order_count']" color="sky"
                 icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    <x-stat-card label="Avg order value" :numeric="(int) $stats['avg_order']" prefix="₹" color="amber"
                 icon="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
</div>

<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex-1 min-w-48 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order #" class="form-input pl-9">
    </div>
    <select name="status" class="form-input w-auto">
        <option value="">All statuses</option>
        <option value="placed"    {{ request('status') === 'placed' ? 'selected' : '' }}>Placed</option>
        <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <button type="submit" class="btn-primary">Filter</button>
</form>

@if ($orders->isEmpty())
    <div class="text-center py-20 card"><p class="text-slate-400 text-sm">No orders match those filters.</p></div>
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
                        <div class="flex items-center gap-2.5">
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
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $orders->links() }}</div>

@endif

</x-admin-layout>
