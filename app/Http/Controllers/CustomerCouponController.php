<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;

/**
 * Customer-facing coupons hub. Lists every active coupon across the platform,
 * lets customers filter & search, shows their personal savings stats, and
 * surfaces their coupon redemption history.
 */
class CustomerCouponController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ── Browse: active, in-window coupons across all stores ─────────────
        $query = Discount::active()
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()));

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $upper = strtoupper($search);
            $query->where(function ($q) use ($upper, $search) {
                $q->where('code', 'like', '%' . $upper . '%')
                  ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $coupons = $query->orderBy('created_at', 'desc')->limit(48)->get();

        // Bulk-load store info for the coupon cards (no N+1)
        $storeIds = $coupons->pluck('store_id')->filter()->unique()->values()->all();
        $stores   = Store::whereIn('_id', $storeIds)
            ->get(['_id', 'name', 'slug', 'banner_color'])
            ->keyBy(fn ($s) => (string) $s->_id);

        // ── Customer savings stats (across their own orders) ────────────────
        $userId       = (string) $user->_id;
        $totalSavings = (float) Order::where('user_id', $userId)->sum('discount_amount');
        $couponsUsed  = Order::where('user_id', $userId)->where('discount_amount', '>', 0)->count();
        $totalOrders  = Order::where('user_id', $userId)->count();

        // ── Recent usage history (last 8) ───────────────────────────────────
        $recentUses = DiscountUsage::where('user_id', $userId)
            ->orderBy('used_at', 'desc')
            ->limit(8)
            ->get();

        $usedDiscountIds = $recentUses->pluck('discount_id')->filter()->unique()->values()->all();
        $usedDiscounts   = Discount::whereIn('_id', $usedDiscountIds)
            ->get(['_id', 'code', 'title', 'store_id'])
            ->keyBy(fn ($d) => (string) $d->_id);

        return view('coupons.index', compact(
            'coupons', 'stores', 'totalSavings', 'couponsUsed', 'totalOrders', 'recentUses', 'usedDiscounts'
        ));
    }
}
