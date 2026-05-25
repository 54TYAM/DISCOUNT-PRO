<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Discount> */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'title'           => fake()->catchPhrase(),
            'code'            => strtoupper(Str::random(8)),
            'description'     => fake()->sentence(),
            'type'            => Discount::TYPE_PERCENTAGE,
            'value'           => fake()->numberBetween(5, 50),
            'min_order_value' => 0,
            'max_uses'        => null,
            'uses_per_user'   => 1,
            'used_count'      => 0,
            'applicable_to'   => Discount::APPLIES_ALL,
            'target_ids'      => [],
            'target_label'    => 'All Products',
            'start_date'      => null,
            'end_date'        => null,
            'is_active'       => true,
            'created_by'      => null,
        ];
    }

    public function percentage(int $pct = 20): static
    {
        return $this->state(['type' => Discount::TYPE_PERCENTAGE, 'value' => $pct]);
    }

    public function fixed(int $amount = 100): static
    {
        return $this->state(['type' => Discount::TYPE_FIXED, 'value' => $amount]);
    }

    public function bogo(): static
    {
        return $this->state(['type' => Discount::TYPE_BOGO, 'value' => 0]);
    }

    public function freeShipping(): static
    {
        return $this->state(['type' => Discount::TYPE_FREE_SHIPPING, 'value' => 0]);
    }

    public function tiered(): static
    {
        return $this->state([
            'type'         => Discount::TYPE_TIERED,
            'value'        => 0,
            'tiered_rules' => [
                ['min' => 500,  'discount_pct' => 5],
                ['min' => 1000, 'discount_pct' => 10],
                ['min' => 2000, 'discount_pct' => 15],
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function expired(): static
    {
        return $this->state(['end_date' => now()->subDays(7)]);
    }

    public function scheduled(): static
    {
        return $this->state(['start_date' => now()->addDays(3)]);
    }
}
