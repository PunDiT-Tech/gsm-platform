<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AuditLogger;
use App\Models\HomepageSection;
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
        $sections = HomepageSection::get()->keyBy('key');

        return view('admin.homepage.index', compact('showcases', 'services', 'sections'));
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
            'stats_value' => ['array'],
            'stats_value.*' => ['nullable', 'string', 'max:64'],
            'stats_label.*' => ['nullable', 'string', 'max:128'],
            'step_title.*' => ['nullable', 'string', 'max:128'],
            'step_text.*' => ['nullable', 'string', 'max:255'],
            'cta_title' => ['nullable', 'string', 'max:255'],
            'cta_subtitle' => ['nullable', 'string', 'max:255'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
        ]);

        $stats = [];
        foreach (($validated['stats_value'] ?? []) as $i => $value) {
            if ($value !== null && $value !== '' && ! empty($validated['stats_label'][$i] ?? null)) {
                $stats[] = ['value' => $value, 'label' => $validated['stats_label'][$i]];
            }
        }

        $steps = [];
        foreach (($validated['step_title'] ?? []) as $i => $title) {
            if ($title !== null && $title !== '') {
                $steps[] = ['title' => $title, 'text' => $validated['step_text'][$i] ?? ''];
            }
        }

        $this->writeSection('hero', $validated['hero_title'] ?? null, $validated['hero_subtitle'] ?? null);
        $this->writeSection('stats', '', json_encode($stats));
        $this->writeSection('how_it_works', 'How it works', json_encode($steps));
        $this->writeSection('cta', $validated['cta_title'] ?? null, $validated['cta_subtitle'] ?? null);
        $this->writeSection('footer', $validated['footer_copyright'] ?? null, null);

        AuditLogger::log('homepage.content.update', null, null, $validated);

        return back()->with('status', 'Homepage content saved.');
    }

    protected function writeSection(string $key, ?string $title, ?string $content): void
    {
        HomepageSection::updateOrCreate(['key' => $key], [
            'title' => $title,
            'content' => $content,
            'is_active' => true,
        ]);
    }

    public function storeShowcase(Request $request): RedirectResponse
    {
        $data = $this->validateShowcase($request);

        foreach (['image', 'desktop_image', 'mobile_image'] as $variant) {
            if ($request->hasFile($variant)) {
                $data[$variant] = \App\Helpers\ImageOptimizer::store('showcase-images', $request->file($variant));
            }
        }

        $showcase = HomepageShowcase::create($data);
        AuditLogger::log('homepage.showcase.create', $showcase, null, $data);

        return back()->with('status', 'Showcase slide added.');
    }

    public function updateShowcase(Request $request, HomepageShowcase $showcase): RedirectResponse
    {
        $data = $this->validateShowcase($request);

        foreach (['image', 'desktop_image', 'mobile_image'] as $variant) {
            if ($request->hasFile($variant)) {
                if ($showcase->{$variant}) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($showcase->{$variant});
                }
                $data[$variant] = \App\Helpers\ImageOptimizer::store('showcase-images', $request->file($variant));
            } elseif ($request->boolean('remove_' . $variant)) {
                if ($showcase->{$variant}) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($showcase->{$variant});
                }
                $data[$variant] = null;
            }
        }

        $showcase->update($data);
        AuditLogger::log('homepage.showcase.update', $showcase, null, $showcase->toArray());

        return back()->with('status', 'Showcase slide updated.');
    }

    public function destroyShowcase(HomepageShowcase $showcase): RedirectResponse
    {
        $showcase->delete();
        AuditLogger::log('homepage.showcase.delete', $showcase, $showcase->toArray());

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
            'image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'desktop_image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'mobile_image' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
