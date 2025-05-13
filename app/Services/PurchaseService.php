<?php

namespace App\Services;

use App\Models\Supplier;

class PurchaseService
{
    public static function paidablePurchse($supplierId, $amount = 0)
    {
        $purchases = Supplier::find($supplierId)->purchases()->where('due', '>', 0)->get(['id', 'due']);

        $initialTotalDue = 0;
        $purchaseValues = [];

        foreach ($purchases as $purchase) {
            $initialTotalDue += $purchase->due;

            $totalDue = collect($purchaseValues)->sum('due');

            $singleDue = $amount - $totalDue;

            if ($singleDue <= 0) {
                break;
            }

            if ($singleDue > $purchase->due) {
                $currentDue = $purchase->due;
            } else {
                $currentDue = $singleDue;
            }

            $purchaseValues[] = [
                'id' => $purchase->id,
                'due' => $currentDue,
            ];
        }

        return $purchaseValues;
    }
}
