@extends('layouts.admin')

@section('title', 'Homepage CMS')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Homepage CMS</h1>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Page content</h2>
        <form method="POST" action="{{ route('admin.homepage.content') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @php $hero = $sections['hero'] ?? null; @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700">Hero title</label>
                <input type="text" name="hero_title" value="{{ $hero?->title }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Hero subtitle</label>
                <input type="text" name="hero_subtitle" value="{{ is_array($hero?->content) ? '' : $hero?->content }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            @php $stats = is_array($sections['stats']?->content) ? $sections['stats']->content : []; @endphp
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Stats</label>
                <div id="stats-rows" class="space-y-2">
                    @forelse ($stats as $i => $row)
                        <div class="flex gap-2">
                            <input type="text" name="stats_value[]" value="{{ $row['value'] ?? '' }}" placeholder="Value" class="block w-32 rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="text" name="stats_label[]" value="{{ $row['label'] ?? '' }}" placeholder="Label" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    @empty
                        <div class="flex gap-2">
                            <input type="text" name="stats_value[]" placeholder="Value" class="block w-32 rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="text" name="stats_label[]" placeholder="Label" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="document.getElementById('stats-rows').insertAdjacentHTML('beforeend','<div class=\'flex gap-2\'><input type=\'text\' name=\'stats_value[]\' placeholder=\'Value\' class=\'block w-32 rounded-md border-gray-300 shadow-sm text-sm\'><input type=\'text\' name=\'stats_label[]\' placeholder=\'Label\' class=\'block flex-1 rounded-md border-gray-300 shadow-sm text-sm\'></div>')" class="mt-2 text-sm text-blue-600 hover:underline">+ Add stat</button>
            </div>
            @php $steps = is_array($sections['how_it_works']?->content) ? $sections['how_it_works']->content : []; @endphp
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">How it works steps</label>
                <div id="steps-rows" class="space-y-2">
                    @forelse ($steps as $i => $step)
                        <div class="flex gap-2">
                            <input type="text" name="step_title[]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="text" name="step_text[]" value="{{ $step['text'] ?? '' }}" placeholder="Step text" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    @empty
                        <div class="flex gap-2">
                            <input type="text" name="step_title[]" placeholder="Step title" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="text" name="step_text[]" placeholder="Step text" class="block flex-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="document.getElementById('steps-rows').insertAdjacentHTML('beforeend','<div class=\'flex gap-2\'><input type=\'text\' name=\'step_title[]\' placeholder=\'Step title\' class=\'block flex-1 rounded-md border-gray-300 shadow-sm text-sm\'><input type=\'text\' name=\'step_text[]\' placeholder=\'Step text\' class=\'block flex-1 rounded-md border-gray-300 shadow-sm text-sm\'></div>')" class="mt-2 text-sm text-blue-600 hover:underline">+ Add step</div>
            </div>
            @php $cta = $sections['cta'] ?? null; @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700">CTA title</label>
                <input type="text" name="cta_title" value="{{ $cta?->title }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">CTA subtitle</label>
                <input type="text" name="cta_subtitle" value="{{ is_array($cta?->content) ? '' : $cta?->content }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            @php $footer = $sections['footer'] ?? null; @endphp
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Footer copyright</label>
                <input type="text" name="footer_copyright" value="{{ $footer?->title }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">Save content</button>
            </div>
        </form>
    </div>

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
                <form method="POST" action="{{ route('admin.homepage.update', $showcase) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                    @foreach (['image' => 'Image', 'desktop_image' => 'Desktop image', 'mobile_image' => 'Mobile image'] as $key => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $label }} <span class="text-xs text-gray-400">(JPG/PNG/WebP ≤ 5MB)</span></label>
                            <input type="file" name="{{ $key }}" accept=".jpg,.jpeg,.png,.webp"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
                            @if ($showcase->{$key})
                                <div class="flex items-center gap-2 mt-1.5">
                                    <img src="{{ route('showcase.image', [$showcase, $key]) }}" alt="{{ $label }}" class="w-14 h-14 object-cover rounded-md border border-gray-200">
                                    <label class="flex items-center text-xs text-gray-600">
                                        <input type="checkbox" name="remove_{{ $key }}" value="1" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-1">Remove</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    @endforeach
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
        <form method="POST" action="{{ route('admin.homepage.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
            @foreach (['image' => 'Image', 'desktop_image' => 'Desktop image', 'mobile_image' => 'Mobile image'] as $key => $label)
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $label }} <span class="text-xs text-gray-400">(JPG/PNG/WebP ≤ 5MB)</span></label>
                    <input type="file" name="{{ $key }}" accept=".jpg,.jpeg,.png,.webp"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
                </div>
            @endforeach
            <div class="md:col-span-3 flex items-end gap-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">Add slide</button>
            </div>
        </form>
    </div>
@endsection