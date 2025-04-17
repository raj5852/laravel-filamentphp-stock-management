<?php

use App\Models\Purchase;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    // return Purchase::find(1)->purchaseitems;

    return singleUnitSalePrice(4);
});
