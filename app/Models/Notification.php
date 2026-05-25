<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * In-app notification. Created server-side (controllers, observers) and rendered
 * via the bell icon in both layouts. Marked read when user opens the dropdown.
 */
class Notification extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id',
        'type',     // order_placed | order_fulfilled | coupon_expiring | manager_approved | new_application | ...
        'title',
        'body',
        'link',
        'icon',     // svg path d=
        'color',    // tailwind colour token (brand|emerald|amber|rose|sky)
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /** Server-side helper to fire a notification. */
    public static function notify(string $userId, array $attrs): self
    {
        return self::create(array_merge(['user_id' => $userId, 'read_at' => null], $attrs));
    }
}
