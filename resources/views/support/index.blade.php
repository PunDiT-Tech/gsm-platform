@extends('layouts.dashboard')

@section('title', 'Support')

@section('panel')
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Support</h2>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h3 class="font-semibold text-gray-900 mb-4">Open a new ticket</h3>
        <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Order number (optional)</label>
                    <input type="text" name="order_number" value="{{ old('order_number') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="3" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('message') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Attachment (optional)</label>
                <input type="file" name="attachment" class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Submit ticket</button>
        </form>
    </div>

    <h3 class="text-lg font-semibold text-gray-900 mb-3">Your tickets</h3>
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        @forelse ($tickets as $ticket)
            <a href="{{ route('support.show', $ticket) }}" class="block p-4 border-b border-gray-100 hover:bg-gray-50 last:border-0">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900">{{ $ticket->subject }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $ticket->status }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $ticket->updated_at->format('M d, Y H:i') }}</p>
            </a>
        @empty
            <div class="p-8 text-center text-gray-500">No support tickets.</div>
        @endforelse
    </div>
@endsection