<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
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
        ServiceField::create([
            'service_id' => $this->service->id,
            'label' => 'Notes',
            'internal_name' => 'notes',
            'type' => 'TEXTAREA',
            'is_required' => false,
            'sort_order' => 2,
        ]);
    }

    protected function placeGuestOrder(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('orders.store'), array_merge([
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'sec@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345', 2 => 'hello'],
            'consent' => '1',
        ], $overrides));
    }

    public function test_duplicate_submission_is_blocked(): void
    {
        $this->placeGuestOrder();
        $this->assertDatabaseCount('orders', 1);

        // Customer clicks submit twice with the same IMEI.
        $this->placeGuestOrder()->assertSessionHasErrors('fields');
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_duplicate_with_different_device_is_allowed(): void
    {
        $this->placeGuestOrder();
        $this->placeGuestOrder(['fields' => [1 => '998877665544332', 2 => 'hello']]);
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_xss_in_dynamic_field_is_escaped_on_render(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);

        $this->actingAs($user)->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'account',
            'fields' => [1 => '123456789012345', 2 => '<script>alert(1)</script>'],
            'consent' => '1',
        ]);

        $order = Order::firstOrFail();
        $html = $this->actingAs($user)->get(route('orders.show', $order))->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_invalid_route_returns_404_page(): void
    {
        $this->get('/does-not-exist')->assertStatus(404)->assertSee('Page not found');
    }

    public function test_internal_notes_never_rendered_to_customer(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);

        $this->actingAs($user)->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'account',
            'fields' => [1 => '123456789012345', 2 => 'x'],
            'consent' => '1',
        ]);

        $order = Order::firstOrFail();

        \App\Models\OrderMessage::create([
            'order_id' => $order->id,
            'type' => 'INTERNAL',
            'message' => 'Internal secret note',
        ]);

        $html = $this->actingAs($user)->get(route('orders.show', $order))->getContent();
        $this->assertStringNotContainsString('Internal secret note', $html);
    }
}
