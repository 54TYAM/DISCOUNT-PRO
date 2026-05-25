<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndAdminSeeder::class,     // super admin + demo customer
            StoreSeeder::class,             // 4 demo stores + their manager accounts
            ProductSeeder::class,           // products per store
            DiscountSeeder::class,          // store-scoped coupons
            PromotionSeeder::class,         // store-scoped promotions
            DiscountUsageSeeder::class,
            AnalyticsSnapshotSeeder::class,
        ]);
    }
}
