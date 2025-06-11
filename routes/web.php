<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Services\SmsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return redirect('/user');
});

Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {});

Route::get('redirect-to-user/{email}', [UserController::class, 'redirectToUser'])->name('redirecttouser');
Route::get('demo', function () {


    $customer_name = "Mehedi Hasan";
    $amount = "5000";
    $order_date = "2023-01-01";
    $bill_no = "123456";
    $company_name = "Company Name";

    $message = "Hi {customer_name}
{amount}
{order_date}
{bill_no}
{company_name}";

    // Define placeholders and their replacements
    $replacements = [
        '{customer_name}' => $customer_name,
        '{amount}' => $amount,
        '{order_date}' => $order_date,
        '{bill_no}' => $bill_no,
        '{company_name}' => $company_name,
    ];

    // Replace all placeholders if they exist
    foreach ($replacements as $key => $value) {
        if (Str::contains($message, $key)) {
            $message = str_replace($key, $value, $message);
        }
    }

    echo nl2br($message);
});
