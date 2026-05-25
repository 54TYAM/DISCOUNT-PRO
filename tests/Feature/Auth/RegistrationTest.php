<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\RefreshMongoDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshMongoDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_customers_can_register_and_are_auto_logged_in(): void
    {
        $response = $this->post('/register', [
            'role'                  => 'customer',
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shop.index', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->isCustomer());
        $this->assertTrue($user->isApproved());
    }

    public function test_store_manager_registration_is_pending_until_approved(): void
    {
        $response = $this->post('/register', [
            'role'                  => 'manager',
            'name'                  => 'Future Manager',
            'email'                 => 'future-manager@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'store_name'            => 'My New Store',
            'store_category'        => 'Electronics',
        ]);

        // Pending managers are NOT logged in — they're redirected to login with a notice
        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'pending-approval');

        $user = User::where('email', 'future-manager@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isManager());
        $this->assertFalse($user->isApproved());
        $this->assertEquals('My New Store', $user->requested_store_name);
        $this->assertEquals('Electronics', $user->requested_store_category);
    }

    public function test_manager_registration_requires_store_name_and_category(): void
    {
        $response = $this->post('/register', [
            'role'                  => 'manager',
            'name'                  => 'Future Manager',
            'email'                 => 'incomplete@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['store_name', 'store_category']);
    }
}
