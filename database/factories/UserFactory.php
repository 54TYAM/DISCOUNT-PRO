<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Role is not in $fillable (anti-privilege-escalation). Factory states use
     * forceFill via the afterCreating hook so factory-created users get a role
     * assigned in a controlled way.
     */
    private function withRole(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->forceFill(['role' => $role])->save();
        });
    }

    /** Create a customer-role user. */
    public function customer(): static
    {
        return $this->withRole(User::ROLE_CUSTOMER);
    }

    /** Create a store-manager-role user. */
    public function manager(): static
    {
        return $this->withRole(User::ROLE_MANAGER);
    }

    /** Create a super-admin-role user. */
    public function admin(): static
    {
        return $this->withRole(User::ROLE_ADMIN);
    }
}
