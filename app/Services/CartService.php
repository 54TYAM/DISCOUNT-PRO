<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed shopping cart. Items stored as ['product_id' => qty].
 * Hydrates product details from the DB on read so prices always reflect
 * the current product state.
 */
class CartService
{
    private const KEY = 'cart.items';

    /** Add (or increase qty of) a product in the cart. */
    public function add(string $productId, int $qty = 1): void
    {
        $items = Session::get(self::KEY, []);
        $items[$productId] = ($items[$productId] ?? 0) + max(1, $qty);
        Session::put(self::KEY, $items);
    }

    public function setQty(string $productId, int $qty): void
    {
        $items = Session::get(self::KEY, []);
        if ($qty <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = $qty;
        }
        Session::put(self::KEY, $items);
    }

    public function remove(string $productId): void
    {
        $items = Session::get(self::KEY, []);
        unset($items[$productId]);
        Session::put(self::KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
        Session::forget('cart.applied_coupon');
    }

    /** Returns enriched cart: each line = ['product' => Product, 'qty', 'line_total']. */
    public function lines(): array
    {
        $items = Session::get(self::KEY, []);
        if (empty($items)) return [];

        $products = Product::whereIn('_id', array_keys($items))
            ->get()
            ->keyBy(fn ($p) => (string) $p->_id);

        $lines = [];
        foreach ($items as $id => $qty) {
            $p = $products->get((string) $id);
            if (! $p || ! $p->is_active) continue;
            $lines[] = [
                'product'    => $p,
                'qty'        => (int) $qty,
                'line_total' => round((float) $p->price * (int) $qty, 2),
            ];
        }
        return $lines;
    }

    /** Total quantity of items (badge count). */
    public function count(): int
    {
        return array_sum(Session::get(self::KEY, []));
    }

    public function isEmpty(): bool
    {
        return empty(Session::get(self::KEY, []));
    }

    public function subtotal(): float
    {
        $sum = 0;
        foreach ($this->lines() as $line) {
            $sum += $line['line_total'];
        }
        return round($sum, 2);
    }

    /** Distinct store IDs represented in the cart. */
    public function storeIds(): array
    {
        $ids = [];
        foreach ($this->lines() as $line) {
            $sid = (string) $line['product']->store_id;
            if ($sid) $ids[$sid] = true;
        }
        return array_keys($ids);
    }

    /** Cart items formatted for CouponService (product targeting). */
    public function couponPayload(): array
    {
        return array_map(fn ($line) => [
            'product_id' => (string) $line['product']->_id,
            'category'   => $line['product']->category,
            'store_id'   => (string) $line['product']->store_id,
        ], $this->lines());
    }
}
