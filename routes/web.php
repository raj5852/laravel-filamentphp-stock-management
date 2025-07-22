<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});

Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {});

Route::get('redirect-to-user/{email}', [UserController::class, 'redirectToUser'])->name('redirecttouser');

Route::get('/demo', function () {
   
});
