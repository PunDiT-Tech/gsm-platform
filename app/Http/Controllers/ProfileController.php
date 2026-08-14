<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]{7,32}$/'],
        ]);

        $request->user()->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        if ($request->user()->customer) {
            $request->user()->customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);
        }

        return back()->with('status', 'Profile updated.');
    }

    public function security(): View
    {
        return view('profile.security');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()->mixedCase()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'Password changed successfully.');
    }
}
