<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.manage',
            'customers.view', 'customers.manage',
            'orders.view', 'orders.create', 'orders.edit', 'orders.status', 'orders.message', 'orders.result',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'payments.view', 'payments.verify', 'payments.reject', 'payments.refund',
            'announcements.manage',
            'homepage.manage',
            'telegram.manage',
            'settings.manage',
            'reports.view',
            'audit_logs.view',
            'admins.manage',
            'support.view', 'support.manage',
        ];

        $roles = [
            'SUPER_ADMIN' => $permissions,
            'ADMIN' => array_values(array_diff($permissions, ['payments.refund'])),
            'SUPPORT' => [
                'customers.view',
                'orders.view', 'orders.create', 'orders.status', 'orders.message',
                'services.view',
                'payments.view',
                'support.view', 'support.manage',
            ],
            'FINANCE' => [
                'customers.view',
                'orders.view',
                'payments.view', 'payments.verify', 'payments.reject', 'payments.refund',
                'reports.view',
                'support.view',
            ],
        ];

        DB::table('permission_role')->delete();
        DB::table('role_user')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        $permissionIds = [];
        foreach ($permissions as $name) {
            $permissionIds[$name] = DB::table('permissions')->insertGetId([
                'name' => $name,
                'label' => ucwords(str_replace('.', ' ', $name)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($roles as $name => $rolePermissions) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $name,
                'label' => str_replace('_', ' ', $name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($rolePermissions as $permission) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionIds[$permission],
                    'role_id' => $roleId,
                ]);
            }
        }
    }
}
