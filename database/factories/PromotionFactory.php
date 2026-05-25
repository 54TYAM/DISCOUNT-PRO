<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Promotion> */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->words(3, true),
            'description'    => fake()->paragraph(),
            'type'           => Promotion::TYPE_FLASH_SALE,
            'discount_id'    => null,
            'banner_color'   => fake()->randomElement(['violet', 'emerald', 'amber', 'rose', 'sky']),
            'target_segment' => Promotion::SEGMENT_ALL,
            'start_at'       => null,
            'end_at'         => null,
            'is_active'      => true,
            'view_count'     => 0,
            'created_by'     => null,
        ];
    }

    public function flashSale(): static
    {
        return $this->state(['type' => Promotion::TYPE_FLASH_SALE]);
    }

    public function seasonal(): static
    {
        return $this->state(['type' => Promotion::TYPE_SEASONAL]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
