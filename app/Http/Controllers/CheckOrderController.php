<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CheckOrderController extends Controller
{
    public function create(): View
    {
        return view('pages.check-order');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => ['required', 'string'],
            'tracking_token' => ['required', 'string'],
        ]);

        $order = Order::where('order_number', $request->order_number)
            ->with(['service', 'statusHistory', 'messages' => fn ($q) => $q->where('type', '!=', 'INTERNAL'), 'results'])
            ->first();

        if (! $order || ! Hash::check($request->tracking_token, $order->tracking_token)) {
            return back()->withErrors(['order_number' => 'Order not found or tracking code is incorrect.']);
        }

        return view('pages.order-track', ['order' => $order]);
    }
}
