<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageShowcase;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        $showcases = HomepageShowcase::orderBy('sort_order')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.homepage.index', compact('showcases', 'services'));
    }

    public function storeShowcase(Request $request): RedirectResponse
    {
        $data = $this->validateShowcase($request);
        HomepageShowcase::create($data);

        return back()->with('status', 'Showcase slide added.');
    }

    public function updateShowcase(Request $request, HomepageShowcase $showcase): RedirectResponse
    {
        $showcase->update($this->validateShowcase($request));

        return back()->with('status', 'Showcase slide updated.');
    }

    public function destroyShowcase(HomepageShowcase $showcase): RedirectResponse
    {
        $showcase->delete();

        return back()->with('status', 'Showcase slide deleted.');
    }

    protected function validateShowcase(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_type' => ['required', 'in:none,service,url'],
            'service_id' => ['nullable', 'exists:services,id'],
            'link_url' => ['nullable', 'url'],
            'animation' => ['required', 'in:FADE,SLIDE,ZOOM,FLOAT,ZOOM_FADE,PARALLAX,NONE'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
