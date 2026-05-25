<?php

namespace Tests\Unit;

use App\Models\Discount;
use App\Services\CouponService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Unit tests for CouponService's private calculation methods.
 *
 * We use ReflectionMethod to access calculateSavings() and tieredSavings()
 * without going through the full validate/apply flow (which requires DB).
 * Model instances are created in-memory — no MongoDB connection is needed.
 */
class CouponServiceTest extends TestCase
{
    private CouponService $service;
    private ReflectionMethod $calculateSavings;
    private ReflectionMethod $tieredSavings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CouponService();

        $this->calculateSavings = new ReflectionMethod(CouponService::class, 'calculateSavings');
        $this->calculateSavings->setAccessible(true);

        $this->tieredSavings = new ReflectionMethod(CouponService::class, 'tieredSavings');
        $this->tieredSavings->setAccessible(true);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Build an in-memory Discount model with the given attributes (no DB save). */
    private function makeDiscount(array $attrs): Discount
    {
        $d = new Discount();
        foreach ($attrs as $key => $value) {
            $d->$key = $value;
        }
        return $d;
    }

    // ── calculateSavings — percentage ────────────────────────────────────────

    public function test_percentage_discount_returns_correct_savings(): void
    {
        $discount = $this->makeDiscount(['type' => 'percentage', 'value' => 20.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 1000.0);

        $this->assertEquals(200.0, $result);
    }

    public function test_percentage_discount_on_fractional_order_total(): void
    {
        $discount = $this->makeDiscount(['type' => 'percentage', 'value' => 15.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 299.0);

        $this->assertEqualsWithDelta(44.85, $result, 0.001);
    }

    public function test_percentage_discount_at_100_percent_removes_full_total(): void
    {
        $discount = $this->makeDiscount(['type' => 'percentage', 'value' => 100.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 500.0);

        $this->assertEquals(500.0, $result);
    }

    // ── calculateSavings — fixed ─────────────────────────────────────────────

    public function test_fixed_discount_returns_value_when_less_than_total(): void
    {
        $discount = $this->makeDiscount(['type' => 'fixed', 'value' => 200.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 1000.0);

        $this->assertEquals(200.0, $result);
    }

    public function test_fixed_discount_is_capped_at_order_total(): void
    {
        // Coupon value exceeds order total → savings = order total (can't save more than you spend)
        $discount = $this->makeDiscount(['type' => 'fixed', 'value' => 500.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 300.0);

        $this->assertEquals(300.0, $result);
    }

    public function test_fixed_discount_exact_match_on_order_total(): void
    {
        $discount = $this->makeDiscount(['type' => 'fixed', 'value' => 250.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 250.0);

        $this->assertEquals(250.0, $result);
    }

    // ── calculateSavings — bogo ──────────────────────────────────────────────

    public function test_bogo_discount_returns_half_of_order_total(): void
    {
        $discount = $this->makeDiscount(['type' => 'bogo', 'value' => 0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 800.0);

        $this->assertEquals(400.0, $result);
    }

    public function test_bogo_discount_on_odd_total_splits_correctly(): void
    {
        $discount = $this->makeDiscount(['type' => 'bogo', 'value' => 0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 999.0);

        $this->assertEqualsWithDelta(499.5, $result, 0.001);
    }

    // ── calculateSavings — free_shipping ─────────────────────────────────────

    public function test_free_shipping_returns_shipping_fee_constant(): void
    {
        $discount = $this->makeDiscount(['type' => 'free_shipping', 'value' => 0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 500.0);

        $this->assertEquals(CouponService::SHIPPING_FEE, $result);
        $this->assertEquals(99.0, $result);
    }

    public function test_free_shipping_capped_at_order_total_when_total_is_smaller(): void
    {
        // Order total (₹50) is less than shipping fee (₹99) — savings can't exceed total
        $discount = $this->makeDiscount(['type' => 'free_shipping', 'value' => 0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 50.0);

        $this->assertEquals(50.0, $result);
    }

    // ── calculateSavings — unknown type ─────────────────────────────────────

    public function test_unknown_discount_type_returns_zero(): void
    {
        $discount = $this->makeDiscount(['type' => 'mystery_box', 'value' => 100.0]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 500.0);

        $this->assertEquals(0.0, $result);
    }

    // ── tieredSavings ────────────────────────────────────────────────────────

    public function test_tiered_picks_highest_matching_tier(): void
    {
        // Order 1500 → matches 500 (5%) and 1000 (10%) → highest matching is 10%
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [
                ['min' => 500,  'discount_pct' => 5],
                ['min' => 1000, 'discount_pct' => 10],
                ['min' => 2000, 'discount_pct' => 15],
            ],
        ]);

        $result = $this->tieredSavings->invoke($this->service, $discount, 1500.0);

        $this->assertEquals(150.0, $result);
    }

    public function test_tiered_picks_top_tier_when_all_match(): void
    {
        // Order 2500 matches all three tiers → uses 15%
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [
                ['min' => 500,  'discount_pct' => 5],
                ['min' => 1000, 'discount_pct' => 10],
                ['min' => 2000, 'discount_pct' => 15],
            ],
        ]);

        $result = $this->tieredSavings->invoke($this->service, $discount, 2500.0);

        $this->assertEqualsWithDelta(375.0, $result, 0.001);
    }

    public function test_tiered_returns_zero_when_total_below_lowest_min(): void
    {
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [
                ['min' => 1000, 'discount_pct' => 10],
                ['min' => 2000, 'discount_pct' => 15],
            ],
        ]);

        $result = $this->tieredSavings->invoke($this->service, $discount, 200.0);

        $this->assertEquals(0.0, $result);
    }

    public function test_tiered_returns_zero_for_empty_rules(): void
    {
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [],
        ]);

        $result = $this->tieredSavings->invoke($this->service, $discount, 500.0);

        $this->assertEquals(0.0, $result);
    }

    public function test_tiered_exact_boundary_match_applies_tier(): void
    {
        // Order total exactly equals the tier minimum → tier should apply
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [
                ['min' => 1000, 'discount_pct' => 10],
            ],
        ]);

        $result = $this->tieredSavings->invoke($this->service, $discount, 1000.0);

        $this->assertEquals(100.0, $result);
    }

    public function test_tiered_is_dispatched_correctly_via_calculate_savings(): void
    {
        // calculateSavings with type='tiered' should delegate to tieredSavings
        $discount = $this->makeDiscount([
            'type'         => 'tiered',
            'tiered_rules' => [
                ['min' => 500, 'discount_pct' => 10],
            ],
        ]);

        $result = $this->calculateSavings->invoke($this->service, $discount, 700.0);

        $this->assertEquals(70.0, $result);
    }
}
