<form method="POST" action="{{ $action }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
            <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $announcement?->title) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>

    <div>
        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
        <textarea id="message" name="message" rows="3" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('message', $announcement?->message) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
            <select id="type" name="type" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                @foreach (['INFO', 'SUCCESS', 'WARNING', 'DANGER', 'MAINTENANCE'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $announcement?->type ?? 'INFO') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <select id="location" name="location" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                @foreach (['homepage', 'services', 'dashboard'] as $loc)
                    <option value="{{ $loc }}" @selected(old('location', $announcement?->location ?? 'homepage') === $loc)>{{ ucfirst($loc) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="starts_at" class="block text-sm font-medium text-gray-700">Starts at</label>
            <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $announcement?->starts_at?->format('Y-m-d\TH:i')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>
        <div>
            <label for="ends_at" class="block text-sm font-medium text-gray-700">Ends at</label>
            <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $announcement?->ends_at?->format('Y-m-d\TH:i')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>
    </div>

    <label class="flex items-center text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement?->is_active ?? true))
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <span class="ml-2">Active</span>
    </label>

    <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-md hover:bg-blue-700 text-sm font-medium">Save</button>
</form>