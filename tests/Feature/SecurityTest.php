<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Tests\RefreshMongoDatabase;
use Tests\TestCase;

/**
 * Security smoke-tests covering:
 *  1. Authentication gates — unauthenticated requests are redirected to login.
 *  2. Authorisation gates — customers (non-managers) receive 403 on admin routes.
 *  3. Manager access — store managers can reach admin routes.
 *  4. Rate-limiting presence — throttle middleware is registered on coupon routes.
 *  5. CSRF audit note — VerifyCsrfToken is active for all POST/PATCH/DELETE routes.
 *     Laravel's runningUnitTests() check bypasses the token check here, which is
 *     the expected behaviour. In production, @csrf / X-CSRF-TOKEN is required.
 *
 * Note: uses RefreshMongoDatabase (not RefreshDatabase) because MongoDB standalone
 * mode does not support transactions — collections are dropped before each test instead.
 */
class SecurityTest extends TestCase
{
    use RefreshMongoDatabase;

    /** Create a manager who already has a store (passes the require.store gate). */
    private function managerWithStore(): User
    {
        $user = User::factory()->manager()->create();
        Store::create([
            'name'      => 'Test Store',
            'category'  => 'Electronics',
            'owner_id'  => (string) $user->_id,
            'is_active' => true,
        ]);
        return $user->fresh();
    }

    // ── 1. Authentication gate ───────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_admin_discounts(): void
    {
        $response = $this->get('/admin/discounts');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_admin_promotions(): void
    {
        $response = $this->get('/admin/promotions');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_admin_analytics(): void
    {
        $response = $this->get('/admin/analytics');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_customer_deals(): void
    {
        $response = $this->get('/deals');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_coupon_page(): void
    {
        $response = $this->get('/coupon');

        $response->assertRedirect('/login');
    }

    // ── 2. Authorisation gate (customer → 403 on admin routes) ──────────────

    public function test_customer_is_forbidden_on_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_customer_is_forbidden_on_admin_discounts_index(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/discounts');

        $response->assertForbidden();
    }

    public function test_customer_is_forbidden_on_admin_discounts_create(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/discounts/create');

        $response->assertForbidden();
    }

    public function test_customer_is_forbidden_on_admin_promotions(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/promotions');

        $response->assertForbidden();
    }

    public function test_customer_is_forbidden_on_admin_analytics(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/analytics');

        $response->assertForbidden();
    }

    // ── 3. Manager access ────────────────────────────────────────────────────

    public function test_store_manager_can_reach_admin_dashboard(): void
    {
        $response = $this->actingAs($this->managerWithStore())->get('/admin/dashboard');
        $response->assertSuccessful();
    }

    public function test_store_manager_can_reach_discounts_index(): void
    {
        $response = $this->actingAs($this->managerWithStore())->get('/admin/discounts');
        $response->assertSuccessful();
    }

    public function test_store_manager_can_reach_promotions_index(): void
    {
        $response = $this->actingAs($this->managerWithStore())->get('/admin/promotions');
        $response->assertSuccessful();
    }

    public function test_super_admin_can_reach_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertSuccessful();
    }

    public function test_manager_without_store_is_redirected_to_register_store(): void
    {
        // Manager has no store yet — they should be redirected to register one
        $manager = User::factory()->manager()->create();
        $response = $this->actingAs($manager)->get('/admin/dashboard');
        $response->assertRedirect('/admin/store/create');
    }

    public function test_manager_without_store_can_access_store_creation_page(): void
    {
        $manager = User::factory()->manager()->create();
        $response = $this->actingAs($manager)->get('/admin/store/create');
        $response->assertSuccessful();
    }

    // ── 4. Customer can access their own routes ──────────────────────────────

    public function test_authenticated_customer_can_view_shop(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/shop');
        $response->assertSuccessful();
    }

    public function test_authenticated_customer_deals_redirects_to_shop(): void
    {
        // /deals is now a legacy alias that redirects to /shop
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/deals');
        $response->assertRedirect('/shop');
    }

    public function test_authenticated_customer_can_view_coupon_page(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/coupon');

        $response->assertSuccessful();
    }

    // ── 5. Rate limiting registration ────────────────────────────────────────

    /**
     * Verify the throttle middleware is attached to the coupon POST routes.
     * We hit the route 31 times (just over the 30 req/min limit) using
     * the same IP and confirm we eventually receive a 429 Too Many Requests.
     *
     * Note: the RateLimiter uses the cache driver. phpunit.xml sets CACHE_STORE=array,
     * so the counter resets between test runs and doesn't pollute other tests.
     */
    public function test_coupon_validate_is_rate_limited_after_30_requests(): void
    {
        $customer = User::factory()->customer()->create();

        // Exhaust the 30-per-minute allowance
        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($customer)->postJson('/coupon/validate', [
                'code'        => 'TESTCODE',
                'order_total' => 100,
            ]);
        }

        // The 31st request should be throttled
        $response = $this->actingAs($customer)->postJson('/coupon/validate', [
            'code'        => 'TESTCODE',
            'order_total' => 100,
        ]);

        $response->assertStatus(429);
    }

    public function test_coupon_apply_is_rate_limited_after_5_requests(): void
    {
        $customer = User::factory()->customer()->create();

        // Exhaust the 5-per-minute allowance
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($customer)->postJson('/coupon/apply', [
                'code'        => 'TESTCODE',
                'order_total' => 100,
            ]);
        }

        // The 6th request should be throttled
        $response = $this->actingAs($customer)->postJson('/coupon/apply', [
            'code'        => 'TESTCODE',
            'order_total' => 100,
        ]);

        $response->assertStatus(429);
    }
}
