<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $isAdmin   = $user->isAdmin();
        $storeId   = $user->store?->_id ? (string) $user->store->_id : null;

        // Scope helper for managers vs admins
        $scope = function ($query) use ($isAdmin, $storeId) {
            if ($isAdmin) return $query;
            return $query->where('store_id', $storeId);
        };

        $stats = [
            'active_discounts'  => $scope(Discount::active())->count(),
            'total_discounts'   => $scope(Discount::query())->count(),
            'total_products'    => $scope(Product::query())->count(),
            'total_promotions'  => $scope(Promotion::active())->count(),
            'revenue_saved'     => $this->revenueSavedFor($scope),
            'total_usages'      => $this->usageCountFor($scope),
            'usages_today'      => $this->usagesTodayFor($scope),
            'total_users'       => $isAdmin ? User::count() : null,
            'total_stores'      => $isAdmin ? Store::count() : null,
        ];

        // ── Week-over-week trends ───────────────────────────────────────────
        $scopedDiscountIds = $scope(Discount::query())->get(['_id'])->map(fn ($d) => (string) $d->_id)->all();
        $trends = $this->computeTrends($scopedDiscountIds, $isAdmin);

        // Discount IDs in scope (for usage queries)
        $scopedDiscountIds = $scope(Discount::query())->get(['_id'])->map(fn ($d) => (string) $d->_id)->all();

        $rawUsages = DiscountUsage::when(! $isAdmin, fn ($q) => $q->whereIn('discount_id', $scopedDiscountIds))
            ->orderBy('used_at', 'desc')->limit(5)->get();

        $discountIds = $rawUsages->pluck('discount_id')->filter()->unique()->values()->all();
        $userIds     = $rawUsages->pluck('user_id')->filter()->unique()->values()->all();

        $discountMap = Discount::whereIn('_id', $discountIds)->get(['_id', 'code'])->keyBy(fn ($d) => (string) $d->_id);
        $userMap     = User::whereIn('_id', $userIds)->get(['_id', 'name'])->keyBy(fn ($u) => (string) $u->_id);

        $recentUsages = $rawUsages->map(fn ($u) => [
            'order_id'         => $u->order_id,
            'discount_code'    => $discountMap->get((string) $u->discount_id)?->code ?? 'N/A',
            'user_name'        => $userMap->get((string) $u->user_id)?->name ?? 'Unknown',
            'discount_applied' => $u->discount_applied,
            'used_at'          => $u->used_at,
        ]);

        $expiringDiscounts = $scope(Discount::active())
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->orderBy('end_date')
            ->limit(4)
            ->get();

        $topDiscounts = $scope(Discount::query())
            ->orderBy('used_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'trends', 'recentUsages', 'expiringDiscounts', 'topDiscounts'));
    }

    /**
     * Compares last 7 days vs the prior 7 days and returns percentage deltas
     * for the three rate-of-change stats the dashboard cards display.
     */
    private function computeTrends(array $scopedDiscountIds, bool $isAdmin): array
    {
        $thisStart = now()->subDays(7);
        $prevStart = now()->subDays(14);

        $base = fn () => $isAdmin
            ? DiscountUsage::query()
            : DiscountUsage::whereIn('discount_id', $scopedDiscountIds);

        $thisUses    = (clone $base())->where('used_at', '>=', $thisStart)->count();
        $prevUses    = (clone $base())->whereBetween('used_at', [$prevStart, $thisStart])->count();
        $thisSavings = (float) (clone $base())->where('used_at', '>=', $thisStart)->sum('discount_applied');
        $prevSavings = (float) (clone $base())->whereBetween('used_at', [$prevStart, $thisStart])->sum('discount_applied');

        $pct = function (float $now, float $before): ?int {
            if ($before <= 0) return $now > 0 ? 100 : null;
            return (int) round((($now - $before) / $before) * 100);
        };

        return [
            'uses_pct'    => $pct($thisUses, $prevUses),
            'savings_pct' => $pct($thisSavings, $prevSavings),
        ];
    }

    private function revenueSavedFor(callable $scope): float
    {
        $ids = $scope(Discount::query())->get(['_id'])->map(fn ($d) => (string) $d->_id)->all();
        if (empty($ids) && ! auth()->user()->isAdmin()) return 0.0;
        if (auth()->user()->isAdmin()) {
            return (float) DiscountUsage::sum('discount_applied');
        }
        return (float) DiscountUsage::whereIn('discount_id', $ids)->sum('discount_applied');
    }

    private function usageCountFor(callable $scope): int
    {
        $ids = $scope(Discount::query())->get(['_id'])->map(fn ($d) => (string) $d->_id)->all();
        if (auth()->user()->isAdmin()) {
            return DiscountUsage::count();
        }
        if (empty($ids)) return 0;
        return DiscountUsage::whereIn('discount_id', $ids)->count();
    }

    private function usagesTodayFor(callable $scope): int
    {
        $ids = $scope(Discount::query())->get(['_id'])->map(fn ($d) => (string) $d->_id)->all();
        $query = DiscountUsage::where('used_at', '>=', now()->startOfDay());
        if (! auth()->user()->isAdmin()) {
            if (empty($ids)) return 0;
            $query->whereIn('discount_id', $ids);
        }
        return $query->count();
    }
}
