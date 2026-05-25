<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'products';

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

    protected $perPage = 12;

    protected $fillable = [
        'store_id',
        'name',
        'category',
        'price',
        'description',
        'image_url',
        'tags',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'float',
            'stock'     => 'integer',
            'is_active' => 'boolean',
            'tags'      => 'array',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
