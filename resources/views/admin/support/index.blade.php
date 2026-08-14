@extends('layouts.admin')

@section('title', 'Support Tickets')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Support tickets</h1>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Subject</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Customer</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Assignee</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($tickets as $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.support.show', $ticket) }}" class="text-blue-600 hover:underline font-medium">{{ $ticket->subject }}</a></td>
                        <td class="px-4 py-3">{{ $ticket->customer?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $ticket->order_number ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $ticket->status }}</span></td>
                        <td class="px-4 py-3">{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $ticket->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No tickets.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
@endsection