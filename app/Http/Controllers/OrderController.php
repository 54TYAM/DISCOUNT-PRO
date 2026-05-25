<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', (string) auth()->user()->_id)
            ->orderBy('placed_at', 'desc')
            ->paginate();

        return view('orders.index', compact('orders'));
    }

    public function show(string $id)
    {
        $order = Order::where('user_id', (string) auth()->user()->_id)
            ->findOrFail($id);

        $storeIds = collect($order->items)->pluck('store_id')->filter()->unique()->all();
        $stores   = Store::whereIn('_id', $storeIds)->get(['_id', 'name', 'slug'])
            ->keyBy(fn ($s) => (string) $s->_id);

        return view('orders.show', compact('order', 'stores'));
    }

    /** Re-add every still-available item from a past order back to the cart. */
    public function reorder(Request $request, string $id, CartService $cart)
    {
        $order = Order::where('user_id', (string) auth()->user()->_id)->findOrFail($id);

        $productIds = collect($order->items)->pluck('product_id')->filter()->unique()->values()->all();
        $live       = Product::whereIn('_id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($p) => (string) $p->_id);

        $added = 0;
        $missing = 0;
        foreach ($order->items as $item) {
            $pid = (string) ($item['product_id'] ?? '');
            $p   = $live->get($pid);
            if (! $p) { $missing++; continue; }
            $cart->add($pid, (int) ($item['qty'] ?? 1));
            $added++;
        }

        if ($added === 0) {
            return redirect()->route('orders.show', $id)->with('error', 'None of the items in that order are still available.');
        }

        $msg = "Added {$added} item" . ($added === 1 ? '' : 's') . ' to your cart.';
        if ($missing > 0) $msg .= " ({$missing} no longer available.)";

        return redirect()->route('cart.show')->with('success', $msg);
    }
}
