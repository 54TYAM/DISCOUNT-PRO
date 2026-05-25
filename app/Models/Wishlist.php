<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * A customer's wishlist entry. Composite uniqueness is enforced in the controller
 * with firstOrCreate (MongoDB unique-compound indexes are doable but overkill here).
 */
class Wishlist extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'wishlists';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
