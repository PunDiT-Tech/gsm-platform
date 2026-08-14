<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AuditLogger;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->whereHas('roles')->orderBy('name')->paginate(20);
        $roles = Role::with('permissions')->get();

        return view('admin.staff.index', compact('users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $request->phone,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($data['roles']);
        AuditLogger::log('staff.create', $user, null, ['name' => $user->name, 'email' => $user->email, 'roles' => array_values($data['roles'])]);

        return back()->with('status', 'Staff member created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'is_active' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $before = ['name' => $user->name, 'email' => $user->email, 'is_active' => $user->is_active];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        AuditLogger::log('staff.update', $user, $before, ['name' => $user->name, 'email' => $user->email, 'is_active' => $user->is_active]);

        return back()->with('status', 'Staff member updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(request()->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->roles()->detach();
        $user->update(['is_active' => false]);
        AuditLogger::log('staff.deactivate', $user, ['is_active' => true], ['is_active' => false]);

        return back()->with('status', 'Staff member deactivated.');
    }
}