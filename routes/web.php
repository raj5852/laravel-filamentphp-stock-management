<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return Customer::find(1)->totaldue;
});
