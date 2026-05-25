<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        Discount::truncate();

        $admin   = User::where('email', 'admin@discountpro.com')->first();
        $adminId = $admin ? (string) $admin->_id : null;
        $now     = now();

        $stores = Store::all()->keyBy('name');

        // Each discount is assigned to a store (where its products live)
        $rows = [
            ['store' => 'Aurora Electronics', 'data' => [
                'title' => 'Aurora Launch Offer', 'code' => 'AURORA15',
                'description' => 'Welcome offer: 15% off all electronics at Aurora.',
                'type' => Discount::TYPE_PERCENTAGE, 'value' => 15,
                'min_order_value' => 500, 'max_uses' => 1000, 'uses_per_user' => 2,
                'used_count' => 412, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Aurora products',
                'start_date' => $now->copy()->subDays(15), 'end_date' => $now->copy()->addDays(30),
                'is_active' => true,
            ]],
            ['store' => 'Aurora Electronics', 'data' => [
                'title' => 'Flat ₹500 Off Tech', 'code' => 'TECH500',
                'description' => 'Flat ₹500 off on orders above ₹2500 at Aurora.',
                'type' => Discount::TYPE_FIXED, 'value' => 500,
                'min_order_value' => 2500, 'max_uses' => 500, 'uses_per_user' => 1,
                'used_count' => 187, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Aurora products',
                'start_date' => $now->copy()->subDays(10), 'end_date' => $now->copy()->addDays(20),
                'is_active' => true,
            ]],
            ['store' => 'Nomad Apparel', 'data' => [
                'title' => 'Nomad Summer 25', 'code' => 'NOMAD25',
                'description' => '25% off all summer apparel at Nomad.',
                'type' => Discount::TYPE_PERCENTAGE, 'value' => 25,
                'min_order_value' => 800, 'max_uses' => 800, 'uses_per_user' => 2,
                'used_count' => 312, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Nomad products',
                'start_date' => $now->copy()->subDays(5), 'end_date' => $now->copy()->addDays(25),
                'is_active' => true,
            ]],
            ['store' => 'Nomad Apparel', 'data' => [
                'title' => 'BOGO Tees', 'code' => 'NOMAD_BOGO',
                'description' => 'Buy one tee, get the second 50% off at Nomad.',
                'type' => Discount::TYPE_BOGO, 'value' => 0,
                'min_order_value' => 499, 'max_uses' => 400, 'uses_per_user' => 1,
                'used_count' => 156, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Nomad products',
                'start_date' => $now->copy()->subDays(7), 'end_date' => $now->copy()->addDays(7),
                'is_active' => true,
            ]],
            ['store' => 'Hearth & Kettle', 'data' => [
                'title' => 'Hearth Welcome', 'code' => 'HEARTH10',
                'description' => '10% off your first home purchase at Hearth & Kettle.',
                'type' => Discount::TYPE_PERCENTAGE, 'value' => 10,
                'min_order_value' => 0, 'max_uses' => 2000, 'uses_per_user' => 1,
                'used_count' => 627, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Hearth products',
                'start_date' => $now->copy()->subDays(45), 'end_date' => $now->copy()->addDays(45),
                'is_active' => true,
            ]],
            ['store' => 'Hearth & Kettle', 'data' => [
                'title' => 'Free Shipping Week', 'code' => 'HEARTH_SHIP',
                'description' => 'Free shipping on Hearth orders above ₹299.',
                'type' => Discount::TYPE_FREE_SHIPPING, 'value' => 0,
                'min_order_value' => 299, 'max_uses' => 0, 'uses_per_user' => 5,
                'used_count' => 891, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Hearth products',
                'start_date' => $now->copy()->subDays(7), 'end_date' => $now->copy()->addDays(3),
                'is_active' => true,
            ]],
            ['store' => 'Glow Beauty', 'data' => [
                'title' => 'Glow Tiered Savings', 'code' => 'GLOW_TIER',
                'description' => 'Spend more, save more on Glow. Up to 15% off!',
                'type' => Discount::TYPE_TIERED, 'value' => 0,
                'tiered_rules' => [
                    ['min' => 500,  'discount_pct' => 5],
                    ['min' => 1000, 'discount_pct' => 10],
                    ['min' => 2000, 'discount_pct' => 15],
                ],
                'min_order_value' => 500, 'max_uses' => 800, 'uses_per_user' => 4,
                'used_count' => 234, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Glow products',
                'start_date' => $now->copy()->subDays(20), 'end_date' => $now->copy()->addDays(40),
                'is_active' => true,
            ]],
            ['store' => 'Glow Beauty', 'data' => [
                'title' => 'Skin Glow 20', 'code' => 'GLOW20',
                'description' => '20% off Glow Beauty skincare. Weekend special!',
                'type' => Discount::TYPE_PERCENTAGE, 'value' => 20,
                'min_order_value' => 300, 'max_uses' => 500, 'uses_per_user' => 1,
                'used_count' => 312, 'applicable_to' => Discount::APPLIES_ALL,
                'target_ids' => [], 'target_label' => 'Glow products',
                'start_date' => $now->copy()->subDays(3), 'end_date' => $now->copy()->addDays(4),
                'is_active' => true,
            ]],
        ];

        $created = 0;
        foreach ($rows as $row) {
            $store = $stores->get($row['store']);
            if (! $store) continue;
            Discount::create($row['data'] + [
                'store_id'   => (string) $store->_id,
                'created_by' => (string) ($store->owner_id ?? $adminId),
            ]);
            $created++;
        }

        $this->command->info("Created {$created} store-scoped discounts.");
    }
}
