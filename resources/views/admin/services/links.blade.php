<div id="links" class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Links</h3>
    <p class="text-sm text-gray-500 mb-4">Useful links displayed on the public service page. Unsafe schemes (e.g. javascript:) are rejected.</p>

    @if ($service->links->isNotEmpty())
        <table class="min-w-full divide-y divide-gray-200 text-sm mb-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Title</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">URL</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">New tab</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Active</th>
                    <th class="px-3 py-2 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($service->links as $link)
                    <tr>
                        <td class="px-3 py-2">{{ $link->title }}</td>
                        <td class="px-3 py-2 text-gray-500 text-xs truncate max-w-xs">{{ $link->url }}</td>
                        <td class="px-3 py-2">{{ $link->open_new_tab ? 'Yes' : 'No' }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('admin.services.links.toggle', [$service, $link]) }}" class="inline">
                                @csrf
                                <button class="px-2 py-0.5 rounded-full text-xs {{ $link->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $link->is_active ? 'On' : 'Off' }}</button>
                            </form>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <details class="inline-block align-middle">
                                <summary class="inline cursor-pointer text-blue-600 hover:underline text-xs">Edit</summary>
                                <form method="POST" action="{{ route('admin.services.links.update', [$service, $link]) }}" class="absolute right-2 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 space-y-2 z-10 text-left">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Title</label>
                                        <input type="text" name="title" value="{{ $link->title }}" required class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">URL</label>
                                        <input type="url" name="url" value="{{ $link->url }}" required class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Description</label>
                                            <input type="text" name="description" value="{{ $link->description }}" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Sort order</label>
                                            <input type="number" name="sort_order" value="{{ $link->sort_order }}" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-gray-700">
                                        <label class="flex items-center"><input type="checkbox" name="open_new_tab" value="1" @checked($link->open_new_tab) class="rounded border-gray-300 text-blue-600"><span class="ml-1">New tab</span></label>
                                        <label class="flex items-center"><input type="checkbox" name="is_active" value="1" @checked($link->is_active) class="rounded border-gray-300 text-blue-600"><span class="ml-1">Active</span></label>
                                    </div>
                                    <button class="w-full bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-blue-700">Save link</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.services.links.destroy', [$service, $link]) }}" class="inline" onsubmit="return confirm('Delete this link?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <form method="POST" action="{{ route('admin.services.links.store', $service) }}" class="border-t border-gray-100 pt-4 space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" required placeholder="Supported models"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">URL</label>
                <input type="url" name="url" required placeholder="https://..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" name="description"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div class="flex items-end justify-between">
                <label class="flex items-center text-sm text-gray-700">
                    <input type="checkbox" name="open_new_tab" value="1" checked class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2">Open in new tab</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sort order</label>
                    <input type="number" name="sort_order" min="0" value="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Add link</button>
    </form>
</div>
