<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('demo', function () {
    return Customer::query()
        ->where('is_default', '!=', 1)
        ->withSum(['orders as total_sell'], 'receivable')
        ->orderBy('total_sell', 'desc')
        ->get();
});
