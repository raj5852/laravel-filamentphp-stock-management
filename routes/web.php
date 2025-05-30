<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('demo', function () {

    echo Product::query()
        ->withSum(['orderitems as quantity' => function ($query) {
            $query->whereHas('order', function ($query) {
                $query->whereBetween('order_date', ['2025-05-31', '2025-05-31']);
            });
        }], 'total_qty')
        ->withCount(['orderitems as total_sale' => function ($query) {
            $query->whereHas('order', function ($query) {
                $query->whereBetween('order_date', ['2025-05-31', '2025-05-31']);
            });
        }])
        ->withSum(['orderitems as sale_amount' => function ($query) {
            $query->whereHas('order', function ($query) {
                $query->whereBetween('order_date', ['2025-05-31', '2025-05-31']);
            });
        }], 'total_rate')
        ->orderBy('quantity', 'desc')
        ->having('quantity', '>', 0)
        ->get();
});
