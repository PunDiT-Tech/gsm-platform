<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        $contactEmail = WebsiteSetting::get('contact_email', '');
        $contactPhone = WebsiteSetting::get('contact_phone', '');
        $orderExpiryHours = WebsiteSetting::get('order_expiry_hours', config('app.order_expiry_hours'));

        return view('admin.settings.index', compact('methods', 'contactEmail', 'contactPhone', 'orderExpiryHours'));
    }

    public function updateWebsite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'order_expiry_hours' => ['required', 'integer', 'min:1', 'max:720'],
        ]);

        WebsiteSetting::set('contact_email', $data['contact_email']);
        WebsiteSetting::set('contact_phone', $data['contact_phone']);
        WebsiteSetting::set('order_expiry_hours', $data['order_expiry_hours']);

        return back()->with('status', 'Settings saved.');
    }

    public function updateMethod(Request $request, PaymentMethod $method): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $config = $request->only(['qr_image', 'payment_id', 'wallet', 'network', 'account_name', 'account_number', 'swift', 'branch', 'address']);
        $config = array_filter($config, fn ($v) => ! is_null($v));

        $method->update($data + ['configuration' => $config]);

        return back()->with('status', 'Payment method updated.');
    }
}