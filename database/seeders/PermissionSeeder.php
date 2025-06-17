<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'dashboard',
            'accounts',
            'owners',
            'pos',
            'sales',
            'purchases',
            'stock',
            'damages',
            'racks',
            'units',
            'products',
            'categories',
            'brands',
            'expenses',
            'expense categories',
            'payments',
            'promotional_sms',
            'customers',
            'suppliers',
            'profit loss report',
            'today report',
            'current month report',
            'summary report',
            'daily report',
            'customer due report',
            'supplier due report',
            'low stock report',
            'top customer',
            'top selling products',
            'top selling Products all time',
            'category wise report',
            'purchase report',
            'customer ledger',
            'supplier ledger',
            'roles',
            'users',
            'settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create the role
        $role = Role::firstOrCreate(['name' => 'main_user', 'tenant_id' => 0]);

        // Assign all permissions to the role
        $role->syncPermissions(Permission::all());
    }
}
