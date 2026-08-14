<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['order', 'method'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(20)->withQueryString();
        $statuses = ['UNPAID', 'PROOF_SUBMITTED', 'UNDER_REVIEW', 'VERIFIED', 'REJECTED', 'REFUNDED'];

        return view('admin.payments.index', compact('payments', 'statuses'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'VERIFIED',
                'verified_by' => request()->user()->id,
                'verified_at' => now(),
            ]);

            $order = $payment->order;
            if ($order->payment_status === 'UNPAID' || $order->payment_status === 'PROOF_SUBMITTED' || $order->payment_status === 'UNDER_REVIEW') {
                $order->update(['payment_status' => 'VERIFIED']);
            }
        });

        $this->notify($payment->order, 'Payment verified', 'Your payment has been verified. We will begin processing your order.');
        \App\Jobs\SendTelegramOrderNotification::dispatch($payment->order, 'payment_verified');
        \App\Helpers\AuditLogger::log('payment.verify', $payment);

        return back()->with('status', 'Payment verified.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'REJECTED',
                'verified_by' => request()->user()->id,
                'verified_at' => now(),
            ]);

            $payment->order()->update(['payment_status' => 'REJECTED']);
        });

        $this->notify($payment->order, 'Payment rejected', 'Your payment proof was rejected. Please check your order and resubmit a valid proof.');
        \App\Jobs\SendTelegramOrderNotification::dispatch($payment->order, 'payment_rejected');
        \App\Helpers\AuditLogger::log('payment.reject', $payment);

        return back()->with('status', 'Payment rejected.');
    }

    protected function notify(\App\Models\Order $order, string $title, string $message): void
    {
        if ($order->customer?->user_id) {
            \App\Models\User::find($order->customer->user_id)?->notify(new \App\Notifications\OrderStatusNotification($order, $title, $message));
        }
    }

    public function downloadProof(PaymentProof $proof)
    {
        abort_unless($proof->file_path, 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->download($proof->file_path, $proof->original_name);
    }
}
