<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});
