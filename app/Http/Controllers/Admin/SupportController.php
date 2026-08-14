<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::with(['customer', 'assignee', 'messages' => fn ($q) => $q->whereNull('read_at')->whereNotNull('user_id')])
            ->latest()
            ->paginate(20);

        return view('admin.support.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        $staff = User::whereHas('roles')->orderBy('name')->get();

        $ticket->load(['messages.user', 'customer']);

        SupportMessage::where('support_ticket_id', $ticket->id)
            ->whereNull('read_at')
            ->whereNull('user_id')
            ->update(['read_at' => now()]);

        return view('admin.support.show', compact('ticket', 'staff'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-files', 'local')
            : null;

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'attachment_path' => $path,
        ]);

        if ($ticket->status === 'CLOSED') {
            $ticket->update(['status' => 'REOPENED']);
        } elseif ($ticket->status === 'OPEN') {
            $ticket->update(['status' => 'REPLIED']);
        }

        return back()->with('status', 'Reply sent.');
    }

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['user_id' => ['nullable', 'exists:users,id']]);

        $ticket->update([
            'user_id' => $request->user_id,
            'status' => 'ASSIGNED',
        ]);

        return back()->with('status', 'Ticket assigned.');
    }

    public function status(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:OPEN,ASSIGNED,REPLIED,CLOSED,REOPENED']]);

        $ticket->update(['status' => $request->status]);

        return back()->with('status', 'Ticket updated.');
    }

    public function downloadAttachment(SupportMessage $message)
    {
        abort_unless($message->attachment_path, 404);

        return Storage::disk('local')->download($message->attachment_path);
    }
}
