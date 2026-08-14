<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $userId = (int) $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->is_active || ! $this->twoFactor->isEnabled($user)) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $code = trim($request->code);
        $valid = $this->twoFactor->verify((string) $user->two_factor_secret, $code)
            || $this->twoFactor->recover($user, $code);

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.',
            ]);
        }

        $remember = (bool) $request->session()->get('login.remember', false);

        Auth::login($user, $remember);

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        AdminActivityLog::create([
            'user_id' => $user->id,
            'type' => 'login',
            'description' => 'Admin login (2FA)',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->intended($user->isStaff() ? route('admin.dashboard') : route('dashboard'));
    }
}