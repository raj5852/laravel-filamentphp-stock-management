<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\History;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('demo', function () {

    // return  $top_sale_product =  Product::query()
    //     ->withSum('orderitems as sold', 'total_qty')
    //     ->withCount('orderitems as no_of_sales')
    //     ->withSum('orderitems as sale_amount', 'total_rate')
    //     ->orderBy('sale_amount', 'desc')
    //     ->having('sale_amount', '>', 0)
    //     ->get();

    // return History::query()
    //     ->where('supplier_id', '!=', '')
    //     ->withWhereHas('account')
    //     ->get();

    // return History::query()
    //     ->where('customer_id', '!=', '')
    //     ->withWhereHas('account')
    //     ->get();
});
