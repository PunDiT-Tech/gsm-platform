<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $category = ServiceCategory::create(['name' => 'Repair', 'slug' => 'repair', 'is_active' => true, 'sort_order' => 1]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Diagnostic',
            'slug' => 'diagnostic',
            'price' => 50.00,
            'currency' => 'USD',
            'service_type' => 'PAID',
            'payment_required' => true,
            'is_active' => true,
            'consent_required' => true,
        ]);
        ServiceField::create([
            'service_id' => $this->service->id,
            'label' => 'IMEI',
            'internal_name' => 'imei',
            'type' => 'IMEI',
            'validation_regex' => '/^[0-9]{15}$/',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        PaymentMethod::create(['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer', 'is_active' => true, 'sort_order' => 1]);
    }

    protected function registeredOrder(): array
    {
        $user = User::factory()->create(['email' => 'owner@example.com', 'email_verified_at' => now()]);
        Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone]);

        $this->actingAs($user)->post(route('orders.store'), [
            'service_slug' => 'diagnostic',
            'customer_lookup' => 'account',
            'fields' => [1 => '123456789012345'],
            'consent' => '1',
        ]);

        return [$user, Order::firstOrFail()];
    }

    public function test_payment_proof_upload_then_admin_verify(): void
    {
        [$user, $order] = $this->registeredOrder();
        $payment = $order->payments()->first();

        $response = $this->actingAs($user)->post(route('orders.payment.upload', $order), [
            'payment_id' => $payment->id,
            'transaction_id' => 'TX-123',
            'proof' => UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf'),
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'PROOF_SUBMITTED']);
        $this->assertDatabaseHas('payment_proofs', ['transaction_id' => 'TX-123']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'PROOF_SUBMITTED']);
        $this->assertNotNull(PaymentProof::first()->file_path);
    }

    public function test_invalid_proof_file_is_rejected(): void
    {
        [$user, $order] = $this->registeredOrder();
        $payment = $order->payments()->first();

        $this->actingAs($user)->post(route('orders.payment.upload', $order), [
            'payment_id' => $payment->id,
            'proof' => UploadedFile::fake()->create('malware.exe', 100),
        ])->assertSessionHasErrors('proof');

        $this->assertDatabaseCount('payment_proofs', 0);
    }

    public function test_admin_verifies_payment_and_order_updates(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->attach(Role::where('name', 'SUPER_ADMIN')->firstOrFail());

        [, $order] = $this->registeredOrder();
        $payment = $order->payments()->first();
        $payment->update(['status' => 'PROOF_SUBMITTED']);

        $this->actingAs($admin)->post(route('admin.payments.verify', $payment))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'VERIFIED']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'VERIFIED']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.verify']);
    }

    public function test_guest_cannot_verify_payment(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-PAY',
            'tracking_token' => 'x',
            'customer_id' => Customer::create(['name' => 'G', 'email' => 'g@example.com'])->id,
            'status' => 'PENDING',
            'payment_status' => 'PROOF_SUBMITTED',
        ]);
        $payment = Payment::create(['order_id' => $order->id, 'amount' => 50, 'currency' => 'USD', 'status' => 'PROOF_SUBMITTED']);

        $this->post(route('admin.payments.verify', $payment))->assertRedirect(route('login'));
    }
}
