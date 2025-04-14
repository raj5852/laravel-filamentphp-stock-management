<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {



    $giveQtyFromUser = 9;

    $product = Product::find(1);
    $productdetails = $product->productdetails;
    $openingQty = getTotalStock($product->id, $product->first_opening_stock, $product->second_opening_stock);

    $totalSellQty = ($productdetails->purchased - $productdetails->available_stock);

    $openingQtyAvailable = ($openingQty - $totalSellQty);

    $lastOpeningQty = 0;
    if ($openingQtyAvailable >= 1) {
        $lastOpeningQty = $openingQtyAvailable;
    } else {
        $lastOpeningQty = 0;
    }

    if ($lastOpeningQty == 0) {
        $openingAndPurchaseQty = [0, $giveQtyFromUser];
    } elseif ($lastOpeningQty == $giveQtyFromUser) {
        $openingAndPurchaseQty = [$lastOpeningQty, 0];
    } elseif ($lastOpeningQty > $giveQtyFromUser) {
        $openingAndPurchaseQty = [$giveQtyFromUser, 0];
    } elseif ($lastOpeningQty < $giveQtyFromUser) {
        $openingAndPurchaseQty = [$lastOpeningQty, $giveQtyFromUser - $lastOpeningQty];
    }


    // //////////////////////////////////////////////////////////////////////////////
    //    $product =  Product::find(2)->productdetails->available_stock;



    if ($openingAndPurchaseQty[1] != 0) {

        $purchaseItems = $product->purchaseitems()->where('available_qty', '!=', 0)->select('id', 'total_qty', 'available_qty')->get();

        $initialTotalQty = 0;
        $indexByValue = [];

        foreach ($purchaseItems as $item) {

            $initialTotalQty += $item->available_qty;


            if($item->available_qty <= ($openingAndPurchaseQty[1] - array_sum($indexByValue))){
                $indexByValue[] = $item->available_qty;
            }else{
                $indexByValue[] = $openingAndPurchaseQty[1] - array_sum($indexByValue);
            }


            if ($initialTotalQty >= $openingAndPurchaseQty[1]) {
                break;
            }
        }

        dd($indexByValue);

    } else {
        dd('no');
    }



});
