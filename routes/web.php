<?php

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    // return Purchase::query()->with(['supplier:id,supplier_name,address', 'purchaseitems' => function ($query) {
    //     $query->with('product:id,product_name,product_code');
    // }])->find(1);

    return PurchaseItem::all();

    // return number_format(10000000, 2, '.', '');
});
