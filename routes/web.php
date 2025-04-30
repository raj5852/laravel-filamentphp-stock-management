<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return supplierDue(2);
    // return customerDue(2);

    // return 1;

    $supplierId = 2;
    $tenantId = auth()->user()->tenant_id;

    // return DB::table('purchases')
    //     ->where('tenant_id', $tenantId)
    //     ->where('supplier_id', $supplierId)
    //     ->select('id', 'purchase_date as date', 'payable as amount', 'total_amount', 'created_at', DB::raw('"purchase" as type'), 'billno as particulars')
    //     ->union(
    //         DB::table('histories')
    //             ->where('tenant_id', $tenantId)
    //             ->where('supplier_id', $supplierId)
    //             ->select('id', 'date', 'amount', 'total_amount', 'created_at', DB::raw('"history" as type'), DB::raw('"Paid to Supplier" as particulars'))
    //     )
    //     ->orderBy('created_at', 'asc')
    //     ->get();

    // customer

    return DB::table('orders')
        ->where('tenant_id', $tenantId)
        ->where('customer_id', $supplierId)

        ->select('id', 'order_date as date', 'receivable as amount', 'total_amount', 'created_at', DB::raw('"order" as type'), 'invoiceno as particulars')
        ->union(
            DB::table('histories')
                ->where('tenant_id', $tenantId)
                ->where('customer_id', $supplierId)
                ->select('id', 'date', 'amount', 'total_amount', 'created_at', DB::raw('"history" as type'), DB::raw('"Received from Customer" as particulars'))
        )
        ->orderBy('created_at', 'asc')
        ->get();

});
