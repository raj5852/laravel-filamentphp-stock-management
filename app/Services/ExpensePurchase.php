<?php

namespace App\Services;

use App\Models\PurchaseItem;

class ExpensePurchase
{
    public static function addPurchaseExpense($productId, $qty)
    {

        $purchaseItems = PurchaseItem::where('product_id', $productId)
            ->where('available_qty', '>', 0)
            ->get();

        $openingAndPurchaseQty = $qty;
        $initialTotalQty = 0;
        $indexByValue = [];

        foreach ($purchaseItems as $item) {

            $initialTotalQty += $item->available_qty;

            if ($initialTotalQty > $qty) {
                $available_qty = $item->available_qty - ($initialTotalQty - $qty);
            } else {
                $available_qty = $item->available_qty;
            }

            $indexByValue[] = [
                'qty' => $available_qty,
                'purchase_item_id' => $item->id,
                'purchase_id' => $item->purchase_id,
                'qty_in_text' => getTotalStockInText($item->product_id, $available_qty),
                'purchase_value' => singleUnitPurchasePrice($item->product_id, $item->rate ?: 0) * $available_qty,
            ];

            if ($initialTotalQty >= $openingAndPurchaseQty) {
                break;
            }
        }

        foreach ($indexByValue as $key => $value) {
            $purchaseItem = PurchaseItem::query()->find($value['purchase_item_id']);
            $purchaseItem->decrement('available_qty', $value['qty']);
            $purchaseItem->update([
                'available_purchase_value' => singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
            ]);
        }

        return collect($indexByValue)->toArray();
    }

    public static function deletePurchaseExpense(array $purchase_ids)
    {

        foreach ($purchase_ids as $key => $value) {
            PurchaseItem::query()->find($value['purchase_item_id'])->increment('available_qty', $value['qty']);
        }

        return 1;
    }
}
