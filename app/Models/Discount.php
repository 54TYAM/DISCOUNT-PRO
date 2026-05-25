<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'discounts';
    protected $perPage    = 12;

    const TYPE_PERCENTAGE    = 'percentage';
    const TYPE_FIXED         = 'fixed';
    const TYPE_BOGO          = 'bogo';
    const TYPE_FREE_SHIPPING = 'free_shipping';
    const TYPE_TIERED        = 'tiered';

    const APPLIES_ALL      = 'all';
    const APPLIES_CATEGORY = 'category';
    const APPLIES_PRODUCT  = 'product';

    protected $fillable = [
        'store_id',
        'title',
        'code',
        'description',
        'type',
        'value',
        'tiered_rules',
        'min_order_value',
        'max_uses',
        'uses_per_user',
        'used_count',
        'applicable_to',
        'target_ids',
        'target_label',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value'           => 'float',
            'min_order_value' => 'float',
            'max_uses'        => 'integer',
            'uses_per_user'   => 'integer',
            'used_count'      => 'integer',
            'is_active'       => 'boolean',
            'tiered_rules'    => 'array',
            'target_ids'      => 'array',
            'start_date'      => 'datetime',
            'end_date'        => 'datetime',
        ];
    }

    public function getStatusAttribute(): string
    {
        $now = now();
        if (! $this->is_active) return 'paused';
        if ($this->end_date && $this->end_date->lt($now)) return 'expired';
        if ($this->start_date && $this->start_date->gt($now)) return 'scheduled';
        if ($this->max_uses && $this->used_count >= $this->max_uses) return 'exhausted';
        return 'active';
    }

    public function getUsagePercentAttribute(): float
    {
        if (! $this->max_uses) return 0;
        return round(($this->used_count / $this->max_uses) * 100, 1);
    }

    public function isValidForUser(User $user): bool
    {
        if ($this->status !== 'active') return false;
        $userUsageCount = DiscountUsage::where('discount_id', (string) $this->_id)
            ->where('user_id', (string) $user->_id)
            ->count();
        return $userUsageCount < $this->uses_per_user;
    }

    // NOTE: Savings calculation lives in App\Services\CouponService (single source
    // of truth). The previously-duplicated computeDiscount() method was removed.

    public function usages()
    {
        return $this->hasMany(DiscountUsage::class, 'discount_id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'discount_id');
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
