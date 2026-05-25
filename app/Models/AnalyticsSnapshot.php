<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AnalyticsSnapshot extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'analytics_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'discount_id',
        'date',
        'total_uses',
        'revenue_saved',
        'orders_count',
        'conversion_rate',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'datetime',
            'total_uses'      => 'integer',
            'revenue_saved'   => 'float',
            'orders_count'    => 'integer',
            'conversion_rate' => 'float',
        ];
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public static function summaryForDiscount(string $discountId, int $days = 30): array
    {
        $rows = static::where('discount_id', $discountId)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get();

        return [
            'total_uses'    => $rows->sum('total_uses'),
            'revenue_saved' => $rows->sum('revenue_saved'),
            'avg_conv_rate' => $rows->avg('conversion_rate') ?? 0,
            'daily'         => $rows->map(fn ($r) => [
                'date'         => $r->date->toDateString(),
                'uses'         => $r->total_uses,
                'revenue'      => $r->revenue_saved,
                'conv_rate'    => $r->conversion_rate,
            ])->values(),
        ];
    }
}
