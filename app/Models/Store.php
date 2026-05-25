<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

/**
 * A Store represents a store-manager's storefront on the platform.
 * Each store-manager owns exactly one Store; the Store owns its products,
 * its discounts, and its promotions.
 */
class Store extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'stores';
    protected $perPage    = 12;

    const CATEGORIES = [
        'Electronics',
        'Fashion',
        'Home & Kitchen',
        'Books',
        'Sports & Fitness',
        'Beauty & Personal Care',
        'Grocery',
        'Toys & Games',
        'Other',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'logo_url',
        'banner_color',
        'address',
        'contact_email',
        'contact_phone',
        'owner_id',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            if (empty($store->slug)) {
                $store->slug = static::makeUniqueSlug($store->name);
            }
        });
    }

    private static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'store';
        $slug = $base;
        $i    = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'store_id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'store_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
