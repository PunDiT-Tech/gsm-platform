@extends('layouts.admin')

@section('title', 'Services')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Services</h1>
        <a href="{{ route('admin.services.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">New service</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Name</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Category</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Type</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Price</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Featured</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($services as $service)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="mr-2">{{ $service->icon }}</span>{{ $service->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $service->category?->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $service->service_type }}</td>
                        <td class="px-4 py-3">{{ $service->service_type === 'FREE' ? 'Free' : $service->currency . ' ' . number_format((float) $service->price, 2) }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline">
                                @csrf
                                <button class="px-2 py-0.5 rounded-full text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $service->is_active ? 'Online' : 'Offline' }}</button>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.services.feature', $service) }}" class="inline">
                                @csrf
                                <button class="px-2 py-0.5 rounded-full text-xs {{ $service->is_featured ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}">{{ $service->is_featured ? '★' : '☆' }}</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.services.edit', $service) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline" onsubmit="return confirm('Delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $services->links() }}</div>
@endsection