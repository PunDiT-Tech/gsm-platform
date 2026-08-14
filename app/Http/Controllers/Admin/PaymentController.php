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

        return back()->with('status', 'Payment rejected.');
    }

    public function downloadProof(PaymentProof $proof)
    {
        abort_unless($proof->file_path, 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->download($proof->file_path, $proof->original_name);
    }
}
