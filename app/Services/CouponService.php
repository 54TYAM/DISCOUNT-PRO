<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\User;
use Illuminate\Support\Str;

class CouponService
{
    const SHIPPING_FEE = 99.0;

    /**
     * @param  array  $cartItems  Optional. Each item: ['product_id' => string, 'category' => string].
     *                            When non-empty, applies category/product targeting checks.
     *                            When empty, targeting is skipped (legacy behaviour).
     */
    public function validate(string $code, float $orderTotal, ?User $user = null, array $cartItems = []): array
    {
        $code     = strtoupper(trim($code));
        $discount = Discount::where('code', $code)->first();

        if (! $discount) {
            return $this->fail('This coupon code does not exist.');
        }

        if (! $discount->is_active) {
            return $this->fail('This coupon is currently inactive.');
        }

        $now = now();

        if ($discount->start_date && $discount->start_date->gt($now)) {
            return $this->fail(
                'This coupon activates on ' . $discount->start_date->format('M j, Y') . '.'
            );
        }

        if ($discount->end_date && $discount->end_date->lt($now)) {
            return $this->fail('This coupon has expired.');
        }

        if ($discount->max_uses && $discount->used_count >= $discount->max_uses) {
            return $this->fail('This coupon has reached its usage limit.');
        }

        if ($discount->min_order_value && $orderTotal < $discount->min_order_value) {
            return $this->fail(
                'A minimum order of ₹' . number_format($discount->min_order_value, 0) . ' is required.'
            );
        }

        if ($user) {
            $userUses = DiscountUsage::where('discount_id', (string) $discount->_id)
                ->where('user_id', (string) $user->_id)
                ->count();

            if ($userUses >= $discount->uses_per_user) {
                return $this->fail('You have already used this coupon the maximum number of times.');
            }
        }

        // Product / category targeting. Only enforced when the caller passes cart items.
        if (! empty($cartItems) && $discount->applicable_to !== 'all') {
            if (! $this->cartMatchesTarget($discount, $cartItems)) {
                $label = $discount->target_label ?: 'specific items';
                return $this->fail("This coupon only applies to {$label}.");
            }
        }

        // Store-scope check: if the discount belongs to a specific store, every cart
        // item must be from that store. Coupons with no store_id are platform-wide.
        if (! empty($cartItems) && $discount->store_id) {
            $cartStoreIds = collect($cartItems)->pluck('store_id')->filter()->unique()->all();
            $foreign = collect($cartStoreIds)->reject(fn ($id) => (string) $id === (string) $discount->store_id);
            if ($foreign->isNotEmpty()) {
                return $this->fail('This coupon only applies to items from one specific store. Please remove other items first.');
            }
        }

        $savings    = $this->calculateSavings($discount, $orderTotal);
        $finalTotal = max(0.0, $orderTotal - $savings);

        return [
            'valid'       => true,
            'discount'    => $discount,
            'savings'     => round($savings, 2),
            'final_total' => round($finalTotal, 2),
            'error'       => null,
        ];
    }

    /** Does at least one cart item match the discount's product/category target? */
    private function cartMatchesTarget(Discount $discount, array $cartItems): bool
    {
        $targetIds = $discount->target_ids ?? [];
        if (empty($targetIds)) return true; // mis-configured discount → don't block

        foreach ($cartItems as $item) {
            if ($discount->applicable_to === 'product' && in_array(($item['product_id'] ?? null), $targetIds, true)) {
                return true;
            }
            if ($discount->applicable_to === 'category' && in_array(($item['category'] ?? null), $targetIds, true)) {
                return true;
            }
        }
        return false;
    }

    public function apply(Discount $discount, float $orderTotal, User $user): array
    {
        // Re-validate atomically before applying. Prevents race where the discount
        // was valid in validate() but expired / hit its cap before apply() ran.
        $check = $this->validate($discount->code, $orderTotal, $user);
        if (! $check['valid']) {
            return [
                'applied' => false,
                'error'   => $check['error'],
            ];
        }

        // Use the freshly-loaded discount instance from re-validation
        $discount   = $check['discount'];
        $savings    = $check['savings'];
        $finalTotal = $check['final_total'];
        $orderId    = (string) Str::uuid();

        DiscountUsage::create([
            'discount_id'      => (string) $discount->_id,
            'user_id'          => (string) $user->_id,
            'order_id'         => $orderId,
            'original_amount'  => round($orderTotal, 2),
            'discount_applied' => round($savings, 2),
            'final_amount'     => round($finalTotal, 2),
            'used_at'          => now(),
        ]);

        $discount->increment('used_count');

        return [
            'applied'         => true,
            'order_id'        => $orderId,
            'code'            => $discount->code,
            'title'           => $discount->title,
            'original_amount' => round($orderTotal, 2),
            'savings'         => round($savings, 2),
            'final_total'     => round($finalTotal, 2),
        ];
    }

    private function calculateSavings(Discount $discount, float $orderTotal): float
    {
        return match ($discount->type) {
            'percentage'    => $orderTotal * ($discount->value / 100),
            'fixed'         => min((float) $discount->value, $orderTotal),
            'bogo'          => $orderTotal / 2,
            'free_shipping' => min(self::SHIPPING_FEE, $orderTotal),
            'tiered'        => $this->tieredSavings($discount, $orderTotal),
            default         => 0.0,
        };
    }

    private function tieredSavings(Discount $discount, float $orderTotal): float
    {
        $rules = collect($discount->tiered_rules ?? [])->sortByDesc('min');

        foreach ($rules as $rule) {
            if ($orderTotal >= (float) $rule['min']) {
                return $orderTotal * ((float) $rule['discount_pct'] / 100);
            }
        }

        return 0.0;
    }

    private function fail(string $message): array
    {
        return [
            'valid'       => false,
            'discount'    => null,
            'savings'     => 0.0,
            'final_total' => 0.0,
            'error'       => $message,
        ];
    }
}
