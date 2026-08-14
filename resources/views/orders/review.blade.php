@extends('layouts.app')

@section('title', 'Review your order')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Review your order</h1>
        <p class="text-gray-500 mb-6">Please review the details below before submitting.</p>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-6">
            <div>
                <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Service</h2>
                <p class="font-semibold text-gray-900">{{ $preview['service']->name }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ $preview['service']->short_description }}</p>
            </div>

            @if ($preview['customer']['name'] || $preview['customer']['email'])
                <div>
                    <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Your details</h2>
                    <dl class="text-sm space-y-1">
                        @if ($preview['customer']['name'])
                            <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ $preview['customer']['name'] }}</dd></div>
                        @endif
                        @if ($preview['customer']['email'])
                            <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $preview['customer']['email'] }}</dd></div>
                        @endif
                        @if ($preview['customer']['phone'])
                            <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium">{{ $preview['customer']['phone'] }}</dd></div>
                        @endif
                    </dl>
                </div>
            @endif

            <div>
                <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Order details</h2>
                <dl class="text-sm space-y-1">
                    @foreach ($preview['service']->activeFields as $field)
                        @php
                            $raw = $preview['fields'][$field->id] ?? null;
                        @endphp
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">{{ $field->label }}</dt>
                            <dd class="font-medium text-right break-all">
                                @if (is_array($raw) && isset($raw['tmp']))
                                    📎 {{ $raw['name'] }}
                                @elseif (is_array($raw))
                                    {{ implode(', ', $raw) }}
                                @else
                                    {{ $raw ?: '—' }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Service price</span>
                    <span class="font-medium text-gray-900">{{ $preview['service']->currency }} {{ number_format((float) $preview['base_price'], 2) }}</span>
                </div>
                @if ($preview['coupon'])
                    <div class="flex justify-between items-center text-green-700">
                        <span class="text-sm">Discount ({{ $preview['coupon']->code }})</span>
                        <span>-{{ $preview['service']->currency }} {{ number_format((float) ($preview['base_price'] - $preview['price']), 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center font-bold text-lg pt-2 border-t">
                    <span class="text-gray-900">Total</span>
                    <span class="text-gray-900">{{ $preview['service']->currency }} {{ number_format((float) $preview['price'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('services.show', $preview['service']->slug) }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-md hover:border-blue-400 text-sm font-medium text-center">← Edit details</a>
            <form method="POST" action="{{ route('orders.store') }}">
                @csrf
                <input type="hidden" name="service_slug" value="{{ $preview['service']->slug }}">
                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 text-sm font-medium">Confirm & submit order</button>
            </form>
        </div>
    </div>
@endsection