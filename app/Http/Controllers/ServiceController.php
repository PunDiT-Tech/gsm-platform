<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Service;
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

        return view('catalog.show', compact('service', 'announcements'));
    }
}
