<?php

namespace Database\Seeders;

use App\HistoryTypeEnum;
use App\Models\Category;
use App\Models\Product;
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
            'name' => 'Demo User',
            'email' => 'demo@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => 1,
            'type' => '1',
            'expires_at' => now()->addYears(5),
        ]);

        // superadmin
        $user = DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@superadmin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('MaMa12@@@'),
            'remember_token' => Str::random(10),
            'tenant_id' => 2,
            'type' => '2',
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

        // product
        DB::table('products')->insert([
            'product_name' => 'Product',
            'product_code' => '0000001',
            'category_id' => 1,
            'unit_id' => 1,
            'first_opening_stock' => 55,
            'sale_price' => 22,
            'purchase_cost' => 11,
            'total_purchase_cost' => 605,
            'tenant_id' => 1,
            'created_by' => 1,
            'created_at' => now(),
        ]);

        $model = DB::table('products')->first();

        $qty = getTotalStock($model->id, $model->first_opening_stock, $model->second_opening_stock);
        $qty_in_text = getTotalStockInText($model->id, $qty);

        $empty_qty = 0;
        $empty_qty_in_text = getTotalStockInText($model->id, $empty_qty);
        $single_unit_purchase_price = singleUnitPurchasePrice($model->id);

        DB::table('product_details')->insert([
            'product_id' => $model->id,
            'single_unit_sale_price' => singleUnitSalePrice($model->id),
            'single_unit_purchase_price' => $single_unit_purchase_price,

            'purchased' => $qty,
            'purchased_in_text' => $qty_in_text,

            'sold' => $empty_qty,
            'sold_in_text' => $empty_qty_in_text,

            'damaged' => $empty_qty,
            'damaged_in_text' => $empty_qty_in_text,

            'returned' => $empty_qty,
            'returned_in_text' => $empty_qty_in_text,

            'available_stock' => $qty,
            'available_stock_in_text' => $qty_in_text,
            'tenant_id' => 1,
            'created_by' => 1,
            'created_at' => now(),
        ]);

        DB::table('products')->where('id', 1)->update([
            'total_purchase_cost' => $single_unit_purchase_price * $qty,
            'total_opening_stock' => $qty,
        ]);

        // customer
        DB::table('customers')->insert([
            'customer_name' => 'Cus1',
            'email' => 'customer1@customer1.com',
            'phone' => '0000000001',
            'tenant_id' => 1,
            'wallet' => 0,
        ]);

        // supplier
        DB::table('suppliers')->insert([
            'supplier_name' => 'Sup1',
            'email' => 'supplier1@supplier1.com',
            'phone' => '0000000001',
            'tenant_id' => 1,
            'wallet' => 0,
        ]);
    }
}
