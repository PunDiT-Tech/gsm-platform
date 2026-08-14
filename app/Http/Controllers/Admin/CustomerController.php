<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount('orders')->with('user')->latest();

        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%")
                ->orWhere('phone', 'like', "%{$request->q}%"));
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['user', 'orders']);
        $orders = $customer->orders()->latest()->limit(50)->get();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function suspend(Customer $customer): RedirectResponse
    {
        $user = User::find($customer->user_id);

        if (! $user) {
            return back()->with('error', 'This customer has no linked account.');
        }

        if ($user->isStaff()) {
            return back()->with('error', 'Cannot suspend a staff account.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        \App\Helpers\AuditLogger::log('customer.suspend', $customer, ['is_active' => ! $user->is_active], ['is_active' => $user->is_active]);

        return back()->with('status', $user->is_active ? 'Customer account re-enabled.' : 'Customer account suspended.');
    }
}
