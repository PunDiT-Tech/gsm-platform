<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    protected function makeAdmin(): User
    {
        $roleModel = Role::where('name', 'SUPER_ADMIN')->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($roleModel);

        return $user;
    }

    public function test_admin_suspends_customer_account(): void
    {
        $admin = $this->makeAdmin();
        $customerUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $customer = Customer::create(['user_id' => $customerUser->id, 'name' => 'Bob', 'email' => $customerUser->email]);

        $this->actingAs($admin)->post(route('admin.customers.suspend', $customer))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['id' => $customerUser->id, 'is_active' => 0]);
    }

    public function test_admin_can_reenable_customer(): void
    {
        $admin = $this->makeAdmin();
        $customerUser = User::factory()->create(['email_verified_at' => now(), 'is_active' => false]);
        $customer = Customer::create(['user_id' => $customerUser->id, 'name' => 'Bob', 'email' => $customerUser->email]);

        $this->actingAs($admin)->post(route('admin.customers.suspend', $customer))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['id' => $customerUser->id, 'is_active' => 1]);
    }

    public function test_customer_cannot_suspend_self(): void
    {
        $roleModel = Role::where('name', 'SUPER_ADMIN')->first();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->attach($roleModel);
        $customerUser = User::factory()->create(['email_verified_at' => now()]);
        $customer = Customer::create(['user_id' => $customerUser->id, 'name' => 'Bob', 'email' => $customerUser->email]);

        $this->actingAs($customerUser)->post(route('admin.customers.suspend', $customer))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $customerUser->id, 'is_active' => 1]);
    }
}