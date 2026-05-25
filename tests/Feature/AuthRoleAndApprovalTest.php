<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Tests\RefreshMongoDatabase;
use Tests\TestCase;

/**
 * Coverage for the new role-aware login + super-admin secret + manager-approval flow.
 */
class AuthRoleAndApprovalTest extends TestCase
{
    use RefreshMongoDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set a known secret for the duration of the test
        config(['auth.super_admin_key' => 'test-secret-key-xyz']);
        $_ENV['SUPER_ADMIN_SECRET_KEY'] = 'test-secret-key-xyz';
        putenv('SUPER_ADMIN_SECRET_KEY=test-secret-key-xyz');
    }

    // ── Role-tab enforcement ──────────────────────────────────────────────

    public function test_customer_logging_in_via_customer_tab_succeeds(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->post('/login', [
            'role'     => 'customer',
            'email'    => $customer->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_customer_cannot_log_in_via_manager_tab(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->post('/login', [
            'role'     => 'manager',
            'email'    => $customer->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_manager_cannot_log_in_via_customer_tab(): void
    {
        $manager = User::factory()->manager()->create();
        $manager->approve();
        Store::create(['name' => 'X', 'category' => 'Other', 'owner_id' => (string) $manager->_id, 'is_active' => true]);

        $response = $this->post('/login', [
            'role'     => 'customer',
            'email'    => $manager->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // ── Super-admin secret key ────────────────────────────────────────────

    public function test_admin_can_log_in_with_correct_secret_key(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'role'       => 'admin',
            'email'      => $admin->email,
            'password'   => 'password',
            'secret_key' => 'test-secret-key-xyz',
        ]);

        $this->assertAuthenticated();
        // Admins (who are also managers) redirect to /admin/dashboard after login
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_admin_login_fails_without_secret_key(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'role'     => 'admin',
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('secret_key');
    }

    public function test_admin_login_fails_with_wrong_secret_key(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'role'       => 'admin',
            'email'      => $admin->email,
            'password'   => 'password',
            'secret_key' => 'totally-wrong-key',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('secret_key');
    }

    // ── Pending approval gate ────────────────────────────────────────────

    public function test_pending_manager_cannot_log_in(): void
    {
        $pending = User::factory()->manager()->create();
        // factory's afterCreating doesn't approve — keep is_approved false manually
        $pending->forceFill(['is_approved' => false])->save();

        $response = $this->post('/login', [
            'role'     => 'manager',
            'email'    => $pending->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_approved_manager_can_log_in(): void
    {
        $manager = User::factory()->manager()->create();
        $manager->approve();
        Store::create(['name' => 'X', 'category' => 'Other', 'owner_id' => (string) $manager->_id, 'is_active' => true]);

        $response = $this->post('/login', [
            'role'     => 'manager',
            'email'    => $manager->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    // ── Approval flow ─────────────────────────────────────────────────────

    public function test_admin_sees_pending_managers_in_approvals_page(): void
    {
        $admin = User::factory()->admin()->create();

        $pending = User::factory()->manager()->create();
        $pending->forceFill([
            'is_approved'              => false,
            'requested_store_name'     => 'New Cool Shop',
            'requested_store_category' => 'Electronics',
        ])->save();

        $response = $this->actingAs($admin)->get('/admin/approvals');
        $response->assertSuccessful();
        $response->assertSee('New Cool Shop');
    }

    public function test_manager_cannot_access_approvals_page(): void
    {
        $manager = User::factory()->manager()->create();
        $manager->approve();
        Store::create(['name' => 'X', 'category' => 'Other', 'owner_id' => (string) $manager->_id, 'is_active' => true]);

        $response = $this->actingAs($manager->fresh())->get('/admin/approvals');
        $response->assertForbidden();
    }

    public function test_admin_can_approve_a_pending_manager(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->manager()->create();
        $pending->forceFill([
            'is_approved'              => false,
            'requested_store_name'     => 'Brand New Store',
            'requested_store_category' => 'Electronics',
        ])->save();

        $this->actingAs($admin)
            ->post('/admin/approvals/' . $pending->_id . '/approve')
            ->assertRedirect(route('admin.approvals.index', absolute: false));

        $this->assertTrue($pending->fresh()->isApproved());
        // Store was auto-created from the requested fields
        $store = Store::where('owner_id', (string) $pending->_id)->first();
        $this->assertNotNull($store);
        $this->assertEquals('Brand New Store', $store->name);
    }

    public function test_admin_can_reject_a_pending_manager(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->manager()->create();
        $pending->forceFill(['is_approved' => false])->save();

        $pendingId = (string) $pending->_id;
        $this->actingAs($admin)
            ->delete('/admin/approvals/' . $pendingId)
            ->assertRedirect(route('admin.approvals.index', absolute: false));

        $this->assertNull(User::find($pendingId));
    }
}
