@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Payments</h1>

    <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap gap-3 mb-6">
        <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Filter</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Method</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Amount</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $payment->order_id) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $payment->order?->order_number }}</a></td>
                        <td class="px-4 py-3">{{ $payment->method?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $payment->amount }} {{ $payment->currency }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $payment->status === 'VERIFIED' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $payment->status }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if ($payment->status !== 'VERIFIED' && $payment->status !== 'REJECTED')
                                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">
                                    @csrf
                                    <button class="text-green-600 hover:underline">Verify</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline">
                                    @csrf
                                    <button class="text-red-600 hover:underline">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection