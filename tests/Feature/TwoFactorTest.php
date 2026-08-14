<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    protected function secret(): string
    {
        return (new TwoFactorService)->generateSecret();
    }

    protected function currentCode(string $secret): string
    {
        return (new TwoFactorService)->codeFor($secret, time());
    }

    protected function makeStaff(): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('StrongPass1'),
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach(Role::where('name', 'SUPER_ADMIN')->firstOrFail());

        return $user;
    }

    public function test_staff_can_enable_two_factor(): void
    {
        $staff = $this->makeStaff();

        $this->actingAs($staff)->post(route('profile.two-factor.enable'));
        $this->assertTrue(session()->has('two_factor_secret'));

        $secret = session('two_factor_secret');

        $this->post(route('profile.two-factor.confirm'), ['code' => $this->currentCode($secret)])
            ->assertRedirect(route('profile.two-factor'));

        $staff->refresh();

        $this->assertNotNull($staff->two_factor_confirmed_at);
        $this->assertNotEmpty($staff->two_factor_recovery_codes);
        $this->assertCount(10, $staff->two_factor_recovery_codes);
    }

    public function test_enable_requires_valid_code(): void
    {
        $staff = $this->makeStaff();

        $this->actingAs($staff)->post(route('profile.two-factor.enable'));
        $this->assertTrue(session()->has('two_factor_secret'));

        $this->post(route('profile.two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $staff->refresh();
        $this->assertNull($staff->two_factor_confirmed_at);
    }

    public function test_totp_matches_rfc6238_vectors(): void
    {
        $service = new TwoFactorService;
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertEquals('287082', $service->codeFor($secret, 59));
        $this->assertEquals('081804', $service->codeFor($secret, 1111111109));
        $this->assertEquals('050471', $service->codeFor($secret, 1111111111));
        $this->assertEquals('005924', $service->codeFor($secret, 1234567890));
        $this->assertEquals('279037', $service->codeFor($secret, 2000000000));
        $this->assertEquals('353130', $service->codeFor($secret, 20000000000));
    }

    public function test_login_requires_challenge_when_enabled(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();

        // Enable 2FA directly.
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => (new TwoFactorService)->generateRecoveryCodes(),
        ]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'StrongPass1',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
    }

    public function test_challenge_with_valid_code_logs_in(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => (new TwoFactorService)->generateRecoveryCodes(),
        ]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'StrongPass1',
        ]);

        $this->post(route('two-factor.challenge.store'), ['code' => $this->currentCode($secret)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($staff);
    }

    public function test_login_without_2fa_does_not_challenge(): void
    {
        $staffUser = $this->makeStaff();

        $this->post(route('login'), [
            'email' => $staffUser->email,
            'password' => 'StrongPass1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($staffUser);
    }

    public function test_challenge_with_invalid_code_fails(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => (new TwoFactorService)->generateRecoveryCodes(),
        ]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'StrongPass1',
        ]);

        $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_recovery_code_logs_in_and_is_consumed(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();
        $codes = (new TwoFactorService)->generateRecoveryCodes();
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $codes,
        ]);

        $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'StrongPass1',
        ]);

        $code = $codes[0];

        $this->post(route('two-factor.challenge.store'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $staff->refresh();
        $this->assertCount(9, $staff->two_factor_recovery_codes);
        $this->assertNotContains($code, $staff->two_factor_recovery_codes);
    }

    public function test_customer_cannot_access_staff_two_factor_setup(): void
    {
        $customer = User::factory()->create(['password' => Hash::make('StrongPass1')]);

        $this->actingAs($customer)->get(route('profile.two-factor'))->assertForbidden();
        $this->actingAs($customer)->post(route('profile.two-factor.enable'))->assertForbidden();
    }

    public function test_disable_requires_password_and_disables(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => (new TwoFactorService)->generateRecoveryCodes(),
        ]);

        $this->actingAs($staff)->post(route('profile.two-factor.disable'), [
            'current_password' => 'StrongPass1',
        ])->assertRedirect(route('profile.two-factor'));

        $staff->refresh();
        $this->assertNull($staff->two_factor_confirmed_at);
        $this->assertNull($staff->two_factor_secret);
    }

    public function test_disable_rejects_wrong_password(): void
    {
        $staff = $this->makeStaff();
        $secret = $this->secret();
        $staff->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => (new TwoFactorService)->generateRecoveryCodes(),
        ]);

        $this->actingAs($staff)->post(route('profile.two-factor.disable'), [
            'current_password' => 'WrongPass1',
        ])->assertSessionHasErrors('current_password');

        $staff->refresh();
        $this->assertNotNull($staff->two_factor_confirmed_at);
    }

    public function test_staff_security_page_links_to_two_factor(): void
    {
        $staff = $this->makeStaff();

        $this->actingAs($staff)->get(route('profile.security'))->assertOk()->assertSee('Two-factor authentication');
    }

    public function test_qr_generator_produces_valid_svg(): void
    {
        $svg = (new \App\Services\QrCodeGenerator)->svg('otpauth://totp/test@example.com?secret=TEST');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }
}