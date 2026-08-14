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
                            @if ($payment->status === 'VERIFIED' && auth()->user()->hasPermission('payments.refund'))
                                <details class="inline-block align-middle">
                                    <summary class="cursor-pointer text-amber-600 hover:underline text-sm">Refund</summary>
                                    <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-4 space-y-2 z-10">
                                        @csrf
                                        <p class="text-xs text-gray-500">Recording a refund marks this payment and order as refunded. No automatic transfer occurs.</p>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Amount</label>
                                            <input type="number" step="0.01" min="0.01" max="{{ $payment->amount }}" name="amount" value="{{ $payment->amount }}" required
                                                class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Reason</label>
                                            <textarea name="reason" rows="2" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                        </div>
                                        <div class="flex gap-2">
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-700">Method</label>
                                                <input type="text" name="method" placeholder="e.g. Bank Transfer" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-700">Reference</label>
                                                <input type="text" name="reference" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            </div>
                                        </div>
                                        <button class="w-full bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-amber-700">Record refund</button>
                                    </form>
                                </details>
                            @endif
                            @if ($payment->status !== 'VERIFIED' && $payment->status !== 'REJECTED' && $payment->status !== 'REFUNDED')
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

    @if ($refunds->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mt-8">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900 text-sm">Recent refunds</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Order</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Amount</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Method</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Reason</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Admin</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($refunds as $refund)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $refund->order_id) }}" class="font-mono text-xs text-blue-600 hover:underline">{{ $refund->order?->order_number }}</a></td>
                            <td class="px-4 py-3">{{ $refund->amount }} {{ $refund->currency }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $refund->method ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $refund->reason ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $refund->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $refund->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection