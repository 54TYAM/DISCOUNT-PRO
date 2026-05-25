<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class DiscountUsage extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'discount_usages';
    protected $perPage    = 10;

    public $timestamps = false;

    protected $fillable = [
        'discount_id',
        'user_id',
        'order_id',
        'original_amount',
        'discount_applied',
        'final_amount',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'original_amount'  => 'float',
            'discount_applied' => 'float',
            'final_amount'     => 'float',
            'used_at'          => 'datetime',
        ];
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForDiscount($query, string $discountId)
    {
        return $query->where('discount_id', $discountId);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('used_at', [$from, $to]);
    }
}
