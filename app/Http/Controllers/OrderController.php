<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function review(Request $request): RedirectResponse
    {
        $request->validate([
            'service_slug' => ['required', 'string', 'exists:services,slug'],
            'customer_name' => ['required_if:customer_lookup,guest', 'nullable', 'string', 'max:255'],
            'customer_email' => ['required_if:customer_lookup,guest', 'nullable', 'email'],
            'customer_phone' => ['required_if:customer_lookup,guest', 'nullable', 'string', 'max:32'],
        ]);

        $preview = $this->orderService->preview($request->all(), Auth::user());

        $request->session()->put('order_review', $preview);

        return redirect()->route('orders.review-page');
    }

    public function reviewPage(Request $request): View
    {
        $preview = $request->session()->get('order_review');

        abort_unless($preview, 404);

        return view('orders.review', compact('preview'));
    }

    public function store(Request $request): RedirectResponse
    {
        $preview = $request->session()->get('order_review');

        if ($preview && $preview['service']->slug === $request->input('service_slug')) {
            $data = [
                'fields' => $preview['fields'],
            ];
            $data['service_slug'] = $preview['service']->slug;
            $data['consent'] = '1';
            $data['customer_name'] = $preview['customer']['name'] ?? null;
            $data['customer_email'] = $preview['customer']['email'] ?? null;
            $data['customer_phone'] = $preview['customer']['phone'] ?? null;

            if ($preview['coupon']) {
                $data['coupon_code'] = $preview['coupon']->code;
            }

            $order = $this->orderService->create($data, Auth::user());

            $this->orderService->cleanupStagedFiles($preview['token'] ?? null);
            $request->session()->forget('order_review');

            return redirect()->route('orders.confirmation', ['order' => $order->order_number, 'token' => $order->tracking_code_plain]);
        }

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
