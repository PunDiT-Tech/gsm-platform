<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
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
            'price' => 100.00,
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
    }

    protected function placeOrder(?string $couponCode = null): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Guest',
            'customer_email' => 'coupon@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
            'coupon_code' => $couponCode,
        ]);
    }

    public function test_percent_coupon_applies_discount(): void
    {
        Coupon::create(['code' => 'SAVE10', 'type' => 'PERCENT', 'value' => 10, 'is_active' => true]);

        $this->placeOrder('SAVE10');

        $order = Order::firstOrFail();
        $this->assertEquals(90.00, (float) $order->price_snapshot);
        $this->assertEquals('SAVE10', $order->coupon_code);
        $this->assertDatabaseHas('coupon_usage', ['order_id' => $order->id, 'discount_amount' => 10.00]);
    }

    public function test_fixed_coupon_applies_discount(): void
    {
        Coupon::create(['code' => 'FIX15', 'type' => 'FIXED', 'value' => 15, 'is_active' => true]);

        $this->placeOrder('FIX15');

        $order = Order::firstOrFail();
        $this->assertEquals(85.00, (float) $order->price_snapshot);
    }

    public function test_invalid_coupon_rejected(): void
    {
        $this->placeOrder('NOPE')->assertSessionHasErrors('coupon_code');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_expired_coupon_rejected(): void
    {
        Coupon::create(['code' => 'OLD', 'type' => 'PERCENT', 'value' => 10, 'is_active' => true, 'expires_at' => now()->subDay()]);

        $this->placeOrder('OLD')->assertSessionHasErrors('coupon_code');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_usage_limit_enforced(): void
    {
        Coupon::create(['code' => 'LIMIT', 'type' => 'PERCENT', 'value' => 10, 'usage_limit' => 1, 'is_active' => true]);

        $this->placeOrder('LIMIT');
        $this->assertDatabaseCount('orders', 1);

        // Different customer, same coupon -> exhausted
        $this->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'guest',
            'customer_name' => 'Other',
            'customer_email' => 'other@example.com',
            'customer_phone' => '123456789',
            'fields' => [1 => '998877665544332'],
            'consent' => '1',
            'coupon_code' => 'LIMIT',
        ])->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_per_customer_limit_enforced(): void
    {
        $user = User::factory()->create(['email' => 'member@example.com', 'email_verified_at' => now()]);
        $customer = Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);

        Coupon::create(['code' => 'ONEPP', 'type' => 'PERCENT', 'value' => 10, 'per_customer_limit' => 1, 'is_active' => true]);

        foreach (['123456789012345', '998877665544332'] as $imei) {
            $this->actingAs($user)->post(route('orders.store'), [
                'service_slug' => 'diagnostic',
                'customer_lookup' => 'account',
                'fields' => [1 => $imei],
                'consent' => '1',
                'coupon_code' => 'ONEPP',
            ]);
        }

        $orders = Order::count();
        $this->assertEquals(1, $orders);
        $this->assertDatabaseHas('orders', ['customer_id' => $customer->id, 'coupon_code' => 'ONEPP']);
    }
}
