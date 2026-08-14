<?php

namespace App\Http\Controllers;

use App\Services\QrCodeGenerator;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function show(Request $request): View
    {
        $user = $request->user();

        $this->authorizeStaff($user);

        $secret = session('two_factor_secret') ?: $this->twoFactor->generateSecret();

        return view('profile.two-factor', [
            'user' => $user,
            'enabled' => $this->twoFactor->isEnabled($user),
            'secret' => $secret,
            'qrSvg' => $this->qrFor($user, $secret),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorizeStaff($user);

        if ($this->twoFactor->isEnabled($user)) {
            return redirect()->route('profile.two-factor')->with('status', 'Two-factor authentication is already enabled.');
        }

        $secret = session('two_factor_secret') ?: $this->twoFactor->generateSecret();
        session(['two_factor_secret' => $secret]);

        return redirect()->route('profile.two-factor');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorizeStaff($user);

        $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $secret = (string) session('two_factor_secret');

        if (! $secret || ! $this->twoFactor->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'The verification code is invalid. Please try again.'])->withInput();
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        $request->session()->forget('two_factor_secret');
        $request->session()->flash('recovery_codes', $recoveryCodes);

        return redirect()->route('profile.two-factor')->with('status', 'Two-factor authentication is now enabled. Store your recovery codes in a safe place.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorizeStaff($user);

        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return redirect()->route('profile.two-factor')->with('status', 'Two-factor authentication has been disabled.');
    }

    private function authorizeStaff($user): void
    {
        abort_unless($user->isStaff(), 403);
    }

    private function qrFor($user, string $secret): string
    {
        $uri = $this->twoFactor->otpauthUriForSecret((string) $user->email, $secret);

        return (new QrCodeGenerator)->svg($uri);
    }
}