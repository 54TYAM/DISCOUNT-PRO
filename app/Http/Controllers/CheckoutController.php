<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupon,
    ) {}

    public function show()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('shop.index')->with('info', 'Your cart is empty.');
        }

        $lines      = $this->cart->lines();
        $subtotal   = $this->cart->subtotal();
        $applied    = Session::get('cart.applied_coupon'); // ['code', 'savings', 'final_total']
        $storeIds   = $this->cart->storeIds();
        $storeMap   = Store::whereIn('_id', $storeIds)->get(['_id', 'name'])
            ->keyBy(fn ($s) => (string) $s->_id);

        $relevantCoupons = $this->findRelevantCoupons($storeIds, $subtotal);

        return view('checkout.show', compact('lines', 'subtotal', 'applied', 'storeMap', 'relevantCoupons'));
    }

    /**
     * Coupons that COULD apply to this cart — one or more cart items belong to the coupon's store,
     * the coupon is in its active window, hasn't hit its cap, and the user hasn't exhausted
     * their per-user allowance. Each entry is annotated with `applicable` / `needed_more` so
     * the view can render an "unlock at ₹X more" hint when the subtotal is below min_order_value.
     *
     * Sorted by best deal first: applicable coupons come first, then by estimated savings desc.
     */
    private function findRelevantCoupons(array $storeIds, float $subtotal): array
    {
        if (empty($storeIds)) return [];

        $now = now();

        $candidates = Discount::active()
            ->whereIn('store_id', $storeIds)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now))
            ->orderBy('value', 'desc')
            ->limit(20)
            ->get();

        if ($candidates->isEmpty()) return [];

        // Bulk-load per-user usage counts so we can filter exhausted ones
        $userId = (string) auth()->user()->_id;
        $myUses = DiscountUsage::whereIn('discount_id', $candidates->pluck('_id')->map(fn ($id) => (string) $id)->all())
            ->where('user_id', $userId)
            ->get(['discount_id'])
            ->groupBy('discount_id')
            ->map(fn ($g) => $g->count());

        $storeMap = Store::whereIn('_id', $storeIds)->get(['_id', 'name'])
            ->keyBy(fn ($s) => (string) $s->_id);

        $rows = [];
        foreach ($candidates as $coupon) {
            // Global usage cap reached
            if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) continue;
            // Per-user cap reached
            if (($myUses->get((string) $coupon->_id, 0)) >= ($coupon->uses_per_user ?: 1)) continue;

            $needsMore = max(0.0, ((float) ($coupon->min_order_value ?? 0)) - $subtotal);
            $applicable = $needsMore <= 0.001;

            // Quick savings estimate (best-effort; the real number comes from CouponService)
            $estimatedSavings = match ($coupon->type) {
                'percentage'    => $subtotal * ((float) $coupon->value / 100),
                'fixed'         => min((float) $coupon->value, $subtotal),
                'bogo'          => $subtotal / 2,
                'free_shipping' => 99.0,
                'tiered'        => $this->estimateTiered($coupon->tiered_rules ?? [], $subtotal),
                default         => 0.0,
            };

            $rows[] = [
                'coupon'             => $coupon,
                'store_name'         => $storeMap->get((string) $coupon->store_id)?->name ?? 'Store',
                'applicable'         => $applicable,
                'needs_more'         => round($needsMore, 0),
                'estimated_savings'  => round($estimatedSavings, 0),
            ];
        }

        // Applicable coupons first, then by estimated savings desc
        usort($rows, function ($a, $b) {
            if ($a['applicable'] !== $b['applicable']) return $a['applicable'] ? -1 : 1;
            return $b['estimated_savings'] <=> $a['estimated_savings'];
        });

        return array_slice($rows, 0, 6); // cap at 6 to keep UI compact
    }

    /** Pick the best matching tier for a given subtotal — mirrors CouponService::tieredSavings. */
    private function estimateTiered(array $rules, float $subtotal): float
    {
        $best = 0.0;
        foreach (collect($rules)->sortByDesc('min') as $rule) {
            if ($subtotal >= (float) ($rule['min'] ?? 0)) {
                return $subtotal * ((float) ($rule['discount_pct'] ?? 0) / 100);
            }
        }
        return $best;
    }

    /** AJAX: validate coupon against the current cart and persist the preview. */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:30']]);

        $subtotal = $this->cart->subtotal();
        if ($subtotal <= 0) {
            return response()->json(['valid' => false, 'error' => 'Your cart is empty.'], 422);
        }

        $result = $this->coupon->validate(
            $request->code,
            $subtotal,
            auth()->user(),
            $this->cart->couponPayload(),
        );

        if (! $result['valid']) {
            Session::forget('cart.applied_coupon');
            return response()->json(['valid' => false, 'error' => $result['error']], 422);
        }

        $applied = [
            'code'        => $result['discount']->code,
            'title'       => $result['discount']->title,
            'discount_id' => (string) $result['discount']->_id,
            'savings'     => $result['savings'],
            'final_total' => $result['final_total'],
        ];
        Session::put('cart.applied_coupon', $applied);

        return response()->json([
            'valid'       => true,
            'code'        => $applied['code'],
            'title'       => $applied['title'],
            'savings'     => $applied['savings'],
            'final_total' => $applied['final_total'],
        ]);
    }

    public function removeCoupon()
    {
        Session::forget('cart.applied_coupon');
        return back()->with('info', 'Coupon removed.');
    }

    /** Place the order. Re-validates the coupon atomically via CouponService. */
    public function place(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $lines    = $this->cart->lines();
        $subtotal = $this->cart->subtotal();
        $applied  = Session::get('cart.applied_coupon');

        // ── Stock check (best-effort — MongoDB standalone has no transactions) ──
        // Re-load each product to get the live stock; reject if any item is short.
        $productIds = array_map(fn ($l) => (string) $l['product']->_id, $lines);
        $live       = Product::whereIn('_id', $productIds)->get()->keyBy(fn ($p) => (string) $p->_id);

        foreach ($lines as $line) {
            $live_p = $live->get((string) $line['product']->_id);
            if (! $live_p || ! $live_p->is_active) {
                return redirect()->route('cart.show')
                    ->with('error', "«{$line['product']->name}» is no longer available. Please remove it from your cart.");
            }
            // Only enforce stock when the product tracks it (stock !== null && > 0 means tracked)
            if (isset($live_p->stock) && $live_p->stock !== null && $live_p->stock < $line['qty']) {
                return redirect()->route('cart.show')
                    ->with('error', "Only {$live_p->stock} of «{$live_p->name}» left in stock — please reduce the quantity.");
            }
        }

        $savings    = 0.0;
        $discountId = null;
        $code       = null;

        if ($applied) {
            $result = $this->coupon->validate(
                $applied['code'],
                $subtotal,
                auth()->user(),
                $this->cart->couponPayload(),
            );
            if (! $result['valid']) {
                Session::forget('cart.applied_coupon');
                return redirect()->route('checkout.show')
                    ->with('error', 'Coupon is no longer valid: ' . $result['error']);
            }
            // Apply (records DiscountUsage and increments used_count)
            $applyResult = $this->coupon->apply($result['discount'], $subtotal, auth()->user());
            if (! ($applyResult['applied'] ?? false)) {
                return redirect()->route('checkout.show')
                    ->with('error', $applyResult['error'] ?? 'Could not apply coupon.');
            }
            $savings    = $applyResult['savings'];
            $discountId = (string) $result['discount']->_id;
            $code       = $applyResult['code'];
        }

        $orderItems = array_map(fn ($line) => [
            'product_id'   => (string) $line['product']->_id,
            'product_name' => $line['product']->name,
            'store_id'     => (string) $line['product']->store_id,
            'qty'          => $line['qty'],
            'unit_price'   => (float) $line['product']->price,
            'line_total'   => $line['line_total'],
        ], $lines);

        $shipping = 0.0;
        $total    = max(0, $subtotal - $savings) + $shipping;

        $order = Order::create([
            'user_id'         => (string) auth()->user()->_id,
            'order_number'    => strtoupper(Str::random(10)),
            'items'           => $orderItems,
            'subtotal'        => $subtotal,
            'discount_code'   => $code,
            'discount_id'     => $discountId,
            'discount_amount' => $savings,
            'shipping_fee'    => $shipping,
            'total'           => $total,
            'status'          => Order::STATUS_PLACED,
            'placed_at'       => now(),
        ]);

        // Decrement stock for every ordered line (atomic per-doc via $inc)
        foreach ($lines as $line) {
            Product::where('_id', (string) $line['product']->_id)->decrement('stock', $line['qty']);
        }

        // ── Notifications ────────────────────────────────────────────────
        // Notify the customer
        Notification::notify((string) auth()->user()->_id, [
            'type'  => 'order_placed',
            'title' => "Order #{$order->order_number} placed",
            'body'  => '₹' . number_format($order->total, 0) . ' · ' . count($orderItems) . ' items',
            'link'  => route('orders.show', (string) $order->_id),
            'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2',
            'color' => 'brand',
        ]);

        // Notify each store-owner whose products are in the order
        $storeIds = array_unique(array_map(fn ($i) => $i['store_id'], $orderItems));
        $stores   = Store::whereIn('_id', $storeIds)->get(['_id', 'owner_id', 'name']);
        foreach ($stores as $store) {
            if (! $store->owner_id) continue;
            Notification::notify((string) $store->owner_id, [
                'type'  => 'new_order',
                'title' => "New order: #{$order->order_number}",
                'body'  => 'Total ' . '₹' . number_format($order->total, 0) . ' — open to fulfill',
                'link'  => route('admin.orders.show', (string) $order->_id),
                'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2',
                'color' => 'emerald',
            ]);
        }

        $this->cart->clear();

        return redirect()->route('orders.show', (string) $order->_id)
            ->with('success', "Order #{$order->order_number} placed successfully!");
    }
}
