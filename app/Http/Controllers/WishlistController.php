<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $entries = Wishlist::where('user_id', (string) auth()->user()->_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $productIds = $entries->pluck('product_id')->all();
        $products   = Product::whereIn('_id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($p) => (string) $p->_id);

        $storeIds = $products->pluck('store_id')->filter()->unique()->values()->all();
        $stores   = Store::whereIn('_id', $storeIds)
            ->get(['_id', 'name', 'slug'])
            ->keyBy(fn ($s) => (string) $s->_id);

        // Skip wishlist entries pointing to deleted/deactivated products
        $items = $entries->map(function ($entry) use ($products) {
            return $products->get((string) $entry->product_id);
        })->filter()->values();

        return view('wishlist.index', compact('items', 'stores'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => ['required', 'string']]);

        $userId    = (string) auth()->user()->_id;
        $productId = (string) $request->product_id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
        } else {
            Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);
            $inWishlist = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['in_wishlist' => $inWishlist]);
        }
        return back()->with('success', $inWishlist ? 'Added to wishlist.' : 'Removed from wishlist.');
    }

    public function destroy(string $productId)
    {
        Wishlist::where('user_id', (string) auth()->user()->_id)
            ->where('product_id', $productId)
            ->delete();
        return back()->with('success', 'Removed from wishlist.');
    }
}
