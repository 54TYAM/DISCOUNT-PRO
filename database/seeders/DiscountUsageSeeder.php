<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscountUsageSeeder extends Seeder
{
    public function run(): void
    {
        DiscountUsage::truncate();

        $users     = User::all();
        $discounts = Discount::all();

        if ($users->isEmpty() || $discounts->isEmpty()) {
            $this->command->warn('No users or discounts found — skipping usage seeder.');
            return;
        }

        $usages = [];

        // Spread 90 realistic usage records across the last 30 days
        $orderPrices = [499, 599, 749, 899, 1099, 1299, 1499, 1799, 2099, 2499, 2999, 3499, 4200, 4999];

        foreach ($discounts->where('used_count', '>', 0) as $discount) {
            // Create 6-12 usage records per discount
            $count = min((int) round($discount->used_count / 60), 12);
            $count = max($count, 4);

            for ($i = 0; $i < $count; $i++) {
                $user          = $users->random();
                $daysAgo       = rand(1, 29);
                $originalAmt   = $orderPrices[array_rand($orderPrices)] + rand(0, 500);
                $discountAmt   = $this->calcDiscount($discount, $originalAmt);
                $finalAmt      = max(0, $originalAmt - $discountAmt);

                $usages[] = [
                    'discount_id'      => (string) $discount->_id,
                    'user_id'          => (string) $user->_id,
                    'order_id'         => 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'original_amount'  => (float) $originalAmt,
                    'discount_applied' => (float) $discountAmt,
                    'final_amount'     => (float) $finalAmt,
                    'used_at'          => now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                ];
            }
        }

        foreach ($usages as $usage) {
            DiscountUsage::create($usage);
        }

        $this->command->info('Created ' . count($usages) . ' discount usage records.');
    }

    private function calcDiscount(Discount $d, float $total): float
    {
        return match ($d->type) {
            Discount::TYPE_PERCENTAGE => round($total * ($d->value / 100), 2),
            Discount::TYPE_FIXED      => min($d->value, $total),
            Discount::TYPE_TIERED     => $this->tiered($d, $total),
            default                   => 0,
        };
    }

    private function tiered(Discount $d, float $total): float
    {
        $pct = 0;
        foreach ($d->tiered_rules ?? [] as $tier) {
            if ($total >= $tier['min']) $pct = $tier['discount_pct'];
        }
        return round($total * ($pct / 100), 2);
    }
}
