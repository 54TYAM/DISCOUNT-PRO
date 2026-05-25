<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * An Order is the result of a customer's checkout.
 * `items` is an embedded array of {product_id, product_name, store_id, qty, unit_price, line_total}
 * — no separate OrderItem collection (MongoDB-style denormalisation).
 */
class Order extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'orders';
    protected $perPage    = 10;

    const STATUS_PLACED    = 'placed';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'order_number',
        'items',
        'subtotal',
        'discount_code',
        'discount_id',
        'discount_amount',
        'shipping_fee',
        'total',
        'status',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'items'           => 'array',
            'subtotal'        => 'float',
            'discount_amount' => 'float',
            'shipping_fee'    => 'float',
            'total'           => 'float',
            'placed_at'       => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
