<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'promotions';
    protected $perPage    = 12;

    const TYPE_FLASH_SALE = 'flash_sale';
    const TYPE_SEASONAL   = 'seasonal';
    const TYPE_LOYALTY    = 'loyalty';
    const TYPE_REFERRAL   = 'referral';

    const SEGMENT_ALL        = 'all';
    const SEGMENT_NEW_USERS  = 'new_users';
    const SEGMENT_RETURNING  = 'returning';
    const SEGMENT_HIGH_VALUE = 'high_value';
    const SEGMENT_INACTIVE   = 'inactive';

    const TYPE_LABELS = [
        self::TYPE_FLASH_SALE => 'Flash Sale',
        self::TYPE_SEASONAL   => 'Seasonal',
        self::TYPE_LOYALTY    => 'Loyalty',
        self::TYPE_REFERRAL   => 'Referral',
    ];

    const SEGMENT_LABELS = [
        self::SEGMENT_ALL        => 'All Users',
        self::SEGMENT_NEW_USERS  => 'New Users',
        self::SEGMENT_RETURNING  => 'Returning Customers',
        self::SEGMENT_HIGH_VALUE => 'High-Value Customers',
        self::SEGMENT_INACTIVE   => 'Inactive Users',
    ];

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'type',
        'discount_id',
        'banner_color',
        'target_segment',
        'start_at',
        'end_at',
        'is_active',
        'view_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'view_count' => 'integer',
            'start_at'   => 'datetime',
            'end_at'     => 'datetime',
        ];
    }

    public function getStatusAttribute(): string
    {
        $now = now();
        if (! $this->is_active) return 'paused';
        if ($this->end_at && $this->end_at->lt($now)) return 'expired';
        if ($this->start_at && $this->start_at->gt($now)) return 'scheduled';
        return 'active';
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
