<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Manager/admin order-management. Managers see only orders containing at least one
 * item from their store; admins see every order on the platform.
 */
class OrderController extends Controller
{
    /** Restrict the query to orders that involve the current manager's store. */
    private function scope($query)
    {
        $user = auth()->user();
        if ($user->isAdmin()) return $query;

        $storeId = $user->store?->_id ? (string) $user->store->_id : null;
        if (! $storeId) return $query->whereRaw(['_id' => '__none__']);

        // Match orders that have at least one embedded item with this store_id
        return $query->where('items.store_id', $storeId);
    }

    public function index(Request $request)
    {
        $query = $this->scope(Order::query());

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where('order_number', 'like', '%' . strtoupper($search) . '%');
        }

        $orders = $query->orderBy('placed_at', 'desc')->paginate()->withQueryString();

        // Bulk-load customer info to display next to each order
        $userIds = $orders->pluck('user_id')->filter()->unique()->values()->all();
        $users   = User::whereIn('_id', $userIds)->get(['_id', 'name', 'email'])
            ->keyBy(fn ($u) => (string) $u->_id);

        // Status counts for tabs
        $countsQuery = $this->scope(Order::query());
        $counts = [
            'all'        => (clone $countsQuery)->count(),
            'placed'     => (clone $countsQuery)->where('status', Order::STATUS_PLACED)->count(),
            'fulfilled'  => (clone $countsQuery)->where('status', Order::STATUS_FULFILLED)->count(),
            'cancelled'  => (clone $countsQuery)->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'users', 'counts'));
    }

    public function show(string $id)
    {
        $order = $this->scope(Order::query())->where('_id', $id)->firstOrFail();

        $customer = User::find($order->user_id);

        // For managers: only show items belonging to their store
        $items = $order->items ?? [];
        $user  = auth()->user();
        if (! $user->isAdmin() && $user->store) {
            $items = array_values(array_filter($items, fn ($it) => ($it['store_id'] ?? null) === (string) $user->store->_id));
        }

        return view('admin.orders.show', compact('order', 'customer', 'items'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in([Order::STATUS_PLACED, Order::STATUS_FULFILLED, Order::STATUS_CANCELLED])],
        ]);

        $order = $this->scope(Order::query())->where('_id', $id)->firstOrFail();
        $prevStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Notify customer when status changes
        if ($prevStatus !== $request->status) {
            $labels = ['fulfilled' => 'fulfilled and on the way', 'cancelled' => 'cancelled'];
            $colors = ['fulfilled' => 'emerald', 'cancelled' => 'rose'];
            Notification::notify((string) $order->user_id, [
                'type'  => 'order_status',
                'title' => "Order #{$order->order_number} " . ($labels[$request->status] ?? $request->status),
                'body'  => "Your order total: ₹" . number_format($order->total, 0),
                'link'  => route('orders.show', (string) $order->_id),
                'icon'  => $request->status === 'fulfilled'
                    ? 'M5 13l4 4L19 7'
                    : 'M6 18L18 6M6 6l12 12',
                'color' => $colors[$request->status] ?? 'brand',
            ]);
        }

        return back()->with('success', "Order #{$order->order_number} marked as {$request->status}.");
    }
}
