<?php

namespace Database\Factories;

use App\Models\DiscountUsage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DiscountUsage> */
class DiscountUsageFactory extends Factory
{
    protected $model = DiscountUsage::class;

    public function definition(): array
    {
        $original  = fake()->numberBetween(500, 5000);
        $applied   = round($original * fake()->randomFloat(2, 0.05, 0.5), 2);

        return [
            'discount_id'      => (string) fake()->uuid(),
            'user_id'          => (string) fake()->uuid(),
            'order_id'         => (string) Str::uuid(),
            'original_amount'  => $original,
            'discount_applied' => $applied,
            'final_amount'     => $original - $applied,
            'used_at'          => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
