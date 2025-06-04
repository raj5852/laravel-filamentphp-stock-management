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
            'units',
            'products',
            'categories',
            'brands',
            'payments',
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

        // create roles and assign existing permissions
        // $role1 = Role::create(['name' => 'writer']);
        // $role1->givePermissionTo('edit articles');
        // $role1->givePermissionTo('delete articles');

        // $role2 = Role::create(['name' => 'admin']);
        // $role2->givePermissionTo('publish articles');
        // $role2->givePermissionTo('unpublish articles');

        // $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Example User',
        //     'email' => 'tester@example.com',
        // ]);
        // $user->assignRole($role1);

        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Example Admin User',
        //     'email' => 'admin@example.com',
        // ]);
        // $user->assignRole($role2);

        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Example Super-Admin User',
        //     'email' => 'superadmin@example.com',
        // ]);
        // $user->assignRole($role3);
    }
}
