@extends('layouts.admin')

@section('title', 'Homepage Showcase')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Homepage showcase</h1>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="space-y-6 mb-8">
        @foreach ($showcases as $showcase)
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900">{{ $showcase->title ?? '(no title)' }}</h2>
                    <form method="POST" action="{{ route('admin.homepage.destroy', $showcase) }}" onsubmit="return confirm('Delete slide?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('admin.homepage.update', $showcase) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ $showcase->title }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                        <input type="text" name="subtitle" value="{{ $showcase->subtitle }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Link type</label>
                        <select name="link_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach (['none', 'service', 'url'] as $lt)
                                <option value="{{ $lt }}" @selected($showcase->link_type === $lt)>{{ ucfirst($lt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service</label>
                        <select name="service_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">— none —</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected($showcase->service_id === $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">URL</label>
                        <input type="url" name="link_url" value="{{ $showcase->link_url }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Animation</label>
                        <select name="animation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach (['FADE','SLIDE','ZOOM','FLOAT','ZOOM_FADE','PARALLAX','NONE'] as $anim)
                                <option value="{{ $anim }}" @selected($showcase->animation === $anim)>{{ $anim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-4 col-span-1 md:col-span-3">
                        <label class="flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" @checked($showcase->is_active) class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2">Active</span>
                        </label>
                        <label class="flex items-center text-sm text-gray-700">
                            Sort order:
                            <input type="number" name="sort_order" value="{{ $showcase->sort_order }}" class="w-20 ml-2 rounded-md border-gray-300 text-sm">
                        </label>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">Save</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Add slide</h2>
        <form method="POST" action="{{ route('admin.homepage.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                <input type="text" name="subtitle"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Link type</label>
                <select name="link_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="none">None</option>
                    <option value="service">Service</option>
                    <option value="url">URL</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Service</label>
                <select name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">— none —</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">URL</label>
                <input type="url" name="link_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Animation</label>
                <select name="animation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="FADE">FADE</option>
                    <option value="SLIDE">SLIDE</option>
                    <option value="ZOOM">ZOOM</option>
                </select>
            </div>
            <div class="md:col-span-3 flex items-end gap-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">Add slide</button>
            </div>
        </form>
    </div>
@endsection