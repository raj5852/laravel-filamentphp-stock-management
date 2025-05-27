<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});
Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('demo', function () {

    // $startDate = '2023-05';

    $startDate = '2023-05';
    $startOfMonth = \Carbon\Carbon::parse($startDate)->endOfMonth()->toDateString();

    $authId = auth()->id();

    return $orderitems = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('order_items.tenant_id', $authId)
        // ->whereBetween('orders.order_date', ['2023-01-01', '2023-12-31'])
        ->select(
            DB::raw('YEAR(orders.order_date) as year'),
            DB::raw('MONTH(orders.order_date) as month'),
            DB::raw('SUM(orders.receivable) as sales'),

            DB::raw('SUM(order_items.purchase_cost) as cost_of_goods_sold'),
            DB::raw('SUM(orders.profit) as 	gross_profit'),
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

    // ->select(
    //     DB::raw('YEAR(order_date) as year'),
    //     DB::raw('MONTH(order_date) as month'),
    //     DB::raw('SUM(receivable) as sales'),
    // )
    // ->groupBy('year', 'month')
    // ->orderBy('year', 'asc')
    // ->orderBy('month', 'asc')

    // ->select()
    // ->select(
    //     DB::raw('YEAR(orders.order_date) as year'),
    //     DB::raw('MONTH(orders.order_date) as month'),
    //     DB::raw('SUM(order_items.receivable) as sales'),
    // )
    // ->get();
});
