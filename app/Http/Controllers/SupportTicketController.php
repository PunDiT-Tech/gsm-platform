<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $customer = request()->user()->customer;

        $tickets = $customer ? $customer->tickets()->latest()->get() : collect();

        return view('support.index', compact('tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $customer = request()->user()->customer;

        $ticket = SupportTicket::create([
            'customer_id' => $customer?->id,
            'subject' => $request->subject,
            'order_number' => $request->order_number,
            'status' => 'OPEN',
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-files', 'local')
            : null;

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'customer_id' => $customer?->id,
            'message' => $request->message,
            'attachment_path' => $path,
        ]);

        \App\Helpers\StaffNotifier::notify('New support ticket', 'Ticket "' . $ticket->subject . '" opened by ' . ($customer?->name ?? 'guest') . '.');

        return redirect()->route('support.show', $ticket)->with('status', 'Ticket created. We will respond shortly.');
    }

    public function show(SupportTicket $ticket): View
    {
        $customer = request()->user()->customer;

        abort_if($ticket->customer_id !== $customer?->id, 403);

        $ticket->load(['messages.user', 'customer']);

        $ticket->messages->whereNull('read_at')->whereNotNull('user_id')->each->update(['read_at' => now()]);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $customer = request()->user()->customer;

        abort_if($ticket->customer_id !== $customer?->id, 403);

        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-files', 'local')
            : null;

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'customer_id' => $customer?->id,
            'message' => $request->message,
            'attachment_path' => $path,
        ]);

        if (in_array($ticket->status, ['CLOSED', 'REPLIED'])) {
            $ticket->update(['status' => 'REOPENED']);
        }

        return back()->with('status', 'Message sent.');
    }

    public function downloadAttachment(SupportMessage $message)
    {
        $customer = request()->user()->customer;

        abort_if($message->ticket->customer_id !== $customer?->id, 403);
        abort_unless($message->attachment_path, 404);

        return Storage::disk('local')->download($message->attachment_path);
    }
}
