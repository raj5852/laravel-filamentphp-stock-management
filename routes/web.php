<?php

use App\Models\History;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return History::query()
        ->latest()
        ->withwhereHas('order', function ($query) {
            $query->where('customer_id', 2)->select('id', 'customer_id', 'order_date');
        })
        ->with('account:id,name')->get();

});
