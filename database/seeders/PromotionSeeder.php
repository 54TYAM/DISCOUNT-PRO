<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Promotion;
use App\Models\Store;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::truncate();

        $now = now();

        // Build a [store_id => first_discount] map so each store gets a sample promo
        $stores    = Store::all();
        $discounts = Discount::all()->groupBy('store_id');

        $promotionsByStore = [
            'Aurora Electronics' => [
                'name'         => 'Tech Tuesday Flash',
                'description'  => 'Mid-week flash savings on premium electronics.',
                'type'         => Promotion::TYPE_FLASH_SALE,
                'banner_color' => 'sky',
                'segment'      => Promotion::SEGMENT_ALL,
            ],
            'Nomad Apparel' => [
                'name'         => 'Summer Style Drop',
                'description'  => 'Curated summer collection with site-wide savings.',
                'type'         => Promotion::TYPE_SEASONAL,
                'banner_color' => 'rose',
                'segment'      => Promotion::SEGMENT_ALL,
            ],
            'Hearth & Kettle' => [
                'name'         => 'Home Refresh Sale',
                'description'  => 'Refresh your kitchen and living space for less.',
                'type'         => Promotion::TYPE_SEASONAL,
                'banner_color' => 'amber',
                'segment'      => Promotion::SEGMENT_RETURNING,
            ],
            'Glow Beauty' => [
                'name'         => 'Glow Loyalty Rewards',
                'description'  => 'Exclusive savings for our returning beauty customers.',
                'type'         => Promotion::TYPE_LOYALTY,
                'banner_color' => 'violet',
                'segment'      => Promotion::SEGMENT_HIGH_VALUE,
            ],
        ];

        $created = 0;
        foreach ($stores as $store) {
            $config = $promotionsByStore[$store->name] ?? null;
            if (! $config) continue;

            // Link the promotion to the first discount for that store
            $linkedDiscount = ($discounts->get((string) $store->_id) ?? collect())->first();

            Promotion::create([
                'store_id'       => (string) $store->_id,
                'name'           => $config['name'],
                'description'    => $config['description'],
                'type'           => $config['type'],
                'discount_id'    => $linkedDiscount ? (string) $linkedDiscount->_id : null,
                'banner_color'   => $config['banner_color'],
                'target_segment' => $config['segment'],
                'start_at'       => $now->copy()->subDays(10),
                'end_at'         => $now->copy()->addDays(20),
                'is_active'      => true,
                'view_count'     => rand(800, 5000),
                'created_by'     => (string) $store->owner_id,
            ]);
            $created++;
        }

        $this->command->info("Created {$created} store-scoped promotions.");
    }
}
