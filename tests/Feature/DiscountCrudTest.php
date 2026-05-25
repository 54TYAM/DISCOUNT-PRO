<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Store;
use App\Models\User;
use Tests\RefreshMongoDatabase;
use Tests\TestCase;

/**
 * CRUD coverage for the manager-side discount management routes.
 * Uses RefreshMongoDatabase (not RefreshDatabase) because standalone MongoDB
 * does not support transactions.
 */
class DiscountCrudTest extends TestCase
{
    use RefreshMongoDatabase;

    private User $manager;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        // Per-test manager + store, required by the new require.store middleware
        $this->manager = User::factory()->manager()->create();
        $this->store   = Store::create([
            'name'      => 'Test Store',
            'category'  => 'Electronics',
            'owner_id'  => (string) $this->manager->_id,
            'is_active' => true,
        ]);
        $this->manager = $this->manager->fresh();
    }

    /** Helper: discount that belongs to the test's manager (so they can manage it). */
    private function ownedDiscount(array $overrides = []): Discount
    {
        return Discount::factory()->create($overrides + ['store_id' => (string) $this->store->_id]);
    }

    public function test_manager_can_view_discount_index(): void
    {
        $this->actingAs($this->manager)
            ->get('/admin/discounts')
            ->assertSuccessful()
            ->assertSee('Discount Codes');
    }

    public function test_manager_can_view_create_form(): void
    {
        $this->actingAs($this->manager)
            ->get('/admin/discounts/create')
            ->assertSuccessful();
    }

    public function test_manager_can_create_a_percentage_discount(): void
    {
        $response = $this->actingAs($this->manager)->post('/admin/discounts', [
            'title'         => 'Test Discount',
            'code'          => 'TESTCRUD',
            'description'   => 'Test description',
            'type'          => 'percentage',
            'value'         => 20,
            'uses_per_user' => 1,
            'applicable_to' => 'all',
            'is_active'     => 1,
        ]);

        $response->assertRedirect('/admin/discounts');
        $discount = Discount::where('code', 'TESTCRUD')->first();
        $this->assertNotNull($discount);
        $this->assertEquals((string) $this->store->_id, (string) $discount->store_id);
    }

    public function test_creating_a_discount_with_duplicate_code_fails(): void
    {
        $this->ownedDiscount(['code' => 'EXISTING']);

        $response = $this->actingAs($this->manager)->post('/admin/discounts', [
            'title'         => 'Duplicate',
            'code'          => 'EXISTING',
            'type'          => 'percentage',
            'value'         => 10,
            'uses_per_user' => 1,
            'applicable_to' => 'all',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_percentage_over_100_is_rejected(): void
    {
        $response = $this->actingAs($this->manager)->post('/admin/discounts', [
            'title'         => 'Too Big',
            'code'          => 'TOOBIG',
            'type'          => 'percentage',
            'value'         => 150,
            'uses_per_user' => 1,
            'applicable_to' => 'all',
        ]);

        $response->assertSessionHasErrors('value');
    }

    public function test_manager_can_view_discount_show_page(): void
    {
        $discount = $this->ownedDiscount();

        $this->actingAs($this->manager)
            ->get('/admin/discounts/' . $discount->_id)
            ->assertSuccessful()
            ->assertSee($discount->code);
    }

    public function test_manager_can_view_edit_form(): void
    {
        $discount = $this->ownedDiscount();

        $this->actingAs($this->manager)
            ->get('/admin/discounts/' . $discount->_id . '/edit')
            ->assertSuccessful();
    }

    public function test_manager_can_update_a_discount(): void
    {
        $discount = $this->ownedDiscount(['title' => 'Old']);

        $response = $this->actingAs($this->manager)->put('/admin/discounts/' . $discount->_id, [
            'title'         => 'New Title',
            'code'          => $discount->code,
            'type'          => 'percentage',
            'value'         => 25,
            'uses_per_user' => 1,
            'applicable_to' => 'all',
            'is_active'     => 1,
        ]);

        $response->assertRedirect('/admin/discounts/' . $discount->_id);
        $this->assertEquals('New Title', $discount->fresh()->title);
    }

    public function test_manager_can_delete_a_discount(): void
    {
        $discount = $this->ownedDiscount();

        $this->actingAs($this->manager)
            ->delete('/admin/discounts/' . $discount->_id)
            ->assertRedirect('/admin/discounts');

        $this->assertNull(Discount::find($discount->_id));
    }

    public function test_toggle_endpoint_flips_is_active(): void
    {
        $discount = $this->ownedDiscount(['is_active' => true]);

        $response = $this->actingAs($this->manager)
            ->patch('/admin/discounts/' . $discount->_id . '/toggle');

        $response->assertSuccessful();
        $response->assertJson(['is_active' => false]);
    }

    public function test_duplicate_creates_a_copy_with_prefix(): void
    {
        $original = $this->ownedDiscount(['code' => 'ORIGINAL']);

        $this->actingAs($this->manager)
            ->post('/admin/discounts/' . $original->_id . '/duplicate')
            ->assertRedirect();

        $copy = Discount::where('code', 'COPY_ORIGINAL')->first();
        $this->assertNotNull($copy);
        $this->assertFalse($copy->is_active);
        $this->assertEquals(0, $copy->used_count);
    }

    public function test_manager_cannot_edit_another_managers_discount(): void
    {
        // Another manager's store + discount
        $other       = User::factory()->manager()->create();
        $otherStore  = Store::create(['name' => 'Other', 'category' => 'Electronics', 'owner_id' => (string) $other->_id, 'is_active' => true]);
        $foreign     = Discount::factory()->create(['store_id' => (string) $otherStore->_id]);

        $this->actingAs($this->manager)
            ->get('/admin/discounts/' . $foreign->_id . '/edit')
            ->assertForbidden();
    }

    public function test_customer_cannot_create_a_discount(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->post('/admin/discounts', [
            'title' => 'Bad',
            'code'  => 'BAD',
            'type'  => 'percentage',
            'value' => 10,
        ])->assertForbidden();
    }
}
