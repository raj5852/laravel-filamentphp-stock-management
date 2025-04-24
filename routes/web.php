<?php

use App\Models\OrderItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return OrderItem::query()
    ->with('order:id,order_date,invoiceno', 'product:id,product_name')
    ->whereJsonContains('purchase_ids', ['purchase_id' => 1])
    ->get();
});
