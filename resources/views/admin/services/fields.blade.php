<div id="fields" class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Dynamic Fields</h3>
    <p class="text-sm text-gray-500 mb-4">The customer form is generated from these fields. Field types: TEXT, TEXTAREA, NUMBER, EMAIL, PHONE, IMEI, SERIAL_NUMBER, SELECT, MULTI_SELECT, RADIO, CHECKBOX, DATE, FILE, URL.</p>

    @if ($service->activeFields->isNotEmpty())
        <table class="min-w-full divide-y divide-gray-200 text-sm mb-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Label</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Type</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Required</th>
                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Order</th>
                    <th class="px-3 py-2 text-right text-gray-600 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($service->activeFields as $field)
                    <tr>
                        <td class="px-3 py-2">{{ $field->label }} <span class="text-gray-400 text-xs">({{ $field->internal_name }})</span></td>
                        <td class="px-3 py-2 text-gray-500">{{ $field->type }}</td>
                        <td class="px-3 py-2">{{ $field->is_required ? 'Yes' : 'No' }}</td>
                        <td class="px-3 py-2">{{ $field->sort_order }}</td>
                        <td class="px-3 py-2 text-right">
                            <form method="POST" action="{{ route('admin.services.fields.destroy', [$service, $field]) }}" class="inline" onsubmit="return confirm('Delete this field?')">
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

    <form method="POST" action="{{ route('admin.services.fields.store', $service) }}" class="border-t border-gray-100 pt-4 space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Label</label>
                <input type="text" name="label" required placeholder="e.g. IMEI"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @foreach (['TEXT','TEXTAREA','NUMBER','EMAIL','PHONE','IMEI','SERIAL_NUMBER','SELECT','MULTI_SELECT','RADIO','CHECKBOX','DATE','FILE','URL'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Internal name</label>
                <input type="text" name="internal_name" placeholder="imei"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort order</label>
                <input type="number" name="sort_order" min="0" value="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Validation regex</label>
                <input type="text" name="validation_regex" placeholder="/^[0-9]{15}$/"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Min length</label>
                <input type="number" name="min_length" min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max length</label>
                <input type="number" name="max_length" min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div class="flex items-end">
                <label class="flex items-center text-sm text-gray-700">
                    <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2">Required</span>
                </label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Options <span class="text-gray-400">(for SELECT/RADIO/CHECKBOX/MULTI_SELECT — comma separated)</span></label>
            <input type="text" name="options[]" placeholder="Apple, Samsung, Google"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Add field</button>
    </form>
</div>
