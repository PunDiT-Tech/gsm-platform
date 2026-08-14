<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(string $slug): View
    {
        $service = Service::public()
            ->with(['category', 'activeFields.options', 'activeInformationBlocks', 'activeLinks', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        $announcements = Announcement::active('services')->latest()->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->limit(4)->get();

        return view('catalog.show', compact('service', 'announcements', 'faqs'));
    }

    public function image(Service $service)
    {
        abort_if(! $service->image, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($service->image), 404);

        return $disk->response($service->image);
    }
}
