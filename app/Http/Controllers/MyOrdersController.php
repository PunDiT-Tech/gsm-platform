<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MyOrdersController extends Controller
{
    public function index(): View
    {
        $customer = request()->user()->customer;

        $orders = $customer
            ? $customer->orders()->latest()->paginate(15)
            : collect();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_if($order->customer->user_id !== request()->user()->id, 403);

        $order->load([
            'statusHistory',
            'messages' => fn ($q) => $q->where('type', '!=', 'INTERNAL'),
            'results',
            'payments.method',
            'fieldValues',
            'couponUsage',
        ]);

        $order->messages->whereNull('read_at')->each->update(['read_at' => now()]);

        return view('orders.show', compact('order'));
    }

    public function message(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->customer->user_id !== request()->user()->id, 403);

        $request->validate(['message' => ['required', 'string', 'max:4000']]);

        OrderMessage::create([
            'order_id' => $order->id,
            'type' => 'CUSTOMER',
            'message' => $request->message,
        ]);

        return back()->with('status', 'Message sent.');
    }

    public function upload(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->customer->user_id !== request()->user()->id, 403);

        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('order-files', 'local')
            : null;

        OrderMessage::create([
            'order_id' => $order->id,
            'type' => 'CUSTOMER',
            'message' => $request->message ?: 'Uploaded requested information.',
            'attachment_path' => $path,
        ]);

        return back()->with('status', 'Information uploaded.');
    }

    public function downloadResult(\App\Models\OrderResult $result)
    {
        abort_if($result->order->customer->user_id !== request()->user()->id, 403);
        abort_if(! $result->file_path, 404);

        return Storage::disk('local')->download($result->file_path);
    }

    public function downloadMessage(OrderMessage $message)
    {
        abort_if($message->order->customer->user_id !== request()->user()->id, 403);
        abort_if(! $message->attachment_path, 404);

        return Storage::disk('local')->download($message->attachment_path);
    }
}
