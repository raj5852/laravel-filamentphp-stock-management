<?php

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return OrderItem::query()
    // ->with('order:id,order_date,invoiceno', 'product:id,product_name')
    // ->whereJsonContains('purchase_ids', ['purchase_id' => 1])
    // ->get();

    // $receivable = Order::query()
    //     ->where('order_date', today())
    //     ->sum('receivable');

    // $paid = Order::query()
    //     ->where('order_date', today())
    //     ->sum('paid');

    // $due = Order::query()
    //     ->where('order_date', today())
    //     ->sum('due');

    // $total_receivable = Order::query()
    //     ->sum('receivable');

    return OrderItem::query()->whereHas('order', function ($query) {
        $query->where('order_date', today());
    })
        ->sum('purchase_cost');

});
