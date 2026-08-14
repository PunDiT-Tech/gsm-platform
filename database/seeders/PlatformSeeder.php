<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'SUPER_ADMIN')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@gsmplatform.test'],
            [
                'name' => 'Platform Admin',
                'phone' => '0000000000',
                'password' => Hash::make('AdminPass1'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if ($superAdminRole && ! $admin->roles()->exists()) {
            $admin->roles()->attach($superAdminRole);
        }

        $methods = [
            ['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer', 'description' => 'Pay by bank transfer and upload proof.', 'instructions' => 'Transfer the exact amount and upload your receipt.', 'sort_order' => 1],
            ['code' => 'BINANCE', 'name' => 'Binance Pay', 'description' => 'Pay via Binance Pay using the QR or ID.', 'instructions' => 'Complete payment in the Binance app and submit the transaction ID.', 'sort_order' => 2],
            ['code' => 'MANUAL_CRYPTO', 'name' => 'Manual Crypto', 'description' => 'Manual crypto transfer.', 'instructions' => 'Send to the provided wallet and upload proof.', 'sort_order' => 3],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }
    }
}
