<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\OrderResult;
use App\Models\OrderStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('customer')->latest();

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();
        $statuses = ['PENDING', 'PROCESSING', 'WAITING_FOR_CUSTOMER', 'COMPLETED', 'REJECTED', 'CANCELLED'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'customer',
            'service',
            'couponUsage',
            'fieldValues',
            'statusHistory.user',
            'messages.user',
            'results.user',
            'payments.method',
            'payments.proofs',
            'payments.refunds',
        ]);

        OrderMessage::where('order_id', $order->id)->whereNull('read_at')->update(['read_at' => now()]);

        $statuses = ['PENDING', 'PROCESSING', 'WAITING_FOR_CUSTOMER', 'COMPLETED', 'REJECTED', 'CANCELLED'];

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function status(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:PENDING,PROCESSING,WAITING_FOR_CUSTOMER,COMPLETED,REJECTED,CANCELLED'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($order->status === $request->status) {
            return back()->with('status', 'Order already has this status.');
        }

        $from = $order->status;

        DB::transaction(function () use ($request, $order, $from) {
            $order->update([
                'status' => $request->status,
                'completed_at' => $request->status === 'COMPLETED' ? now() : $order->completed_at,
                'cancelled_at' => in_array($request->status, ['CANCELLED', 'REJECTED']) ? now() : $order->cancelled_at,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $request->status,
                'user_id' => $request->user()->id,
                'note' => $request->note,
                'created_at' => now(),
            ]);
        });

        \App\Helpers\AuditLogger::log('order.status-change', $order, ['status' => $from], ['status' => $request->status]);

        $this->notifyCustomer($order, 'Order ' . str_replace('_', ' ', $request->status), 'Your order status changed to ' . str_replace('_', ' ', $request->status) . '.');

        $event = match ($request->status) {
            'PROCESSING' => 'processing',
            'WAITING_FOR_CUSTOMER' => 'waiting_for_customer',
            'COMPLETED' => 'completed',
            'CANCELLED', 'REJECTED' => 'cancelled',
            default => null,
        };

        if ($event) {
            \App\Jobs\SendTelegramOrderNotification::dispatch($order, $event);
        }

        return back()->with('status', 'Order status updated.');
    }

    protected function notifyCustomer(Order $order, string $title, string $message): void
    {
        if ($order->customer?->user_id) {
            \App\Models\User::find($order->customer->user_id)?->notify(new \App\Notifications\OrderStatusNotification($order, $title, $message));
        }
    }

    public function message(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'type' => ['required', 'in:CUSTOMER,INTERNAL'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        OrderMessage::create([
            'order_id' => $order->id,
            'type' => $request->type,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        return back()->with('status', 'Message sent.');
    }

    public function result(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'type' => ['required', 'in:TEXT,LINK,CODE,INSTRUCTIONS,FILE'],
            'content' => ['required_without:file', 'nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,zip,txt'],
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('order-results', 'local');
        }

        OrderResult::create([
            'order_id' => $order->id,
            'type' => $request->type,
            'content' => $request->content,
            'file_path' => $path,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Result added.');
    }

    public function downloadResult(OrderResult $result)
    {
        abort_if(! $result->file_path, 404);

        return Storage::disk('local')->download($result->file_path);
    }

    public function downloadField(\App\Models\OrderFieldValue $fieldValue)
    {
        abort_if(! $fieldValue->file_path, 404);

        return Storage::disk('local')->download($fieldValue->file_path);
    }
}
