@extends('layouts.admin')

@section('title', 'Edit Service')

@section('content')
    <div class="max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit service</h1>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('admin.services._form', ['service' => $service, 'categories' => $categories])
            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Save changes</button>
        </form>

        @if (isset($service) && $service->exists)
            <div class="mt-10">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Service content</h2>
                <div class="flex flex-wrap gap-3 mb-6">
                    <a href="#fields" class="px-4 py-2 rounded-md text-sm border border-gray-300 hover:border-blue-400">Dynamic Fields</a>
                    <a href="#blocks" class="px-4 py-2 rounded-md text-sm border border-gray-300 hover:border-blue-400">Information Blocks</a>
                    <a href="#links" class="px-4 py-2 rounded-md text-sm border border-gray-300 hover:border-blue-400">Links</a>
                </div>

                @include('admin.services.fields', ['service' => $service])
                @include('admin.services.blocks', ['service' => $service])
                @include('admin.services.links', ['service' => $service])
            </div>
        @endif
    </div>
@endsection
