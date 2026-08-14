<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $category = ServiceCategory::create($data);
        \App\Helpers\AuditLogger::log('category.create', $category);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ServiceCategory $category): RedirectResponse
    {
        $data = $this->validateData($request, $category->id);

        \App\Helpers\AuditLogger::log('category.update', $category, $category->toArray(), $data);
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if ($category->services()->count() > 0) {
            return back()->withErrors(['category' => 'This category has services. Move or delete them first to avoid corrupting history.']);
        }

        \App\Helpers\AuditLogger::log('category.delete', $category);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    public function toggle(ServiceCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', 'Category updated.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_categories,slug,' . ($ignoreId ?? '')],
            'icon' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
