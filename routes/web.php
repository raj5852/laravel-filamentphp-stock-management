<?php

use App\Models\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return Payment::find(1)->histories;
    return Payment::query()->whereHas('histories')->withSum('histories', 'amount')->latest('id')->get();
});
