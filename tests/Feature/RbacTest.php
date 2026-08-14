<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    protected function makeStaff(string $role): User
    {
        $roleModel = Role::where('name', $role)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($roleModel);

        return $user;
    }

    public function test_admin_requires_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_regular_customer_cannot_access_admin(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-TEST-1',
            'tracking_token' => 'x',
            'customer_id' => \App\Models\Customer::create(['name' => 'X', 'email' => 'x@example.com'])->id,
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
        ]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.orders.status', $order))->assertForbidden();
    }

    public function test_super_admin_can_access_admin(): void
    {
        $user = $this->makeStaff('SUPER_ADMIN');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('admin.services.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.payments.index'))->assertOk();
    }

    public function test_support_cannot_edit_services(): void
    {
        $user = $this->makeStaff('SUPPORT');

        $this->actingAs($user)->get(route('admin.services.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.services.store'), [])->assertForbidden();
    }

    public function test_support_can_view_orders_but_not_verify_payments(): void
    {
        $user = $this->makeStaff('SUPPORT');
        $customer = \App\Models\Customer::create(['name' => 'X', 'email' => 'x@example.com']);
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-TEST-2',
            'tracking_token' => 'x',
            'customer_id' => $customer->id,
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
        ]);
        $payment = \App\Models\Payment::create(['order_id' => $order->id, 'amount' => 1, 'currency' => 'USD', 'status' => 'PROOF_SUBMITTED']);

        $this->actingAs($user)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($user)->post(route('admin.payments.verify', $payment))->assertForbidden();
        $this->actingAs($user)->post(route('admin.payments.reject', $payment))->assertForbidden();
    }

    public function test_finance_cannot_edit_services(): void
    {
        $user = $this->makeStaff('FINANCE');

        $this->actingAs($user)->get(route('admin.services.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.payments.index'))->assertOk();
    }

    public function test_admin_role_matches_matrix(): void
    {
        $user = $this->makeStaff('ADMIN');

        $this->assertTrue($user->hasPermission('services.create'));
        $this->assertTrue($user->hasPermission('payments.verify'));
        $this->assertFalse($user->hasPermission('payments.refund'));
        $this->assertTrue($user->hasPermission('payments.reject'));
    }

    public function test_unauthorized_direct_url_is_blocked(): void
    {
        $user = $this->makeStaff('SUPPORT');

        $this->actingAs($user)->get(route('admin.categories.create'))->assertForbidden();
    }
}
