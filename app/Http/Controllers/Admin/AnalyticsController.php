<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountUsage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /** Human-readable label for each discount type (used by the doughnut chart). */
    private const TYPE_LABELS = [
        'percentage'    => '% Off',
        'fixed'         => 'Fixed',
        'bogo'          => 'BOGO',
        'free_shipping' => 'Free Shipping',
        'tiered'        => 'Tiered',
        'unknown'       => 'Unknown',
    ];

    /**
     * Returns the list of discount IDs the current user is allowed to see analytics for.
     * Admins see everything; managers only see their own store's discounts.
     * Returns `null` to signal "no filtering" (admin), or an array of IDs (manager).
     */
    private function scopedDiscountIds(): ?array
    {
        $user = auth()->user();
        if ($user->isAdmin()) return null;

        $storeId = $user->store?->_id ? (string) $user->store->_id : null;
        if (! $storeId) return [];

        return Discount::where('store_id', $storeId)
            ->get(['_id'])
            ->map(fn ($d) => (string) $d->_id)
            ->all();
    }

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        if (! in_array($days, [7, 30, 90], true)) $days = 30;

        $periodStart = now()->subDays($days - 1)->startOfDay();
        $prevStart   = now()->subDays(($days * 2) - 1)->startOfDay();
        $scopeIds    = $this->scopedDiscountIds();

        // ── All-time overview (scoped) ─────────────────────────────────────
        $allUsages = $this->scopedUsageQuery($scopeIds);

        $totalSaved      = (float) (clone $allUsages)->sum('discount_applied');
        $totalUses       = (clone $allUsages)->count();
        $totalDiscounts  = $this->scopedDiscountQuery($scopeIds)->count();
        $activeDiscounts = $this->scopedDiscountQuery($scopeIds)->where('is_active', true)->count();
        $uniqueUsers     = (clone $allUsages)->distinct('user_id')->count('user_id');
        $avgOrderValue   = (float) ((clone $allUsages)->avg('original_amount') ?? 0);

        // ── Period stats (one query, grouped in PHP) ───────────────────────
        $periodUsages = $this->scopedUsageQuery($scopeIds)
            ->where('used_at', '>=', $periodStart)
            ->get();

        $periodSaved    = round($periodUsages->sum('discount_applied'), 2);
        $periodUses     = $periodUsages->count();
        $periodAvgSaved = $periodUses > 0
            ? round($periodUsages->avg('discount_applied'), 2)
            : 0;

        // ── Previous period (for deltas) ────────────────────────────────────
        $prevUsages = $this->scopedUsageQuery($scopeIds)
            ->whereBetween('used_at', [$prevStart, $periodStart])
            ->get();

        $prevSaved = round($prevUsages->sum('discount_applied'), 2);
        $prevUses  = $prevUsages->count();

        $pct = function (float $now, float $before): ?int {
            if ($before <= 0) return $now > 0 ? 100 : null;
            return (int) round((($now - $before) / $before) * 100);
        };

        $periodDelta = [
            'saved_pct' => $pct($periodSaved, $prevSaved),
            'uses_pct'  => $pct($periodUses, $prevUses),
        ];

        // ── Daily chart data (uses + savings per day) ──────────────────────
        $usagesByDate = $periodUsages->groupBy(
            fn ($u) => Carbon::parse($u->used_at)->toDateString()
        );

        $dailyData = collect(range($days - 1, 0))->map(function ($daysAgo) use ($usagesByDate) {
            $date     = now()->subDays($daysAgo)->toDateString();
            $dayItems = $usagesByDate->get($date, collect());
            return [
                'date'  => $date,
                'label' => Carbon::parse($date)->format('M j'),
                'uses'  => $dayItems->count(),
                'saved' => round((float) $dayItems->sum('discount_applied'), 2),
            ];
        });

        // ── Type breakdown (uses + savings per discount type) ──────────────
        $usedDiscountIds = $periodUsages->pluck('discount_id')->filter()->unique()->values()->all();
        $discountTypeMap = Discount::whereIn('_id', $usedDiscountIds)
            ->get(['_id', 'type'])
            ->mapWithKeys(fn ($d) => [(string) $d->_id => $d->type]);

        $typeBreakdown = $periodUsages
            ->groupBy(fn ($u) => $discountTypeMap->get((string) $u->discount_id, 'unknown'))
            ->map(fn ($group) => [
                'uses'  => $group->count(),
                'saved' => round((float) $group->sum('discount_applied'), 2),
            ])
            ->sortByDesc(fn ($v) => $v['uses']);

        // Pre-compute clean arrays for the JS chart (no inline mapping in Blade)
        $typeChartLabels = $typeBreakdown->keys()
            ->map(fn ($k) => self::TYPE_LABELS[$k] ?? ucfirst((string) $k))
            ->values()
            ->all();
        $typeChartCounts = $typeBreakdown->pluck('uses')->values()->all();

        // ── Top 10 discounts (scoped) ──────────────────────────────────────
        $topDiscounts = $this->scopedDiscountQuery($scopeIds)
            ->orderBy('used_count', 'desc')
            ->limit(10)
            ->get();

        $topIds        = $topDiscounts->map(fn ($d) => (string) $d->_id)->all();
        $topSavingsMap = DiscountUsage::whereIn('discount_id', $topIds)
            ->get(['discount_id', 'discount_applied'])
            ->groupBy('discount_id')
            ->map(fn ($g) => round((float) $g->sum('discount_applied'), 2));

        // ── Recent activity (scoped) ───────────────────────────────────────
        $recentUsages = $this->scopedUsageQuery($scopeIds)
            ->orderBy('used_at', 'desc')
            ->limit(20)
            ->get();

        $recentDiscountIds = $recentUsages->pluck('discount_id')->filter()->unique()->values()->all();
        $recentDiscounts   = Discount::whereIn('_id', $recentDiscountIds)
            ->get(['_id', 'code', 'type'])
            ->mapWithKeys(fn ($d) => [(string) $d->_id => $d]);

        return view('admin.analytics.index', compact(
            'days',
            'totalSaved', 'totalUses', 'totalDiscounts', 'activeDiscounts',
            'uniqueUsers', 'avgOrderValue',
            'periodSaved', 'periodUses', 'periodAvgSaved', 'periodDelta',
            'dailyData', 'typeBreakdown', 'typeChartLabels', 'typeChartCounts',
            'topDiscounts', 'topSavingsMap',
            'recentUsages', 'recentDiscounts'
        ));
    }

    public function export(Request $request)
    {
        $days = (int) $request->get('days', 30);
        if (! in_array($days, [7, 30, 90], true)) $days = 30;

        $start    = now()->subDays($days - 1)->startOfDay();
        $scopeIds = $this->scopedDiscountIds();

        $usages = $this->scopedUsageQuery($scopeIds)
            ->where('used_at', '>=', $start)
            ->orderBy('used_at', 'desc')
            ->get();

        $discountIds = $usages->pluck('discount_id')->filter()->unique()->values()->all();
        $discounts   = Discount::whereIn('_id', $discountIds)
            ->get(['_id', 'code', 'type'])
            ->mapWithKeys(fn ($d) => [(string) $d->_id => $d]);

        $filename = 'discount-analytics-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($usages, $discounts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date', 'Order ID', 'Coupon Code', 'Type',
                'Original Amount (₹)', 'Discount Applied (₹)', 'Final Amount (₹)',
            ]);

            foreach ($usages as $u) {
                $disc = $discounts->get((string) $u->discount_id);
                fputcsv($handle, [
                    Carbon::parse($u->used_at)->format('Y-m-d H:i:s'),
                    $u->order_id,
                    $disc?->code ?? '—',
                    $disc?->type ?? '—',
                    number_format($u->original_amount, 2),
                    number_format($u->discount_applied, 2),
                    number_format($u->final_amount, 2),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Discount query scoped to user (admin = all, manager = own store). */
    private function scopedDiscountQuery(?array $scopeIds)
    {
        $q = Discount::query();
        if ($scopeIds === null) return $q;                  // admin
        if (empty($scopeIds))   return $q->whereRaw(['_id' => '__none__']); // manager with no discounts
        return $q->whereIn('_id', $scopeIds);
    }

    /** Usage query scoped to user. */
    private function scopedUsageQuery(?array $scopeIds)
    {
        $q = DiscountUsage::query();
        if ($scopeIds === null) return $q;                  // admin
        if (empty($scopeIds))   return $q->whereRaw(['discount_id' => '__none__']); // empty result
        return $q->whereIn('discount_id', $scopeIds);
    }
}
