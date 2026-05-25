<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Store;
use App\Models\Wishlist;
use Illuminate\Http\Request;

/**
 * Customer-facing storefront — browse stores and products across the platform.
 */
class ShopController extends Controller
{
    /** Landing page: featured stores + product highlights. */
    public function index(Request $request)
    {
        $query = Product::active();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Map product → store for badges (bulk to avoid N+1)
        $storeIds = $products->pluck('store_id')->filter()->unique()->values()->all();
        $stores   = Store::whereIn('_id', $storeIds)
            ->get(['_id', 'name', 'slug', 'banner_color'])
            ->keyBy(fn ($s) => (string) $s->_id);

        // Prefer admin-curated featured stores; fall back to newest active stores
        $featuredStores = Store::active()->where('is_featured', true)->limit(6)->get();
        if ($featuredStores->isEmpty()) {
            $featuredStores = Store::active()->orderBy('created_at', 'desc')->limit(6)->get();
        }
        $categories     = Product::CATEGORIES;

        // Hero: the single most attractive active coupon
        $heroCoupon = Discount::active()
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->whereIn('type', ['percentage', 'tiered'])
            ->orderBy('value', 'desc')
            ->first();
        $heroStore = $heroCoupon ? Store::find($heroCoupon->store_id) : null;

        return view('shop.index', compact('products', 'stores', 'featuredStores', 'categories', 'heroCoupon', 'heroStore'));
    }

    /** A single store's storefront page. */
    public function store(string $slug)
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $products = Product::active()
            ->where('store_id', (string) $store->_id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Active coupons + promotions for this store
        $activeDiscounts = Discount::active()
            ->where('store_id', (string) $store->_id)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->get();

        $promotions = Promotion::active()
            ->where('store_id', (string) $store->_id)
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->get();

        if ($promotions->isNotEmpty()) {
            Promotion::whereIn('_id', $promotions->pluck('_id')->all())->increment('view_count');
        }

        return view('shop.store', compact('store', 'products', 'activeDiscounts', 'promotions'));
    }

    /** Single product detail page. */
    public function product(string $id)
    {
        $product = Product::where('is_active', true)->findOrFail($id);
        $store   = Store::find($product->store_id);

        $similar = Product::active()
            ->where('store_id', (string) $product->store_id)
            ->where('_id', '!=', (string) $product->_id)
            ->limit(4)
            ->get();

        // Reviews
        $reviews    = Review::where('product_id', (string) $product->_id)
            ->orderBy('created_at', 'desc')
            ->get();
        $avgRating  = $reviews->isEmpty() ? null : round($reviews->avg('rating'), 1);

        // Has the current user purchased this? (eligible to review)
        $userId       = (string) auth()->user()->_id;
        $hasPurchased = Order::where('user_id', $userId)
            ->where('items.product_id', (string) $product->_id)
            ->exists();
        $myReview     = Review::where('user_id', $userId)
            ->where('product_id', (string) $product->_id)
            ->first();

        // Wishlist state
        $inWishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', (string) $product->_id)
            ->exists();

        return view('shop.product', compact(
            'product', 'store', 'similar',
            'reviews', 'avgRating', 'hasPurchased', 'myReview', 'inWishlist'
        ));
    }
}
