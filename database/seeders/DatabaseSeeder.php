<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
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
        ]);

        // unit
        DB::table('units')->insert([
            'unit_name' => 'PC',
            'tenant_id' => 1,
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

    }
}
