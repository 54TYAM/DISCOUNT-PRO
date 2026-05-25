<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    const ROLE_ADMIN    = 'super_admin';
    const ROLE_MANAGER  = 'store_manager';
    const ROLE_CUSTOMER = 'customer';

    // Note: `role` and `is_approved` are intentionally NOT fillable. Privilege escalation
    // prevention — use $user->assignRole() / $user->approve() from server-side code only.
    protected $fillable = [
        'name',
        'email',
        'password',
        'requested_store_name',
        'requested_store_category',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_approved'       => 'boolean',
        ];
    }

    /** Approval flag — null/missing means approved (legacy users). False = pending. */
    public function isApproved(): bool
    {
        return $this->is_approved !== false;
    }

    /** Approve a pending manager from server-side code. */
    public function approve(): self
    {
        $this->forceFill(['is_approved' => true])->save();
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN    => 'Super Admin',
            self::ROLE_MANAGER  => 'Store Manager',
            self::ROLE_CUSTOMER => 'Customer',
            default             => 'Unknown',
        };
    }

    /**
     * Server-side-only role assignment. Bypasses fillable rules deliberately
     * so user input can never reach this — only application code can.
     */
    public function assignRole(string $role): self
    {
        if (! in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_CUSTOMER], true)) {
            throw new \InvalidArgumentException("Unknown role: {$role}");
        }
        $this->forceFill(['role' => $role])->save();
        return $this;
    }

    /** A store-manager owns one Store. */
    public function store()
    {
        return $this->hasOne(Store::class, 'owner_id');
    }

    /** Convenience: does this manager have a registered store yet? */
    public function hasStore(): bool
    {
        return $this->isManager() && $this->store()->exists();
    }
}
