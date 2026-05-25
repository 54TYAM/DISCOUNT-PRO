<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate();

        // Map seed categories → store name (where the product is sold)
        $storeMap = [
            'Electronics'            => 'Aurora Electronics',
            'Fashion'                => 'Nomad Apparel',
            'Sports & Fitness'       => 'Nomad Apparel',
            'Home & Kitchen'         => 'Hearth & Kettle',
            'Beauty & Personal Care' => 'Glow Beauty',
        ];

        $stores = Store::all()->keyBy('name');

        $products = [
            // Aurora Electronics
            ['name' => 'Wireless Noise-Cancelling Earbuds', 'category' => 'Electronics', 'price' => 2499, 'stock' => 85,  'image_url' => 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=600'],
            ['name' => 'Smart Watch with Health Tracker',   'category' => 'Electronics', 'price' => 3999, 'stock' => 60,  'image_url' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600'],
            ['name' => 'Portable Bluetooth Speaker',        'category' => 'Electronics', 'price' => 1599, 'stock' => 120, 'image_url' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600'],
            ['name' => '7-in-1 USB-C Hub',                  'category' => 'Electronics', 'price' => 999,  'stock' => 200, 'image_url' => 'https://images.unsplash.com/photo-1625948515291-69613efd103f?w=600'],
            ['name' => 'Ergonomic Laptop Stand',            'category' => 'Electronics', 'price' => 799,  'stock' => 150, 'image_url' => 'https://images.unsplash.com/photo-1527443195645-1133f7f28990?w=600'],
            ['name' => 'Mechanical Keyboard',               'category' => 'Electronics', 'price' => 2999, 'stock' => 45,  'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600'],

            // Nomad Apparel
            ['name' => 'Premium Cotton Oversized T-Shirt', 'category' => 'Fashion',          'price' => 499,  'stock' => 300, 'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600'],
            ['name' => 'Classic Denim Jacket',             'category' => 'Fashion',          'price' => 1899, 'stock' => 75,  'image_url' => 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=600'],
            ['name' => 'Lightweight Running Shoes',        'category' => 'Sports & Fitness', 'price' => 2199, 'stock' => 90,  'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600'],
            ['name' => 'High-Waist Yoga Pants',            'category' => 'Sports & Fitness', 'price' => 849,  'stock' => 180, 'image_url' => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=600'],
            ['name' => 'Linen Summer Dress',               'category' => 'Fashion',          'price' => 1299, 'stock' => 110, 'image_url' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600'],
            ['name' => 'Formal Slim-Fit Shirt',            'category' => 'Fashion',          'price' => 699,  'stock' => 220, 'image_url' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600'],

            // Hearth & Kettle
            ['name' => 'Drip Coffee Maker 1.5L',           'category' => 'Home & Kitchen', 'price' => 2299, 'stock' => 55,  'image_url' => 'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?w=600'],
            ['name' => 'HEPA Air Purifier 400 sq.ft',      'category' => 'Home & Kitchen', 'price' => 4999, 'stock' => 30,  'image_url' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=600'],
            ['name' => 'Non-Stick Cookware Set (5 pcs)',   'category' => 'Home & Kitchen', 'price' => 1799, 'stock' => 65,  'image_url' => 'https://images.unsplash.com/photo-1584990347449-a82d177c1f9d?w=600'],
            ['name' => 'LED Desk Lamp with USB Port',      'category' => 'Home & Kitchen', 'price' => 649,  'stock' => 190, 'image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600'],
            ['name' => 'Bamboo Storage Organiser Set',     'category' => 'Home & Kitchen', 'price' => 599,  'stock' => 140, 'image_url' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600'],

            // Glow Beauty
            ['name' => 'Vitamin C Brightening Serum 30ml', 'category' => 'Beauty & Personal Care', 'price' => 799, 'stock' => 220, 'image_url' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600'],
            ['name' => 'SPF 50 Sunscreen Lotion 100ml',    'category' => 'Beauty & Personal Care', 'price' => 449, 'stock' => 350, 'image_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600'],
            ['name' => 'Hydrating Sheet Mask Pack (10)',   'category' => 'Beauty & Personal Care', 'price' => 349, 'stock' => 500, 'image_url' => 'https://images.unsplash.com/photo-1570194065650-d99fb4bedf0a?w=600'],
        ];

        $created = 0;
        foreach ($products as $data) {
            $storeName = $storeMap[$data['category']] ?? null;
            $store     = $storeName ? $stores->get($storeName) : null;
            if (! $store) continue;

            Product::create([
                'store_id'    => (string) $store->_id,
                'name'        => $data['name'],
                'category'    => $data['category'],
                'price'       => $data['price'],
                'stock'       => $data['stock'],
                'image_url'   => $data['image_url'],
                'description' => 'Quality ' . strtolower($data['name']) . ' from ' . $storeName . '.',
                'tags'        => [],
                'is_active'   => true,
            ]);
            $created++;
        }

        $this->command->info("Seeded {$created} products across all demo stores.");
    }
}
