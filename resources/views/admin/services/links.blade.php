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
                    <th class="px-3 py-2 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($service->links as $link)
                    <tr>
                        <td class="px-3 py-2">{{ $link->title }}</td>
                        <td class="px-3 py-2 text-gray-500 text-xs truncate max-w-xs">{{ $link->url }}</td>
                        <td class="px-3 py-2">{{ $link->open_new_tab ? 'Yes' : 'No' }}</td>
                        <td class="px-3 py-2 text-right">
                            <form method="POST" action="{{ route('admin.services.links.destroy', [$service, $link]) }}" class="inline" onsubmit="return confirm('Delete this link?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
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
