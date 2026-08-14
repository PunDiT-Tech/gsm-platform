<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\HomepageShowcase;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $showcases = HomepageShowcase::where('is_active', true)->with('service')->orderBy('sort_order')->get();
        $announcements = Announcement::active('homepage')->latest()->limit(3)->get();
        $featuredServices = Service::public()->where('is_featured', true)->with('category')->limit(8)->get();
        $categories = ServiceCategory::where('is_active', true)->withCount('services')->orderBy('sort_order')->limit(8)->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->limit(6)->get();

        return view('home', compact('showcases', 'announcements', 'featuredServices', 'categories', 'faqs'));
    }

    public function services(Request $request): View
    {
        $query = Service::public()->with('category');

        if ($request->has('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $services = $query->orderBy('sort_order')->get();
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('catalog.index', compact('services', 'categories'));
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    public function faq(): View
    {
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();

        return view('pages.faq', compact('faqs'));
    }

    public function announcements(): View
    {
        $announcements = Announcement::active()->latest()->get();

        return view('pages.announcements', compact('announcements'));
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        logger()->channel('single')->info('Contact form submission', $data);

        return back()->with('status', 'Thank you. We have received your message and will respond soon.');
    }

    public function page(string $slug): View
    {
        $allowed = ['terms', 'privacy', 'refunds', 'acceptable-use'];

        abort_unless(in_array($slug, $allowed, true), 404);

        return view("pages.{$slug}");
    }
}
