<?php

namespace Database\Seeders;

use App\Models\AnalyticsSnapshot;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class AnalyticsSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        AnalyticsSnapshot::truncate();

        $discounts  = Discount::all();
        $snapshots  = 0;

        foreach ($discounts as $discount) {
            // Skip future-scheduled or fully exhausted discounts
            if ($discount->used_count === 0) continue;

            $daysOfData = 28;
            // Peak usage mid-way, tapering at start and end
            $peakDay = rand(8, 20);

            for ($day = $daysOfData; $day >= 1; $day--) {
                $distance   = abs($day - $peakDay);
                $multiplier = max(0.1, 1 - ($distance * 0.05));
                $baseUses   = (int) round(($discount->used_count / $daysOfData) * $multiplier * rand(80, 120) / 100);
                $baseUses   = max(0, $baseUses);

                $avgOrderValue  = rand(800, 2500);
                $revenueSaved   = match ($discount->type) {
                    'percentage'    => round($baseUses * $avgOrderValue * ($discount->value / 100), 2),
                    'fixed'         => round($baseUses * $discount->value, 2),
                    'tiered'        => round($baseUses * $avgOrderValue * 0.10, 2),
                    default         => 0,
                };

                $ordersCount    = max($baseUses, (int) round($baseUses * rand(110, 180) / 100));
                $convRate       = $ordersCount > 0 ? round($baseUses / $ordersCount, 3) : 0;

                AnalyticsSnapshot::create([
                    'discount_id'     => (string) $discount->_id,
                    'date'            => now()->subDays($day)->startOfDay(),
                    'total_uses'      => $baseUses,
                    'revenue_saved'   => $revenueSaved,
                    'orders_count'    => $ordersCount,
                    'conversion_rate' => $convRate,
                ]);

                $snapshots++;
            }
        }

        $this->command->info("Created {$snapshots} analytics snapshot records.");
    }
}
