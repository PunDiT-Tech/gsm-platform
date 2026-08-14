<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $services = Service::public()->get();

        $content = view('sitemap', compact('services'))->render();

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }
}