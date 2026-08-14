<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::with('category')->orderBy('sort_order')->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        $categories = ServiceCategory::orderBy('sort_order')->get();

        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $service = Service::create($data);
        \App\Helpers\AuditLogger::log('service.create', $service);

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $service->load(['activeFields.options', 'informationBlocks', 'links', 'images']);
        $categories = ServiceCategory::orderBy('sort_order')->get();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validateData($request, $service->id);

        \App\Helpers\AuditLogger::log('service.update', $service, $service->toArray(), $data);
        $service->update($data);

        return redirect()->route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        \App\Helpers\AuditLogger::log('service.delete', $service);
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service deleted (soft).');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);
        \App\Helpers\AuditLogger::log('service.toggle', $service, ['is_active' => ! $service->is_active], ['is_active' => $service->is_active]);

        return back()->with('status', 'Service updated.');
    }

    public function feature(Service $service): RedirectResponse
    {
        $service->update(['is_featured' => ! $service->is_featured]);
        \App\Helpers\AuditLogger::log('service.feature', $service, ['is_featured' => ! $service->is_featured], ['is_featured' => $service->is_featured]);

        return back()->with('status', 'Service updated.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug,' . ($ignoreId ?? '')],
            'short_description' => ['nullable', 'string', 'max:255'],
            'full_description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'processing_time' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', 'in:STANDARD,PAID,FREE,EXTERNAL'],
            'payment_required' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'customer_notice' => ['nullable', 'string'],
            'customer_instructions' => ['nullable', 'string'],
            'admin_internal_notes' => ['nullable', 'string'],
            'consent_required' => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $existing = Service::where('slug', $data['slug'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->withTrashed()
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'The generated slug is already in use. Please provide a unique slug.']);
        }

        $data['payment_required'] = $request->boolean('payment_required');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['consent_required'] = $request->boolean('consent_required');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}