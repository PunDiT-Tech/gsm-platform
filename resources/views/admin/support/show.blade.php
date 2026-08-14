@extends('layouts.admin')

@section('title', 'Ticket: ' . $ticket->subject)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.support.index') }}" class="text-sm text-blue-600 hover:underline">← Back to tickets</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $ticket->subject }}</h1>
            <p class="text-gray-500 text-sm">{{ $ticket->customer?->name ?? 'Guest' }} · {{ $ticket->order_number ?? 'No order' }} · <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100">{{ $ticket->status }}</span></p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Conversation</h2>
            <div class="space-y-3 mb-6">
                @forelse ($ticket->messages as $message)
                    <div class="text-sm p-3 rounded-md {{ $message->user_id ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }}">
                        <p class="font-medium">{{ $message->user?->name ?? ($message->customer?->name ?? 'Customer') }} <span class="text-gray-400 font-normal">· {{ $message->created_at->format('M d, H:i') }}</span></p>
                        <p class="mt-0.5 text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No messages yet.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="space-y-2">
                @csrf
                <textarea name="message" rows="3" required placeholder="Write a reply…"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Reply</button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Status</h2>
                <form method="POST" action="{{ route('admin.support.status', $ticket) }}" class="space-y-2">
                    @csrf
                    <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach (['OPEN', 'ASSIGNED', 'REPLIED', 'CLOSED', 'REOPENED'] as $status)
                            <option value="{{ $status }}" @selected($ticket->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection