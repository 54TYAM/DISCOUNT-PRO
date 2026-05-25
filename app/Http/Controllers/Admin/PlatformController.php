<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Super-admin-only platform oversight: every store, every user, every order.
 * Managers cannot reach these endpoints (guard at the top of each method).
 */
class PlatformController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Super admin only.');
    }

    // ── All Stores ──────────────────────────────────────────────────────────
    public function stores(Request $request)
    {
        $this->ensureAdmin();

        $query = Store::query();
        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }

        $stores = $query->orderBy('created_at', 'desc')->paginate()->withQueryString();

        // Bulk-load owners + per-store product/order counts
        $storeIds = $stores->pluck('_id')->map(fn ($id) => (string) $id)->all();
        $ownerIds = $stores->pluck('owner_id')->filter()->unique()->values()->all();

        $owners = User::whereIn('_id', $ownerIds)->get(['_id', 'name', 'email'])
            ->keyBy(fn ($u) => (string) $u->_id);

        $productCounts = Product::whereIn('store_id', $storeIds)->get(['store_id'])
            ->groupBy('store_id')->map->count();

        $couponCounts = Discount::whereIn('store_id', $storeIds)->get(['store_id'])
            ->groupBy('store_id')->map->count();

        return view('admin.platform.stores', compact('stores', 'owners', 'productCounts', 'couponCounts'));
    }

    public function toggleStore(string $id)
    {
        $this->ensureAdmin();
        $store = Store::findOrFail($id);
        $store->update(['is_active' => ! $store->is_active]);
        return back()->with('success', "Store «{$store->name}» " . ($store->is_active ? 'reactivated.' : 'suspended.'));
    }

    public function toggleFeatured(string $id)
    {
        $this->ensureAdmin();
        $store = Store::findOrFail($id);
        $store->update(['is_featured' => ! $store->is_featured]);
        return back()->with('success', $store->is_featured
            ? "«{$store->name}» is now featured on the shop homepage."
            : "Removed «{$store->name}» from featured stores.");
    }

    // ── All Users ───────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $this->ensureAdmin();

        $query = User::query();
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate()->withQueryString();

        $counts = [
            'all'       => User::count(),
            'customer'  => User::where('role', User::ROLE_CUSTOMER)->count(),
            'manager'   => User::where('role', User::ROLE_MANAGER)->count(),
            'admin'     => User::where('role', User::ROLE_ADMIN)->count(),
            'pending'   => User::where('role', User::ROLE_MANAGER)->where('is_approved', false)->count(),
        ];

        return view('admin.platform.users', compact('users', 'counts'));
    }

    // ── All Orders (platform-wide) ──────────────────────────────────────────
    public function orders(Request $request)
    {
        $this->ensureAdmin();

        $query = Order::query();
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where('order_number', 'like', '%' . strtoupper($search) . '%');
        }

        $orders = $query->orderBy('placed_at', 'desc')->paginate()->withQueryString();

        $userIds = $orders->pluck('user_id')->filter()->unique()->values()->all();
        $users   = User::whereIn('_id', $userIds)->get(['_id', 'name', 'email'])
            ->keyBy(fn ($u) => (string) $u->_id);

        $stats = [
            'total_revenue' => (float) Order::sum('total'),
            'total_savings' => (float) Order::sum('discount_amount'),
            'order_count'   => Order::count(),
            'avg_order'     => (float) (Order::avg('total') ?? 0),
        ];

        return view('admin.platform.orders', compact('orders', 'users', 'stats'));
    }
}
