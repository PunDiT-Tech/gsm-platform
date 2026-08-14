@extends('layouts.app')

@section('title', $service->name)

@push('head')
<meta name="description" content="{{ Str::limit($service->short_description, 160) }}">
<meta property="og:title" content="{{ $service->name }}">
<meta property="og:description" content="{{ Str::limit($service->short_description, 160) }}">
@endpush

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('services.index') }}" class="hover:text-blue-600">Services</a>
            <span class="mx-2">/</span>
            @if ($service->category)
                <a href="{{ route('services.index', ['category' => $service->category->slug]) }}" class="hover:text-blue-600">{{ $service->category->name }}</a>
                <span class="mx-2">/</span>
            @endif
            <span class="text-gray-900">{{ $service->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-8">
            <div>
                <div class="flex items-center gap-4 mb-4">
                    <span class="text-5xl">{{ $service->icon ?? '🔧' }}</span>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $service->name }}</h1>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $service->is_active ? 'Online' : 'Offline' }}</span>
                    </div>
                </div>

                <p class="text-gray-600 mb-4">{{ $service->short_description }}</p>

                @if ($service->full_description)
                    <div class="prose prose-sm text-gray-700 mb-6 whitespace-pre-wrap">{{ $service->full_description }}</div>
                @endif

                @if ($service->customer_notice)
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg p-4 mb-6">{{ $service->customer_notice }}</div>
                @endif

                @foreach ($service->activeInformationBlocks as $block)
                    <div class="bg-white border border-gray-200 rounded-lg p-5 mb-4">
                        @if ($block->title)
                            <h2 class="font-semibold text-gray-900 mb-2">{{ $block->title }}</h2>
                        @endif
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $block->content }}</p>
                    </div>
                @endforeach

                @if ($service->activeLinks->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-lg p-5 mb-8">
                        <h2 class="font-semibold text-gray-900 mb-3">Useful links</h2>
                        <ul class="space-y-2 text-sm">
                            @foreach ($service->activeLinks as $link)
                                <li>
                                    <a href="{{ $link->url }}" {{ $link->open_new_tab ? 'target="_blank" rel="noopener nofollow"' : '' }} class="text-blue-600 hover:underline">
                                        {{ $link->title }} →
                                    </a>
                                    @if ($link->description)
                                        <p class="text-gray-500 text-xs mt-0.5">{{ $link->description }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="lg:sticky lg:top-20 h-fit">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900 text-lg">Order this service</h2>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $service->is_active ? 'Online' : 'Offline' }}</span>
                    </div>

                    @if ($service->service_type === 'EXTERNAL')
                        <p class="text-sm text-gray-600">This service is provided externally. Please follow the provided links.</p>
                    @elseif (! $service->is_active)
                        <p class="text-sm text-gray-500">This service is temporarily unavailable.</p>
                    @else
                        <div class="mb-4">
                            <p class="text-2xl font-bold text-gray-900">
                                @if ($service->service_type === 'FREE')
                                    Free
                                @else
                                    {{ $service->currency }} {{ number_format((float) $service->price, 2) }}
                                @endif
                            </p>
                            @if ($service->processing_time)
                                <p class="text-sm text-gray-500 mt-1">Est. processing: {{ $service->processing_time }}</p>
                            @endif
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="service_slug" value="{{ $service->slug }}">

                            @auth
                                <input type="hidden" name="customer_lookup" value="account">
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Your name</label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                            @endauth

                            @foreach ($service->activeFields as $field)
                                @include('partials.dynamic-field', ['field' => $field])
                            @endforeach

                            @if ($service->consent_required)
                                <label class="flex items-start text-sm text-gray-600">
                                    <input type="checkbox" name="consent" value="1" required class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2">I confirm that I am authorized to request this service for this device.</span>
                                </label>
                            @endif

                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-md hover:bg-blue-700 text-sm font-medium">Place order</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection