<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        WebsiteSetting::set('api_key', 'test-api-key-123456');
    }

    public function test_services_endpoint_requires_key(): void
    {
        $this->getJson('/api/services')->assertStatus(401);
    }

    public function test_services_endpoint_returns_services(): void
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

        $this->withToken('test-api-key-123456')->getJson('/api/services')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Screen Repair');
    }

    public function test_lookup_returns_order(): void
    {
        $category = ServiceCategory::create(['name' => 'Repair', 'slug' => 'repair', 'is_active' => true, 'sort_order' => 1]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Screen Repair',
            'slug' => 'screen-repair',
            'price' => 25,
            'currency' => 'USD',
            'service_type' => 'PAID',
            'is_active' => true,
        ]);
        $customer = Customer::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        $order = Order::create([
            'order_number' => 'ORD-API-1',
            'tracking_token' => Hash::make('secret-code'),
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name_snapshot' => 'Screen Repair',
            'price_snapshot' => 25,
            'currency_snapshot' => 'USD',
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
        ]);

        $this->withToken('test-api-key-123456')->postJson('/api/orders/lookup', [
            'order_number' => 'ORD-API-1',
            'tracking_code' => 'secret-code',
        ])
            ->assertOk()
            ->assertJsonPath('data.order_number', 'ORD-API-1');
    }

    public function test_lookup_rejects_wrong_code(): void
    {
        $this->withToken('test-api-key-123456')->postJson('/api/orders/lookup', [
            'order_number' => 'ORD-MISSING',
            'tracking_code' => 'wrong',
        ])->assertStatus(404);
    }
}