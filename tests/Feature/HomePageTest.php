<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\HomepageShowcase;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_homepage_sections_render(): void
    {
        \App\Models\HomepageSection::create(['key' => 'hero', 'title' => 'Custom Hero', 'content' => 'Custom subtitle', 'is_active' => true]);
        \App\Models\HomepageSection::create(['key' => 'stats', 'content' => json_encode([['value' => '100', 'label' => 'Devices fixed']]), 'is_active' => true]);
        \App\Models\HomepageSection::create(['key' => 'how_it_works', 'title' => 'How it works', 'content' => json_encode([['title' => 'Step A', 'text' => 'Do thing']]), 'is_active' => true]);
        \App\Models\HomepageSection::create(['key' => 'cta', 'title' => 'Custom CTA', 'content' => 'Custom cta subtitle', 'is_active' => true]);
        \App\Models\HomepageSection::create(['key' => 'footer', 'title' => 'MyShop', 'is_active' => true]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Custom Hero');
        $response->assertSee('Custom subtitle');
        $response->assertSee('100');
        $response->assertSee('Devices fixed');
        $response->assertSee('Step A');
        $response->assertSee('Custom CTA');
    }

    public function test_service_page_shows_faq(): void
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
        \App\Models\Faq::create(['question' => 'How long?', 'answer' => 'A day', 'sort_order' => 1, 'is_active' => true]);

        $this->get(route('services.show', 'screen-repair'))->assertOk()->assertSee('How long?');
    }

    public function test_service_image_serves_with_resize_params(): void
    {
        Storage::fake('local');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        $category = ServiceCategory::create(['name' => 'Repair', 'slug' => 'repair', 'is_active' => true, 'sort_order' => 1]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Screen Repair',
            'slug' => 'screen-repair',
            'price' => 25,
            'currency' => 'USD',
            'service_type' => 'PAID',
            'is_active' => true,
            'image' => 'service-images/test.png',
        ]);
        Storage::disk('local')->put('service-images/test.png', $png);

        $this->get(route('services.image', $service) . '?w=100&h=100')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
        $this->assertTrue(Storage::disk('local')->exists('service-images/test.png'));
    }

    public function test_showcase_image_route_rejects_unknown_variant(): void
    {
        $showcase = HomepageShowcase::create(['title' => 'Slide', 'sort_order' => 1, 'is_active' => true]);

        $this->get(route('showcase.image', [$showcase, 'evil']))->assertNotFound();
    }

    public function test_admin_can_update_homepage_content(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $role = \App\Models\Role::where('name', 'SUPER_ADMIN')->first();
        $admin = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->attach($role);

        $this->actingAs($admin)->post(route('admin.homepage.content'), [
            'hero_title' => 'Admin Hero',
            'hero_subtitle' => 'Admin subtitle',
            'stats_value' => ['500'],
            'stats_label' => ['Jobs done'],
            'step_title' => ['Step 1'],
            'step_text' => ['Do it'],
            'cta_title' => 'Ready?',
            'cta_subtitle' => 'Go now',
            'footer_copyright' => 'ShopCo',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('homepage_sections', ['key' => 'hero', 'title' => 'Admin Hero', 'content' => 'Admin subtitle']);
        $this->assertDatabaseHas('homepage_sections', ['key' => 'cta', 'content' => 'Go now']);
        $this->assertDatabaseHas('homepage_sections', ['key' => 'footer', 'title' => 'ShopCo']);
    }
}
