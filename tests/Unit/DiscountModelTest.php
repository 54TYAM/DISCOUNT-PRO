<?php

namespace Tests\Unit;

use App\Models\Discount;
use Tests\TestCase;

/**
 * Unit tests for Discount model computed attributes.
 *
 * All tests create in-memory Discount instances (no DB writes) to verify
 * that the getStatusAttribute and getUsagePercentAttribute accessors
 * return the correct values for every possible scenario.
 */
class DiscountModelTest extends TestCase
{
    // ── getStatusAttribute ──────────────────────────────────────────────────

    public function test_status_is_paused_when_is_active_is_false(): void
    {
        $d = new Discount(['is_active' => false]);

        $this->assertEquals('paused', $d->status);
    }

    public function test_status_is_expired_when_end_date_is_in_the_past(): void
    {
        $d = new Discount([
            'is_active' => true,
            'end_date'  => now()->subDay(),
        ]);

        $this->assertEquals('expired', $d->status);
    }

    public function test_status_is_scheduled_when_start_date_is_in_the_future(): void
    {
        $d = new Discount([
            'is_active'  => true,
            'start_date' => now()->addDay(),
            'end_date'   => null,
        ]);

        $this->assertEquals('scheduled', $d->status);
    }

    public function test_status_is_exhausted_when_used_count_equals_max_uses(): void
    {
        $d = new Discount([
            'is_active'  => true,
            'start_date' => null,
            'end_date'   => null,
            'max_uses'   => 100,
            'used_count' => 100,
        ]);

        $this->assertEquals('exhausted', $d->status);
    }

    public function test_status_is_exhausted_when_used_count_exceeds_max_uses(): void
    {
        // Edge case: used_count somehow went past max_uses
        $d = new Discount([
            'is_active'  => true,
            'start_date' => null,
            'end_date'   => null,
            'max_uses'   => 50,
            'used_count' => 55,
        ]);

        $this->assertEquals('exhausted', $d->status);
    }

    public function test_status_is_active_for_a_healthy_unlimited_discount(): void
    {
        $d = new Discount([
            'is_active'  => true,
            'start_date' => null,
            'end_date'   => null,
            'max_uses'   => null,
            'used_count' => 0,
        ]);

        $this->assertEquals('active', $d->status);
    }

    public function test_status_is_active_when_within_valid_date_window(): void
    {
        $d = new Discount([
            'is_active'  => true,
            'start_date' => now()->subDay(),
            'end_date'   => now()->addDay(),
            'max_uses'   => 200,
            'used_count' => 99,
        ]);

        $this->assertEquals('active', $d->status);
    }

    public function test_status_precedence_inactive_beats_expired(): void
    {
        // is_active = false should return 'paused' even when the end_date is also past
        $d = new Discount([
            'is_active' => false,
            'end_date'  => now()->subYear(),
        ]);

        $this->assertEquals('paused', $d->status);
    }

    // ── getUsagePercentAttribute ─────────────────────────────────────────────

    public function test_usage_percent_is_zero_when_max_uses_is_null(): void
    {
        $d = new Discount(['max_uses' => null, 'used_count' => 50]);

        $this->assertEquals(0.0, $d->usage_percent);
    }

    public function test_usage_percent_is_zero_when_max_uses_is_zero(): void
    {
        // Avoid division-by-zero: max_uses = 0 is treated same as null
        $d = new Discount(['max_uses' => 0, 'used_count' => 0]);

        $this->assertEquals(0.0, $d->usage_percent);
    }

    public function test_usage_percent_calculates_correctly(): void
    {
        $d = new Discount(['max_uses' => 200, 'used_count' => 50]);

        $this->assertEquals(25.0, $d->usage_percent);
    }

    public function test_usage_percent_at_full_capacity(): void
    {
        $d = new Discount(['max_uses' => 100, 'used_count' => 100]);

        $this->assertEquals(100.0, $d->usage_percent);
    }

    public function test_usage_percent_rounds_to_one_decimal_place(): void
    {
        // 1 / 3 = 33.333... → should round to 33.3
        $d = new Discount(['max_uses' => 3, 'used_count' => 1]);

        $this->assertEquals(33.3, $d->usage_percent);
    }

    public function test_usage_percent_at_zero_uses(): void
    {
        $d = new Discount(['max_uses' => 100, 'used_count' => 0]);

        $this->assertEquals(0.0, $d->usage_percent);
    }
}
