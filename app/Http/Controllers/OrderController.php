<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_slug' => ['required', 'string', 'exists:services,slug'],
            'customer_name' => ['required_if:customer_lookup,guest', 'nullable', 'string', 'max:255'],
            'customer_email' => ['required_if:customer_lookup,guest', 'nullable', 'email'],
            'customer_phone' => ['required_if:customer_lookup,guest', 'nullable', 'string', 'max:32'],
        ]);

        $order = $this->orderService->create($request->all(), Auth::user());

        return redirect()->route('orders.confirmation', ['order' => $order->order_number, 'token' => $order->tracking_code_plain]);
    }

    public function confirmation(string $order, string $token)
    {
        $orderModel = \App\Models\Order::where('order_number', $order)
            ->with(['statusHistory', 'couponUsage', 'service'])
            ->firstOrFail();

        if (! \Illuminate\Support\Facades\Hash::check($token, $orderModel->tracking_token)) {
            abort(404);
        }

        return view('orders.confirmation', ['order' => $orderModel, 'token' => $token]);
    }
}
