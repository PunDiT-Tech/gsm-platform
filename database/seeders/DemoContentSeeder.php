<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\HomepageShowcase;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\ServiceFieldOption;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Device Repair',
            'slug' => 'device-repair',
            'icon' => '🔧',
            'description' => 'Authorized repair services for mobile devices.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $category2 = ServiceCategory::create([
            'name' => 'Diagnostics',
            'slug' => 'diagnostics',
            'icon' => '🩺',
            'description' => 'Device diagnostic services.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Screen Repair Assessment',
            'slug' => 'screen-repair-assessment',
            'short_description' => 'Assessment and quotation for authorized screen repair.',
            'full_description' => 'Submit your device details and our authorized technicians will assess your screen and provide a repair quotation.',
            'icon' => '📱',
            'price' => 25.00,
            'currency' => 'USD',
            'processing_time' => '24-48 hours',
            'service_type' => 'PAID',
            'payment_required' => true,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'customer_notice' => 'Please ensure the IMEI entered matches your device.',
            'customer_instructions' => 'Provide accurate device information.',
            'admin_internal_notes' => 'Internal notes not visible to customers.',
            'consent_required' => true,
        ]);

        $brand = ServiceField::create([
            'service_id' => $service->id,
            'label' => 'Brand',
            'internal_name' => 'brand',
            'type' => 'SELECT',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        foreach (['Apple', 'Samsung', 'Google', 'Xiaomi', 'Other'] as $i => $name) {
            ServiceFieldOption::create(['service_field_id' => $brand->id, 'label' => $name, 'value' => strtolower($name), 'sort_order' => $i]);
        }

        ServiceField::create([
            'service_id' => $service->id,
            'label' => 'Model',
            'internal_name' => 'model',
            'type' => 'TEXT',
            'placeholder' => 'e.g. iPhone 14',
            'is_required' => true,
            'min_length' => 2,
            'max_length' => 64,
            'sort_order' => 2,
        ]);

        ServiceField::create([
            'service_id' => $service->id,
            'label' => 'IMEI',
            'internal_name' => 'imei',
            'type' => 'IMEI',
            'validation_regex' => '/^[0-9]{15}$/',
            'is_required' => true,
            'sort_order' => 3,
        ]);

        ServiceField::create([
            'service_id' => $service->id,
            'label' => 'Device Photo',
            'internal_name' => 'device_photo',
            'type' => 'FILE',
            'description' => 'A clear photo of the device.',
            'is_required' => false,
            'sort_order' => 4,
        ]);

        ServiceField::create([
            'service_id' => $service->id,
            'label' => 'Additional Information',
            'internal_name' => 'notes',
            'type' => 'TEXTAREA',
            'is_required' => false,
            'sort_order' => 5,
        ]);

        Service::create([
            'category_id' => $category2->id,
            'name' => 'Battery Health Diagnostic',
            'slug' => 'battery-health-diagnostic',
            'short_description' => 'Diagnostic report on your device battery health.',
            'full_description' => 'We run an authorized diagnostic on your device battery and provide a health report.',
            'icon' => '🔋',
            'price' => 10.00,
            'currency' => 'USD',
            'processing_time' => '12 hours',
            'service_type' => 'STANDARD',
            'payment_required' => true,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
            'consent_required' => true,
        ]);

        $free = Service::create([
            'category_id' => $category2->id,
            'name' => 'Free Warranty Check',
            'slug' => 'free-warranty-check',
            'short_description' => 'Check whether your device is under warranty.',
            'full_description' => 'We check the official warranty status for your device at no cost.',
            'icon' => '🛡️',
            'price' => 0.00,
            'currency' => 'USD',
            'processing_time' => '2 hours',
            'service_type' => 'FREE',
            'payment_required' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        ServiceField::create([
            'service_id' => $free->id,
            'label' => 'Serial Number',
            'internal_name' => 'serial',
            'type' => 'SERIAL_NUMBER',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        HomepageShowcase::create([
            'title' => 'Professional Device Services',
            'subtitle' => 'Authorized repair, diagnostics and maintenance with secure order tracking.',
            'link_type' => 'url',
            'link_url' => route('services.index'),
            'animation' => 'FADE',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Announcement::create([
            'title' => 'Welcome',
            'message' => 'Welcome to our service platform. Browse services to get started.',
            'type' => 'INFO',
            'location' => 'homepage',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'General',
            'question' => 'How do I track my order?',
            'answer' => 'Use the Track Order page with your order number and tracking code.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Payments',
            'question' => 'What payment methods are accepted?',
            'answer' => 'Bank transfer and Binance Pay. Payment instructions are shown after placing an order.',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
