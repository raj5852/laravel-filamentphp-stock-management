<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('demo', function () {
    // $productid = 1;
    // return  $purchase = Purchase::where('is_purchase', 1)
    //     ->whereHas('purchaseitems', function ($query) use ($productid) {
    //         $query->where('product_id', $productid);
    //     })->exists();
});
