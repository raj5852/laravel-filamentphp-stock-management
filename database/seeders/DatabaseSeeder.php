<?php

namespace Database\Seeders;

use App\HistoryTypeEnum;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use App\UserTypeEnum;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // user
        DB::table('users')->insert([
            'name' => fake()->name(),
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => 1,
            'type' => UserTypeEnum::USER->value,
            'expires_at' => now()->addYears(5),
        ]);

        // superadmin
        $user = DB::table('users')->insert([
            'name' => fake()->name(),
            'email' => 'superadmin@superadmin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => 2,
            'type' => UserTypeEnum::SUPERADMIN->value,
            'expires_at' => now()->addYears(5),
        ]);

        // unit
        DB::table('units')->insert([
            'unit_name' => 'PC',
            'tenant_id' => 1,
            'is_default' => 1,
        ]);

        DB::table('customers')->insert([
            'customer_name' => 'Walk-in Customer',
            'email' => 'customer@customer.com',
            'phone' => '0000000000',
            'tenant_id' => 1,
            'is_default' => 1,
        ]);

        // category
        DB::table('categories')->insert([
            'name' => 'Product',
            'tenant_id' => 1,
        ]);

        // setting
        DB::table('settings')->insert([
            'tenant_id' => 1,
        ]);

        DB::table('accounts')->insert([
            'name' => 'Cash',
            'opening_balance' => 0,
            'current_balance' => 0,
            'tenant_id' => 1,
        ]);

        $account = DB::table('accounts')->latest('id')->first();

        DB::table('histories')->insert([
            'account_id' => $account->id,
            'date' => now(),
            'amount' => $account->opening_balance ?: 0,
            'type' => HistoryTypeEnum::OPENING_BALANCE->value,
            'note' => '',
            'tenant_id' => 1,
            'created_at' => now(),
        ]);

    }
}
