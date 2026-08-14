<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::withCount('usages')->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['code'] = strtoupper($data['code']);

        if (Coupon::where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'A coupon with this code already exists.']);
        }

        Coupon::create($data);

        return back()->with('status', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validateData($request));

        return back()->with('status', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('status', 'Coupon deleted.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'type' => ['required', 'in:PERCENT,FIXED'],
            'value' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['per_customer_limit'] = $data['per_customer_limit'] ?? 1;

        return $data;
    }
}
