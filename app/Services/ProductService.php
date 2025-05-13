<?php

namespace App\Services;

use App\Models\Customer;

class ProductService
{
    public static function paidableSales($customerId, $amount = 0)
    {
        $sales = Customer::find($customerId)->orders()->where('due', '>', 0)->get(['id', 'due']);

        $initialTotalDue = 0;
        $salesValues = [];

        foreach ($sales as $sale) {
            $initialTotalDue += $sale->due ?: 0;

            $totalDue = collect($salesValues)->sum('due');

            $singleDue = $amount - $totalDue;

            if ($singleDue <= 0) {
                break;
            }

            if ($singleDue > $sale->due) {
                $currentDue = $sale->due;
            } else {
                $currentDue = $singleDue;
            }

            $salesValues[] = [
                'id' => $sale->id,
                'due' => $currentDue,
            ];
        }

        return $salesValues;
    }
}
