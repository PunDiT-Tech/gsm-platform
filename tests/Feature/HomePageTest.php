<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\HomepageShowcase;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(config('app.name'));
    }

    public function test_services_page_renders(): void
    {
        $category = ServiceCategory::create(['name' => 'Repair', 'slug' => 'repair', 'is_active' => true, 'sort_order' => 1]);
        Service::create([
            'category_id' => $category->id,
            'name' => 'Screen Repair',
            'slug' => 'screen-repair',
            'price' => 25,
            'currency' => 'USD',
            'service_type' => 'PAID',
            'is_active' => true,
        ]);

        $this->get(route('services.index'))->assertOk()->assertSee('Screen Repair');
    }

    public function test_announcements_only_show_active_in_range(): void
    {
        Announcement::create(['title' => 'Old', 'message' => 'x', 'type' => 'INFO', 'location' => 'homepage', 'starts_at' => now()->subDays(10), 'ends_at' => now()->subDays(5), 'is_active' => true]);
        Announcement::create(['title' => 'Current', 'message' => 'y', 'type' => 'INFO', 'location' => 'homepage', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Current');
        $response->assertDontSee('Old');
    }

    public function test_legal_pages_render(): void
    {
        foreach (['terms', 'privacy', 'refunds', 'acceptable-use'] as $page) {
            $this->get(route('page', $page))->assertOk();
        }
    }

    public function test_check_order_page_renders(): void
    {
        $this->get(route('order.lookup'))->assertOk();
    }

    public function test_faq_page_renders(): void
    {
        $this->get(route('faq'))->assertOk();
    }
}
