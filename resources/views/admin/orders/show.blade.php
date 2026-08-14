@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:underline">← Back to orders</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Order {{ $order->order_number }}</h1>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Order details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Service</dt><dd class="font-medium">{{ $order->service_name_snapshot }}</dd></div>
                    <div><dt class="text-gray-500">Amount</dt><dd class="font-medium">{{ $order->currency_snapshot }} {{ number_format((float) $order->price_snapshot, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Customer</dt><dd class="font-medium">{{ $order->customer_name }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $order->customer_email }}</dd></div>
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium">{{ $order->customer_phone }}</dd></div>
                    <div><dt class="text-gray-500">Submitted</dt><dd class="font-medium">{{ $order->created_at->format('M d, Y H:i') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Submitted fields</h2>
                @if ($order->fieldValues->isEmpty())
                    <p class="text-sm text-gray-500">No fields submitted.</p>
                @else
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @foreach ($order->fieldValues as $value)
                            <div>
                                <dt class="text-gray-500">{{ $value->label }}</dt>
                                <dd class="font-medium break-all">{{ $value->value ?: '—' }}</dd>
                                @if ($value->file_path)
                                    <a href="{{ route('admin.orders.field-download', $value) }}" class="text-blue-600 text-xs hover:underline">Download file</a>
                                @endif
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Status timeline</h2>
                <ol class="space-y-3 text-sm">
                    @foreach ($order->statusHistory as $history)
                        <li class="flex gap-3">
                            <span class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                            <div>
                                <p class="font-medium">{{ $history->from_status ? $history->from_status . ' → ' : '' }}{{ $history->to_status }}</p>
                                @if ($history->note)<p class="text-gray-600">{{ $history->note }}</p>@endif
                                <p class="text-xs text-gray-400">{{ optional($history->created_at)->format('M d, Y H:i') }} {{ $history->user?->name ? '· ' . $history->user->name : '' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Messages</h2>
                <div class="space-y-3 mb-4">
                    @forelse ($order->messages as $message)
                        <div class="text-sm p-3 rounded-md {{ $message->type === 'INTERNAL' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50' }}">
                            <p class="font-medium">{{ $message->type }} <span class="text-gray-400 font-normal">· {{ $message->created_at->format('M d, H:i') }} · {{ $message->user?->name ?? 'system' }}</span></p>
                            <p class="mt-0.5 text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No messages yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('admin.orders.message', $order) }}" class="space-y-2">
                    @csrf
                    <select name="type" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="CUSTOMER">Customer-visible message</option>
                        <option value="INTERNAL">Internal staff note</option>
                    </select>
                    <textarea name="message" rows="2" required placeholder="Write a message…"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Send message</button>
                </form>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Results</h2>
                <div class="space-y-3 mb-4">
                    @forelse ($order->results as $result)
                        <div class="text-sm p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="font-medium">{{ $result->type }} <span class="text-gray-400 font-normal">· {{ $result->created_at->format('M d, H:i') }} · {{ $result->user?->name ?? 'system' }}</span></p>
                            <p class="mt-0.5 text-gray-700 whitespace-pre-wrap break-all">{{ $result->content }}</p>
                            @if ($result->file_path)
                                <a href="{{ route('admin.orders.result-download', $result) }}" class="text-blue-600 text-xs hover:underline">Download file</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No results added yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('admin.orders.result', $order) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <select name="type" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="TEXT">Text</option>
                        <option value="CODE">Code</option>
                        <option value="LINK">Link</option>
                        <option value="INSTRUCTIONS">Instructions</option>
                        <option value="FILE">File</option>
                    </select>
                    <textarea name="content" rows="2" placeholder="Result content (or upload a file)…"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    <input type="file" name="file" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Add result</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Change status</h2>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-2">
                    @csrf
                    <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" placeholder="Note (optional)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">Update status</button>
                </form>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Payments</h2>
                @forelse ($order->payments as $payment)
                    <div class="border border-gray-200 rounded-md p-3 mb-3">
                        <p class="text-sm font-medium">{{ $payment->method?->name ?? 'Manual' }}</p>
                        <p class="text-sm text-gray-500">{{ $payment->amount }} {{ $payment->currency }}</p>
                        <p class="text-sm"><span class="px-2 py-0.5 rounded-full text-xs {{ $payment->status === 'VERIFIED' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $payment->status }}</span></p>
                        @foreach ($payment->proofs as $proof)
                            <div class="mt-2 text-sm">
                                <p class="text-gray-500">Proof: <a href="{{ route('admin.payments.proof-download', $proof) }}" class="text-blue-600 hover:underline">{{ $proof->original_name }}</a></p>
                                @if ($proof->transaction_id)<p class="text-gray-500">Tx: {{ $proof->transaction_id }}</p>@endif
                            </div>
                        @endforeach
                        @if ($payment->status !== 'VERIFIED' && $payment->status !== 'REJECTED')
                            <div class="flex gap-2 mt-3">
                                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                    @csrf
                                    <button class="bg-green-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-green-700">Verify</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                    @csrf
                                    <button class="bg-red-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-red-700">Reject</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No payments.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection