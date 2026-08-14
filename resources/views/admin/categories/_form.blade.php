<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
    <input id="name" type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
</div>

<div>
    <label for="slug" class="block text-sm font-medium text-gray-700">Slug <span class="text-gray-400">(auto if blank)</span></label>
    <input id="slug" type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="icon" class="block text-sm font-medium text-gray-700">Icon</label>
        <input id="icon" type="text" name="icon" maxlength="20" value="{{ old('icon', $category->icon ?? '') }}" placeholder="🔧"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700">Display order</label>
        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
</div>

<div>
    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
    <textarea id="description" name="description" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<label class="flex items-center text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
    <span class="ml-2">Active (visible publicly)</span>
</label>
