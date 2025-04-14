<?php

use App\Models\Purchase;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return Purchase::query()
        ->with(['histories', 'supplier:id,supplier_name,phone', 'purchaseitems' => function ($query) {
            $query->select('id', 'product_id', 'purchase_id', 'total_in_text', 'rate', 'total_rate')
                ->with('product:id,product_name,product_code');
        }])
        ->find(10);

});
