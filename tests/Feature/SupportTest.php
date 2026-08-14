<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    protected function makeStaff(string $role): User
    {
        $roleModel = Role::where('name', $role)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($roleModel);

        return $user;
    }

    protected function makeCustomer(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $customer = Customer::create(['user_id' => $user->id, 'name' => 'Bob', 'email' => $user->email]);

        return [$user, $customer];
    }

    public function test_admin_can_assign_ticket(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $admin = $this->makeStaff('SUPER_ADMIN');
        $assignee = $this->makeStaff('SUPPORT');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'OPEN']);

        $this->actingAs($admin)->post(route('admin.support.assign', $ticket), ['user_id' => $assignee->id])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('support_tickets', ['id' => $ticket->id, 'user_id' => $assignee->id, 'status' => 'ASSIGNED']);
    }

    public function test_admin_can_download_attachment(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $admin = $this->makeStaff('SUPER_ADMIN');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'OPEN']);
        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'message' => 'see file',
            'attachment_path' => 'support-files/test.txt',
        ]);

        \Illuminate\Support\Facades\Storage::disk('local')->put('support-files/test.txt', 'hello');

        $this->actingAs($admin)->get(route('admin.support.attachment-download', $message))->assertOk();
    }

    public function test_admin_reads_customer_message(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $admin = $this->makeStaff('SUPER_ADMIN');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'OPEN']);
        SupportMessage::create(['support_ticket_id' => $ticket->id, 'customer_id' => $customer->id, 'message' => 'question']);

        $this->actingAs($admin)->get(route('admin.support.show', $ticket))->assertOk();

        $this->assertNotNull(SupportMessage::where('support_ticket_id', $ticket->id)->first()->read_at);
    }

    public function test_customer_reads_staff_reply(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $staff = $this->makeStaff('SUPPORT');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'REPLIED']);
        SupportMessage::create(['support_ticket_id' => $ticket->id, 'user_id' => $staff->id, 'message' => 'answer']);

        $this->actingAs($user)->get(route('support.show', $ticket))->assertOk();

        $this->assertNotNull(SupportMessage::where('support_ticket_id', $ticket->id)->first()->read_at);
    }

    public function test_customer_can_download_attachment(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $staff = $this->makeStaff('SUPPORT');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'REPLIED']);
        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'message' => 'see file',
            'attachment_path' => 'support-files/test.txt',
        ]);

        \Illuminate\Support\Facades\Storage::disk('local')->put('support-files/test.txt', 'hello');

        $this->actingAs($user)->get(route('support.attachment-download', $message))->assertOk();
    }

    public function test_stranger_cannot_download_ticket_attachment(): void
    {
        [$user, $customer] = $this->makeCustomer();
        $stranger = User::factory()->create(['email_verified_at' => now()]);
        $staff = $this->makeStaff('SUPPORT');

        $ticket = SupportTicket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'status' => 'REPLIED']);
        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'message' => 'see file',
            'attachment_path' => 'support-files/test.txt',
        ]);

        $this->actingAs($stranger)->get(route('support.attachment-download', $message))->assertForbidden();
    }
}