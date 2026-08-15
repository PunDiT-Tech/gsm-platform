<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_logout_is_audited(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $admin = User::factory()->create(['email' => 'admin@example.com', 'password' => Hash::make('StrongPass1'), 'email_verified_at' => now()]);
        $admin->roles()->attach(Role::where('name', 'SUPER_ADMIN')->firstOrFail());

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'StrongPass1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $admin->id,
            'type' => 'logout',
        ]);
    }

    public function test_customer_logout_is_not_audited(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = User::factory()->create(['password' => Hash::make('StrongPass1')]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'StrongPass1',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertDatabaseMissing('admin_activity_logs', ['type' => 'logout']);
    }

    public function test_user_can_view_login_page(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in');
    }

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'password' => 'StrongPass1',
            'password_confirmation' => 'StrongPass1',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
        $this->assertAuthenticated();
    }

    public function test_duplicate_email_registration_is_rejected(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->post(route('register'), [
            'name' => 'Dup User',
            'email' => 'dup@example.com',
            'phone' => '123456789',
            'password' => 'StrongPass1',
            'password_confirmation' => 'StrongPass1',
            'terms' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post(route('register'), [
            'name' => 'No Terms',
            'email' => 'noterms@example.com',
            'phone' => '123456789',
            'password' => 'StrongPass1',
            'password_confirmation' => 'StrongPass1',
        ])->assertSessionHasErrors('terms');
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->post(route('register'), [
            'name' => 'Weak',
            'email' => 'weak@example.com',
            'phone' => '123456789',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ])->assertSessionHasErrors('password');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('StrongPass1')]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'StrongPass1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('StrongPass1')]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'WrongPass1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_suspended_account_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('StrongPass1'),
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'StrongPass1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_password_reset_link_is_sent(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_password_reset_flow_works(): void
    {
        $user = User::factory()->create();

        $token = app('auth.password.broker')->createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrong1',
            'password_confirmation' => 'NewStrong1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewStrong1', $user->fresh()->password));
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $token = app('auth.password.broker')->createToken($user);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subHours(2)]);

        config(['auth.passwords.users.expire' => 60]);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrong1',
            'password_confirmation' => 'NewStrong1',
        ])->assertSessionHasErrors('email');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
