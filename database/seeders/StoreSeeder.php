<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::truncate();

        $stores = [
            [
                'manager_name'  => 'Aurora Electronics',
                'manager_email' => 'aurora@discountpro.com',
                'name'          => 'Aurora Electronics',
                'category'      => 'Electronics',
                'banner_color'  => 'sky',
                'description'   => 'Premium audio, wearables, and tech accessories curated for everyday life.',
                'contact_email' => 'hello@aurora-electronics.com',
            ],
            [
                'manager_name'  => 'Nomad Apparel',
                'manager_email' => 'nomad@discountpro.com',
                'name'          => 'Nomad Apparel',
                'category'      => 'Fashion',
                'banner_color'  => 'rose',
                'description'   => 'Comfortable, modern clothing for everyday adventures.',
                'contact_email' => 'hello@nomad-apparel.com',
            ],
            [
                'manager_name'  => 'Hearth & Kettle',
                'manager_email' => 'hearth@discountpro.com',
                'name'          => 'Hearth & Kettle',
                'category'      => 'Home & Kitchen',
                'banner_color'  => 'amber',
                'description'   => 'Thoughtfully designed kitchen and home essentials.',
                'contact_email' => 'hello@hearth-kettle.com',
            ],
            [
                'manager_name'  => 'Glow Beauty',
                'manager_email' => 'glow@discountpro.com',
                'name'          => 'Glow Beauty',
                'category'      => 'Beauty & Personal Care',
                'banner_color'  => 'violet',
                'description'   => 'Clean, effective skincare and personal care products.',
                'contact_email' => 'hello@glow-beauty.com',
            ],
        ];

        foreach ($stores as $data) {
            // Create the store manager user
            $user = User::firstOrCreate(
                ['email' => $data['manager_email']],
                [
                    'name'              => $data['manager_name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole(User::ROLE_MANAGER);
            $user->approve(); // seeded managers are pre-approved

            // Create the store owned by this manager
            Store::create([
                'name'          => $data['name'],
                'category'      => $data['category'],
                'description'   => $data['description'],
                'banner_color'  => $data['banner_color'],
                'contact_email' => $data['contact_email'],
                'owner_id'      => (string) $user->_id,
                'is_active'     => true,
            ]);
        }

        // Seed one PENDING manager application so the super admin has something to review
        $pending = User::firstOrCreate(
            ['email' => 'pending@discountpro.com'],
            [
                'name'                     => 'Pending Applicant',
                'password'                 => Hash::make('password'),
                'email_verified_at'        => now(),
                'requested_store_name'     => 'Pixel Toys & Games',
                'requested_store_category' => 'Toys & Games',
            ]
        );
        $pending->assignRole(User::ROLE_MANAGER);
        $pending->forceFill(['is_approved' => false])->save();

        $this->command->info('Created ' . count($stores) . ' demo stores with manager accounts (+ 1 pending applicant).');
        $this->command->table(
            ['Store', 'Manager email', 'Password'],
            collect($stores)->map(fn ($s) => [$s['name'], $s['manager_email'], 'password'])->all()
        );
    }
}
