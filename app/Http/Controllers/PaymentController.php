<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function show(Order $order, ?string $token = null)
    {
        $isOwner = auth()->check() && auth()->user()->id === $order->customer?->user_id;
        $validToken = $token && Hash::check($token, $order->tracking_token);

        if (! $isOwner && ! $validToken) {
            abort(404);
        }

        $order->load(['payments.method']);
        $methods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('orders.payment', compact('order', 'token', 'methods'));
    }

    public function upload(Request $request, Order $order): RedirectResponse
    {
        $isOwner = auth()->check() && auth()->user()->id === $order->customer?->user_id;
        $validToken = $request->token && Hash::check($request->token, $order->tracking_token);

        if (! $isOwner && ! $validToken) {
            abort(403);
        }

        $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'proof' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = Payment::where('id', $request->payment_id)->where('order_id', $order->id)->firstOrFail();

        $filePath = null;
        if ($request->hasFile('proof')) {
            $filePath = $request->file('proof')->store('payment-proofs', 'local');
        }

        DB::transaction(function () use ($payment, $order, $request, $filePath) {
            PaymentProof::create([
                'payment_id' => $payment->id,
                'file_path' => $filePath,
                'original_name' => $request->hasFile('proof') ? $request->file('proof')->getClientOriginalName() : null,
                'mime_type' => $request->hasFile('proof') ? $request->file('proof')->getMimeType() : null,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
            ]);

            if ($payment->status === 'UNPAID') {
                $payment->update(['status' => 'PROOF_SUBMITTED']);
            }
            if (in_array($order->payment_status, ['UNPAID', 'REJECTED'])) {
                $order->update(['payment_status' => 'PROOF_SUBMITTED']);
            }
        });

        \App\Jobs\SendTelegramOrderNotification::dispatch($order, 'payment_proof');

        return back()->with('status', 'Payment proof submitted. We will review it shortly.');
    }
}
