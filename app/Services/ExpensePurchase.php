<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseItem;

class ExpensePurchase
{
    public static function addPurchaseExpense($productId, $qty)
    {
        $giveQtyFromUser = $qty;

        $product = Product::find($productId);
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

        if ($openingAndPurchaseQty[1] != 0) {

            $purchaseItems = $product->purchaseitems()->where('available_qty', '!=', 0)->select('id', 'total_qty', 'available_qty', 'purchase_id', 'product_id')->get();

            $initialTotalQty = 0;
            $indexByValue = [];

            foreach ($purchaseItems as $item) {

                $initialTotalQty += $item->available_qty;

                $totalQty = collect($indexByValue)->sum('qty');

                if ($item->available_qty <= ($openingAndPurchaseQty[1] - $totalQty)) {
                    $indexByValue[] = [
                        'qty' => $item->available_qty,
                        'purchase_item_id' => $item->id,
                        'purchase_id' => $item->purchase_id,
                        'qty_in_text' => getTotalStockInText($item->product_id, $item->available_qty),
                    ];
                } else {
                    $indexByValue[] = [
                        'qty' => ($openingAndPurchaseQty[1] - $totalQty),
                        'purchase_item_id' => $item->id,
                        'purchase_id' => $item->purchase_id,
                        'qty_in_text' => getTotalStockInText($item->product_id, $openingAndPurchaseQty[1] - $totalQty),

                    ];

                }

                if ($initialTotalQty >= $openingAndPurchaseQty[1]) {
                    break;
                }
            }

            foreach ($indexByValue as $key => $value) {
                PurchaseItem::query()->find($value['purchase_item_id'])->decrement('available_qty', $value['qty']);
            }

            return collect($indexByValue)->toArray();

        } else {
            return [];
        }
    }

    public static function deletePurchaseExpense(array $purchase_ids)
    {

        foreach ($purchase_ids as $key => $value) {
            PurchaseItem::query()->find($value['purchase_item_id'])->increment('available_qty', $value['qty']);
        }

        return 1;
    }
}
