@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Our services</h1>

        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('services.index') }}"
               class="px-4 py-2 rounded-full text-sm border {{ request()->has('category') ? 'border-gray-300 text-gray-600 hover:border-blue-400' : 'bg-blue-600 text-white border-blue-600' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                   class="px-4 py-2 rounded-full text-sm border {{ request('category') === $category->slug ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:border-blue-400' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        @if ($services->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-10 text-center text-gray-500">
                No services available right now. Please check back later.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-400 hover:shadow-sm transition flex flex-col">
                        @if ($service->image)
                            <img src="{{ route('services.image', $service) }}?w=480" alt="{{ $service->name }}" class="w-full h-44 object-cover">
                        @endif
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">{{ $service->icon ?? '🔧' }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $service->is_active ? 'Online' : 'Offline' }}</span>
                            </div>
                            <h2 class="font-semibold text-gray-900 text-lg">{{ $service->name }}</h2>
                            <p class="text-sm text-gray-500 mt-1 flex-1">{{ $service->short_description }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-bold text-gray-900">
                                    @if ($service->service_type === 'FREE')
                                        Free
                                    @elseif ($service->service_type === 'EXTERNAL')
                                        External
                                    @else
                                        {{ $service->currency }} {{ number_format((float) $service->price, 2) }}
                                    @endif
                                </span>
                                <span class="text-blue-600 text-sm font-medium">Order →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
