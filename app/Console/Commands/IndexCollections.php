<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent MongoDB index creation for performance-critical collections.
 *
 * Run after seeding (or any time):
 *     php artisan db:index
 *
 * Safe to re-run — MongoDB silently no-ops on existing identical indexes.
 */
class IndexCollections extends Command
{
    protected $signature = 'db:index {--drop : Drop existing indexes before recreating}';
    protected $description = 'Create MongoDB indexes for fast queries on discounts, usages, promotions, and users';

    public function handle(): int
    {
        $conn = DB::connection('mongodb');

        $plan = [
            'discounts' => [
                ['keys' => ['code' => 1],                     'options' => ['unique' => true, 'name' => 'code_unique']],
                ['keys' => ['is_active' => 1, 'end_date' => 1],   'options' => ['name' => 'active_enddate']],
                ['keys' => ['is_active' => 1, 'start_date' => 1], 'options' => ['name' => 'active_startdate']],
                ['keys' => ['used_count' => -1],              'options' => ['name' => 'usedcount_desc']],
                ['keys' => ['type' => 1],                     'options' => ['name' => 'type']],
            ],
            'discount_usages' => [
                ['keys' => ['discount_id' => 1],              'options' => ['name' => 'discount_id']],
                ['keys' => ['user_id' => 1],                  'options' => ['name' => 'user_id']],
                ['keys' => ['used_at' => -1],                 'options' => ['name' => 'used_at_desc']],
                ['keys' => ['user_id' => 1, 'discount_id' => 1], 'options' => ['name' => 'user_discount']],
            ],
            'promotions' => [
                ['keys' => ['discount_id' => 1],              'options' => ['name' => 'discount_id']],
                ['keys' => ['is_active' => 1, 'start_at' => 1, 'end_at' => 1], 'options' => ['name' => 'active_window']],
            ],
            'users' => [
                ['keys' => ['email' => 1],                    'options' => ['unique' => true, 'name' => 'email_unique']],
                ['keys' => ['role' => 1],                     'options' => ['name' => 'role']],
            ],
            'products' => [
                ['keys' => ['is_active' => 1, 'category' => 1], 'options' => ['name' => 'active_category']],
                ['keys' => ['store_id' => 1],                   'options' => ['name' => 'store_id']],
                ['keys' => ['store_id' => 1, 'is_active' => 1], 'options' => ['name' => 'store_active']],
            ],
            'stores' => [
                ['keys' => ['slug' => 1],     'options' => ['unique' => true, 'name' => 'slug_unique']],
                ['keys' => ['owner_id' => 1], 'options' => ['name' => 'owner_id']],
                ['keys' => ['is_active' => 1], 'options' => ['name' => 'is_active']],
            ],
            'orders' => [
                ['keys' => ['user_id' => 1],   'options' => ['name' => 'user_id']],
                ['keys' => ['placed_at' => -1], 'options' => ['name' => 'placed_at_desc']],
            ],
        ];

        $created = 0;
        foreach ($plan as $collection => $indexes) {
            $this->info("→ {$collection}");
            $mongoCollection = $conn->getCollection($collection);

            if ($this->option('drop')) {
                try {
                    $mongoCollection->dropIndexes();
                    $this->line("  dropped existing indexes");
                } catch (\Throwable $e) {
                    // collection may not exist yet — that's fine
                }
            }

            // Read existing index keys so we can skip duplicates regardless of name
            $existingKeys = [];
            try {
                foreach ($mongoCollection->listIndexes() as $existing) {
                    $existingKeys[] = json_encode($existing->getKey());
                }
            } catch (\Throwable $e) {
                // collection doesn't exist yet — that's fine
            }

            foreach ($indexes as $idx) {
                $name = $idx['options']['name'] ?? '(unnamed)';
                if (in_array(json_encode($idx['keys']), $existingKeys, true)) {
                    $this->line("  · {$name} (already exists)");
                    continue;
                }
                try {
                    $mongoCollection->createIndex($idx['keys'], $idx['options']);
                    $this->line("  ✓ {$name}");
                    $created++;
                } catch (\Throwable $e) {
                    $this->error("  ✗ {$name} — " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info("{$created} indexes ensured.");
        return self::SUCCESS;
    }
}
