@extends('layouts.dashboard')

@section('title', 'Ticket')

@section('panel')
    <a href="{{ route('support.index') }}" class="text-sm text-blue-600 hover:underline">← Support</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-2">{{ $ticket->subject }}</h2>
    <p class="text-sm text-gray-500 mb-6">Status: <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100">{{ $ticket->status }}</span></p>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <div class="space-y-3 mb-4">
            @forelse ($ticket->messages as $message)
                <div class="text-sm p-3 rounded-md {{ $message->user_id ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }}">
                    <p class="font-medium">{{ $message->user?->name ?? 'You' }} <span class="text-gray-400 font-normal">· {{ $message->created_at->format('M d, H:i') }}</span></p>
                    <p class="mt-0.5 text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                    @if ($message->attachment_path)
                        <a href="{{ route('support.attachment-download', $message) }}" class="mt-1 inline-flex items-center text-xs text-blue-600 hover:underline">📎 Download attachment</a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No messages.</p>
            @endforelse
        </div>

        @if ($ticket->status !== 'CLOSED')
            <form method="POST" action="{{ route('support.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <textarea name="message" rows="2" required placeholder="Reply…"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                <input type="file" name="attachment" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Send</button>
            </form>
        @else
            <p class="text-sm text-gray-500">This ticket is closed.</p>
        @endif
    </div>
@endsection