<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnreadMessageBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

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

    protected function registeredUserWithOrder(): array
    {
        $user = User::factory()->create(['email' => 'owner@example.com', 'email_verified_at' => now()]);
        Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);

        $this->actingAs($user)->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'account',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        return [$user, Order::firstOrFail()];
    }

    public function test_dashboard_shows_unread_badge_when_staff_has_messages(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $staff = User::factory()->create(['email' => 'staff@example.com', 'email_verified_at' => now()]);

        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'user_id' => $staff->id, 'message' => 'Hello']);
        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'user_id' => $staff->id, 'message' => 'Update']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2 unread messages');
    }

    public function test_dashboard_hides_badge_when_messages_are_read(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $staff = User::factory()->create(['email' => 'staff@example.com', 'email_verified_at' => now()]);

        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'user_id' => $staff->id, 'message' => 'Hello', 'read_at' => now()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('unread message');
    }

    public function test_orders_list_shows_badge_per_order(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $staff = User::factory()->create(['email' => 'staff@example.com', 'email_verified_at' => now()]);

        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'user_id' => $staff->id, 'message' => 'Update']);

        $this->actingAs($user)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('1 unread message');
    }

    public function test_orders_list_badge_clears_after_viewing_order(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $staff = User::factory()->create(['email' => 'staff@example.com', 'email_verified_at' => now()]);

        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'user_id' => $staff->id, 'message' => 'Update']);

        $this->actingAs($user)->get(route('orders.show', $order))->assertOk();

        $this->actingAs($user)->get(route('orders.index'))
            ->assertOk()
            ->assertDontSee('unread message');
    }

    public function test_customer_own_messages_do_not_count_as_unread(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();

        OrderMessage::create(['order_id' => $order->id, 'type' => 'CUSTOMER', 'message' => 'Sent by me']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('unread message');
    }

    public function test_internal_messages_do_not_count_as_unread(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $staff = User::factory()->create(['email' => 'staff@example.com', 'email_verified_at' => now()]);

        OrderMessage::create(['order_id' => $order->id, 'type' => 'INTERNAL', 'user_id' => $staff->id, 'message' => 'Internal']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('unread message');
    }

    public function test_admin_assign_role_can_reply_and_customer_sees_badge(): void
    {
        [$user, $order] = $this->registeredUserWithOrder();
        $admin = User::factory()->create(['email' => 'admin@example.com', 'email_verified_at' => now()]);
        $admin->roles()->attach(Role::where('name', 'SUPER_ADMIN')->firstOrFail());

        $this->actingAs($admin)->post(route('admin.orders.message', $order), [
            'type' => 'CUSTOMER',
            'message' => 'We are working on it',
        ])->assertSessionHas('status');

        $this->actingAs($user)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('1 unread message');
    }
}
