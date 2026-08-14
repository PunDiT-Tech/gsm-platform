<div id="blocks" class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Information Blocks</h3>
    <p class="text-sm text-gray-500 mb-4">Blocks render on the public service page in order. Types: INFORMATION, NOTICE, WARNING, INSTRUCTION, FAQ, LINK, DOWNLOAD, IMAGE, VIDEO.</p>

    @if ($service->informationBlocks->isNotEmpty())
        <table class="min-w-full divide-y divide-gray-200 text-sm mb-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Title</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Type</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Active</th>
                    <th class="px-3 py-2 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($service->informationBlocks as $block)
                    <tr>
                        <td class="px-3 py-2">{{ $block->title ?? '(no title)' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $block->type }}</td>
                        <td class="px-3 py-2">{{ $block->sort_order }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('admin.services.blocks.toggle', [$service, $block]) }}" class="inline">
                                @csrf
                                <button class="px-2 py-0.5 rounded-full text-xs {{ $block->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $block->is_active ? 'On' : 'Off' }}</button>
                            </form>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <details class="inline-block align-middle">
                                <summary class="inline cursor-pointer text-blue-600 hover:underline text-xs">Edit</summary>
                                <form method="POST" action="{{ route('admin.services.blocks.update', [$service, $block]) }}" class="absolute right-2 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 space-y-2 z-10 text-left">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Title</label>
                                            <input type="text" name="title" value="{{ $block->title }}" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Type</label>
                                            <select name="type" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach (['INFORMATION','NOTICE','WARNING','INSTRUCTION','FAQ','LINK','DOWNLOAD','IMAGE','VIDEO'] as $type)
                                                    <option value="{{ $type }}" @selected($block->type === $type)>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Content</label>
                                        <textarea name="content" rows="2" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $block->content }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">URL</label>
                                            <input type="url" name="url" value="{{ $block->url }}" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Sort order</label>
                                            <input type="number" name="sort_order" value="{{ $block->sort_order }}" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                    </div>
                                    <label class="flex items-center text-xs text-gray-700">
                                        <input type="checkbox" name="is_active" value="1" @checked($block->is_active) class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-1">Active</span>
                                    </label>
                                    <button class="w-full bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-blue-700">Save block</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.services.blocks.destroy', [$service, $block]) }}" class="inline" onsubmit="return confirm('Delete this block?')">
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

    <form method="POST" action="{{ route('admin.services.blocks.store', $service) }}" class="border-t border-gray-100 pt-4 space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" placeholder="What to expect"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @foreach (['INFORMATION','NOTICE','WARNING','INSTRUCTION','FAQ','LINK','DOWNLOAD','IMAGE','VIDEO'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Content</label>
            <textarea name="content" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">URL <span class="text-gray-400">(for LINK/DOWNLOAD)</span></label>
                <input type="url" name="url"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort order</label>
                <input type="number" name="sort_order" min="0" value="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Add block</button>
    </form>
</div>
