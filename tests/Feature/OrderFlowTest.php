<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Repair', 'slug' => 'repair', 'is_active' => true, 'sort_order' => 1]);

        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Diagnostic',
            'slug' => 'diagnostic',
            'price' => 50.00,
            'currency' => 'USD',
            'service_type' => 'PAID',
            'payment_required' => true,
            'is_active' => true,
            'consent_required' => true,
        ]);

        ServiceField::create([
            'service_id' => $this->service->id,
            'label' => 'IMEI',
            'internal_name' => 'imei',
            'type' => 'IMEI',
            'validation_regex' => '/^[0-9]{15}$/',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        PaymentMethod::create(['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer', 'is_active' => true, 'sort_order' => 1]);
    }

    public function test_guest_can_place_order(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['service_name_snapshot' => 'Diagnostic', 'price_snapshot' => 50.00]);
        $this->assertDatabaseHas('order_field_values', ['value' => '123456789012345']);
        $this->assertDatabaseHas('payments', ['amount' => 50.00, 'status' => 'UNPAID']);
    }

    public function test_guest_cannot_use_browser_price(): void
    {
        // No browser-submitted price field exists; server must load price from DB.
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
            'price' => '1.00',
        ]);

        $order = Order::first();
        $this->assertEquals(50.00, (float) $order->price_snapshot);
    }

    public function test_registered_user_order_is_linked_to_account(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'account',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        $order = Order::first();
        $this->assertEquals($user->id, $order->customer->user_id);
        $this->assertEquals($user->email, $order->customer_email);
    }

    public function test_invalid_imei_rejects_order(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => 'not-an-imei'],
            'consent' => '1',
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_inactive_service_cannot_be_ordered(): void
    {
        $this->service->update(['is_active' => false]);

        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ])->assertSessionHasErrors('service_slug');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_consent_is_required(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
        ])->assertSessionHasErrors('consent');
    }

    public function test_consent_timestamp_is_persisted(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        $order = Order::first();
        $this->assertNotNull($order->consent_given_at);
    }

    public function test_invalid_serial_number_rejects_order(): void
    {
        ServiceField::create([
            'service_id' => $this->service->id,
            'label' => 'Serial',
            'internal_name' => 'serial',
            'type' => 'SERIAL_NUMBER',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345', 2 => '!!bad serial!!'],
            'consent' => '1',
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_valid_serial_number_passes(): void
    {
        ServiceField::create([
            'service_id' => $this->service->id,
            'label' => 'Serial',
            'internal_name' => 'serial',
            'type' => 'SERIAL_NUMBER',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345', 2 => 'SN-1234_AB'],
            'consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_tracking_token_is_verified_on_lookup(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        $order = Order::first();

        $this->post(route('order.lookup.submit'), [
            'order_number' => $order->order_number,
            'tracking_token' => 'wrong-token',
        ])->assertSessionHasErrors('order_number');
    }

    public function test_order_has_status_history(): void
    {
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        $order = Order::first();
        $this->assertDatabaseHas('order_status_history', ['order_id' => $order->id, 'to_status' => 'PENDING']);
    }

    public function test_order_review_step_shows_summary_and_confirms(): void
    {
        $this->post(route('orders.review'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Reviewer',
            'customer_email' => 'reviewer@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ])->assertRedirect(route('orders.review-page'));

        $this->get(route('orders.review-page'))
            ->assertOk()
            ->assertSee('Review your order')
            ->assertSee('123456789012345')
            ->assertSee('50.00');

        $this->assertDatabaseCount('orders', 0);

        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
        ])->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', ['customer_email' => 'reviewer@example.com', 'price_snapshot' => 50.00]);
        $this->assertDatabaseHas('order_field_values', ['value' => '123456789012345']);
    }

    public function test_order_review_page_is_not_accessible_without_submission(): void
    {
        $this->get(route('orders.review-page'))->assertNotFound();
    }

    public function test_review_rejects_invalid_field_before_creating_order(): void
    {
        $this->post(route('orders.review'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => 'bad-imei'],
            'consent' => '1',
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('orders', 0);
    }
}