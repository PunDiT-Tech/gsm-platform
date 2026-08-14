<div>
    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
    <select id="category_id" name="category_id" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <option value="">Select category…</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $service?->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $service?->name) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug <span class="text-gray-400">(auto)</span></label>
        <input id="slug" type="text" name="slug" value="{{ old('slug', $service?->slug) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
</div>

<div>
    <label for="short_description" class="block text-sm font-medium text-gray-700">Short description</label>
    <input id="short_description" type="text" name="short_description" maxlength="255" value="{{ old('short_description', $service?->short_description) }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
</div>

<div>
    <label for="full_description" class="block text-sm font-medium text-gray-700">Full description</label>
    <textarea id="full_description" name="full_description" rows="4"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('full_description', $service?->full_description) }}</textarea>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label for="icon" class="block text-sm font-medium text-gray-700">Icon</label>
        <input id="icon" type="text" name="icon" maxlength="20" value="{{ old('icon', $service?->icon) }}" placeholder="🔧"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
    <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
        <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $service?->price ?? 0) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
    <div>
        <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
        <input id="currency" type="text" name="currency" maxlength="3" value="{{ old('currency', $service?->currency ?? 'USD') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm uppercase">
    </div>
</div>

<div>
    <label for="image" class="block text-sm font-medium text-gray-700">Cover image <span class="text-gray-400">(JPG/PNG/WebP, max 5MB)</span></label>
    <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
        class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
    @if ($service?->image)
        <div class="flex items-center gap-3 mt-2">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('local')->url($service->image) }}" alt="{{ $service->name }}" class="w-16 h-16 object-cover rounded-md border border-gray-200">
            <label class="flex items-center text-xs text-gray-600">
                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-blue-600">
                <span class="ml-1.5">Remove current image</span>
            </label>
        </div>
    @endif
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="service_type" class="block text-sm font-medium text-gray-700">Service type</label>
        <select id="service_type" name="service_type" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @foreach (['STANDARD', 'PAID', 'FREE', 'EXTERNAL'] as $type)
                <option value="{{ $type }}" @selected(old('service_type', $service?->service_type ?? 'STANDARD') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="processing_time" class="block text-sm font-medium text-gray-700">Estimated processing time</label>
        <input id="processing_time" type="text" name="processing_time" value="{{ old('processing_time', $service?->processing_time) }}" placeholder="24-48 hours"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort order</label>
        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $service?->sort_order ?? 0) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
        <div class="flex items-center gap-6 text-sm text-gray-700 pt-1">
            <label class="flex items-center"><input type="checkbox" name="payment_required" value="1" @checked(old('payment_required', $service?->payment_required ?? false)) class="rounded border-gray-300 text-blue-600"><span class="ml-2">Payment required</span></label>
            <label class="flex items-center"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service?->is_active ?? true)) class="rounded border-gray-300 text-blue-600"><span class="ml-2">Active</span></label>
            <label class="flex items-center"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service?->is_featured ?? false)) class="rounded border-gray-300 text-blue-600"><span class="ml-2">Featured</span></label>
            <label class="flex items-center"><input type="checkbox" name="consent_required" value="1" @checked(old('consent_required', $service?->consent_required ?? false)) class="rounded border-gray-300 text-blue-600"><span class="ml-2">Consent required</span></label>
        </div>
    </div>
</div>

<div>
    <label for="customer_notice" class="block text-sm font-medium text-gray-700">Customer notice (public)</label>
    <textarea id="customer_notice" name="customer_notice" rows="2"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('customer_notice', $service?->customer_notice) }}</textarea>
</div>

<div>
    <label for="customer_instructions" class="block text-sm font-medium text-gray-700">Customer instructions</label>
    <textarea id="customer_instructions" name="customer_instructions" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('customer_instructions', $service?->customer_instructions) }}</textarea>
</div>

<div>
    <label for="admin_internal_notes" class="block text-sm font-medium text-gray-700">Internal staff notes (never shown to customers)</label>
    <textarea id="admin_internal_notes" name="admin_internal_notes" rows="2"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('admin_internal_notes', $service?->admin_internal_notes) }}</textarea>
</div>
