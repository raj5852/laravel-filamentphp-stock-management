<?php

use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});

Route::middleware(SuperAdminMiddleware::class)->prefix('superadmin')->name('superadmin.')->group(function () {
    // Route::get('/login-to-user/{id}', [LoginController::class, 'loginToUser'])->name('login-to-user');
});

Route::get('redirect-to-user/{email}', [UserController::class, 'redirectToUser'])->name('redirecttouser');
Route::get('demo', function () {

    $str = now()->startOfYear()->format('Y-m');
    // Optionally set the default end date (e.g., to the last day of the current month)
    $end = $this->end_date = now()->endOfMonth()->format('Y-m');

    $startDate = \Carbon\Carbon::parse($str)->endOfMonth()->toDateString();
    $endDate = \Carbon\Carbon::parse($end)->endOfMonth()->toDateString();

    $tenantId = auth()->user()->tenant_id;
    // Query for order items
    $orderItemsQuery = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('order_items.tenant_id', $tenantId)
        ->whereBetween('orders.order_date', [$startDate, $endDate])
        ->select(
            DB::raw('YEAR(orders.order_date) as year'),
            DB::raw('MONTH(orders.order_date) as month'),
            DB::raw('SUM(orders.receivable) as sales'),
            DB::raw('SUM(order_items.purchase_cost) as cost_of_goods_sold'),
            DB::raw('SUM(orders.profit) as gross_profit')
        )
        ->groupBy('year', 'month');

    // Query for expenses
    $expensesQuery = DB::table('expenses')
        ->where('tenant_id', $tenantId)
        ->whereBetween('date', [$startDate, $endDate])
        ->select(
            DB::raw('YEAR(date) as year'),
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(amount) as total_expenses')
        )
        ->groupBy('year', 'month');

    // Combine results using aliases correctly
    $result = DB::table(DB::raw("({$orderItemsQuery->toSql()}) as orderitems"))
        ->mergeBindings($orderItemsQuery)
        ->leftJoinSub($expensesQuery, 'expenses', function ($join) {
            $join->on('orderitems.year', '=', 'expenses.year')
                ->on('orderitems.month', '=', 'expenses.month');
        })
        ->select(
            'orderitems.year',
            'orderitems.month',
            'orderitems.sales',
            'orderitems.cost_of_goods_sold',
            'orderitems.gross_profit',
            'expenses.total_expenses'
        )
        ->orderBy('orderitems.year', 'asc')
        ->orderBy('orderitems.month', 'asc')
        ->get();

    // dd($result);
    return $result;
});
